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
            'nickname' => ['nullable', 'string', 'max:50'],
            'gender' => ['required', 'in:male,female'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'address' => ['required', 'string', 'max:1000'],
            'previous_school' => ['required', 'string', 'max:150'],
            'previous_school_address' => ['nullable', 'string', 'max:1000'],
            'graduation_year' => ['required', 'integer', 'min:2000', 'max:2105'],
            'academic_notes' => ['nullable', 'string', 'max:2000'],
            'father.name' => ['required', 'string', 'max:100'],
            'father.phone' => ['nullable', 'string', 'max:30'],
            'father.occupation' => ['nullable', 'string', 'max:100'],
            'father.education' => ['nullable', 'string', 'max:100'],
            'father.income_range' => ['nullable', 'string', 'max:50'],
            'mother.name' => ['required', 'string', 'max:100'],
            'mother.phone' => ['nullable', 'string', 'max:30'],
            'mother.occupation' => ['nullable', 'string', 'max:100'],
            'mother.education' => ['nullable', 'string', 'max:100'],
            'mother.income_range' => ['nullable', 'string', 'max:50'],
            'guardian.name' => ['nullable', 'string', 'max:100'],
            'guardian.phone' => ['nullable', 'string', 'max:30'],
            'guardian.relation' => ['nullable', 'string', 'max:50'],
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
