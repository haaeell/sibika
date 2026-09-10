<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCohortRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:cohorts,name'],
            'entry_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'graduation_year' => ['required', 'integer', 'gte:entry_year', 'max:2105'],
            'status' => ['required', 'in:active,graduated,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama angkatan wajib diisi.',
            'name.unique' => 'Nama angkatan sudah digunakan.',
            'entry_year.required' => 'Tahun masuk wajib diisi.',
            'graduation_year.required' => 'Tahun lulus wajib diisi.',
            'graduation_year.gte' => 'Tahun lulus tidak boleh lebih kecil dari tahun masuk.',
            'status.required' => 'Status wajib dipilih.',
        ];
    }
}
