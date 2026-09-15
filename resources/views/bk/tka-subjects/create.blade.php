@component('layouts.app', ['title' => 'Tambah Mapel TKA'])
    <x-page-header title="Tambah Mapel TKA" description="Tambahkan mapel TKA ke master biodata." />
    <x-card><form action="{{ route('bk.tka-subjects.store') }}" method="POST">@include('bk.tka-subjects._form')</form></x-card>
@endcomponent
