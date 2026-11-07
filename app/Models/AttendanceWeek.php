<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceWeek extends Model
{
    protected $fillable = ['week_number', 'starts_on', 'ends_on'];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date'];
    }

    public function absences(): HasMany
    {
        return $this->hasMany(AttendanceAbsence::class);
    }
}
