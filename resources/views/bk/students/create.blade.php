@component('layouts.app', ['title' => 'Tambah Siswa'])
    <x-page-header title="Tambah Siswa" description="Tambahkan data siswa baru." />
    <x-card>
        <form action="{{ route('bk.students.store') }}" method="POST">
            @include('bk.students._form')
        </form>
    </x-card>
@endcomponent
