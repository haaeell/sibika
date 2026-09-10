@component('layouts.app', ['title' => 'Tambah Mata Pelajaran'])
    <x-page-header title="Tambah Mata Pelajaran" description="Tambahkan master mata pelajaran baru." />
    <x-card><form action="{{ route('bk.subjects.store') }}" method="POST">@include('bk.subjects._form')</form></x-card>
@endcomponent
