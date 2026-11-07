<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceAbsence extends Model
{
    protected $fillable = ['student_id', 'meetings', 'absences', 'created_by'];

    public function week(): BelongsTo
    {
        return $this->belongsTo(AttendanceWeek::class, 'attendance_week_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getPercentageAttribute(): float
    {
        return round($this->absences / $this->meetings * 100, 2);
    }
}
