<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMajorRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('majors', 'code')->ignore($this->major)],
            'name' => ['required', 'string', 'max:100', Rule::unique('majors', 'name')->ignore($this->major)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode jurusan wajib diisi.',
            'code.unique' => 'Kode jurusan sudah digunakan.',
            'name.required' => 'Nama jurusan wajib diisi.',
            'name.unique' => 'Nama jurusan sudah digunakan.',
        ];
    }
}
