<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => '2026 / 2027', 'start_year' => 2026, 'end_year' => 2027, 'semester' => 'ganjil', 'is_active' => true],
            ['name' => '2025 / 2026', 'start_year' => 2025, 'end_year' => 2026, 'semester' => 'genap', 'is_active' => false],
            ['name' => '2024 / 2025', 'start_year' => 2024, 'end_year' => 2025, 'semester' => 'genap', 'is_active' => false],
        ] as $year) {
            AcademicYear::updateOrCreate(['name' => $year['name']], $year);
        }
    }
}
