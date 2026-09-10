# PRD — SIBIKA / E-Learning BK SMA Plus Astha Hannas

> **Dokumen:** Product Requirements Document  
> **Produk:** E-Learning BK SMA Plus Astha Hannas  
> **Versi:** 1.0  
> **Status:** Draft pengembangan  
> **Platform utama:** Web responsive  
> **Backend yang direkomendasikan:** Laravel  
> **Frontend:** Blade + Tailwind CSS + jQuery  
> **Database:** MySQL / PostgreSQL

---

## 1. Ringkasan Produk

E-Learning BK SMA Plus Astha Hannas adalah sistem berbasis web yang menggabungkan fungsi:

- pendataan siswa,
- monitoring akademik,
- bimbingan dan konseling,
- perencanaan karir dan studi lanjut,
- informasi perguruan tinggi,
- prestasi siswa,
- tugas,
- CBT,
- pengumuman eligible,
- pengumuman kelulusan,
- alumni,
- tracer study,
- dan laporan BK.

Sistem dirancang untuk mengikuti perjalanan siswa sejak masih aktif bersekolah, persiapan kelas XII, kelulusan, sampai menjadi alumni.

Sistem tidak hanya diposisikan sebagai LMS, tetapi sebagai **Student Development, Counseling, Career & Alumni Tracking System**.

---

## 2. Tujuan Produk

### 2.1 Tujuan Utama

1. Memusatkan data akademik dan non-akademik siswa.
2. Memudahkan BK melakukan monitoring perkembangan siswa.
3. Memudahkan wali kelas mengetahui kelengkapan dan progres siswa.
4. Membantu siswa merencanakan karir dan pendidikan setelah lulus.
5. Menyediakan informasi kampus, program studi, jalur masuk, dan beasiswa.
6. Mengelola tugas dan CBT secara terintegrasi.
7. Mengelola proses eligible dan pengumuman kelulusan.
8. Menjadikan data lulusan sebagai data alumni tanpa input ulang.
9. Menyediakan tracer study dan statistik lulusan untuk sekolah.
10. Menyediakan laporan yang mudah dibaca dan diekspor.

---

## 3. Sasaran Pengguna

### 3.1 Siswa

Siswa adalah pusat sistem.

Siswa dapat:

- melihat dashboard pribadi,
- melengkapi biodata,
- melihat rekap absensi,
- menginput nilai semester,
- memilih mata pelajaran TKA,
- menentukan rencana karir,
- memilih kampus dan program studi,
- melihat informasi kampus,
- menambahkan kampus favorit,
- menginput prestasi,
- mengerjakan tugas,
- mengikuti CBT,
- melihat status eligible,
- melihat pengumuman kelulusan,
- mengisi data tujuan setelah lulus,
- melihat informasi alumni.

### 3.2 Guru Mata Pelajaran

Guru mapel dapat:

- melihat kelas yang diampu,
- melihat siswa pada kelas yang diampu,
- membuat tugas,
- mengelola pengumpulan tugas,
- memberikan nilai dan feedback,
- membuat bank soal CBT,
- membuat paket CBT,
- melihat hasil CBT.

Guru mapel tidak memiliki akses ke data BK yang tidak berkaitan dengan tugasnya.

### 3.3 Wali Kelas

Wali kelas dapat:

- melihat dashboard kelas,
- melihat siswa di kelasnya,
- melihat absensi,
- memonitor biodata,
- memverifikasi nilai semester,
- memverifikasi prestasi,
- melihat pilihan karir,
- melihat pilihan TKA,
- memonitor kelengkapan siswa,
- melihat dan mengunduh rekap kelas.

### 3.4 Guru BK / Super Admin

Guru BK / Super Admin memiliki akses paling luas.

Fungsi utama:

- master data,
- data siswa,
- data guru,
- akademik,
- karir,
- kampus,
- prestasi,
- alumni,
- tracer study,
- CBT,
- eligible,
- kelulusan,
- pengumuman,
- monitoring,
- laporan,
- konfigurasi sistem.

---

## 4. Role & Permission

Role minimal:

```text
super_admin
bk
wali_kelas
guru
siswa
```

Jika ingin lebih sederhana, `super_admin` dan `bk` dapat memiliki menu hampir sama, namun `super_admin` memiliki akses konfigurasi teknis.

### 4.1 Matriks Hak Akses

| Modul | Siswa | Guru | Wali Kelas | BK | Super Admin |
|---|---:|---:|---:|---:|---:|
| Dashboard | ✓ | ✓ | ✓ | ✓ | ✓ |
| Biodata siswa sendiri | CRUD terbatas | - | Lihat | CRUD | CRUD |
| Data siswa semua | - | Kelas diampu | Kelas sendiri | ✓ | ✓ |
| Absensi | Lihat sendiri | Opsional | Kelas sendiri | ✓ | ✓ |
| Nilai semester | Input sendiri | - | Verifikasi | Verifikasi | ✓ |
| TKA | Pilih sendiri | - | Monitor | CRUD/lock | ✓ |
| Karir | CRUD sendiri | - | Lihat | CRUD/monitor | ✓ |
| Kampus | Lihat | Lihat | Lihat | CRUD | CRUD |
| Prestasi | Input sendiri | - | Verifikasi | Verifikasi | ✓ |
| Tugas | Kerjakan | CRUD kelas | Monitor | Lihat | ✓ |
| CBT | Kerjakan | CRUD paket sendiri | Monitor | CRUD | ✓ |
| Eligible | Lihat publish | - | Lihat | CRUD/publish | ✓ |
| Kelulusan | Lihat publish | - | Lihat | CRUD/publish | ✓ |
| Alumni | Lihat | Lihat | Lihat | CRUD | ✓ |
| Laporan | Pribadi terbatas | Kelas/mapel | Kelas | ✓ | ✓ |
| Pengguna & konfigurasi | - | - | - | Terbatas | ✓ |

---

## 5. Alur Besar Sistem

```text
USER LOGIN
    ↓
VALIDASI AKUN
    ↓
DETEKSI ROLE
    ↓
REDIRECT DASHBOARD
    ↓
AKSES MODUL SESUAI ROLE
```

Perjalanan siswa:

```text
SISWA AKTIF
    ↓
LENGKAPI BIODATA
    ↓
DATA AKADEMIK
    ↓
MINAT & KARIR
    ↓
PRESTASI
    ↓
TUGAS & CBT
    ↓
PERSIAPAN KELAS XII
    ↓
TKA + ELIGIBLE + KAMPUS
    ↓
KELULUSAN
    ↓
DATA TUJUAN LULUSAN
    ↓
ALUMNI / TRACER STUDY
```

---

# 6. Modul Authentication

## 6.1 Login

Field:

- username / NIS / email
- password
- remember me opsional

Setelah login:

```text
role siswa       → /siswa/dashboard
role guru        → /guru/dashboard
role wali_kelas  → /wali-kelas/dashboard
role bk          → /bk/dashboard
role super_admin → /admin/dashboard
```

## 6.2 Reset Password

Minimal tersedia:

- request reset password,
- token reset,
- password baru,
- konfirmasi password.

## 6.3 Account Status

Status akun:

```text
active
inactive
graduated
```

Akun alumni dapat tetap aktif jika sekolah ingin menyediakan portal alumni.

---

# 7. Dashboard

## 7.1 Dashboard Siswa

Informasi utama:

- nama,
- kelas,
- tahun ajaran,
- persentase kehadiran,
- jumlah tugas belum dikumpulkan,
- jumlah prestasi,
- progres kelengkapan nilai,
- progres biodata,
- progres karir,
- jadwal CBT,
- pengumuman terbaru.

### Progress Persiapan Siswa

Checklist:

- biodata lengkap,
- data orang tua lengkap,
- nilai semester lengkap,
- nilai terverifikasi,
- mapel TKA dipilih,
- karir dipilih,
- kampus pilihan diisi,
- prestasi diinput,
- CBT wajib selesai.

Contoh:

```text
Progress Persiapan Kelulusan 80%

✓ Biodata lengkap
✓ Nilai semester 1-5
✓ Pilihan karir
! Mapel TKA belum final
! Pilihan kampus belum final
```

## 7.2 Dashboard Wali Kelas

Menampilkan:

- total siswa,
- siswa aktif,
- kehadiran kelas,
- biodata lengkap,
- nilai lengkap,
- nilai menunggu verifikasi,
- pilihan TKA,
- pilihan karir,
- prestasi menunggu verifikasi,
- siswa yang belum melengkapi data.

## 7.3 Dashboard Guru

Menampilkan:

- kelas diampu,
- tugas aktif,
- tugas menunggu penilaian,
- paket CBT aktif,
- CBT terjadwal,
- submission terbaru.

## 7.4 Dashboard BK

Menampilkan:

- total siswa,
- total kelas,
- total alumni,
- statistik akademik,
- statistik karir,
- progres kelas XII,
- siswa belum melengkapi data,
- distribusi karir,
- kampus tujuan terbanyak,
- program studi terbanyak,
- siswa eligible,
- tracer study alumni.

---

# 8. Modul Master Data

Dikelola oleh BK / Super Admin.

## 8.1 Tahun Ajaran

Field:

- name
- start_year
- end_year
- semester
- is_active

Rule:

- hanya satu tahun ajaran aktif pada satu waktu.

## 8.2 Angkatan

Field:

- name
- entry_year
- graduation_year
- status

Contoh:

```text
Angkatan 19
Masuk 2024
Lulus 2027
```

## 8.3 Kelas

Field:

- name
- grade_level
- major
- academic_year_id
- homeroom_teacher_id

Contoh:

```text
XII IPA 1
XII IPA 2
XI IPS 1
```

## 8.4 Mata Pelajaran

Field:

- code
- name
- category
- active

Kategori dapat meliputi:

```text
general
tka_mandatory
tka_optional
```

## 8.5 Guru

Field minimal:

- NIP / kode,
- nama,
- email,
- nomor HP,
- status,
- mapel.

## 8.6 Siswa

Field minimal:

- NIS,
- NISN,
- nama,
- kelas,
- angkatan,
- status,
- akun login.

Import Excel disarankan tersedia.

---

# 9. Modul Biodata Siswa

## 9.1 Data Pribadi

Field:

- NIS
- NISN
- full_name
- nickname
- gender
- birth_place
- birth_date
- phone
- email
- photo

## 9.2 Alamat

Field:

- province
- city
- district
- village
- postal_code
- address

## 9.3 Data Orang Tua

Ayah:

- name
- phone
- occupation
- education
- income_range

Ibu:

- name
- phone
- occupation
- education
- income_range

Opsional:

- guardian_name
- guardian_phone
- guardian_relation

## 9.4 Data Pendidikan

- previous_school
- previous_school_address
- graduation_year
- academic_notes

## 9.5 Dokumen

Contoh:

- foto,
- KIP,
- kartu keluarga,
- dokumen tambahan.

## 9.6 Progress Biodata

Sistem menghitung persentase kelengkapan.

Contoh:

```text
Data pribadi      100%
Alamat             80%
Orang tua         100%
Dokumen            50%

Total              82%
```

---

# 10. Modul Absensi

## 10.1 Status Absensi

```text
present
permission
sick
alpha
late
```

## 10.2 Siswa

Siswa hanya melihat data miliknya.

Tampilan:

- ringkasan bulan,
- total hadir,
- izin,
- sakit,
- alpha,
- terlambat,
- tabel riwayat harian.

## 10.3 Wali Kelas / BK

Fitur:

- filter tanggal,
- filter kelas,
- filter siswa,
- statistik kelas,
- detail absensi,
- export Excel/PDF.

## 10.4 Sumber Absensi

Sistem harus mendukung:

1. input manual, atau
2. sinkronisasi dari sistem absensi lain pada tahap berikutnya.

---

# 11. Modul Nilai Semester

## 11.1 Alur

```text
SISWA
  ↓
PILIH SEMESTER
  ↓
INPUT NILAI
  ↓
SIMPAN DRAFT
  ↓
AJUKAN
  ↓
WALI KELAS / BK
  ↓
VERIFIKASI / TOLAK
```

## 11.2 Status

```text
draft
submitted
verified
rejected
```

## 11.3 Rule

- nilai hanya boleh berada pada range yang ditentukan sekolah,
- siswa dapat mengubah nilai ketika `draft`,
- siswa tidak dapat mengubah setelah `verified`,
- jika `rejected`, siswa dapat memperbaiki lalu mengajukan ulang,
- verifikator dapat memberi catatan.

## 11.4 Data Nilai

Struktur:

```text
semester
subject
score
status
verified_by
verified_at
verification_note
```

---

# 12. Modul TKA

## 12.1 Konfigurasi

BK menentukan:

- periode pilihan TKA,
- mapel wajib,
- mapel pilihan,
- jumlah pilihan yang diperbolehkan,
- deadline,
- status lock.

## 12.2 Siswa

Siswa dapat:

- melihat mapel wajib,
- memilih mapel pilihan,
- menyimpan,
- mengonfirmasi.

## 12.3 Rule

- setelah dikunci, siswa tidak dapat mengubah pilihan,
- BK dapat membuka lock jika diperlukan,
- BK dapat melihat rekap seluruh siswa.

Status:

```text
draft
confirmed
locked
```

---

# 13. Modul Karir & Studi Lanjut

Modul ini merupakan salah satu fitur inti BK.

## 13.1 Rencana Setelah Lulus

Pilihan:

```text
college
government_school
military_police
work
entrepreneurship
gap_year
other
```

## 13.2 Jika Memilih Kuliah

Siswa dapat menentukan beberapa prioritas:

```text
Pilihan 1
Pilihan 2
Pilihan 3
```

Setiap pilihan:

- university_id,
- study_program_id,
- admission_path,
- priority,
- notes.

## 13.3 Jika Memilih Bekerja

Field:

- industry,
- company_target,
- position_target,
- city_target,
- notes.

## 13.4 Jika Memilih TNI/POLRI/Kedinasan

Field:

- institution,
- program,
- target_year,
- notes.

## 13.5 Monitoring BK

BK dapat melihat:

- distribusi rencana karir,
- siswa belum menentukan karir,
- kampus pilihan terbanyak,
- program studi terbanyak,
- jalur masuk paling diminati.

---

# 14. Modul Informasi Kampus

## 14.1 Perguruan Tinggi

Field:

- name
- slug
- short_name
- logo
- type
- city
- province
- address
- website
- description
- accreditation
- active

Type:

```text
PTN
PTS
Kedinasan
Luar Negeri
Lainnya
```

## 14.2 Program Studi

Field:

- university_id
- name
- degree
- faculty
- accreditation
- description
- capacity optional

## 14.3 Jalur Masuk

Contoh:

- SNBP
- SNBT
- Mandiri
- Jalur Prestasi
- Kedinasan
- lainnya

Field:

- name
- description
- registration_start
- registration_end
- announcement_date
- official_url

## 14.4 Beasiswa

Field:

- title
- provider
- description
- requirements
- registration_start
- registration_end
- official_url

## 14.5 Favorit Kampus

Siswa dapat menyimpan kampus favorit.

Favorit dapat digunakan sebagai sumber pilihan ketika mengisi rencana karir.

---

# 15. Modul Alumni

## 15.1 Konsep Data

Tidak membuat entitas alumni baru dari nol.

```text
student.status = active
        ↓
student.status = graduated
        ↓
student menjadi bagian data alumni
```

Identitas siswa tetap menggunakan data lama.

## 15.2 Data Alumni Tambahan

- graduation_year
- current_status
- university
- study_program
- admission_path
- company
- position
- military_institution
- entrepreneurship
- tracer_updated_at

## 15.3 Statistik Alumni

BK dapat melihat:

- persentase kuliah,
- bekerja,
- TNI/POLRI,
- kedinasan,
- usaha,
- gap year,
- kampus terbanyak,
- prodi terbanyak,
- tren antar angkatan.

---

# 16. Modul Tracer Study

Tracer study digunakan untuk memperbarui kondisi alumni setelah lulus.

## 16.1 Form Alumni

Alumni dapat mengisi:

- status saat ini,
- kampus,
- program studi,
- pekerjaan,
- instansi,
- kota,
- tahun mulai,
- kontak,
- catatan.

## 16.2 Riwayat

Data tracer dapat memiliki riwayat perubahan agar perkembangan alumni tetap tercatat.

---

# 17. Modul Prestasi

## 17.1 Input Siswa

Field:

- title
- category
- level
- achievement
- organizer
- event_date
- year
- certificate_file
- description

## 17.2 Tingkat

```text
school
district
regency
province
national
international
```

## 17.3 Status

```text
draft
submitted
verified
rejected
```

## 17.4 Verifikasi

Wali kelas atau BK dapat:

- verifikasi,
- tolak,
- memberi catatan.

Prestasi yang sudah verified dapat digunakan untuk laporan sekolah.

---

# 18. Modul Tugas

## 18.1 Guru

Guru membuat tugas dengan:

- title
- description
- class
- subject
- attachment
- published_at
- deadline
- max_score

## 18.2 Siswa

Siswa:

- melihat detail tugas,
- upload jawaban,
- menyimpan draft,
- submit,
- melihat nilai,
- melihat feedback.

## 18.3 Status Submission

```text
draft
submitted
late
graded
```

## 18.4 Rule

- submission setelah deadline dapat ditandai `late`,
- guru dapat mengatur apakah late submission diizinkan,
- guru dapat memberi nilai dan feedback.

---

# 19. Modul CBT

## 19.1 Bank Soal

Jenis soal awal:

```text
multiple_choice
```

Pengembangan berikutnya dapat mendukung:

```text
multiple_answer
essay
true_false
```

Field soal:

- subject,
- question,
- image optional,
- options,
- correct_answer,
- explanation,
- difficulty,
- active.

## 19.2 Paket CBT

Field:

- title
- subject
- class
- duration_minutes
- total_question
- shuffle_questions
- shuffle_options
- show_result
- show_discussion
- passing_score
- start_at
- end_at

## 19.3 Fitur CBT

Wajib:

- timer,
- autosave jawaban,
- random soal,
- random opsi,
- navigasi nomor,
- tandai ragu-ragu,
- auto submit,
- resume jika halaman reload,
- nilai otomatis pilihan ganda,
- riwayat pengerjaan.

## 19.4 Attempt

Satu attempt menyimpan:

- user_id,
- cbt_package_id,
- started_at,
- submitted_at,
- score,
- status,
- remaining_time,
- answers.

Status:

```text
not_started
in_progress
submitted
expired
```

## 19.5 Keamanan CBT

Minimal:

- server-side timer validation,
- validasi jadwal ujian,
- user hanya bisa mengakses paket yang ditugaskan,
- attempt tidak dapat dimodifikasi setelah submit,
- autosave menggunakan endpoint terpisah,
- CSRF aktif,
- audit submit.

---

# 20. Modul Eligible

## 20.1 BK

BK dapat:

- memilih tahun ajaran,
- menentukan siswa eligible,
- import data,
- preview,
- publish,
- unpublish.

## 20.2 Status

```text
eligible
not_eligible
pending
```

## 20.3 Publish Rule

Data hanya dapat dilihat siswa jika:

```text
is_published = true
AND published_at <= current_time
```

---

# 21. Modul Kelulusan

## 21.1 BK

BK dapat:

- input manual,
- import Excel,
- preview,
- menentukan jadwal publish,
- publish.

Status:

```text
graduated
not_graduated
pending
```

## 21.2 Siswa

Siswa hanya dapat melihat hasil jika waktu publish sudah tiba.

Opsional:

- download surat kelulusan PDF,
- nomor surat,
- QR verification.

---

# 22. Modul Pengumuman

Field:

- title
- content
- target_role
- target_class optional
- attachment optional
- published_at
- expired_at optional
- status

Target dapat berupa:

```text
all
student
teacher
homeroom
specific_class
specific_user
```

---

# 23. Modul Monitoring BK

Monitoring harus actionable, bukan hanya statistik.

Contoh filter:

- angkatan,
- kelas,
- status kelengkapan,
- karir,
- TKA,
- nilai,
- prestasi,
- CBT.

Contoh hasil:

```text
Nama          Biodata   Nilai   TKA   Karir   Prestasi
Ahmad         ✓         ✓       ✓     ✓       ✓
Budi          ✓         !       ✓     !       ✓
Citra         !         ✓       !     ✓       !
```

BK harus dapat langsung membuka detail siswa dari tabel tersebut.

---

# 24. Modul Laporan

Laporan minimal:

1. Rekap siswa.
2. Rekap biodata.
3. Rekap absensi.
4. Rekap nilai.
5. Rekap TKA.
6. Rekap prestasi.
7. Rekap karir.
8. Rekap kampus tujuan.
9. Rekap eligible.
10. Rekap kelulusan.
11. Rekap CBT.
12. Rekap alumni.
13. Tracer study.
14. Rekap per kelas.
15. Rekap per angkatan.

Filter umum:

- tanggal,
- tahun ajaran,
- angkatan,
- kelas,
- siswa,
- status.

Export:

- Excel,
- PDF.

---

# 25. Notifikasi Sistem

Notifikasi in-app minimal untuk:

- nilai berhasil diajukan,
- nilai diverifikasi / ditolak,
- prestasi diverifikasi / ditolak,
- tugas baru,
- deadline tugas,
- CBT baru,
- jadwal CBT,
- eligible dipublish,
- kelulusan dipublish,
- pengumuman baru.

Struktur umum:

```text
user_id
type
title
message
url
read_at
created_at
```

---

# 26. Pencarian Global

BK / Super Admin disarankan memiliki pencarian untuk:

- siswa,
- NIS,
- kelas,
- alumni,
- kampus.

---

# 27. Audit Log

Aktivitas penting harus tercatat:

- login,
- perubahan nilai,
- verifikasi nilai,
- perubahan status eligible,
- publish eligible,
- perubahan kelulusan,
- publish kelulusan,
- perubahan data siswa,
- penghapusan data penting.

Field:

```text
user_id
action
module
record_type
record_id
old_value
new_value
ip_address
user_agent
created_at
```

---

# 28. Struktur Database Konseptual

Struktur nama tabel dapat disesuaikan saat implementasi.

## 28.1 Core

```text
users
students
teachers
academic_years
generations
classes
subjects
teacher_classes
teacher_subjects
```

## 28.2 Biodata

```text
student_profiles
student_parents
student_documents
```

## 28.3 Akademik

```text
attendances
student_semester_scores
student_semester_score_details
tka_settings
student_tka_choices
```

## 28.4 Karir

```text
career_plans
career_plan_choices
universities
study_programs
admission_paths
scholarships
student_university_favorites
```

## 28.5 Prestasi

```text
student_achievements
```

## 28.6 Tugas

```text
assignments
assignment_attachments
assignment_submissions
assignment_submission_files
```

## 28.7 CBT

```text
question_banks
questions
question_options
cbt_packages
cbt_package_questions
cbt_assignments
cbt_attempts
cbt_answers
```

## 28.8 Kelulusan

```text
student_eligibilities
student_graduations
announcements
```

## 28.9 Alumni

```text
alumni_profiles
alumni_tracer_histories
```

## 28.10 Sistem

```text
notifications
audit_logs
settings
```

---

# 29. Relasi Utama

```text
academic_years
     │
     └── classes
            │
            └── students
                   │
                   ├── student_profiles
                   ├── attendances
                   ├── student_semester_scores
                   ├── student_tka_choices
                   ├── career_plans
                   ├── student_achievements
                   ├── assignment_submissions
                   ├── cbt_attempts
                   ├── student_eligibilities
                   ├── student_graduations
                   └── alumni_profiles
```

Kampus:

```text
universities
    └── study_programs
```

Guru:

```text
teachers
   ├── teacher_classes
   └── teacher_subjects
```

---

# 30. Status Siswa

Status utama:

```text
active
graduated
inactive
transferred
```

Rule:

- `active` = siswa aktif,
- `graduated` = alumni,
- data historis tidak dihapus ketika status berubah.

---

# 31. Navigation

## 31.1 Siswa

```text
Dashboard

Akademik
├── Absensi
├── Nilai Semester
└── Mapel TKA

BK & Karir
├── Biodata
├── Rencana Karir
├── Informasi Kampus
├── Prestasi
└── Alumni

Pembelajaran
├── Tugas
└── CBT

Informasi
├── Eligible
├── Kelulusan
└── Pengumuman

Profil
```

## 31.2 Wali Kelas

```text
Dashboard
Siswa
Absensi
Nilai
Prestasi
Karir
TKA
Monitoring
Laporan
Profil
```

## 31.3 Guru Mapel

```text
Dashboard
Kelas Saya
Tugas
CBT
Hasil CBT
Profil
```

## 31.4 BK / Super Admin

```text
Dashboard

Master Data
├── Tahun Ajaran
├── Angkatan
├── Kelas
├── Mata Pelajaran
├── Guru
└── Siswa

Akademik
├── Absensi
├── Nilai
└── TKA

BK & Karir
├── Biodata
├── Karir Siswa
├── Kampus
├── Prestasi
└── Alumni

Pembelajaran
├── Tugas
└── CBT

Kelulusan
├── Eligible
├── Kelulusan
└── Pengumuman

Monitoring
Laporan
Pengaturan
```

---

# 32. Functional Requirements

## FR-01 Authentication

- user dapat login,
- sistem redirect berdasarkan role,
- user tidak boleh membuka route role lain.

## FR-02 Student Profile

- siswa dapat mengubah data pribadi yang diizinkan,
- sistem menghitung progress biodata.

## FR-03 Attendance

- siswa melihat riwayat miliknya,
- wali kelas hanya melihat kelasnya,
- BK melihat semua.

## FR-04 Semester Scores

- siswa input nilai,
- siswa submit nilai,
- wali kelas/BK verifikasi,
- verified score terkunci.

## FR-05 TKA

- BK membuka periode,
- siswa memilih,
- siswa konfirmasi,
- BK dapat lock.

## FR-06 Career

- siswa menentukan rencana,
- siswa menambah prioritas,
- BK melihat statistik.

## FR-07 University

- BK CRUD kampus,
- siswa mencari dan favorit kampus.

## FR-08 Achievement

- siswa submit prestasi,
- wali/BK verifikasi.

## FR-09 Assignment

- guru membuat tugas,
- siswa submit,
- guru menilai.

## FR-10 CBT

- guru/BK membuat bank soal,
- sistem membuat attempt,
- autosave,
- timer,
- auto submit,
- skor otomatis.

## FR-11 Eligible

- BK menentukan hasil,
- hasil hanya tampil setelah publish.

## FR-12 Graduation

- BK menentukan kelulusan,
- hasil hanya tampil setelah jadwal publish.

## FR-13 Alumni

- siswa graduated otomatis masuk data alumni.

## FR-14 Reporting

- filter,
- preview,
- export.

---

# 33. Non-Functional Requirements

## 33.1 Performance

Target awal:

- halaman umum < 3 detik pada koneksi normal,
- pagination untuk tabel besar,
- query menggunakan eager loading,
- index database untuk foreign key dan field pencarian.

## 33.2 Responsive

Mendukung minimal:

```text
mobile
tablet
desktop
```

Dashboard siswa wajib nyaman digunakan dari HP.

## 33.3 Security

Minimal:

- CSRF protection,
- authorization policy / middleware,
- server-side validation,
- escaped output default Blade,
- rate limit login,
- password hashing,
- file validation,
- private document access,
- signed/authorized download,
- audit log untuk fitur penting.

## 33.4 Maintainability

- controller tidak terlalu besar,
- gunakan Form Request untuk validasi kompleks,
- gunakan Service untuk proses bisnis kompleks,
- gunakan Policy / middleware untuk authorization,
- query filter dipisahkan jika mulai kompleks,
- Blade component untuk UI yang berulang.

---

# 34. Struktur Laravel yang Direkomendasikan

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── BK/
│   │   ├── Guru/
│   │   ├── WaliKelas/
│   │   └── Siswa/
│   ├── Middleware/
│   └── Requests/
│
├── Models/
├── Policies/
├── Services/
└── Support/

resources/views/
├── layouts/
├── components/
├── admin/
├── bk/
├── guru/
├── wali-kelas/
└── siswa/

routes/
├── web.php
├── admin.php
├── bk.php
├── guru.php
├── wali_kelas.php
└── siswa.php
```

Route per role direkomendasikan agar file route tetap mudah dibaca.

---

# 35. Pola Service yang Disarankan

Tidak semua CRUD harus memakai service.

Gunakan service jika proses memiliki beberapa rule atau transaksi.

Contoh:

```text
ScoreVerificationService
CareerPlanService
CBTAttemptService
EligibilityService
GraduationService
StudentProgressService
TracerStudyService
ReportService
```

CRUD sederhana tetap boleh langsung melalui controller + model.

Tujuannya menghindari over-engineering.

---

# 36. Error Handling

Format pesan harus mudah dimengerti user.

Contoh:

```text
Data berhasil disimpan.
Nilai berhasil diajukan.
Nilai sudah terverifikasi dan tidak dapat diubah.
Waktu pengerjaan CBT telah habis.
Anda tidak memiliki akses ke data ini.
```

Tidak menampilkan stack trace atau error teknis di production.

---

# 37. Empty State

Setiap halaman list harus memiliki empty state.

Contoh:

```text
Belum ada tugas.

Tugas yang diberikan guru akan muncul di halaman ini.
```

Jangan hanya menampilkan tabel kosong.

---

# 38. Search, Filter, Pagination

Halaman data besar wajib mendukung:

- search,
- filter,
- sort,
- pagination.

Contoh:

- siswa,
- alumni,
- nilai,
- prestasi,
- kampus,
- tugas,
- CBT,
- laporan.

DataTables direkomendasikan untuk panel admin/BK/guru.

Untuk daftar siswa di mobile, boleh menggunakan card responsive.

---

# 39. Export

Format export:

- Excel untuk pengolahan data,
- PDF untuk laporan formal.

File export harus mencantumkan:

- nama sekolah,
- judul laporan,
- filter,
- tanggal cetak,
- total data.

---

# 40. Upload File

Konfigurasi awal yang disarankan:

| Jenis | Format |
|---|---|
| Foto | jpg, jpeg, png, webp |
| Sertifikat | pdf, jpg, jpeg, png |
| Tugas | pdf, doc, docx, xls, xlsx, zip |
| Dokumen siswa | pdf, jpg, jpeg, png |
| Lampiran pengumuman | pdf |

Limit file ditentukan melalui setting aplikasi.

File sensitif jangan dibuat public tanpa authorization.

---

# 41. Naming Convention

Database:

```text
snake_case
```

Model:

```text
StudentAchievement
StudentGraduation
CareerPlan
```

Controller:

```text
StudentController
CareerPlanController
CBTController
```

Route:

```text
siswa.nilai.index
siswa.karir.index
bk.siswa.index
bk.eligible.index
guru.tugas.index
```

---

# 42. MVP

Tahap pertama sebaiknya fokus ke modul paling penting.

## Phase 1 — Core BK

- Authentication & role
- Tahun ajaran
- Angkatan
- Kelas
- Guru
- Siswa
- Biodata
- Absensi
- Nilai semester
- TKA
- Karir
- Kampus
- Prestasi
- Monitoring
- Pengumuman
- Laporan dasar

## Phase 2 — Learning

- Tugas
- Submission
- Penilaian
- CBT
- Hasil CBT

## Phase 3 — Kelulusan

- Eligible
- Kelulusan
- PDF pengumuman

## Phase 4 — Alumni

- Alumni
- Tracer study
- Statistik alumni
- Analitik antar angkatan

---

# 43. Acceptance Criteria Umum

Sebuah modul dianggap selesai jika:

1. Authorization sesuai role.
2. Server-side validation tersedia.
3. CRUD utama berjalan.
4. Search/filter bekerja.
5. Responsive.
6. Empty state tersedia.
7. Loading state tersedia.
8. SweetAlert untuk aksi berisiko.
9. Feedback sukses/gagal jelas.
10. DataTables/Select2 tidak rusak di mobile.
11. Tidak ada query N+1 yang signifikan.
12. Audit tersedia untuk aksi kritis.
13. User tidak dapat mengakses record milik user/kelas lain melalui manipulasi URL.

---

# 44. Definition of Done

Sebuah fitur dinyatakan `Done` ketika:

```text
Requirement selesai
→ Database/migration selesai
→ Model & relation selesai
→ Authorization selesai
→ Validation selesai
→ Business rule selesai
→ UI selesai
→ Responsive selesai
→ Error/empty/loading state selesai
→ Testing basic selesai
→ Review akses role selesai
```

---

# 45. Pengembangan Jangka Panjang

Fitur potensial:

- konseling siswa,
- booking jadwal BK,
- catatan konseling privat,
- psikotes/minat bakat,
- rekomendasi jurusan,
- integrasi WhatsApp,
- integrasi email,
- integrasi sistem absensi,
- API mobile,
- dashboard orang tua,
- survey alumni,
- recommendation engine kampus,
- AI assistant BK dengan data yang terkontrol.

---

# 46. Fokus Produk

Prinsip utama sistem:

```text
DATA SISWA
   ↓
AKADEMIK + BK + PRESTASI
   ↓
MONITORING
   ↓
PERSIAPAN KELAS XII
   ↓
KARIR + CBT + ELIGIBLE
   ↓
KELULUSAN
   ↓
ALUMNI
   ↓
TRACER STUDY
   ↓
DATA STRATEGIS BK & SEKOLAH
```

Dengan konsep ini, sistem tidak berhenti sebagai media pembelajaran, tetapi menjadi platform pendamping siswa dari masa sekolah sampai setelah lulus.
