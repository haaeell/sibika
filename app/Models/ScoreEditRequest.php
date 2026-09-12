<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreEditRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'semester_number', 'reason', 'status',
        'reviewed_by', 'reviewed_at', 'review_note', 'consumed_at',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime', 'consumed_at' => 'datetime'];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isUsableApproval(): bool
    {
        return $this->status === 'approved' && is_null($this->consumed_at);
    }
}
