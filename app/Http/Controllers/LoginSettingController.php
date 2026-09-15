<?php

namespace App\Http\Controllers;

use App\Models\LoginSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LoginSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.login-settings.edit', [
            'setting' => LoginSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'school_name' => ['required', 'string', 'max:150'],
            'seo_description' => ['required', 'string', 'max:255'],
            'help_text' => ['required', 'string', 'max:100'],
            'help_whatsapp_number' => ['nullable', 'string', 'max:30'],
            'hero_title' => ['required', 'string', 'max:150'],
            'hero_description' => ['required', 'string', 'max:1000'],
            'footer_name' => ['required', 'string', 'max:150'],
            'footer_tagline' => ['required', 'string', 'max:150'],
            'welcome_title' => ['required', 'string', 'max:100'],
            'welcome_subtitle' => ['required', 'string', 'max:150'],
            'copyright_text' => ['required', 'string', 'max:150'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        unset($data['logo'], $data['hero_image']);

        $setting = LoginSetting::query()->firstOrNew([]);
        $setting->fill($data);

        if ($request->hasFile('logo')) {
            $this->replaceImage($setting, 'logo_path', $request->file('logo')->store('login-settings', 'public'));
        }

        if ($request->hasFile('hero_image')) {
            $this->replaceImage($setting, 'hero_image_path', $request->file('hero_image')->store('login-settings', 'public'));
        }

        $setting->save();

        return back()->with('success', 'Pengaturan login berhasil diperbarui.');
    }

    private function replaceImage(LoginSetting $setting, string $field, string $path): void
    {
        if ($setting->{$field}) {
            Storage::disk('public')->delete($setting->{$field});
        }

        $setting->{$field} = $path;
    }
}
