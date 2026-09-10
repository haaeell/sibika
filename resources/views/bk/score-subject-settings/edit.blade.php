@component('layouts.app', ['title' => 'Edit Setting Nilai'])
    <x-page-header title="Edit Setting Nilai" description="Perbarui aturan mapel nilai semester." />
    <x-card><form action="{{ route('bk.score-subject-settings.update', $setting) }}" method="POST">@method('PUT') @include('bk.score-subject-settings._form')</form></x-card>
@endcomponent
