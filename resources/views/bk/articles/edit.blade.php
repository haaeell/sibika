@component('layouts.app', ['title' => 'Edit Artikel'])
    <x-page-header title="Edit Artikel" description="Perbarui artikel dan status publikasi." />
    <x-card><form action="{{ route('bk.articles.update', $article) }}" method="POST" enctype="multipart/form-data">@method('PUT') @include('bk.articles._form')</form></x-card>
@endcomponent
