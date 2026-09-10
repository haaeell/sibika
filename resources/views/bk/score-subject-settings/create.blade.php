@component('layouts.app', ['title' => 'Tambah Setting Nilai'])
    <x-page-header title="Tambah Setting Nilai" description="Atur mapel yang harus diisi siswa per semester dan jurusan." />
    <x-card><form action="{{ route('bk.score-subject-settings.store') }}" method="POST">@include('bk.score-subject-settings._form')</form></x-card>
@endcomponent
