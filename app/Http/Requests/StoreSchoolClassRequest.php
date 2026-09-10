<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolClassRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('classes', 'name')->where(fn ($query) => $query->where('academic_year_id', $this->academic_year_id))],
            'grade_level' => ['required', 'in:X,XI,XII'],
            'major_id' => ['nullable', 'required_if:grade_level,XI,XII', 'exists:majors,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kelas wajib diisi.',
            'name.unique' => 'Nama kelas sudah digunakan pada tahun ajaran ini.',
            'grade_level.required' => 'Tingkat kelas wajib dipilih.',
            'major_id.required_if' => 'Jurusan wajib dipilih untuk kelas XI dan XII.',
            'major_id.exists' => 'Jurusan tidak valid.',
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak valid.',
            'homeroom_teacher_id.exists' => 'Guru wali kelas tidak valid.',
        ];
    }
}
