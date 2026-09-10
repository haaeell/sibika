<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScoreSubjectSettingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'semester_number' => ['required', 'integer', 'between:1,5'],
            'major_id' => ['nullable', 'prohibited_if:semester_number,1', 'prohibited_if:semester_number,2', 'exists:majors,id'],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::unique('score_subject_settings', 'subject_id')
                    ->where(fn ($query) => $query->where('semester_number', $this->semester_number)->where('major_id', $this->major_id))
                    ->ignore($this->route('score_subject_setting')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'semester_number.required' => 'Semester wajib dipilih.',
            'semester_number.between' => 'Semester harus antara 1 sampai 5.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.unique' => 'Setting mata pelajaran untuk semester dan jurusan ini sudah ada.',
            'major_id.prohibited_if' => 'Semester 1 dan 2 tidak boleh memilih jurusan.',
        ];
    }
}
