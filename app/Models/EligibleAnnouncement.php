<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EligibleAnnouncement extends Model
{
    protected $fillable = ['cohort_id', 'eligible_quota', 'reserve_quota', 'published_at', 'eligible_wa_url', 'reserve_wa_url'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(EligibleResult::class);
    }
}
