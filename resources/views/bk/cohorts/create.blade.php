@component('layouts.app', ['title' => 'Tambah Angkatan'])
    <x-page-header title="Tambah Angkatan" description="Tambahkan data angkatan baru." />

    <x-card>
        <form action="{{ route('bk.cohorts.store') }}" method="POST">
            @include('bk.cohorts._form')
        </form>
    </x-card>
@endcomponent
