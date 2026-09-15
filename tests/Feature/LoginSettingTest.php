<?php

namespace Tests\Feature;

use App\Models\LoginSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoginSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_login_text_and_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));

        $this->actingAs($user)->put(route('admin.login-settings.update'), [
            'app_name' => 'Portal Baru',
            'school_name' => 'Sekolah Baru',
            'seo_description' => 'Deskripsi SEO baru',
            'help_text' => 'Hubungi admin',
            'help_whatsapp_number' => '0882006381163',
            'hero_title' => 'Judul Hero Baru',
            'hero_description' => 'Deskripsi hero baru',
            'footer_name' => 'Footer Baru',
            'footer_tagline' => 'Tagline Baru',
            'welcome_title' => 'Masuk Portal',
            'welcome_subtitle' => 'Portal Baru Sekolah Baru',
            'copyright_text' => 'Copyright Baru',
            'logo' => UploadedFile::fake()->image('logo.png'),
            'hero_image' => UploadedFile::fake()->image('hero.jpg', 1200, 800),
        ])->assertSessionHasNoErrors()
            ->assertRedirect();

        $setting = LoginSetting::firstOrFail();
        Storage::disk('public')->assertExists($setting->logo_path);
        Storage::disk('public')->assertExists($setting->hero_image_path);

        $this->post(route('logout'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Portal Baru')
            ->assertSee('Judul Hero Baru')
            ->assertSee('Hubungi admin')
            ->assertSee('https://wa.me/62882006381163', false);
    }
}
