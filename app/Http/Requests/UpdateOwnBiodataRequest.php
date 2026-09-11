<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOwnBiodataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gender' => ['required', 'in:male,female'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'phone' => ['required', 'string', 'max:30'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'address' => ['required', 'string', 'max:1000'],
            'height_cm' => ['nullable', 'integer', 'min:100', 'max:250'],
            'weight_kg' => ['nullable', 'integer', 'min:20', 'max:200'],
            'medical_history' => ['nullable', 'string', 'max:1000'],
            'university_choice_1' => ['nullable', 'string', 'max:150'],
            'university_choice_2' => ['nullable', 'string', 'max:150'],
            'grade_11_preparation' => ['nullable', 'string', 'max:2000'],
            'career_concern' => ['nullable', 'string', 'max:2000'],
            'school_achievements' => ['nullable', 'string', 'max:2000'],
            'organization_participation' => ['nullable', 'string', 'max:2000'],
            'self_improvement_notes' => ['nullable', 'string', 'max:2000'],
            'mcu_status' => ['nullable', 'in:belum,sudah,proses'],
        ];
    }

    public function messages(): array
    {
        return [
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'email.email' => 'Format email tidak valid.',
            'graduation_year.integer' => 'Tahun lulus harus berupa angka.',
        ];
    }
}
