<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'gender' => ['nullable', 'in:male,female'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'phone' => ['nullable', 'string', 'max:30'],
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:1000'],
            'height_cm' => ['nullable', 'integer', 'min:100', 'max:250'],
            'weight_kg' => ['nullable', 'integer', 'min:20', 'max:200'],
            'medical_history' => ['nullable', 'string', 'max:1000'],
            'mcu_status' => ['nullable', 'in:belum,sudah,proses'],
            'mcu_count' => ['nullable', 'integer', 'min:1', 'max:99'],
            'mcu_last_date' => ['nullable', 'date', 'before_or_equal:today'],
            'university_choice_1_id' => ['nullable', 'integer', 'exists:universities,id'],
            'university_major_choice_1' => ['nullable', 'string', 'max:150'],
            'university_choice_2_id' => ['nullable', 'integer', 'exists:universities,id'],
            'university_major_choice_2' => ['nullable', 'string', 'max:150'],
            'university_choice_3_id' => ['nullable', 'integer', 'exists:universities,id'],
            'university_major_choice_3' => ['nullable', 'string', 'max:150'],
            'parent_father_name' => ['nullable', 'string', 'max:100'],
            'parent_father_occupation' => ['nullable', 'string', 'max:100'],
            'parent_mother_name' => ['nullable', 'string', 'max:100'],
            'parent_mother_occupation' => ['nullable', 'string', 'max:100'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'parent_address' => ['nullable', 'string', 'max:1000'],
            'organization_status' => ['nullable', 'in:ya,tidak'],
            'achievement_status' => ['nullable', 'in:ya,tidak'],
            'self_improvement_notes' => ['nullable', 'string', 'max:2000'],
            'achievements' => ['nullable', 'array'],
            'achievements.*.type' => ['nullable', 'in:akademik,non_akademik'],
            'achievements.*.name' => ['nullable', 'string', 'max:150'],
            'achievements.*.level' => ['nullable', 'in:kab_kota,provinsi,nasional,internasional'],
            'achievements.*.year' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'achievements.*.certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'achievements.*.certificate_temp' => ['nullable', 'string', 'max:255'],
            'organizations' => ['nullable', 'array'],
            'organizations.*.name' => ['nullable', 'string', 'max:150'],
            'organizations.*.position' => ['nullable', 'string', 'max:100'],
            'organizations.*.level' => ['nullable', 'in:sekolah,kab_kota,provinsi,nasional,internasional'],
            'organizations.*.year' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'tka_subjects' => ['nullable', 'array'],
            'tka_subjects.*' => ['nullable', 'integer', Rule::exists('tka_subjects', 'id')->where('is_active', true)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'ijazah_smp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'akte' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'kartu_keluarga' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'mcu_last_date.before_or_equal' => 'Tanggal medical check-up terakhir tidak boleh melebihi hari ini.',
            'achievements.*.certificate.max' => 'Ukuran sertifikat maksimal 2 MB.',
            'ijazah_smp.max' => 'Ukuran Ijazah SMP maksimal 2 MB.',
            'akte.max' => 'Ukuran Akte maksimal 2 MB.',
            'kartu_keluarga.max' => 'Ukuran Kartu Keluarga maksimal 2 MB.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $choices = collect([
                'university_choice_1_id' => $this->input('university_choice_1_id'),
                'university_choice_2_id' => $this->input('university_choice_2_id'),
                'university_choice_3_id' => $this->input('university_choice_3_id'),
            ])->filter();

            $duplicates = $choices->duplicates();
            if ($duplicates->isNotEmpty()) {
                foreach ($choices as $field => $value) {
                    if ($duplicates->contains($value)) {
                        $validator->errors()->add($field, 'Pilihan kampus tidak boleh sama.');
                    }
                }
            }

            $tkaIds = collect((array) $this->input('tka_subjects', []))
                ->filter(fn ($value) => filled($value))
                ->map(fn ($value) => (int) $value);

            if ($tkaIds->duplicates()->isNotEmpty()) {
                $validator->errors()->add('tka_subjects', 'Mapel TKA tidak boleh sama.');
            }
        });
    }
}
