<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentScore extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'subject_id', 'semester_number', 'score', 'status', 'submitted_at', 'verified_by', 'verified_at', 'verification_note'];

    protected function casts(): array
    {
        return ['score' => 'decimal:2', 'submitted_at' => 'datetime', 'verified_at' => 'datetime'];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
}
