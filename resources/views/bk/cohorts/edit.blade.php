@component('layouts.app', ['title' => 'Edit Angkatan'])
    <x-page-header title="Edit Angkatan" description="Perbarui data angkatan." />

    <x-card>
        <form action="{{ route('bk.cohorts.update', $cohort) }}" method="POST">
            @method('PUT')
            @include('bk.cohorts._form')
        </form>
    </x-card>
@endcomponent
