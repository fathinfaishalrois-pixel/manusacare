<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Services\OfficerRecommendationService;
use App\Services\ReportWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::query()->with(['reporter', 'category', 'activeAssignment.officer']);

        if ($request->filled('status') && in_array($request->status, ['dikirim', 'diproses', 'selesai', 'ditolak'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = trim((string) $request->search);
            $query->where(fn ($q) => $q->where('title', 'like', "%{$term}%")
                ->orWhere('id', 'like', "%{$term}%")
                ->orWhereHas('reporter', fn ($u) => $u->where('name', 'like', "%{$term}%")));
        }

        $reports = $query->latest()->paginate(15)->withQueryString();

        return view('admin.reports.index', compact('reports'));
    }

    public function show(Report $report, OfficerRecommendationService $recommendations)
    {
        $report->load([
            'reporter', 'category', 'media', 'updates.user',
            'assignments.officer', 'review', 'verifier', 'activeAssignment.officer',
        ]);

        // Keep the original $officers variable so the existing Blade dropdown
        // continues to work without any frontend changes. Sorted by workload.
        $officers = $recommendations->workloads();
        $recommendedOfficer = $officers->first();
        $officerWorkloadWarning = null;

        if ($report->activeAssignment?->officer) {
            $officerWorkloadWarning = $recommendations->warningFor($report->activeAssignment->officer);
        }

        return view('admin.reports.show', compact(
            'report',
            'officers',
            'recommendedOfficer',
            'officerWorkloadWarning'
        ));
    }

    public function verify(Request $request, Report $report, ReportWorkflow $workflow)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:accept,reject'],
            'reason' => ['required_if:decision,reject', 'nullable', 'string', 'max:2000'],
        ]);

        $workflow->verify($report, $request->user(), $data['decision'], $data['reason'] ?? null);

        return back()->with('success', $data['decision'] === 'accept'
            ? 'Laporan diverifikasi dan menunggu penugasan petugas.'
            : 'Laporan berhasil ditolak.');
    }

    public function assign(
        Request $request,
        Report $report,
        ReportWorkflow $workflow,
        OfficerRecommendationService $recommendations
    ) {
        $data = $request->validate([
            'officer_id' => ['required', 'integer', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $officer = User::query()->findOrFail($data['officer_id']);

        // Validate the chosen officer independently of the submitted form.
        abort_unless($officer->is_active && $officer->hasRole('officer') && ! $officer->hasRole('admin'), 422, 'Akun bukan petugas aktif yang valid.');

        $warning = $recommendations->warningFor($officer);
        $workflow->assign($report, $request->user(), $officer, $data['note'] ?? null);

        $response = back()->with('success', 'Petugas berhasil ditugaskan. Status laporan berubah menjadi Diproses.');

        // Workload warning is informational only; assignment is never blocked.
        if ($warning) {
            $response->with('warning', $warning);
        }

        return $response;
    }

    /**
     * JSON endpoint for the current recommendation and active officer workload.
     * Does not change any frontend by itself.
     */
    public function recommendations(OfficerRecommendationService $recommendations)
    {
        $officers = $recommendations->workloads()->map(fn (User $officer) => [
            'id' => $officer->id,
            'name' => $officer->name,
            'identity_number' => $officer->identity_number,
            'class_or_position' => $officer->class_or_position,
            'active_workload' => (int) $officer->active_workload,
            'recommended' => false,
        ]);

        if ($officers->isNotEmpty()) {
            $officers[0]['recommended'] = true;
        }

        return response()->json([
            'recommended_officer_id' => $officers->first()['id'] ?? null,
            'officers' => $officers->values(),
        ]);
    }


    public function updateRepairProgress(Request $request, Report $report)
    {
        $data = $request->validate([
            'repair_stage' => ['required', 'in:menunggu_perbaikan,pemeriksaan,menunggu_material,dalam_perbaikan,pengujian,selesai,tertunda'],
            'note' => ['required', 'string', 'min:3', 'max:2000'],
        ], [
            'repair_stage.required' => 'Tahap perbaikan wajib dipilih.',
            'repair_stage.in' => 'Tahap perbaikan tidak valid.',
            'note.required' => 'Keterangan perkembangan wajib diisi.',
            'note.min' => 'Keterangan minimal 3 karakter.',
        ]);

        DB::transaction(function () use ($request, $report, $data) {
            $locked = Report::query()->lockForUpdate()->findOrFail($report->id);

            abort_unless(
                $locked->verified_at !== null && ! in_array($locked->status, ['ditolak', 'selesai'], true),
                422,
                'Laporan harus sudah diverifikasi dan belum berstatus ditolak atau selesai.'
            );

            $isCompleted = $data['repair_stage'] === 'selesai';

            // Tahap perbaikan "selesai" juga harus menyelesaikan status utama laporan.
            if ($isCompleted) {
                $locked->update([
                    'status' => 'selesai',
                    'completed_at' => now(),
                ]);
            }

            $locked->updates()->create([
                'user_id' => $request->user()->id,
                'status' => $isCompleted ? 'selesai' : null,
                'repair_stage' => $data['repair_stage'],
                'note' => trim($data['note']),
            ]);
        });

        return back()->with('success', $data['repair_stage'] === 'selesai'
            ? 'Laporan berhasil diselesaikan. Status utama laporan sekarang Selesai.'
            : 'Proses perbaikan berhasil diperbarui. Pelapor dapat melihat perkembangannya.');
    }

    public function destroy(Request $request, Report $report)
    {
        $request->validate(['confirmation' => ['required', 'in:HAPUS']]);
        $paths = $report->media()->get(['disk', 'path']);

        DB::transaction(fn () => $report->delete());

        foreach ($paths as $media) {
            Storage::disk($media->disk)->delete($media->path);
        }

        return redirect()->route('admin.reports.index')->with('success', 'Laporan dihapus.');
    }
}
