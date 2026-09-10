<?php

namespace Database\Seeders;

use App\Models\Cohort;
use Illuminate\Database\Seeder;

class CohortSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Angkatan 2024', 'entry_year' => 2024, 'graduation_year' => 2027, 'status' => 'active'],
            ['name' => 'Angkatan 2025', 'entry_year' => 2025, 'graduation_year' => 2028, 'status' => 'active'],
            ['name' => 'Angkatan 2023', 'entry_year' => 2023, 'graduation_year' => 2026, 'status' => 'graduated'],
        ] as $cohort) {
            Cohort::updateOrCreate(['name' => $cohort['name']], $cohort);
        }
    }
}
