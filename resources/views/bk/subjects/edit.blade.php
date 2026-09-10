@component('layouts.app', ['title' => 'Edit Mata Pelajaran'])
    <x-page-header title="Edit Mata Pelajaran" description="Perbarui master mata pelajaran." />
    <x-card><form action="{{ route('bk.subjects.update', $subject) }}" method="POST">@method('PUT') @include('bk.subjects._form')</form></x-card>
@endcomponent
