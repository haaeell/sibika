<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('short_name', 30)->nullable();
            $table->string('type', 30)->default('lainnya')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->foreignId('university_choice_1_id')->nullable()->after('medical_history')->constrained('universities')->nullOnDelete();
            $table->foreignId('university_choice_2_id')->nullable()->after('university_choice_1_id')->constrained('universities')->nullOnDelete();
        });

        foreach (DB::table('student_profiles')->select('id', 'university_choice_1', 'university_choice_2')->get() as $profile) {
            DB::table('student_profiles')->where('id', $profile->id)->update([
                'university_choice_1_id' => $this->universityId($profile->university_choice_1),
                'university_choice_2_id' => $this->universityId($profile->university_choice_2),
            ]);
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['university_choice_1', 'university_choice_2']);
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('university_choice_1', 150)->nullable();
            $table->string('university_choice_2', 150)->nullable();
        });

        foreach (DB::table('student_profiles')->select('id', 'university_choice_1_id', 'university_choice_2_id')->get() as $profile) {
            DB::table('student_profiles')->where('id', $profile->id)->update([
                'university_choice_1' => DB::table('universities')->where('id', $profile->university_choice_1_id)->value('name'),
                'university_choice_2' => DB::table('universities')->where('id', $profile->university_choice_2_id)->value('name'),
            ]);
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('university_choice_1_id');
            $table->dropConstrainedForeignId('university_choice_2_id');
        });

        Schema::dropIfExists('universities');
    }

    private function universityId(?string $value): ?int
    {
        if (blank($value)) {
            return null;
        }

        $input = trim(preg_split('/\s+-\s+/', $value, 2)[0]);
        $aliases = [
            'UI' => ['Universitas Indonesia', 'UI', 'negeri'],
            'ITB' => ['Institut Teknologi Bandung', 'ITB', 'negeri'],
            'UGM' => ['Universitas Gadjah Mada', 'UGM', 'negeri'],
            'IPB' => ['IPB University', 'IPB', 'negeri'],
            'UNAIR' => ['Universitas Airlangga', 'UNAIR', 'negeri'],
            'UNPAD' => ['Universitas Padjadjaran', 'UNPAD', 'negeri'],
        ];
        [$name, $shortName, $type] = $aliases[strtoupper($input)] ?? [$input, null, 'lainnya'];

        $existing = DB::table('universities')->where('name', $name)->value('id');
        if ($existing) {
            return (int) $existing;
        }

        return DB::table('universities')->insertGetId([
            'name' => $name,
            'short_name' => $shortName,
            'type' => $type,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
