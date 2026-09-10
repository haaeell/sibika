@component('layouts.app', ['title' => 'Tambah Jurusan'])
    <x-page-header title="Tambah Jurusan" description="Tambahkan master jurusan baru." />
    <x-card><form action="{{ route('bk.majors.store') }}" method="POST">@include('bk.majors._form')</form></x-card>
@endcomponent
