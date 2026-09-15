<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LoginSetting extends Model
{
    protected $fillable = [
        'app_name',
        'school_name',
        'seo_description',
        'help_text',
        'help_whatsapp_number',
        'hero_title',
        'hero_description',
        'footer_name',
        'footer_tagline',
        'welcome_title',
        'welcome_subtitle',
        'copyright_text',
        'logo_path',
        'hero_image_path',
    ];

    public static function current(): self
    {
        return self::query()->first() ?? new self(self::defaults());
    }

    public static function defaults(): array
    {
        return [
            'app_name' => 'SIBIKA',
            'school_name' => 'SMA Plus Astha Hannas',
            'seo_description' => 'Masuk ke SIBIKA SMA Plus Astha Hannas — portal BK, akademik, dan karir siswa.',
            'help_text' => 'Butuh bantuan?',
            'help_whatsapp_number' => '0882006381163',
            'hero_title' => 'Portal BK dan Karir Siswa',
            'hero_description' => 'Satu akses untuk pendampingan akademik, pengembangan diri, dan rencana masa depan.',
            'footer_name' => 'SMA Plus Astha Hannas',
            'footer_tagline' => 'Berilmu • Berakhlak • Berprestasi',
            'welcome_title' => 'Selamat Datang',
            'welcome_subtitle' => 'SIBIKA SMA Plus Astha Hannas',
            'copyright_text' => '© 2026 SMA Plus Astha Hannas. All rights reserved.',
        ];
    }

    public function logoUrl(): string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : asset('images/logo.png');
    }

    public function heroImageUrl(): string
    {
        return $this->hero_image_path ? Storage::disk('public')->url($this->hero_image_path) : asset('images/login-school-hero.png');
    }

    public function helpWhatsappUrl(): ?string
    {
        $number = preg_replace('/\D+/', '', (string) $this->help_whatsapp_number);
        if ($number === '') {
            return null;
        }

        return 'https://wa.me/'.(str_starts_with($number, '0') ? '62'.substr($number, 1) : $number);
    }
}
