@component('layouts.app', ['title' => 'Tambah Kelas'])
    <x-page-header title="Tambah Kelas" description="Tambahkan rombongan belajar baru." />
    <x-card>
        <form action="{{ route('bk.school-classes.store') }}" method="POST">
            @include('bk.school-classes._form')
        </form>
    </x-card>
@endcomponent
