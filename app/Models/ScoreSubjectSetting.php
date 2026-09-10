<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreSubjectSetting extends Model
{
    use HasFactory;

    protected $fillable = ['subject_id', 'major_id', 'semester_number', 'is_required', 'is_active'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'is_active' => 'boolean'];
    }

    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function major(): BelongsTo { return $this->belongsTo(Major::class); }
}
