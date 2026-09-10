@component('layouts.app', ['title' => 'Edit Tahun Ajaran'])
    <x-page-header title="Edit Tahun Ajaran" description="Perbarui data periode akademik." />

    <x-card>
        <form action="{{ route('bk.academic-years.update', $academicYear) }}" method="POST">
            @method('PUT')
            @include('bk.academic-years._form')
        </form>
    </x-card>
@endcomponent
