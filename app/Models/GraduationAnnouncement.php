<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GraduationAnnouncement extends Model
{
    protected $fillable = [
        'cohort_id', 'published_at', 'is_active', 'meeting_date', 'issued_place',
        'principal_name', 'principal_nip', 'signature_path', 'stamp_path',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'meeting_date' => 'date', 'is_active' => 'boolean'];
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }
}
