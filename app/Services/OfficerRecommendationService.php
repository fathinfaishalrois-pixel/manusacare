<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OfficerRecommendationService
{
    /**
     * Return active officers ordered by lowest open workload.
     * Workload is advisory only; it never blocks an assignment.
     */
    public function workloads(): Collection
    {
        $role = Role::firstOrCreate(
            ['slug' => 'officer'],
            ['name' => 'Petugas', 'description' => 'Petugas penanganan laporan MANUSACARE.']
        );

        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) => $query->where('roles.id', $role->id))
            ->whereDoesntHave('roles', fn (Builder $query) => $query->where('slug', 'admin'))
            ->withCount([
                'assignments as active_workload' => fn (Builder $query) => $query
                    ->where('assignments.is_active', true)
                    ->whereHas('report', fn (Builder $reports) => $reports
                        ->whereNotIn('status', ['selesai', 'ditolak'])),
            ])
            ->orderBy('name')
            ->get()
            ->sort(function (User $left, User $right) {
                $byWorkload = ((int) $left->active_workload) <=> ((int) $right->active_workload);

                return $byWorkload !== 0 ? $byWorkload : strcasecmp($left->name, $right->name);
            })
            ->values();
    }

    public function recommended(): ?User
    {
        return $this->workloads()->first();
    }

    public function warningFor(User $officer): ?string
    {
        $workload = (int) ($officer->active_workload
            ?? $officer->assignments()
                ->where('is_active', true)
                ->whereHas('report', fn (Builder $query) => $query->whereNotIn('status', ['selesai', 'ditolak']))
                ->count());

        if ($workload >= 5) {
            return "Perhatian: {$officer->name} sedang menangani {$workload} laporan aktif. Penugasan tetap dapat dilanjutkan.";
        }

        return null;
    }
}
