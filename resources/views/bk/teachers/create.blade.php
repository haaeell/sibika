@component('layouts.app', ['title' => 'Tambah Guru'])
    <x-page-header title="Tambah Guru" description="Tambahkan data guru baru." />
    <x-card>
        <form action="{{ route('bk.teachers.store') }}" method="POST">
            @include('bk.teachers._form')
        </form>
    </x-card>
@endcomponent
