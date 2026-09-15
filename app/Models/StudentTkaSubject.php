<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTkaSubject extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'tka_subject_id'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function tkaSubject(): BelongsTo
    {
        return $this->belongsTo(TkaSubject::class);
    }
}
