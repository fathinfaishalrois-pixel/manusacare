<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\ReportWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $ids = $request->user()->assignments()->where('is_active', true)->pluck('report_id');
        $reports = Report::with(['category', 'reporter', 'activeAssignment.officer'])
            ->whereIn('id', $ids)->latest()->paginate(12);
        $stats = [
            'total' => $ids->count(),
            'progress' => Report::whereIn('id', $ids)->where('status', 'diproses')->count(),
            'done' => Report::whereIn('id', $ids)->where('status', 'selesai')->count(),
        ];
        return view('officer.dashboard', compact('reports', 'stats'));
    }

    public function show(Request $request, Report $report)
    {
        app(ReportWorkflow::class)->assertAssignedOfficer($report, $request->user());
        $report->load(['reporter', 'category', 'media', 'updates.user', 'activeAssignment.officer']);
        return view('officer.show', compact('report'));
    }

    public function update(Request $request, Report $report, ReportWorkflow $workflow)
    {
        $workflow->assertAssignedOfficer($report, $request->user());
        abort_unless($report->status === 'diproses', 422, 'Laporan harus berstatus Diproses sebelum progres diperbarui.');

        $data = $request->validate([
            'note' => ['required', 'string', 'max:3000'],
            'status' => ['required', 'in:diproses,selesai'],
            'after_photos' => ['nullable', 'array', 'max:5'],
            'after_photos.*' => ['file', 'mimes:jpg,jpeg,png', 'mimetypes:image/jpeg,image/png', 'max:10240'],
            'after_videos' => ['nullable', 'array', 'max:2'],
            'after_videos.*' => ['file', 'mimes:mp4,mov', 'mimetypes:video/mp4,video/quicktime', 'max:102400'],
        ]);

        $storedPaths = [];
        try {
            DB::transaction(function () use ($request, $report, $data, &$storedPaths) {
                $locked = Report::query()->lockForUpdate()->findOrFail($report->id);
                $assignment = $locked->activeAssignment()->first();
                abort_unless($assignment && (int) $assignment->officer_id === (int) $request->user()->id, 403);
                if ($locked->status !== 'diproses') {
                    throw ValidationException::withMessages(['status' => 'Status laporan sudah berubah. Muat ulang halaman.']);
                }

                foreach (['after_photos' => 'sesudah', 'after_videos' => 'sesudah-video'] as $field => $kind) {
                    foreach ($request->file($field, []) as $file) {
                        $path = $file->store('reports/' . $locked->id, 'local');
                        $storedPaths[] = $path;
                        $locked->media()->create([
                            'uploaded_by' => $request->user()->id,
                            'disk' => 'local', 'path' => $path,
                            'original_name' => Str::limit(basename($file->getClientOriginalName()), 180, ''),
                            'mime_type' => $file->getMimeType(), 'size' => $file->getSize(), 'kind' => $kind,
                        ]);
                    }
                }

                if ($data['status'] === 'selesai'
                    && ! $locked->media()->whereIn('kind', ['sesudah', 'sesudah-video'])->exists()) {
                    throw ValidationException::withMessages(['status' => 'Tambahkan minimal satu foto atau video bukti penanganan sebelum menyelesaikan laporan.']);
                }

                $locked->update([
                    'status' => $data['status'],
                    'completed_at' => $data['status'] === 'selesai' ? now() : null,
                ]);
                $locked->updates()->create([
                    'user_id' => $request->user()->id,
                    'status' => $data['status'],
                    'note' => $data['note'],
                ]);
            });
        } catch (Throwable $exception) {
            if ($storedPaths) Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        return back()->with('success', $data['status'] === 'selesai'
            ? 'Laporan ditandai selesai dan bukti penanganan tersimpan.'
            : 'Progres penanganan berhasil diperbarui.');
    }
}
