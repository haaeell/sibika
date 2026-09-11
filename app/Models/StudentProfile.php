<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'gender', 'birth_place', 'birth_date', 'phone', 'photo_path',
        'province', 'city', 'district', 'village', 'postal_code', 'address',
        'height_cm', 'weight_kg', 'medical_history',
        'university_choice_1', 'university_choice_2',
        'grade_11_preparation', 'career_concern',
        'school_achievements', 'organization_participation',
        'self_improvement_notes', 'mcu_status',
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
