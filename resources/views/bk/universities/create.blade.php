@component('layouts.app', ['title' => 'Tambah Kampus'])
    <x-page-header title="Tambah Kampus" description="Tambahkan perguruan tinggi ke master kampus." />
    <x-card><form action="{{ route('bk.universities.store') }}" method="POST">@include('bk.universities._form')</form></x-card>
@endcomponent
