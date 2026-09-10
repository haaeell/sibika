<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreAverageSubjectSetting extends Model
{
    protected $fillable = ['subject_id', 'major_id', 'include_in_average'];

    protected function casts(): array
    {
        return ['include_in_average' => 'boolean'];
    }

    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function major(): BelongsTo { return $this->belongsTo(Major::class); }
}
