<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::updateOrCreate(
            ['name' => '2026 / 2027'],
            [
                'start_year' => 2026,
                'end_year' => 2027,
                'semester' => 'ganjil',
                'is_active' => true,
            ]
        );
    }
}
