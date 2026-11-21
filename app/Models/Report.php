<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Report extends Model
{
    use HasUuids;

    protected $fillable = [
        'reporter_id', 'category_id', 'title', 'urgency', 'building', 'floor',
        'room', 'location_detail', 'description', 'chronology', 'found_at',
        'additional_notes', 'status', 'rejection_reason', 'completed_at',
        'verified_at', 'verified_by',
    ];

    protected function casts(): array
    {
        return ['found_at' => 'datetime', 'completed_at' => 'datetime', 'verified_at' => 'datetime'];
    }

    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reporter_id'); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function media(): HasMany { return $this->hasMany(ReportMedia::class); }
    public function updates(): HasMany { return $this->hasMany(ReportUpdate::class)->latest(); }
    public function assignments(): HasMany { return $this->hasMany(Assignment::class); }
    public function activeAssignment(): HasOne { return $this->hasOne(Assignment::class)->where('is_active', true); }
    public function review(): HasOne { return $this->hasOne(Review::class); }

    public function isAssigned(): bool
    {
        return $this->activeAssignment()->exists();
    }
}
