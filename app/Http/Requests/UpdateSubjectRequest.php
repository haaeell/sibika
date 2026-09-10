<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('subjects', 'code')->ignore($this->subject)],
            'name' => ['required', 'string', 'max:100', Rule::unique('subjects', 'name')->ignore($this->subject)],
            'category' => ['required', 'in:general,tka_mandatory,tka_optional'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode mata pelajaran wajib diisi.',
            'code.unique' => 'Kode mata pelajaran sudah digunakan.',
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'name.unique' => 'Nama mata pelajaran sudah digunakan.',
            'category.required' => 'Kategori wajib dipilih.',
        ];
    }
}
