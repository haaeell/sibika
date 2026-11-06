<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EligibleResult extends Model
{
    protected $fillable = ['student_id', 'rank', 'initial_rank', 'average', 'total_score', 'category', 'response'];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(EligibleAnnouncement::class, 'eligible_announcement_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
