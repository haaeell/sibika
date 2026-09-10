<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('academic_years', 'name')
                    ->where(fn ($query) => $query->where('semester', $this->semester))
                    ->ignore($this->academic_year),
            ],
            'start_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'end_year' => ['required', 'integer', 'gte:start_year', 'max:2101'],
            'semester' => ['required', 'in:ganjil,genap'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tahun ajaran wajib diisi.',
            'name.unique' => 'Nama tahun ajaran sudah digunakan.',
            'start_year.required' => 'Tahun mulai wajib diisi.',
            'start_year.integer' => 'Tahun mulai harus berupa angka.',
            'end_year.required' => 'Tahun selesai wajib diisi.',
            'end_year.gte' => 'Tahun selesai tidak boleh lebih kecil dari tahun mulai.',
            'semester.required' => 'Semester wajib dipilih.',
            'semester.in' => 'Semester tidak valid.',
        ];
    }
}
