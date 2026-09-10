<?php

namespace Database\Seeders;

use App\Models\Major;
use Illuminate\Database\Seeder;

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        $majors = [
            ['code' => 'IPA', 'name' => 'Ilmu Pengetahuan Alam'],
            ['code' => 'IPS', 'name' => 'Ilmu Pengetahuan Sosial'],
            ['code' => 'BAHASA', 'name' => 'Bahasa dan Budaya'],
        ];

        foreach ($majors as $major) {
            Major::updateOrCreate(
                ['code' => $major['code']],
                ['name' => $major['name'], 'is_active' => true]
            );
        }
    }
}
