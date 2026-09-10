@component('layouts.app', ['title' => 'Edit Guru'])
    <x-page-header title="Edit Guru" description="Perbarui data guru." />
    <x-card>
        <form action="{{ route('bk.teachers.update', $teacher) }}" method="POST">
            @method('PUT')
            @include('bk.teachers._form')
        </form>
    </x-card>
@endcomponent
