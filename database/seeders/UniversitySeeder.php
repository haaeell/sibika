<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        $universities = [
            ['UI', 'Universitas Indonesia', 'negeri'],
            ['ITB', 'Institut Teknologi Bandung', 'negeri'],
            ['UGM', 'Universitas Gadjah Mada', 'negeri'],
            ['IPB', 'IPB University', 'negeri'],
            ['UNAIR', 'Universitas Airlangga', 'negeri'],
            ['UNPAD', 'Universitas Padjadjaran', 'negeri'],
            ['UNDIP', 'Universitas Diponegoro', 'negeri'],
            ['ITS', 'Institut Teknologi Sepuluh Nopember', 'negeri'],
            ['UB', 'Universitas Brawijaya', 'negeri'],
            ['UNHAS', 'Universitas Hasanuddin', 'negeri'],
            ['UNS', 'Universitas Sebelas Maret', 'negeri'],
            ['UNY', 'Universitas Negeri Yogyakarta', 'negeri'],
            ['BINUS', 'BINUS University', 'swasta'],
            ['TEL-U', 'Telkom University', 'swasta'],
            ['USAKTI', 'Universitas Trisakti', 'swasta'],
            ['UAJ', 'Universitas Katolik Indonesia Atma Jaya', 'swasta'],
            ['UG', 'Universitas Gunadarma', 'swasta'],
            ['UII', 'Universitas Islam Indonesia', 'swasta'],
            ['UMY', 'Universitas Muhammadiyah Yogyakarta', 'swasta'],
            ['UPH', 'Universitas Pelita Harapan', 'swasta'],
            ['PRASMUL', 'Universitas Prasetiya Mulya', 'swasta'],
            ['UMB', 'Universitas Mercu Buana', 'swasta'],
            ['PRESUNIV', 'President University', 'swasta'],
            ['LSPR', 'LSPR Institute of Communication and Business', 'swasta'],
            ['PKN STAN', 'Politeknik Keuangan Negara STAN', 'kedinasan'],
            ['IPDN', 'Institut Pemerintahan Dalam Negeri', 'kedinasan'],
            ['POLSTAT STIS', 'Politeknik Statistika STIS', 'kedinasan'],
            ['STIN', 'Sekolah Tinggi Intelijen Negara', 'kedinasan'],
            ['POLTEK SSN', 'Politeknik Siber dan Sandi Negara', 'kedinasan'],
            ['STMKG', 'Sekolah Tinggi Meteorologi Klimatologi dan Geofisika', 'kedinasan'],
            ['POLTEKIM', 'Politeknik Imigrasi', 'kedinasan'],
            ['POLTEKIP', 'Politeknik Ilmu Pemasyarakatan', 'kedinasan'],
            ['PTDI-STTD', 'Politeknik Transportasi Darat Indonesia - STTD', 'kedinasan'],
            ['AKMIL', 'Akademi Militer', 'kedinasan'],
            ['AAL', 'Akademi Angkatan Laut', 'kedinasan'],
            ['AAU', 'Akademi Angkatan Udara', 'kedinasan'],
            ['AKPOL', 'Akademi Kepolisian', 'kedinasan'],
        ];

        foreach ($universities as [$shortName, $name, $type]) {
            University::updateOrCreate(
                ['name' => $name],
                ['short_name' => $shortName, 'type' => $type, 'is_active' => true]
            );
        }
    }
}
