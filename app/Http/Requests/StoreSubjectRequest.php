<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'unique:subjects,code'],
            'name' => ['required', 'string', 'max:100', 'unique:subjects,name'],
            'category' => ['required', 'in:general'],
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
