@component('layouts.app', ['title' => 'Edit Jurusan'])
    <x-page-header title="Edit Jurusan" description="Perbarui master jurusan." />
    <x-card><form action="{{ route('bk.majors.update', $major) }}" method="POST">@method('PUT') @include('bk.majors._form')</form></x-card>
@endcomponent
