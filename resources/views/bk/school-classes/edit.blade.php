@component('layouts.app', ['title' => 'Edit Kelas'])
    <x-page-header title="Edit Kelas" description="Perbarui data rombongan belajar." />
    <x-card>
        <form action="{{ route('bk.school-classes.update', $schoolClass) }}" method="POST">
            @method('PUT')
            @include('bk.school-classes._form')
        </form>
    </x-card>
@endcomponent
