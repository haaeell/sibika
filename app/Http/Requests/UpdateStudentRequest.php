<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'max:30', Rule::unique('students', 'nis')->ignore($this->student)],
            'nisn' => ['nullable', 'string', 'max:30', Rule::unique('students', 'nisn')->ignore($this->student)],
            'name' => ['required', 'string', 'max:100'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'cohort_id' => ['nullable', 'exists:cohorts,id'],
            'status' => ['required', 'in:active,graduated,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan.',
            'nisn.unique' => 'NISN sudah digunakan.',
            'name.required' => 'Nama siswa wajib diisi.',
            'class_id.exists' => 'Kelas tidak valid.',
            'cohort_id.exists' => 'Angkatan tidak valid.',
            'status.required' => 'Status wajib dipilih.',
        ];
    }
}
