<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentViolation extends Model
{
    protected $fillable = ['student_id', 'occurred_at', 'pasal_perdupsis', 'sanction', 'warning_letter_path', 'warning_letter_name', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['occurred_at' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
