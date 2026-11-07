<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Surat Keterangan Lulus</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Serif', 'Times New Roman', Times, serif;
            font-size: 9pt;
            color: #000;
            background: #fff;
            line-height: 1.2;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .footer img {
            width: 100%;
            display: block;
        }

        .page {
            padding: 0px 45px 120px 45px;
        }

        .kop img {
            width: 100%;
            display: block;
        }

        .judul {
            text-align: center;
            margin: 16px 0 16px 0;
        }

        .judul-text {
            font-family: Arial, sans-serif;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
        }

        .pembuka {
            font-size: 9pt;
            margin-bottom: 4px;
        }

        .daftar td {
            font-size: 9pt;
        }

        .menerangkan {
            font-size: 9pt;
            margin-bottom: 2px;
        }

        .biodata {
            width: 100%;
            border-collapse: collapse;
        }

        .biodata td {
            font-size: 9pt;
            padding: 0;
            vertical-align: top;
        }

        .b-label {
            width: 220px;
            padding-left: 8px;
            white-space: nowrap;
        }

        .b-sep {
            width: 16px;
        }

        .ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .td-kanan {
            width: 50%;
            text-align: left;
            padding-left: 60px;
        }

        .ttd-kota {
            margin-bottom: 2px;
        }

        .ttd-jabatan {
            margin-bottom: 4px;
        }

        .ttd-gambar {
            position: relative;
            width: 160px;
            height: 120px;
            margin: 0 auto;
        }

        .ttd-gambar img.g-cap {
            position: absolute;
            width: 2.3in;
            left: -200px;
            top: -80px;
        }

        .ttd-gambar img.g-ttd {
            position: absolute;
            width: 2.8in;
            left: -110px;
            top: -110px;
        }

        .ttd-nama {
            font-weight: bold;
            margin-top: 4px;
        }

        .ttd-nip {
            font-size: 9pt;
        }

        .ket {
            margin-top: 40px;
            padding-top: 10px;
            font-size: 9pt;
        }
    </style>
</head>

<body>

    <div class="footer">
        <img src="{{ $footer }}" alt="Footer">
    </div>

    <div class="page">

        <div class="kop">
            <img src="{{ $kop }}" alt="Kop Surat">
        </div>

        <div class="judul">
            <span class="judul-text">SURAT KETERANGAN LULUS</span>
        </div>

        <br><br>
        <div style="padding: 0 30px">
            <p class="pembuka">
                Kepala {{ $schoolName }} Tahun Pelajaran {{ $schoolYear }}, dengan berdasarkan:
            </p>
            <br>

            <div style="padding: 0 30px">
                <ol class="daftar">
                    <li>Penyelesaian seluruh program pembelajaran pada Kurikulum Nasional;</li>
                    <li>Kriteria kelulusan dari satuan pendidikan sesuai dengan peraturan perundang-undangan;</li>
                    <li>Rapat Pleno Dewan Guru tentang Penetapan Kelulusan pada tanggal {{ $announcement->meeting_date->locale('id')->translatedFormat('j F Y') }};</li>
                </ol>
            </div>

            <br>
            <p class="menerangkan">Menerangkan bahwa:</p>
            <br>

            <div style="padding: 0 50px">
                <table class="biodata">
                    <tr>
                        <td class="b-label">Nama</td>
                        <td class="b-sep">:</td>
                        <td class="b-val"><strong>{{ mb_strtoupper($student->name) }}</strong></td>
                    </tr>
                    <tr>
                        <td class="b-label">Tempat dan Tanggal Lahir</td>
                        <td class="b-sep">:</td>
                        <td class="b-val">{{ $student->profile->birth_place }},
                            {{ $student->profile->birth_date->locale('id')->translatedFormat('j F Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="b-label">Nama Orang Tua</td>
                        <td class="b-sep">:</td>
                        <td class="b-val">{{ mb_strtoupper($row['parent']) }}</td>
                    </tr>
                    <tr>
                        <td class="b-label">Nomor Induk Siswa</td>
                        <td class="b-sep">:</td>
                        <td class="b-val">{{ $student->nis }}</td>
                    </tr>
                    <tr>
                        <td class="b-label">Nomor Induk Siswa Nasional</td>
                        <td class="b-sep">:</td>
                        <td class="b-val">{{ $student->nisn }}</td>
                    </tr>
                    <tr>
                        <td class="b-label">Mapel Pilihan</td>
                        <td class="b-sep">:</td>
                        <td class="b-val">
                            {{ $chosenSubjects ?: '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="b-label">Dinyatakan</td>
                        <td class="b-sep">:</td>
                        <td class="b-val"><strong>LULUS</strong></td>
                    </tr>
                    <tr>
                        <td class="b-label">dengan Rata-rata Nilai*</td>
                        <td class="b-sep">:</td>
                        <td class="b-val"><strong>{{ number_format((float) $row['average'], 2, ',', '.') }}</strong>
                        </td>
                    </tr>
                </table>
            </div>

            <table class="ttd">
                <tr>
                    <td class="td-kiri"></td>
                    <td class="td-kanan">
                        <div class="ttd-kota">{{ $announcement->issued_place }}, {{ $announcement->published_at->locale('id')->translatedFormat('j F Y') }}</div>
                        <div class="ttd-jabatan">Kepala Sekolah,</div>
                        <div class="ttd-gambar">
                            <img class="g-cap" src="{{ $cap }}" alt="Cap Sekolah">
                            <img class="g-ttd" src="{{ $ttd }}" alt="Tanda Tangan">
                        </div>
                        <div class="ttd-nama">{{ $announcement->principal_name }}</div>
                        @if ($announcement->principal_nip)
                            <div class="ttd-nip">NIP. {{ $announcement->principal_nip }}</div>
                        @endif
                    </td>
                </tr>
            </table>

            <br><br>
            <div class="ket">
                Keterangan:<br>
                *) &nbsp;rata-rata nilai peserta didik yang sama dengan nilai yang akan ditulis dalam Transkrip Nilai
                Ijazah.
            </div>
        </div>

    </div>
</body>

</html>
