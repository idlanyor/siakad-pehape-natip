<?php

namespace Database\Seeders;

use App\Models\AnggotaOrganisasi;
use App\Models\JabatanOrganisasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StrukturOrganisasiSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan data lama
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        AnggotaOrganisasi::truncate();
        JabatanOrganisasi::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ──────────────────────────────────────────────────
        // LEVEL 0 — ROOT
        // ──────────────────────────────────────────────────
        $komite = $this->jabatan('Komite Sekolah', null, 'koordinasi', 0,
            'Badan mandiri yang mewadahi peran serta masyarakat dalam peningkatan mutu, pemerataan, dan efisiensi pengelolaan pendidikan.');

        $kepsek = $this->jabatan('Kepala Sekolah', null, 'komando', 1,
            'Pimpinan tertinggi sekolah yang bertanggung jawab atas seluruh kegiatan penyelenggaraan pendidikan.');

        $this->anggota($komite->id, 'Drs. H. Bambang Wiyono, M.M.',      nip: '197203141995031002', periode: '2022–2025');
        $this->anggota($kepsek->id, 'Dr. Siti Rahayu Handayani, M.Pd.', nip: '196807201993032001', periode: '2021–2025');

        // ──────────────────────────────────────────────────
        // LEVEL 1 — LANGSUNG DI BAWAH KEPALA SEKOLAH
        // ──────────────────────────────────────────────────
        $wakaKur = $this->jabatan('Waka Bid. Kurikulum',           $kepsek->id, 'komando', 0, 'Mengkoordinasikan penyusunan dan pelaksanaan kurikulum, jadwal pelajaran, dan penilaian.');
        $wakaKes = $this->jabatan('Waka Bid. Kesiswaan',           $kepsek->id, 'komando', 1, 'Mengkoordinasikan pembinaan, pengembangan, dan ketertiban peserta didik.');
        $wakaSar = $this->jabatan('Waka Bid. Sarana & Prasarana',  $kepsek->id, 'komando', 2, 'Mengkoordinasikan pengadaan, pemeliharaan, dan inventarisasi sarana dan prasarana sekolah.');
        $wakaHum = $this->jabatan('Waka Bid. Hubungan Masyarakat', $kepsek->id, 'komando', 3, 'Mengkoordinasikan hubungan sekolah dengan dunia usaha, industri, dan masyarakat.');
        $katuUsd = $this->jabatan('Kepala Tata Usaha',             $kepsek->id, 'komando', 4, 'Mengelola administrasi umum, kepegawaian, keuangan, dan surat-menyurat sekolah.');
        $bpBk    = $this->jabatan('Guru BK / Konselor',            $kepsek->id, 'komando', 5, 'Memberikan layanan bimbingan dan konseling kepada peserta didik.');

        $this->anggota($wakaKur->id, 'Agus Hermawan, S.Pd., M.Pd.',     nip: '197905122005011003', periode: '2022–2024');
        $this->anggota($wakaKes->id, 'Dra. Nurul Hidayah, M.Si.',       nip: '197112032000122001', periode: '2022–2024');
        $this->anggota($wakaSar->id, 'Ir. Sugeng Prabowo, M.T.',        nip: '196904181997031004', periode: '2022–2024');
        $this->anggota($wakaHum->id, 'Hj. Rina Kusumawati, S.E., M.M.',nip: '197408252003122002', periode: '2022–2024');
        $this->anggota($katuUsd->id, 'Drs. Purwanto Adi Nugroho',       nip: '196606051991031007', periode: '2020–sekarang');
        $this->anggota($bpBk->id,   'Siti Aminah, S.Psi., M.Psi.',     nip: '198302172009022003', periode: '2021–sekarang');

        // ──────────────────────────────────────────────────
        // LEVEL 2 — DI BAWAH WAKA KURIKULUM
        // ──────────────────────────────────────────────────
        $tkj            = $this->jabatan('Kaproli Teknik Komputer & Jaringan',   $wakaKur->id, 'komando', 0, 'Memimpin program keahlian TKJ dan mengkoordinasikan guru produktif TKJ.');
        $akl            = $this->jabatan('Kaproli Akuntansi & Keu. Lembaga',     $wakaKur->id, 'komando', 1, 'Memimpin program keahlian AKL dan mengkoordinasikan guru produktif AKL.');
        $bdp            = $this->jabatan('Kaproli Bisnis Daring & Pemasaran',    $wakaKur->id, 'komando', 2, 'Memimpin program keahlian BDP dan mengkoordinasikan guru produktif BDP.');
        $koordPenilaian = $this->jabatan('Koordinator Penilaian & Evaluasi',     $wakaKur->id, 'komando', 3, 'Mengkoordinasikan pelaksanaan penilaian harian, PTS, PAS, dan ujian sekolah.');
        $perpus         = $this->jabatan('Petugas Perpustakaan',                 $wakaKur->id, 'komando', 4, 'Mengelola koleksi buku, pelayanan peminjaman, dan administrasi perpustakaan.');

        $this->anggota($tkj->id,            'Hendra Setiawan, S.Kom., M.T.',  nip: '198407162009021004');
        $this->anggota($akl->id,            'Dra. Sri Wahyuni, M.Ak.',        nip: '197001121997032005');
        $this->anggota($bdp->id,            'Novi Andriyanti, S.E., M.M.',    nip: '198209042010012008');
        $this->anggota($koordPenilaian->id, 'Budi Santoso, S.Pd.',            nip: '197803201999031006');
        $this->anggota($perpus->id,         'Endang Sulistyowati, A.Md.',     nip: '198511252010012015');

        // ──────────────────────────────────────────────────
        // LEVEL 2 — DI BAWAH WAKA KESISWAAN
        // ──────────────────────────────────────────────────
        $osis   = $this->jabatan('Pembina OSIS',            $wakaKes->id, 'komando', 0, 'Membina dan mendampingi kegiatan OSIS serta organisasi intra sekolah.');
        $ekskul = $this->jabatan('Koordinator Ekskul',      $wakaKes->id, 'komando', 1, 'Mengkoordinasikan seluruh kegiatan ekstrakurikuler dan pembina ekskul.');
        $uks    = $this->jabatan('Koordinator UKS',         $wakaKes->id, 'komando', 2, 'Mengelola Unit Kesehatan Sekolah dan program kesehatan peserta didik.');
        $tatib  = $this->jabatan('Koordinator Tata Tertib', $wakaKes->id, 'komando', 3, 'Menegakkan disiplin dan tata tertib peserta didik sesuai peraturan sekolah.');

        $this->anggota($osis->id,   'Ratna Dewi Kusuma, S.Pd.', nip: '198610082011012017');
        $this->anggota($ekskul->id, 'Wahyu Tri Handoko, S.Pd.', nip: '198912202014021003');
        $this->anggota($uks->id,    'Dewi Puspitasari, S.Kep.', nip: '199002152015032011');
        $this->anggota($tatib->id,  'Agung Prasetyo, S.Pd.',    nip: '198705172012011009');

        // ──────────────────────────────────────────────────
        // LEVEL 2 — DI BAWAH WAKA SARANA & PRASARANA
        // ──────────────────────────────────────────────────
        $teknisi    = $this->jabatan('Teknisi Lab. Komputer',      $wakaSar->id, 'komando', 0, 'Merawat, memperbaiki, dan mengoperasikan perangkat laboratorium komputer.');
        $laboran    = $this->jabatan('Laboran IPA',                $wakaSar->id, 'komando', 1, 'Mengelola dan menyiapkan alat/bahan praktikum laboratorium IPA.');
        $inventaris = $this->jabatan('Petugas Inventaris',         $wakaSar->id, 'komando', 2, 'Mencatat, melabeli, dan melaporkan seluruh aset/inventaris sekolah.');
        $kebersihan = $this->jabatan('Petugas Kebersihan',         $wakaSar->id, 'komando', 3, 'Menjaga kebersihan dan kerapian lingkungan sekolah setiap hari.');
        $penjaga    = $this->jabatan('Penjaga Sekolah / Satpam',   $wakaSar->id, 'komando', 4, 'Menjaga keamanan, ketertiban, dan keselamatan warga sekolah selama 24 jam.');

        $this->anggota($teknisi->id,    'Deni Kurniawan, S.Kom.',       nip: '199207182016031002');
        $this->anggota($laboran->id,    'Yuli Prasetyaningsih, S.Si.',  nip: '198904122017032005');
        $this->anggota($inventaris->id, 'Marsono',                      nip: '197511031998031003');
        $this->anggota($kebersihan->id, 'Slamet Riyadi',                nip: '197803272005011002');
        $this->anggota($penjaga->id,    'Suparno',                      nip: '196905151990031004');

        // ──────────────────────────────────────────────────
        // LEVEL 2 — DI BAWAH WAKA HUMAS
        // ──────────────────────────────────────────────────
        $koordPkl  = $this->jabatan('Koordinator Prakerin / PKL',    $wakaHum->id, 'komando', 0, 'Mengkoordinasikan penempatan siswa PKL di dunia usaha dan industri.');
        $koordAlum = $this->jabatan('Koordinator Penelusuran Alumni', $wakaHum->id, 'komando', 1, 'Memantau perkembangan karier alumni dan menjalin jejaring dengan lulusan.');
        $koordDudi = $this->jabatan('Koordinator Kerja Sama DUDI',    $wakaHum->id, 'komando', 2, 'Menjalin dan memelihara kemitraan dengan dunia usaha dan industri.');

        $this->anggota($koordPkl->id,  'Fitri Handayani, S.Pd.',    nip: '198803122013012006');
        $this->anggota($koordAlum->id, 'Rizky Aditya Putra, S.E.',  nip: '199104252016031007');
        $this->anggota($koordDudi->id, 'Drs. Sumaryono, M.M.',      nip: '196802201994031005');

        // ──────────────────────────────────────────────────
        // LEVEL 2 — DI BAWAH KEPALA TATA USAHA
        // ──────────────────────────────────────────────────
        $bendahara = $this->jabatan('Bendahara Sekolah',          $katuUsd->id, 'komando', 0, 'Mengelola keuangan sekolah, pembayaran SPP, dan pelaporan keuangan.');
        $staffTu   = $this->jabatan('Staf Tata Usaha',            $katuUsd->id, 'komando', 1, 'Menangani administrasi umum, surat-menyurat, dan pengarsipan dokumen.');
        $operator  = $this->jabatan('Operator Sekolah / Dapodik', $katuUsd->id, 'komando', 2, 'Mengelola data pokok pendidikan (Dapodik) dan sistem informasi sekolah.');

        $this->anggota($bendahara->id, 'Hj. Sularsi, S.E.',          nip: '197409182000032002');
        $this->anggota($staffTu->id,   'Dwi Rahayu Ningsih, S.Sos.', nip: '198601112009012014');
        $this->anggota($operator->id,  'Fajar Nugroho, S.Kom.',      nip: '199308062017031003');
    }

    private function jabatan(
        string  $nama,
        ?int    $parentId,
        string  $tipe      = 'komando',
        int     $urutan    = 0,
        ?string $deskripsi = null
    ): JabatanOrganisasi {
        return JabatanOrganisasi::create([
            'nama_jabatan'  => $nama,
            'parent_id'     => $parentId,
            'tipe_hubungan' => $tipe,
            'urutan'        => $urutan,
            'deskripsi'     => $deskripsi,
        ]);
    }

    private function anggota(
        int     $jabatanId,
        string  $nama,
        ?int    $guruId  = null,
        ?string $nip     = null,
        ?string $periode = null
    ): AnggotaOrganisasi {
        return AnggotaOrganisasi::create([
            'jabatan_organisasi_id' => $jabatanId,
            'nama'                  => $nama,
            'guru_id'               => $guruId,
            'nip'                   => $nip,
            'periode'               => $periode,
            'is_aktif'              => true,
        ]);
    }
}
