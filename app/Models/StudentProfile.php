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
        'university_choice_1_id', 'university_choice_2_id', 'university_choice_3_id',
        'grade_11_preparation', 'career_concern',
        'school_achievements', 'organization_status', 'organization_name',
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

    public function universityChoice1(): BelongsTo
    {
        return $this->belongsTo(University::class, 'university_choice_1_id');
    }

    public function universityChoice2(): BelongsTo
    {
        return $this->belongsTo(University::class, 'university_choice_2_id');
    }

    public function universityChoice3(): BelongsTo
    {
        return $this->belongsTo(University::class, 'university_choice_3_id');
    }
}
