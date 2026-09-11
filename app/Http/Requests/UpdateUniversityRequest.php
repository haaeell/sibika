<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUniversityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('universities', 'name')->ignore($this->university)],
            'short_name' => ['nullable', 'string', 'max:30', Rule::unique('universities', 'short_name')->ignore($this->university)],
            'type' => ['required', 'in:negeri,swasta,kedinasan,lainnya'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kampus wajib diisi.',
            'name.unique' => 'Nama kampus sudah digunakan.',
            'short_name.unique' => 'Singkatan kampus sudah digunakan.',
            'type.required' => 'Jenis kampus wajib dipilih.',
        ];
    }
}
