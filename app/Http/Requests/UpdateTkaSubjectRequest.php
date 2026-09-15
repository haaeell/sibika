<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTkaSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('tka_subjects', 'code')->ignore($this->tka_subject)],
            'name' => ['required', 'string', 'max:100', Rule::unique('tka_subjects', 'name')->ignore($this->tka_subject)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode mapel TKA wajib diisi.',
            'code.unique' => 'Kode mapel TKA sudah digunakan.',
            'name.required' => 'Nama mapel TKA wajib diisi.',
            'name.unique' => 'Nama mapel TKA sudah digunakan.',
        ];
    }
}
