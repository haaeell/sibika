# STYLE — E-Learning BK SMA Plus Astha Hannas

> **Dokumen:** UI, Frontend & Coding Style Guide  
> **Tujuan:** Tampilan modern, clean, ringan, konsisten, mudah dipahami, dan mudah di-maintenance.  
> **Frontend utama:** Laravel Blade + Tailwind CSS + jQuery

---

# 1. Prinsip Styling

Gunakan prinsip:

```text
Simple
Clean
Modern
Responsive
Consistent
Accessible
Maintainable
```

Hindari:

- desain terlalu ramai,
- terlalu banyak warna,
- shadow terlalu berat,
- gradient berlebihan,
- animasi yang tidak penting,
- icon campur-campur,
- card terlalu banyak border,
- JavaScript inline berulang,
- CSS custom yang terlalu panjang,
- struktur HTML terlalu nested.

Target visual:

```text
Modern School Dashboard
+
Clean SaaS Dashboard
+
Friendly Student Experience
```

---

# 2. Technology Stack

Frontend:

```text
Laravel Blade
Tailwind CSS
jQuery
DataTables
Select2
SweetAlert2
Font Awesome
Flatpickr
Chart.js
NProgress optional
```

Backend:

```text
Laravel
Form Request
Eloquent ORM
Policy / Middleware
Service hanya untuk bisnis kompleks
```

Tidak perlu menggunakan framework frontend berat untuk dashboard standar.

---

# 3. Asset Strategy

Agar sederhana dan mudah dipelihara:

```text
public/
├── css/
│   ├── app.css
│   └── custom.css
├── js/
│   ├── app.js
│   ├── helpers.js
│   └── pages/
├── vendor/
└── images/
```

Untuk production, gunakan file CSS Tailwind yang sudah di-compile.

Tidak direkomendasikan menggunakan Tailwind Play CDN untuk production.

Jika ingin tanpa bundler frontend kompleks, gunakan Tailwind CLI untuk menghasilkan satu file CSS final.

---

# 4. Visual Identity

## 4.1 Karakter

Visual harus terasa:

- profesional,
- edukatif,
- terpercaya,
- muda,
- ramah,
- modern.

Tidak terlalu corporate dan tidak terlalu kekanak-kanakan.

---

# 5. Color System

Gunakan sedikit warna utama.

Rekomendasi dasar:

```text
Primary      : Indigo / Blue
Success      : Emerald
Warning      : Amber
Danger       : Rose / Red
Info         : Sky
Neutral      : Slate
Background   : Slate 50 / White
```

Contoh class Tailwind:

```html
bg-indigo-600
text-indigo-600

bg-emerald-50
text-emerald-700

bg-amber-50
text-amber-700

bg-rose-50
text-rose-700

bg-slate-50
text-slate-700
```

Jangan membuat setiap modul punya warna utama berbeda.

---

# 6. Background

Layout utama:

```html
<body class="bg-slate-50 text-slate-800">
```

Card:

```html
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
```

Dashboard sebaiknya menggunakan background terang agar nyaman dibaca dalam waktu lama.

---

# 7. Typography

Gunakan font sans-serif modern.

Rekomendasi:

```text
Inter
Plus Jakarta Sans
Manrope
```

Default:

```text
font-sans
```

Hierarki:

```text
Page Title      24-30px semibold/bold
Section Title   18-20px semibold
Card Title      14-16px semibold
Body            14px
Caption          12px
```

Contoh:

```html
<h1 class="text-2xl font-bold tracking-tight text-slate-900">
    Dashboard
</h1>

<p class="mt-1 text-sm text-slate-500">
    Ringkasan aktivitas dan progres siswa.
</p>
```

---

# 8. Spacing

Gunakan spacing konsisten.

Rekomendasi:

```text
Page padding mobile  : 16px
Page padding desktop : 24px
Card padding         : 20-24px
Section gap          : 24px
Form gap             : 16px
```

Contoh:

```html
<main class="p-4 lg:p-6">
    <div class="space-y-6">
        ...
    </div>
</main>
```

---

# 9. Border Radius

Gunakan:

```text
Button     rounded-lg / rounded-xl
Input      rounded-xl
Card       rounded-2xl
Badge      rounded-full
Modal      rounded-2xl
```

Hindari mencampur terlalu banyak jenis radius.

---

# 10. Shadow

Default:

```html
shadow-sm
```

Untuk dropdown/modal:

```html
shadow-xl
```

Hindari `shadow-2xl` untuk semua card.

---

# 11. Layout Utama

Struktur:

```text
Sidebar
Header
Main Content
```

Desktop:

```text
┌────────────┬─────────────────────────────┐
│ Sidebar    │ Header                      │
│            ├─────────────────────────────┤
│            │ Main Content                │
│            │                             │
└────────────┴─────────────────────────────┘
```

Mobile:

```text
Header
Main Content
Bottom/Drawer Navigation
```

Sidebar dapat collapse pada mobile.

---

# 12. Sidebar

Sidebar sederhana.

Contoh:

```html
<aside class="w-64 border-r border-slate-200 bg-white">
```

Menu aktif:

```html
<a class="flex items-center gap-3 rounded-xl bg-indigo-50 px-3 py-2.5 text-sm font-semibold text-indigo-700">
```

Menu biasa:

```html
<a class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
```

Gunakan satu icon library.

Rekomendasi:

```text
Font Awesome
```

Contoh:

```html
<i class="fa-solid fa-house w-5 text-center"></i>
```

---

# 13. Header

Header berisi:

- breadcrumb opsional,
- judul halaman,
- notification,
- profil user.

Jangan membuat header terlalu tinggi.

Rekomendasi:

```text
64px - 72px
```

---

# 14. Page Header

Setiap halaman konsisten:

```html
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">
            Data Siswa
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            Kelola data siswa SMA Plus Astha Hannas.
        </p>
    </div>

    <button class="btn-primary">
        <i class="fa-solid fa-plus"></i>
        Tambah Siswa
    </button>
</div>
```

---

# 15. Card

Base card:

```html
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    ...
</div>
```

Jangan menambahkan card di dalam card kecuali benar-benar dibutuhkan.

---

# 16. Statistic Card

Gunakan:

```html
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-slate-500">Total Siswa</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">432</p>
        </div>

        <div class="flex size-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>
</div>
```

Gunakan icon background lembut.

---

# 17. Button

Sediakan class component.

## Primary

```html
<button class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
```

## Secondary

```html
<button class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
```

## Danger

```html
<button class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
```

Buat Blade component jika dipakai berulang:

```text
<x-button>
<x-button variant="secondary">
<x-button variant="danger">
```

---

# 18. Form

Label:

```html
<label class="mb-1.5 block text-sm font-semibold text-slate-700">
    Nama Lengkap
</label>
```

Input:

```html
<input
    type="text"
    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10"
/>
```

Error:

```html
<p class="mt-1.5 text-xs font-medium text-rose-600">
    Nama lengkap wajib diisi.
</p>
```

Help text:

```html
<p class="mt-1.5 text-xs text-slate-500">
    Gunakan nama sesuai dokumen resmi.
</p>
```

---

# 19. Form Layout

Desktop:

```html
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
```

Field panjang seperti alamat:

```html
<div class="md:col-span-2">
```

Untuk data kompleks, bagi menggunakan section.

Contoh:

```text
Data Pribadi
Data Orang Tua
Alamat
Dokumen
```

Jangan menampilkan 30 input dalam satu blok tanpa grouping.

---

# 20. Select2

Select2 digunakan untuk:

- siswa,
- kelas,
- kampus,
- program studi,
- mata pelajaran.

Standard initialization:

```javascript
$('.select2').select2({
    width: '100%',
    placeholder: 'Pilih data',
    allowClear: true
});
```

Jika di modal:

```javascript
$('.select2-modal').select2({
    width: '100%',
    dropdownParent: $('#formModal')
});
```

Jangan initialize Select2 berkali-kali terhadap element yang sama.

Untuk dynamic content:

```javascript
function initSelect2(scope = document) {
    $(scope).find('.select2').each(function () {
        if (!$(this).hasClass('select2-hidden-accessible')) {
            $(this).select2({
                width: '100%'
            });
        }
    });
}
```

---

# 21. Select2 Styling

Samakan dengan input Tailwind.

Target:

```text
height 42-44px
border slate-300
radius 12px
focus indigo
font-size 14px
```

Custom CSS cukup diletakkan di:

```text
public/css/custom.css
```

Jangan menulis style Select2 panjang di setiap halaman.

---

# 22. DataTables

Gunakan DataTables untuk panel:

- BK,
- Admin,
- Wali Kelas,
- Guru.

Siswa sebaiknya lebih banyak menggunakan list/card jika data sedikit.

Standard:

```javascript
function initDataTable(selector, options = {}) {
    return $(selector).DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            search: '',
            searchPlaceholder: 'Cari...',
            lengthMenu: '_MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Belum ada data',
            zeroRecords: 'Data tidak ditemukan',
            paginate: {
                previous: 'Sebelumnya',
                next: 'Selanjutnya'
            }
        },
        ...options
    });
}
```

Untuk tabel besar gunakan server-side DataTables.

---

# 23. DataTables Column

Jangan terlalu banyak kolom.

Contoh data siswa:

```text
No
Siswa
Kelas
Angkatan
Status
Progress
Aksi
```

Nama siswa dapat digabung:

```text
Avatar
Nama
NIS
```

agar tabel lebih ringkas.

---

# 24. Action Button DataTables

Gunakan dropdown jika aksi lebih dari 3.

Jika hanya:

```text
Lihat
Edit
Hapus
```

boleh menggunakan icon button.

Contoh:

```html
<div class="flex items-center justify-end gap-1">
    <a class="btn-icon" title="Lihat">
        <i class="fa-solid fa-eye"></i>
    </a>

    <button class="btn-icon" title="Edit">
        <i class="fa-solid fa-pen"></i>
    </button>

    <button class="btn-icon text-rose-600" title="Hapus">
        <i class="fa-solid fa-trash"></i>
    </button>
</div>
```

---

# 25. SweetAlert2

Gunakan untuk aksi penting:

- delete,
- publish,
- lock TKA,
- submit nilai,
- submit CBT,
- publish kelulusan.

Helper:

```javascript
function confirmAction({
    title = 'Apakah Anda yakin?',
    text = 'Aksi ini akan memproses data.',
    confirmText = 'Ya, lanjutkan',
    cancelText = 'Batal',
    icon = 'warning'
} = {}) {
    return Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true
    });
}
```

Contoh:

```javascript
confirmAction({
    title: 'Publish kelulusan?',
    text: 'Hasil akan dapat dilihat oleh siswa.',
    confirmText: 'Ya, publish'
}).then(result => {
    if (result.isConfirmed) {
        $('#publish-form').submit();
    }
});
```

---

# 26. Toast

Untuk sukses biasa:

```javascript
Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'success',
    title: 'Data berhasil disimpan',
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true
});
```

Jangan gunakan modal alert besar untuk setiap sukses CRUD.

---

# 27. Loading Button

Saat submit:

```text
normal:
[Simpan]

loading:
[spinner] Menyimpan...
```

Helper:

```javascript
function setButtonLoading(button, loading, text = 'Memproses...') {
    const $button = $(button);

    if (loading) {
        $button.data('original-html', $button.html());
        $button.prop('disabled', true);
        $button.html(`
            <i class="fa-solid fa-spinner fa-spin"></i>
            ${text}
        `);
        return;
    }

    $button.prop('disabled', false);
    $button.html($button.data('original-html'));
}
```

---

# 28. AJAX

Struktur sederhana.

```javascript
$.ajax({
    url: url,
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success(response) {
        // update UI
    },
    error(xhr) {
        handleAjaxError(xhr);
    }
});
```

Buat helper global untuk error.

```javascript
function handleAjaxError(xhr) {
    const message =
        xhr.responseJSON?.message ||
        'Terjadi kesalahan. Silakan coba lagi.';

    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: message
    });
}
```

Jangan copy-paste handler error ke semua halaman.

---

# 29. CSRF

Di layout:

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

Global setup:

```javascript
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

---

# 30. Flatpickr

Gunakan untuk:

- tanggal,
- range tanggal,
- tanggal publish,
- deadline tugas,
- jadwal CBT.

Contoh:

```javascript
flatpickr('.datepicker', {
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd M Y'
});
```

Datetime:

```javascript
flatpickr('.datetimepicker', {
    enableTime: true,
    dateFormat: 'Y-m-d H:i',
    altInput: true,
    altFormat: 'd M Y H:i',
    time_24hr: true
});
```

---

# 31. Badge

Gunakan status badge konsisten.

Success:

```html
<span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
    Terverifikasi
</span>
```

Warning:

```html
<span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
    Menunggu
</span>
```

Danger:

```html
<span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">
    Ditolak
</span>
```

Neutral:

```html
<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
    Draft
</span>
```

---

# 32. Status Mapping

Buat mapping terpusat.

Contoh Blade helper:

```php
function statusBadge(string $status): array
{
    return match ($status) {
        'verified', 'active', 'graduated' => [
            'label' => 'Terverifikasi',
            'class' => 'bg-emerald-50 text-emerald-700',
        ],
        'submitted', 'pending' => [
            'label' => 'Menunggu',
            'class' => 'bg-amber-50 text-amber-700',
        ],
        'rejected', 'inactive' => [
            'label' => 'Ditolak',
            'class' => 'bg-rose-50 text-rose-700',
        ],
        default => [
            'label' => ucfirst($status),
            'class' => 'bg-slate-100 text-slate-600',
        ],
    };
}
```

Jika mapping mulai banyak, pindahkan ke enum/model method.

---

# 33. Modal

Gunakan modal hanya jika:

- form pendek,
- konfirmasi,
- preview sederhana.

Jangan gunakan modal untuk form biodata 30 field.

Form besar harus halaman terpisah.

Modal:

```html
<div class="rounded-2xl bg-white shadow-xl">
```

Struktur:

```text
Header
Body
Footer
```

Footer:

```text
Batal
Simpan
```

---

# 34. Empty State

Contoh:

```html
<div class="flex flex-col items-center justify-center px-6 py-12 text-center">
    <div class="flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
        <i class="fa-solid fa-inbox"></i>
    </div>

    <h3 class="mt-4 text-sm font-semibold text-slate-900">
        Belum ada data
    </h3>

    <p class="mt-1 max-w-sm text-sm text-slate-500">
        Data yang tersedia akan muncul di halaman ini.
    </p>
</div>
```

---

# 35. Skeleton / Loading State

Untuk AJAX dashboard:

```text
skeleton card
spinner kecil
disabled button
```

Hindari full-page spinner untuk request kecil.

NProgress boleh digunakan untuk perpindahan halaman / request utama.

---

# 36. Progress Bar

Untuk biodata:

```html
<div class="h-2 overflow-hidden rounded-full bg-slate-100">
    <div class="h-full rounded-full bg-indigo-600" style="width: 80%"></div>
</div>
```

Tampilkan angka:

```text
80% lengkap
```

---

# 37. Student Dashboard Style

Dashboard siswa harus terasa lebih ringan daripada admin.

Gunakan:

- greeting,
- progress,
- task terbaru,
- pengumuman,
- quick action.

Jangan memulai dashboard siswa dengan tabel DataTables besar.

---

# 38. Admin/BK Dashboard Style

Dashboard BK fokus pada monitoring.

Urutan:

```text
Page Header
Quick Stats
Critical Monitoring
Charts
Recent Activity
Tables
```

Informasi yang membutuhkan aksi diletakkan lebih atas daripada chart dekoratif.

---

# 39. Chart.js

Gunakan chart untuk:

- distribusi karir,
- pilihan kampus,
- status kelengkapan,
- statistik alumni.

Batasi chart per halaman.

Ideal:

```text
2 - 4 chart
```

Jangan menampilkan 8 chart sekaligus.

---

# 40. Responsive Table

Desktop:

```text
DataTables
```

Mobile:

- responsive extension,
- hide kolom minor,
- detail child row,
- atau ubah menjadi card jika perlu.

Kolom utama tidak boleh tersembunyi:

```text
Nama
Status
Aksi
```

---

# 41. Mobile First

Perhatikan siswa kemungkinan besar mengakses melalui HP.

Prioritas mobile:

- tombol besar cukup disentuh,
- sidebar drawer,
- form 1 kolom,
- card ringkas,
- tidak ada horizontal scroll yang tidak perlu,
- upload file mudah,
- CBT nyaman.

---

# 42. CBT UI

Layout CBT berbeda dari dashboard.

Struktur desktop:

```text
Header CBT + Timer
Question Area
Question Navigation
Footer Action
```

Contoh:

```text
Soal 12 dari 50                        01:12:25

[ Pertanyaan ]

○ A
○ B
○ C
○ D
○ E

[Ragu-ragu]

[← Sebelumnya]              [Selanjutnya →]
```

Nomor soal:

```text
1  2  3  4  5
6  7  8  9 10
```

Status visual:

```text
belum dijawab
sudah dijawab
ragu-ragu
aktif
```

Timer harus jelas tetapi tidak terlalu mengganggu.

---

# 43. Icon Rules

Gunakan Font Awesome konsisten.

Contoh mapping:

```text
Dashboard       fa-house
Siswa           fa-users
Guru            fa-chalkboard-user
Kelas           fa-school
Absensi         fa-calendar-check
Nilai           fa-chart-line
TKA             fa-book-open
Karir           fa-compass
Kampus          fa-building-columns
Prestasi        fa-trophy
Tugas           fa-file-pen
CBT             fa-laptop-file
Eligible        fa-star
Kelulusan       fa-graduation-cap
Alumni          fa-user-graduate
Pengumuman      fa-bullhorn
Laporan         fa-chart-pie
Pengaturan      fa-gear
```

Jangan menggunakan emoji sebagai icon UI utama.

---

# 44. Blade Component

Buat component hanya untuk elemen yang benar-benar berulang.

Direkomendasikan:

```text
components/
├── button.blade.php
├── card.blade.php
├── badge.blade.php
├── empty-state.blade.php
├── page-header.blade.php
├── form/
│   ├── input.blade.php
│   ├── select.blade.php
│   ├── textarea.blade.php
│   └── error.blade.php
└── layout/
    ├── sidebar.blade.php
    └── header.blade.php
```

Hindari membuat component untuk HTML yang hanya muncul satu kali.

---

# 45. Blade Style

Gunakan:

```php
@if
@foreach
@forelse
@can
@error
```

Contoh:

```blade
@forelse ($students as $student)
    ...
@empty
    <x-empty-state
        title="Belum ada siswa"
        description="Data siswa akan muncul di sini."
    />
@endforelse
```

Jangan menjalankan query database langsung dari Blade.

Tidak boleh:

```blade
@php
    $students = Student::latest()->get();
@endphp
```

---

# 46. Controller Style

Controller dibuat tipis.

Contoh:

```php
public function store(StoreStudentRequest $request)
{
    $student = Student::create($request->validated());

    return redirect()
        ->route('bk.students.index')
        ->with('success', 'Data siswa berhasil ditambahkan.');
}
```

Untuk proses kompleks:

```php
public function submit(SubmitScoreRequest $request, StudentScore $score)
{
    $this->scoreService->submit($score, auth()->user());

    return response()->json([
        'message' => 'Nilai berhasil diajukan.',
    ]);
}
```

---

# 47. Validation

Gunakan Form Request jika field cukup banyak.

```text
StoreStudentRequest
UpdateStudentRequest
StoreAchievementRequest
SubmitCareerPlanRequest
StoreAssignmentRequest
```

Pesan validasi bahasa Indonesia.

Contoh:

```php
public function messages(): array
{
    return [
        'name.required' => 'Nama wajib diisi.',
        'email.email' => 'Format email tidak valid.',
    ];
}
```

---

# 48. Route Style

Gunakan route name konsisten.

```php
Route::prefix('bk')
    ->name('bk.')
    ->middleware(['auth', 'role:bk'])
    ->group(function () {
        Route::resource('students', StudentController::class);
    });
```

Nama:

```text
bk.students.index
bk.students.create
bk.students.store
bk.students.show
bk.students.edit
bk.students.update
bk.students.destroy
```

---

# 49. Model Style

Model berisi:

- fillable/casts,
- relations,
- query scope sederhana,
- helper terkait entity.

Contoh:

```php
class Student extends Model
{
    protected $fillable = [
        'user_id',
        'class_id',
        'generation_id',
        'nis',
        'nisn',
        'status',
    ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function generation()
    {
        return $this->belongsTo(Generation::class);
    }
}
```

Hindari model berisi ratusan baris business logic.

---

# 50. Service Style

Gunakan service hanya jika dibutuhkan.

Contoh:

```php
class ScoreVerificationService
{
    public function verify(StudentSemesterScore $score, User $user): void
    {
        DB::transaction(function () use ($score, $user) {
            $score->update([
                'status' => 'verified',
                'verified_by' => $user->id,
                'verified_at' => now(),
            ]);
        });
    }
}
```

Jangan membuat service hanya untuk:

```text
StudentService->getAllStudents()
```

jika hanya membungkus satu query sederhana.

---

# 51. Query Style

Gunakan eager loading.

Baik:

```php
Student::query()
    ->with(['classRoom', 'generation'])
    ->latest()
    ->paginate(20);
```

Hindari:

```php
foreach ($students as $student) {
    echo $student->classRoom->name;
}
```

tanpa eager load pada data besar.

---

# 52. Filter Query

Untuk filter kompleks:

```php
$query = Student::query()
    ->with(['classRoom', 'generation'])
    ->when($request->class_id, fn ($query, $classId) =>
        $query->where('class_id', $classId)
    )
    ->when($request->status, fn ($query, $status) =>
        $query->where('status', $status)
    );
```

Jika filter menjadi terlalu banyak, pindahkan ke dedicated filter class.

---

# 53. JavaScript Structure

Jangan simpan semua script di satu file 5000 baris.

Gunakan:

```text
public/js/
├── app.js
├── helpers.js
└── pages/
    ├── students.js
    ├── scores.js
    ├── careers.js
    ├── achievements.js
    ├── assignments.js
    └── cbt.js
```

`helpers.js`:

```text
initSelect2
initDataTable
confirmAction
showToast
handleAjaxError
setButtonLoading
formatRupiah jika perlu
```

---

# 54. JS Initialization

Gunakan satu entry point per page.

Contoh:

```javascript
$(function () {
    initSelect2();
    initStudentTable();
    bindStudentActions();
});
```

Hindari:

```text
<script>
<script>
<script>
<script>
```

berulang di banyak bagian halaman.

---

# 55. Event Handling

Untuk dynamic element gunakan delegation.

```javascript
$(document).on('click', '.btn-delete', function () {
    const id = $(this).data('id');
});
```

Bukan:

```javascript
$('.btn-delete').click(...)
```

jika tombol muncul dari DataTables AJAX.

---

# 56. API / AJAX Response

Standarkan response.

Sukses:

```json
{
  "success": true,
  "message": "Data berhasil disimpan.",
  "data": {}
}
```

Error:

```json
{
  "success": false,
  "message": "Data gagal disimpan.",
  "errors": {}
}
```

Tidak wajib semua route menggunakan AJAX.

Gunakan normal submit Laravel jika AJAX tidak memberi keuntungan UX.

---

# 57. Form Submit Rule

Gunakan normal form untuk:

- halaman create/edit biasa,
- biodata panjang,
- konfigurasi.

Gunakan AJAX untuk:

- toggle status,
- delete,
- approve/reject,
- inline action,
- DataTables,
- autosave CBT,
- favorite kampus.

Prinsip:

```text
jangan pakai AJAX hanya karena bisa.
```

---

# 58. SweetAlert Rule

SweetAlert:

```text
Delete       → confirmation
Publish      → confirmation
Verify       → confirmation optional
Reject       → modal/input reason
Success CRUD → toast
Error        → alert
```

Jangan memakai SweetAlert untuk validasi tiap input.

---

# 59. Notification Style

Success:

```text
icon check
emerald
pesan singkat
```

Warning:

```text
amber
```

Error:

```text
rose
```

Info:

```text
indigo / sky
```

---

# 60. Table Filter Layout

Filter berada di atas tabel.

Desktop:

```text
[Search] [Kelas] [Angkatan] [Status] [Reset]
```

Mobile:

```text
Search
Kelas
Angkatan
Status
Reset
```

Gunakan grid responsive.

---

# 61. Detail Page

Gunakan informasi terstruktur.

Contoh detail siswa:

```text
Header Siswa
Progress

Tab:
- Profil
- Akademik
- Karir
- Prestasi
- CBT
- Riwayat
```

Untuk halaman kompleks, gunakan tabs agar tidak terlalu panjang.

---

# 62. Tabs

Style:

```html
<button class="border-b-2 border-indigo-600 px-1 py-3 text-sm font-semibold text-indigo-600">
    Profil
</button>
```

Inactive:

```html
<button class="border-b-2 border-transparent px-1 py-3 text-sm font-medium text-slate-500 hover:text-slate-700">
```

---

# 63. Breadcrumb

Gunakan hanya untuk halaman yang cukup dalam.

Contoh:

```text
Siswa / Ahmad Fauzan / Nilai
```

Tidak wajib pada dashboard.

---

# 64. Accessibility

Minimal:

- `label` terkait input,
- `button` bukan `div` clickable,
- icon-only button memiliki `title` atau `aria-label`,
- contrast cukup,
- keyboard focus terlihat,
- error tidak hanya menggunakan warna.

Contoh:

```html
<button aria-label="Hapus siswa">
    <i class="fa-solid fa-trash"></i>
</button>
```

---

# 65. Image

Foto siswa:

```text
aspect-square
object-cover
rounded-xl / rounded-full
```

Jangan menampilkan image asli berukuran sangat besar.

Gunakan thumbnail.

---

# 66. File Upload

UI:

```text
Upload area
Nama file
Ukuran
Status upload
Preview/download
```

Tampilkan format yang diizinkan.

Contoh:

```text
PDF, JPG atau PNG. Maksimal 5 MB.
```

---

# 67. Form Wizard

Gunakan wizard untuk biodata jika field sangat banyak.

Contoh:

```text
1 Data Pribadi
2 Orang Tua
3 Alamat
4 Pendidikan
5 Dokumen
```

Namun jangan pakai wizard untuk form kecil.

Autosave draft opsional.

---

# 68. Naming UI

Gunakan bahasa Indonesia konsisten.

Pilih salah satu:

```text
Simpan
Ubah
Hapus
Batal
Lihat
Ajukan
Verifikasi
Tolak
Publish
```

Jangan campur:

```text
Submit / Ajukan
Delete / Hapus
Save / Simpan
```

dalam satu aplikasi.

---

# 69. Date Format

Tampilan:

```text
10 September 2026
10 Sep 2026
```

Database:

```text
Y-m-d
Y-m-d H:i:s
```

Jangan simpan tanggal yang sudah diformat untuk display ke database.

---

# 70. Number Format

Gunakan format Indonesia untuk tampilan angka.

Contoh:

```text
1.250
75,5%
```

Untuk nilai:

```text
87.50
```

atau sesuai kebijakan sekolah, tetapi konsisten.

---

# 71. Delete Rule

Data penting lebih aman menggunakan soft delete jika relevan.

Pertimbangkan untuk:

```text
students
achievements
assignments
universities
questions
```

Jangan hard delete data akademik yang sudah menjadi histori tanpa rule khusus.

---

# 72. UX Rule untuk Aksi Berbahaya

Contoh:

```text
Publish eligible
Publish kelulusan
Delete siswa
Lock TKA
Submit CBT
```

Wajib:

- konfirmasi,
- penjelasan efek,
- disable double click,
- loading,
- server-side validation.

---

# 73. Empty / Error / Loading State

Setiap halaman AJAX harus memiliki tiga state:

```text
Loading
Empty
Error
```

Bukan hanya `success`.

---

# 74. Code Simplicity

Prinsip:

```text
Gunakan Laravel convention
Gunakan Blade
Gunakan jQuery seperlunya
Gunakan helper untuk kode berulang
Pisahkan business logic kompleks
Jangan over-engineering
```

Tidak perlu:

- repository pattern untuk semua model,
- interface untuk seluruh service,
- DTO untuk CRUD sederhana,
- event/listener untuk setiap update,
- JavaScript framework berat untuk tabel/form standar.

---

# 75. Maintainability Rules

1. Satu file tidak terlalu besar.
2. Hindari duplicate code.
3. Query tidak di Blade.
4. Validation tidak di JavaScript saja.
5. Authorization wajib server-side.
6. UI helper dipusatkan.
7. Jangan mencampur data fetch, HTML, dan rule bisnis dalam satu fungsi besar.
8. Gunakan nama variable jelas.
9. Method idealnya melakukan satu tugas utama.
10. Refactor hanya ketika kompleksitas nyata muncul.

---

# 76. PHP Naming

Gunakan:

```php
$student
$studentScore
$careerPlan
$academicYear
$classRoom
```

Hindari:

```php
$data1
$data2
$temp
$x
$arr
```

kecuali scope sangat kecil.

---

# 77. Controller Method

Gunakan REST convention:

```text
index
create
store
show
edit
update
destroy
```

Custom action:

```text
submit
verify
reject
publish
unpublish
lock
unlock
```

---

# 78. Database Column Naming

Gunakan nama eksplisit.

Baik:

```text
academic_year_id
verified_by
verified_at
published_at
graduation_year
```

Hindari:

```text
thn
ver_by
pub
sts
```

---

# 79. Boolean

Gunakan:

```text
is_active
is_published
is_locked
is_verified
```

Bukan:

```text
active_flag
publish_yn
lock_status
```

jika hanya boolean.

---

# 80. Enum / Status

Status harus konsisten.

Contoh:

```text
draft
submitted
verified
rejected
```

UI menggunakan label Indonesia.

Database tetap menggunakan kode sederhana bahasa Inggris agar konsisten secara teknis.

---

# 81. Transaction

Gunakan database transaction jika satu aksi mengubah beberapa tabel penting.

Contoh:

```text
submit CBT
publish kelulusan massal
import siswa
verifikasi nilai batch
ubah status lulus → alumni
```

---

# 82. Authorization

Jangan hanya menyembunyikan tombol.

Wajib validasi server-side.

Contoh:

```php
$this->authorize('view', $student);
```

atau middleware/policy.

Manipulasi URL tidak boleh membuka data user lain.

---

# 83. DataTables Server Side

Gunakan server-side jika:

```text
> 500 - 1000 row
```

atau data akan terus berkembang.

Backend tetap harus mendukung:

- search,
- sorting,
- pagination,
- authorization scope.

---

# 84. Select2 AJAX

Untuk ribuan data, jangan load seluruh option.

Contoh:

```javascript
$('.student-select').select2({
    ajax: {
        url: '/bk/students/search',
        dataType: 'json',
        delay: 300,
        data(params) {
            return {
                search: params.term
            };
        },
        processResults(data) {
            return {
                results: data.data
            };
        }
    },
    minimumInputLength: 2,
    width: '100%'
});
```

---

# 85. AJAX Search Endpoint

Response:

```json
{
  "data": [
    {
      "id": 1,
      "text": "Ahmad Fauzan - XII IPA 1"
    }
  ]
}
```

Endpoint harus tetap di-scope sesuai role.

---

# 86. Frontend Validation

Frontend validation hanya untuk UX.

Server tetap sumber kebenaran.

Tidak boleh mengandalkan:

```text
required HTML
JavaScript
disabled button
hidden input
```

sebagai security.

---

# 87. File Security

Saat upload:

- validasi mime,
- validasi extension,
- validasi size,
- generate filename sendiri,
- simpan lokasi aman,
- authorization ketika download.

Jangan percaya `original_name`.

---

# 88. Dashboard Query

Dashboard tidak boleh melakukan puluhan query berulang.

Gunakan:

- aggregate query,
- eager loading,
- cache jika diperlukan.

Jangan cache terlalu dini sebelum ada bottleneck nyata.

---

# 89. Pagination

Untuk halaman Blade biasa:

```php
->paginate(20)
```

Gunakan:

```blade
{{ $students->links() }}
```

Jangan `get()` ribuan row hanya untuk ditampilkan satu halaman.

---

# 90. Flash Message

Layout membaca:

```text
success
error
warning
info
```

Lalu menampilkan toast.

Controller cukup:

```php
return redirect()
    ->back()
    ->with('success', 'Data berhasil disimpan.');
```

---

# 91. Recommended Component List

UI component yang layak dibuat:

```text
Button
Icon Button
Badge
Card
Page Header
Empty State
Alert
Input
Textarea
Select
Form Error
Modal
Progress
Avatar
Statistic Card
```

Jangan membangun design system terlalu kompleks.

---

# 92. Example Page Structure

```blade
@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
    <div class="space-y-6">
        <x-page-header
            title="Data Siswa"
            description="Kelola seluruh data siswa."
        >
            <x-button>
                <i class="fa-solid fa-plus"></i>
                Tambah Siswa
            </x-button>
        </x-page-header>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <!-- filter -->
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table id="student-table" class="w-full">
                ...
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pages/students.js') }}"></script>
@endpush
```

---

# 93. Example CRUD Flow

```text
Index
↓
Tambah
↓
Form
↓
Validasi
↓
Simpan
↓
Toast sukses
↓
Kembali ke index
```

Edit:

```text
Index
↓
Edit
↓
Form terisi
↓
Update
↓
Toast
↓
Index/detail
```

Delete:

```text
Klik hapus
↓
SweetAlert
↓
Konfirmasi
↓
AJAX delete
↓
Reload DataTable
↓
Toast
```

---

# 94. Recommended UX Student Profile

Biodata:

```text
Header Profile
Progress 82%

Tabs / Step:
Data Pribadi
Orang Tua
Alamat
Pendidikan
Dokumen
```

Tombol simpan tetap jelas.

Pada mobile gunakan sticky action bar jika form panjang.

---

# 95. Recommended UX Monitoring BK

Filter:

```text
Angkatan
Kelas
Status
Kelengkapan
```

Card ringkasan:

```text
32 siswa
29 biodata lengkap
27 nilai lengkap
30 TKA selesai
25 karir lengkap
```

Tabel:

```text
Nama | Biodata | Nilai | TKA | Karir | Prestasi | Aksi
```

Gunakan icon/check dan badge kecil, jangan teks panjang.

---

# 96. Recommended UX Career

Rencana karir menggunakan card pilihan.

Contoh:

```text
[ Kuliah ]
[ Bekerja ]
[ TNI/POLRI ]
[ Kedinasan ]
[ Wirausaha ]
[ Gap Year ]
```

Setelah dipilih, tampilkan form relevan.

Jangan tampilkan semua form sekaligus.

---

# 97. Recommended UX Kampus

List kampus:

```text
Logo
Nama kampus
Lokasi
Tipe
Jumlah prodi
Favorite
```

Detail:

```text
Profil
Program Studi
Jalur Masuk
Beasiswa
Informasi Pendaftaran
```

---

# 98. Recommended UX Eligible & Kelulusan

Sebelum publish:

```text
Belum tersedia.
Pengumuman akan dibuka sesuai jadwal sekolah.
```

Setelah publish gunakan visual yang formal dan elegan.

Hindari animasi konfeti berlebihan untuk konteks pengumuman resmi.

---

# 99. CSS Custom Rule

Gunakan Tailwind sebanyak mungkin.

Custom CSS hanya untuk:

- DataTables,
- Select2,
- Flatpickr,
- plugin pihak ketiga,
- komponen yang sulit dibuat dengan utility class.

Jangan membuat ulang class Tailwind:

Tidak perlu:

```css
.my-flex {
    display: flex;
}
```

karena sudah ada:

```html
flex
```

---

# 100. custom.css Structure

```css
/* Select2 */
.select2-container {
}

/* DataTables */
.dataTables_wrapper {
}

/* Flatpickr */
.flatpickr-calendar {
}

/* Global utilities */
.scrollbar-thin {
}
```

Jangan membuat CSS per halaman jika sebenarnya bisa diselesaikan dengan Tailwind.

---

# 101. Final Development Rule

Gunakan pola:

```text
Laravel Convention
+
Blade sederhana
+
Tailwind sebagai styling utama
+
jQuery hanya untuk interaksi yang dibutuhkan
+
DataTables untuk tabel
+
Select2 untuk select kompleks
+
SweetAlert2 untuk feedback/confirmation
+
Font Awesome untuk icon
+
Chart.js untuk visualisasi
```

Arsitektur frontend tidak perlu dibuat terlalu rumit.

Prioritas:

```text
mudah dibaca
mudah debug
mudah ditambah fitur
mudah dikerjakan developer lain
tetap terlihat profesional
```

---

# 102. Prinsip Terakhir

Setiap kali ingin menambahkan library atau pattern baru, tanyakan:

```text
Apakah fitur ini benar-benar membutuhkan library tersebut?
Apakah Laravel + Blade + Tailwind + jQuery sudah cukup?
Apakah developer berikutnya akan lebih mudah atau justru lebih bingung?
```

Jika tidak memberi manfaat nyata, jangan ditambahkan.

Tujuan akhir bukan membuat codebase terlihat paling canggih.

Tujuan akhir adalah:

> **Sistem yang stabil, cepat dikembangkan, gampang dipelihara, dan tetap punya tampilan modern serta nyaman digunakan.**
