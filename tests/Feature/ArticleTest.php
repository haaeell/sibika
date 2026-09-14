<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_bk_can_create_published_article_with_sanitized_content_and_cover(): void
    {
        Storage::fake('public');
        $user = $this->userWithRole('bk');

        $this->actingAs($user)->post(route('bk.articles.store'), [
            'title' => 'Pembukaan Beasiswa Kampus',
            'excerpt' => 'Informasi singkat beasiswa.',
            'category' => 'beasiswa',
            'status' => 'published',
            'is_pinned' => '1',
            'content_html' => '<h2>Info</h2><p>Daftar <a href="https://example.test">di sini</a></p><script>alert(1)</script><a href="javascript:alert(1)">bad</a>',
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 700),
        ])->assertRedirect(route('bk.articles.index'));

        $article = Article::firstOrFail();
        $this->assertSame('published', $article->status);
        $this->assertTrue($article->is_pinned);
        $this->assertStringNotContainsString('<script', $article->content_html);
        $this->assertStringNotContainsString('javascript:', $article->content_html);
        Storage::disk('public')->assertExists($article->cover_image_path);
    }

    public function test_student_only_sees_published_articles(): void
    {
        $studentUser = $this->userWithRole('siswa');
        Student::create(['nis' => 'ART-001', 'name' => 'Siswa Artikel', 'user_id' => $studentUser->id]);

        $published = Article::create([
            'created_by' => $studentUser->id,
            'title' => 'Pendaftaran Universitas Dibuka',
            'slug' => 'pendaftaran-universitas-dibuka',
            'excerpt' => 'Baca jadwal penting.',
            'content_html' => '<p>Konten aman.</p>',
            'category' => 'universitas',
            'status' => 'published',
            'published_at' => now(),
        ]);
        Article::create([
            'created_by' => $studentUser->id,
            'title' => 'Draft Rahasia',
            'slug' => 'draft-rahasia',
            'content_html' => '<p>Belum publish.</p>',
            'category' => 'lainnya',
            'status' => 'draft',
        ]);

        $this->actingAs($studentUser)
            ->get(route('siswa.articles.index'))
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee('Draft Rahasia');

        $this->actingAs($studentUser)
            ->get(route('siswa.articles.show', $published))
            ->assertOk()
            ->assertSee($published->title);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']));

        return $user;
    }
}
