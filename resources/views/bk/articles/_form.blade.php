@csrf

<div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
    <div class="space-y-4">
        <x-form.input name="title" label="Judul Artikel" icon="fa-solid fa-heading" placeholder="Contoh: Pendaftaran Beasiswa Indonesia Maju Dibuka" :value="old('title', $article->title)" required />
        <x-form.textarea name="excerpt" label="Ringkasan" icon="fa-solid fa-align-left" :value="old('excerpt', $article->excerpt)" rows="3" placeholder="Ringkasan pendek untuk kartu artikel." />

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Konten Artikel <span class="text-rose-500">*</span></label>
            <div class="overflow-hidden rounded-2xl border border-slate-300 bg-white" data-rich-editor data-upload-url="{{ route('bk.articles.upload-image') }}">
                <div class="flex flex-wrap gap-1 border-b border-slate-200 bg-slate-50 p-2">
                    <button type="button" class="editor-btn" data-command="formatBlock" data-value="h2">H2</button>
                    <button type="button" class="editor-btn" data-command="formatBlock" data-value="h3">H3</button>
                    <button type="button" class="editor-btn" data-command="bold"><i class="fa-solid fa-bold"></i></button>
                    <button type="button" class="editor-btn" data-command="italic"><i class="fa-solid fa-italic"></i></button>
                    <button type="button" class="editor-btn" data-command="insertUnorderedList"><i class="fa-solid fa-list-ul"></i></button>
                    <button type="button" class="editor-btn" data-command="insertOrderedList"><i class="fa-solid fa-list-ol"></i></button>
                    <button type="button" class="editor-btn" data-editor-link><i class="fa-solid fa-link"></i></button>
                    <button type="button" class="editor-btn" data-editor-image><i class="fa-regular fa-image"></i></button>
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" data-editor-image-input>
                </div>
                <div class="article-editor min-h-80 p-5 text-slate-800 outline-none" contenteditable="true" data-editor-area>{!! old('content_html', $article->content_html) !!}</div>
                <textarea name="content_html" class="hidden" data-editor-input>{{ old('content_html', $article->content_html) }}</textarea>
            </div>
            <p class="mt-1.5 text-xs text-slate-500">Gunakan toolbar untuk heading, list, link, dan gambar dalam konten.</p>
            <x-form.error name="content_html" />
        </div>
    </div>

    <aside class="space-y-4">
        <x-form.select name="category" label="Kategori" icon="fa-solid fa-layer-group" class="select2" required>
            @foreach (\App\Models\Article::CATEGORIES as $value => $label)
                <option value="{{ $value }}" @selected(old('category', $article->category) === $value)>{{ $label }}</option>
            @endforeach
        </x-form.select>

        <x-form.select name="status" label="Status" icon="fa-solid fa-circle-check" class="select2" required>
            <option value="draft" @selected(old('status', $article->status) === 'draft')>Draft</option>
            <option value="published" @selected(old('status', $article->status) === 'published')>Published</option>
        </x-form.select>

        <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
            <input type="checkbox" name="is_pinned" value="1" class="mt-0.5 size-5 rounded border-slate-300 text-blue-800 focus:ring-blue-700" @checked(old('is_pinned', $article->is_pinned))>
            <span>Pin artikel<span class="block text-xs font-medium text-slate-500">Artikel pinned tampil lebih dulu untuk siswa.</span></span>
        </label>

        <div>
            <label for="cover_image" class="mb-1.5 block text-sm font-semibold text-slate-700">Cover Artikel</label>
            @if ($article->coverImageUrl())
                <img src="{{ $article->coverImageUrl() }}" alt="Cover {{ $article->title }}" class="mb-3 h-36 w-full rounded-2xl object-cover">
            @endif
            <input id="cover_image" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" data-max-image-size="4194304" class="block w-full rounded-xl border border-dashed border-slate-300 bg-white p-3 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-800">
            <p class="mt-1.5 text-xs text-slate-500">Opsional. Maksimal 4MB.</p>
            <x-form.error name="cover_image" />
        </div>
    </aside>
</div>

<div class="mt-6 flex flex-wrap items-center gap-2">
    <x-button type="submit"><i class="fa-solid fa-save"></i> Simpan</x-button>
    <x-button variant="secondary" :href="route('bk.articles.index')">Batal</x-button>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-rich-editor]').forEach(function (root) {
                const area = root.querySelector('[data-editor-area]');
                const input = root.querySelector('[data-editor-input]');
                const imageInput = root.querySelector('[data-editor-image-input]');
                const sync = () => input.value = area.innerHTML.trim();

                root.closest('form').addEventListener('submit', sync);
                area.addEventListener('input', sync);

                root.querySelectorAll('[data-command]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        document.execCommand(button.dataset.command, false, button.dataset.value || null);
                        area.focus();
                        sync();
                    });
                });

                root.querySelector('[data-editor-link]').addEventListener('click', function () {
                    const url = window.prompt('Masukkan URL link');
                    if (!url) return;
                    document.execCommand('createLink', false, url);
                    sync();
                });

                root.querySelector('[data-editor-image]').addEventListener('click', function () {
                    imageInput.click();
                });

                imageInput.addEventListener('change', async function () {
                    if (!imageInput.files.length) return;
                    if (imageInput.files[0].size > 4194304) {
                        imageInput.value = '';
                        return window.handleAjaxError({ responseJSON: { message: 'Ukuran gambar maksimal 4MB.' } });
                    }
                    const formData = new FormData();
                    formData.append('image', imageInput.files[0]);
                    const response = await fetch(root.dataset.uploadUrl, { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                    if (!response.ok) return window.handleAjaxError({ responseJSON: { message: 'Gambar gagal diunggah.' } });
                    const payload = await response.json();
                    document.execCommand('insertImage', false, payload.url);
                    imageInput.value = '';
                    sync();
                });
            });

            document.querySelectorAll('[data-max-image-size]').forEach(function (input) {
                input.addEventListener('change', function () {
                    if (input.files.length && input.files[0].size > Number(input.dataset.maxImageSize)) {
                        input.value = '';
                        window.handleAjaxError({ responseJSON: { message: 'Ukuran cover maksimal 4MB.' } });
                    }
                });
            });
        });
    </script>
@endpush
