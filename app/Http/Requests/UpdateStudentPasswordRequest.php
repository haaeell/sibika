<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateStudentPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => [
                'required',
                'string',
                'max:255',
                Password::min(8)->letters()->numbers(),
                'confirmed',
                'different:current_password',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.string' => 'Password saat ini tidak valid.',
            'current_password.current_password' => 'Password saat ini salah. Gunakan NIS sebagai password saat ini.',

            'password.required' => 'Password baru wajib diisi.',
            'password.string' => 'Password baru tidak valid.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.max' => 'Password baru maksimal 255 karakter.',
            'password.letters' => 'Password baru harus mengandung minimal 1 huruf (A–Z).',
            'password.numbers' => 'Password baru harus mengandung minimal 1 angka (0–9).',
            'password.confirmed' => 'Konfirmasi password tidak sama dengan password baru. Silakan ulangi dengan teliti.',
            'password.different' => 'Password baru harus berbeda dari password saat ini (NIS).',
        ];
    }
}
