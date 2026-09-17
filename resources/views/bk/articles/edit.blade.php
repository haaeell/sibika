@component('layouts.app', ['title' => 'Edit Artikel'])
    <x-page-header title="Edit Artikel" description="Perbarui artikel dan status publikasi.">
        <x-slot:actions>
            <x-button variant="secondary" :href="route('bk.articles.preview', $article)" target="_blank"><i class="fa-regular fa-eye"></i> Preview</x-button>
        </x-slot:actions>
    </x-page-header>
    <x-card><form action="{{ route('bk.articles.update', $article) }}" method="POST" enctype="multipart/form-data">@method('PUT') @include('bk.articles._form')</form></x-card>
@endcomponent
