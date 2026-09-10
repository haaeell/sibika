@component('layouts.app', ['title' => 'Edit Siswa'])
    <x-page-header title="Edit Siswa" description="Perbarui data siswa." />
    <x-card>
        <form action="{{ route('bk.students.update', $student) }}" method="POST">
            @method('PUT')
            @include('bk.students._form')
        </form>
    </x-card>
@endcomponent
