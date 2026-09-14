<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class University extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'short_name', 'type', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function firstChoiceProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'university_choice_1_id');
    }

    public function secondChoiceProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'university_choice_2_id');
    }

    public function thirdChoiceProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'university_choice_3_id');
    }
}
