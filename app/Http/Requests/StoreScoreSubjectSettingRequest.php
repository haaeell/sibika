<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScoreSubjectSettingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'semester_number' => ['required', 'integer', 'between:1,5'],
            'major_id' => ['nullable', 'prohibited_if:semester_number,1', 'prohibited_if:semester_number,2', 'exists:majors,id'],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['required', 'exists:subjects,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'semester_number.required' => 'Semester wajib dipilih.',
            'semester_number.between' => 'Semester harus antara 1 sampai 5.',
            'subject_ids.required' => 'Minimal satu mata pelajaran wajib dipilih.',
            'subject_ids.min' => 'Minimal satu mata pelajaran wajib dipilih.',
            'major_id.prohibited_if' => 'Semester 1 dan 2 tidak boleh memilih jurusan.',
        ];
    }
}
