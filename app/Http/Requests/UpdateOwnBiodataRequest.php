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
            'height_cm' => ['required', 'integer', 'min:100', 'max:250'],
            'weight_kg' => ['required', 'integer', 'min:20', 'max:200'],
            'medical_history' => ['required', 'string', 'max:1000'],
            'university_choice_1_id' => ['required', 'integer', 'exists:universities,id', 'different:university_choice_2_id'],
            'university_choice_2_id' => ['required', 'integer', 'exists:universities,id', 'different:university_choice_1_id'],
            'grade_11_preparation' => ['required', 'string', 'max:2000'],
            'career_concern' => ['required', 'string', 'max:2000'],
            'school_achievements' => ['required', 'string', 'max:2000'],
            'organization_participation' => ['required', 'string', 'max:2000'],
            'self_improvement_notes' => ['required', 'string', 'max:2000'],
            'mcu_status' => ['required', 'in:belum,sudah,proses'],
        ];
    }

    public function messages(): array
    {
        return [
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'medical_history.required' => 'Riwayat kesehatan wajib diisi. Tuliskan (-) jika tidak ada.',
            'school_achievements.required' => 'Prestasi wajib diisi. Tuliskan (-) jika tidak ada.',
            'organization_participation.required' => 'Organisasi wajib diisi. Tuliskan (-) jika tidak ada.',
            'university_choice_1_id.different' => 'Pilihan kampus pertama dan kedua harus berbeda.',
            'university_choice_2_id.different' => 'Pilihan kampus pertama dan kedua harus berbeda.',
        ];
    }
}
