<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectStudentSemesterScoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['note' => ['required', 'string', 'max:500']];
    }

    public function messages(): array
    {
        return ['note.required' => 'Catatan penolakan wajib diisi.'];
    }
}
