@component('layouts.app', ['title' => 'Edit Kampus'])
    <x-page-header title="Edit Kampus" description="Perbarui informasi master kampus." />
    <x-card><form action="{{ route('bk.universities.update', $university) }}" method="POST">@method('PUT') @include('bk.universities._form')</form></x-card>
@endcomponent
