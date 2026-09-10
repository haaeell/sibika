@component('layouts.app', ['title' => 'Tambah Tahun Ajaran'])
    <x-page-header title="Tambah Tahun Ajaran" description="Tambahkan periode akademik baru." />

    <x-card>
        <form action="{{ route('bk.academic-years.store') }}" method="POST">
            @include('bk.academic-years._form')
        </form>
    </x-card>
@endcomponent
