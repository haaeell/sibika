<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'nickname', 'gender', 'birth_place', 'birth_date', 'phone', 'email', 'photo_path',
        'province', 'city', 'district', 'village', 'postal_code', 'address',
        'previous_school', 'previous_school_address', 'graduation_year', 'academic_notes',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
