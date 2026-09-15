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
        'university_choice_1_id', 'university_major_choice_1', 'university_choice_2_id', 'university_major_choice_2', 'university_choice_3_id', 'university_major_choice_3',
        'parent_father_name', 'parent_father_occupation', 'parent_mother_name', 'parent_mother_occupation', 'parent_phone', 'parent_address',
        'school_achievements', 'organization_status', 'organization_name',
        'self_improvement_notes', 'mcu_status', 'mcu_count', 'mcu_last_date',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date', 'mcu_last_date' => 'date'];
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
