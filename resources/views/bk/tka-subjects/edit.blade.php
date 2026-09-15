@component('layouts.app', ['title' => 'Edit Mapel TKA'])
    <x-page-header title="Edit Mapel TKA" description="Perbarui informasi master mapel TKA." />
    <x-card><form action="{{ route('bk.tka-subjects.update', $tkaSubject) }}" method="POST">@method('PUT') @include('bk.tka-subjects._form')</form></x-card>
@endcomponent
