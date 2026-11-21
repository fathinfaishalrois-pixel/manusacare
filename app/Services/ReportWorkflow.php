<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportWorkflow
{
    public function verify(Report $report, User $admin, string $decision, ?string $reason = null): void
    {
        DB::transaction(function () use ($report, $admin, $decision, $reason) {
            $locked = Report::query()->lockForUpdate()->findOrFail($report->getKey());

            if ($locked->verified_at !== null || $locked->status !== 'dikirim') {
                throw ValidationException::withMessages([
                    'decision' => 'Laporan ini sudah diverifikasi atau tidak lagi menunggu verifikasi.',
                ]);
            }

            if ($decision === 'reject') {
                $locked->update(['status' => 'ditolak', 'rejection_reason' => $reason]);
                $locked->updates()->create([
                    'user_id' => $admin->id,
                    'status' => 'ditolak',
                    'note' => $reason,
                ]);

                return;
            }

            // Accepted reports remain awaiting assignment. They are not "diproses"
            // until an active officer has actually been assigned.
            $locked->update([
                'verified_at' => now(),
                'verified_by' => $admin->id,
            ]);
            $locked->updates()->create([
                'user_id' => $admin->id,
                'status' => 'dikirim',
                'note' => 'Laporan diverifikasi dan menunggu penugasan petugas.',
            ]);
        });
    }

    public function assign(Report $report, User $admin, User $officer, ?string $note = null): void
    {
        DB::transaction(function () use ($report, $admin, $officer, $note) {
            $locked = Report::query()->lockForUpdate()->findOrFail($report->getKey());

            if (in_array($locked->status, ['ditolak', 'selesai'], true)) {
                throw ValidationException::withMessages([
                    'officer_id' => 'Laporan yang ditolak atau selesai tidak dapat ditugaskan.',
                ]);
            }

            if ($locked->verified_at === null) {
                throw ValidationException::withMessages([
                    'officer_id' => 'Verifikasi laporan terlebih dahulu sebelum menugaskan petugas.',
                ]);
            }

            $isValidOfficer = $officer->is_active
                && $officer->hasRole('officer')
                && ! $officer->hasRole('admin');

            if (! $isValidOfficer) {
                throw ValidationException::withMessages([
                    'officer_id' => 'Akun yang dipilih bukan petugas aktif yang valid.',
                ]);
            }

            Assignment::query()
                ->where('report_id', $locked->id)
                ->where('is_active', true)
                ->update(['is_active' => false, 'ended_at' => now()]);

            Assignment::create([
                'report_id' => $locked->id,
                'officer_id' => $officer->id,
                'assigned_by' => $admin->id,
                'assigned_at' => now(),
                'is_active' => true,
            ]);

            $locked->update(['status' => 'diproses', 'completed_at' => null]);
            $locked->updates()->create([
                'user_id' => $admin->id,
                'status' => 'diproses',
                'note' => $note ?: "Petugas {$officer->name} ditugaskan. Penanganan dimulai.",
            ]);
        });
    }

    public function assertAssignedOfficer(Report $report, User $officer): void
    {
        $assignment = $report->activeAssignment()->first();
        abort_unless($assignment && (int) $assignment->officer_id === (int) $officer->id, 403);
    }
}
