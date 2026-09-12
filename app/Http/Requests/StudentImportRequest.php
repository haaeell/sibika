<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File Excel wajib dipilih.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ];
    }
}
