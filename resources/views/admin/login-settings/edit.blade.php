@component('layouts.app', ['title' => 'Pengaturan Login'])
    <x-page-header title="Pengaturan Login" description="Ubah teks, logo, dan gambar hero halaman login." />

    <x-card>
        <form action="{{ route('admin.login-settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-4 md:grid-cols-2">
                <x-form.input name="app_name" label="Nama Aplikasi" icon="fa-solid fa-window-maximize" :value="old('app_name', $setting->app_name)" required />
                <x-form.input name="school_name" label="Nama Sekolah" icon="fa-solid fa-school" :value="old('school_name', $setting->school_name)" required />
                <x-form.input name="help_text" label="Teks Bantuan" icon="fa-regular fa-circle-question" :value="old('help_text', $setting->help_text)" required />
                <x-form.input name="welcome_title" label="Judul Form" icon="fa-solid fa-heading" :value="old('welcome_title', $setting->welcome_title)" required />
                <x-form.input name="welcome_subtitle" label="Subjudul Form" icon="fa-solid fa-quote-left" :value="old('welcome_subtitle', $setting->welcome_subtitle)" required />
                <x-form.input name="hero_title" label="Judul Hero" icon="fa-solid fa-heading" :value="old('hero_title', $setting->hero_title)" required />
                <x-form.input name="footer_name" label="Nama Footer Hero" icon="fa-solid fa-signature" :value="old('footer_name', $setting->footer_name)" required />
                <x-form.input name="footer_tagline" label="Tagline Footer Hero" icon="fa-solid fa-star" :value="old('footer_tagline', $setting->footer_tagline)" required />
                <x-form.input name="copyright_text" label="Copyright" icon="fa-regular fa-copyright" :value="old('copyright_text', $setting->copyright_text)" required />
            </div>

            <x-form.textarea name="seo_description" label="SEO Description" icon="fa-solid fa-magnifying-glass" :value="old('seo_description', $setting->seo_description)" rows="3" required />
            <x-form.textarea name="hero_description" label="Deskripsi Hero" icon="fa-solid fa-align-left" :value="old('hero_description', $setting->hero_description)" rows="3" required />

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="logo" class="mb-1.5 block text-sm font-semibold text-slate-700">Logo</label>
                    <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <img src="{{ $setting->logoUrl() }}" alt="Logo login" class="size-16 rounded-xl bg-white object-contain p-2">
                        <input id="logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-800">
                    </div>
                    <x-form.error name="logo" />
                </div>

                <div>
                    <label for="hero_image" class="mb-1.5 block text-sm font-semibold text-slate-700">Gambar Hero</label>
                    <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <img src="{{ $setting->heroImageUrl() }}" alt="Gambar hero login" class="h-16 w-24 rounded-xl object-cover">
                        <input id="hero_image" type="file" name="hero_image" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-800">
                    </div>
                    <x-form.error name="hero_image" />
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-button type="submit">
                    <i class="fa-solid fa-save"></i>
                    Simpan
                </x-button>
                <x-button variant="secondary" :href="route('login')" target="_blank">Lihat Login</x-button>
            </div>
        </form>
    </x-card>
@endcomponent
