<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SuperAdminSeeder::class,
            AcademicYearSeeder::class,
            MajorSeeder::class,
            SubjectSeeder::class,
            CohortSeeder::class,
            TeacherSeeder::class,
            SchoolClassSeeder::class,
            StudentSeeder::class,
        ]);

        // User::factory(10)->create();
    }
}
