@component('layouts.app', ['title' => 'Tulis Artikel'])
    <x-page-header title="Tulis Artikel" description="Buat informasi yang rapi, jelas, dan mudah dibaca siswa." />
    <x-card><form action="{{ route('bk.articles.store') }}" method="POST" enctype="multipart/form-data">@include('bk.articles._form')</form></x-card>
@endcomponent
