<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateOwnBiodataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gender' => ['required', 'in:male,female'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'phone' => ['required', 'string', 'max:30'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'address' => ['required', 'string', 'max:1000'],
            'height_cm' => ['required', 'integer', 'min:100', 'max:250'],
            'weight_kg' => ['required', 'integer', 'min:20', 'max:200'],
            'medical_history' => ['required', 'string', 'max:1000'],
            'mcu_status' => ['required', 'in:belum,sudah,proses'],
            'mcu_count' => ['nullable', 'integer', 'min:1', 'max:99', 'required_if:mcu_status,sudah'],
            'mcu_last_date' => ['nullable', 'date', 'before_or_equal:today', 'required_if:mcu_status,sudah'],
            'university_choice_1_id' => ['required', 'integer', 'exists:universities,id', 'different:university_choice_2_id', 'different:university_choice_3_id'],
            'university_major_choice_1' => ['nullable', 'string', 'max:150'],
            'university_choice_2_id' => ['required', 'integer', 'exists:universities,id', 'different:university_choice_1_id', 'different:university_choice_3_id'],
            'university_major_choice_2' => ['nullable', 'string', 'max:150'],
            'university_choice_3_id' => ['required', 'integer', 'exists:universities,id', 'different:university_choice_1_id', 'different:university_choice_2_id'],
            'university_major_choice_3' => ['nullable', 'string', 'max:150'],
            'parent_father_name' => ['required', 'string', 'max:100'],
            'parent_father_occupation' => ['required', 'string', 'max:100'],
            'parent_mother_name' => ['required', 'string', 'max:100'],
            'parent_mother_occupation' => ['required', 'string', 'max:100'],
            'parent_phone' => ['required', 'string', 'max:30'],
            'parent_address' => ['required', 'string', 'max:1000'],
            'school_achievements' => ['required', 'string', 'max:2000'],
            'organization_status' => ['required', 'in:ya,tidak'],
            'organization_name' => ['nullable', 'string', 'max:200', 'required_if:organization_status,ya'],
            'self_improvement_notes' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'ijazah_smp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'akte' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'kartu_keluarga' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'document_type' => ['nullable', 'string', 'max:100', 'required_with:certificates'],
            'certificates' => ['nullable', 'array'],
            'certificates.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'medical_history.required' => 'Riwayat kesehatan wajib diisi. Tuliskan (-) jika tidak ada.',
            'school_achievements.required' => 'Prestasi wajib diisi. Tuliskan (-) jika tidak ada.',
            'organization_status.required' => 'Status organisasi wajib dipilih.',
            'organization_name.required_if' => 'Nama organisasi wajib diisi jika memilih ya.',
            'mcu_count.required_if' => 'Jumlah medical check-up wajib diisi jika status sudah.',
            'mcu_last_date.required_if' => 'Tanggal medical check-up terakhir wajib diisi jika status sudah.',
            'university_choice_1_id.different' => 'Pilihan kampus tidak boleh sama.',
            'university_choice_2_id.different' => 'Pilihan kampus tidak boleh sama.',
            'university_choice_3_id.required' => 'Pilihan kampus 3 wajib dipilih.',
            'university_choice_3_id.different' => 'Pilihan kampus tidak boleh sama.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->user()?->hasRole('siswa')) {
                return;
            }

            $student = Student::where('user_id', $this->user()->id)->with('documents')->first();
            foreach (['ijazah_smp' => 'Ijazah SMP', 'akte' => 'Akte', 'kartu_keluarga' => 'Kartu Keluarga'] as $field => $label) {
                if (! $this->hasFile($field) && ! $student?->documents->contains('document_type', $label)) {
                    $validator->errors()->add($field, $label.' wajib diupload.');
                }
            }
        });
    }
}
