<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jadwal;
use App\Models\Semester;
use App\Models\TahunAjaran;

use App\Models\Absensi;
use App\Models\Nilai;
use App\Models\TempatPkl;
use App\Models\PenempatanPkl;
use App\Models\JurnalPkl;
use App\Models\AbsensiPkl;
use App\Models\NilaiPkl;
use App\Models\Pelanggaran;
use App\Models\Prestasi;
use App\Models\Ekskul;
use App\Models\AnggotaEkskul;
use App\Models\AbsensiEkskul;
use App\Models\SuratKeterangan;
use App\Models\TarifSpp;
use App\Models\TagihanSpp;
use App\Models\PembayaranSpp;
use App\Models\PendaftarPpdb;
use App\Models\GuruPermission;
use App\Models\Galeri;
use App\Models\GaleriItem;
use App\Models\Artikel;
use App\Models\HalamanStatis;
use App\Models\GelombangPpdb;
use App\Models\PersyaratanPpdb;
use App\Models\FaqPpdb;
use App\Models\Pengumuman;

class LengkapSeeder extends Seeder
{
    public function run(): void
    {
        // ── Ambil data existing ─────────────────────────────
        $admin        = User::where('role', 0)->first();
        $semester     = Semester::where('aktif', true)->first();
        $tahunAjaran  = TahunAjaran::where('aktif', true)->first();
        $gurus        = Guru::with('user')->get();
        $siswas       = Siswa::all();
        $jadwals      = Jadwal::all();
        $guru1        = $gurus->first();
        $guru2        = $gurus->skip(1)->first() ?? $guru1;

        // ── 1. ABSENSI KELAS ───────────────────────────────
        if (DB::table('absensi')->count() === 0 && $jadwals->count() > 0) {
            $statuses = ['Hadir', 'Hadir', 'Hadir', 'Hadir', 'Izin', 'Sakit', 'Alpa'];
            $tanggals = [
                Carbon::now()->subWeeks(3)->startOfWeek()->addDays(1),
                Carbon::now()->subWeeks(2)->startOfWeek()->addDays(1),
                Carbon::now()->subWeeks(1)->startOfWeek()->addDays(1),
            ];

            foreach ($jadwals->take(3) as $jadwal) {
                $siswaKelas = Siswa::where('kelas_id', $jadwal->kelas_id)->get();
                foreach ($tanggals as $tgl) {
                    foreach ($siswaKelas as $siswa) {
                        Absensi::firstOrCreate(
                            ['jadwal_id' => $jadwal->id, 'siswa_id' => $siswa->id, 'tanggal' => $tgl->toDateString()],
                            ['status' => $statuses[array_rand($statuses)], 'dicatat_oleh' => $jadwal->guru_id]
                        );
                    }
                }
            }
            $this->info('✓ Absensi kelas');
        }

        // ── 2. NILAI ────────────────────────────────────────
        if (Nilai::count() === 0 && $jadwals->count() > 0) {
            foreach ($jadwals as $jadwal) {
                $siswaKelas = Siswa::where('kelas_id', $jadwal->kelas_id)->get();
                foreach ($siswaKelas as $siswa) {
                    Nilai::firstOrCreate(
                        ['jadwal_id' => $jadwal->id, 'siswa_id' => $siswa->id],
                        [
                            'tugas'       => rand(70, 95),
                            'pts'         => rand(65, 90),
                            'pas'         => rand(70, 92),
                            'praktik'     => rand(75, 95),
                            'proyek'      => rand(70, 90),
                            'portofolio'  => rand(72, 93),
                            'catatan'     => null,
                            'dicatat_oleh'=> $jadwal->guru_id,
                        ]
                    );
                }
            }
            $this->info('✓ Nilai');
        }

        // ── 3. TEMPAT PKL ───────────────────────────────────
        if (TempatPkl::count() === 0) {
            $tempatPkls = [
                ['nama' => 'PT Telkom Indonesia Tbk', 'bidang_usaha' => 'Telekomunikasi', 'alamat' => 'Jl. Gatot Subroto No.52, Jakarta Selatan', 'kontak_person' => 'Bpk. Darmawan', 'no_hp' => '02152900000', 'kuota' => 5],
                ['nama' => 'CV Nusantara Digital', 'bidang_usaha' => 'Teknologi Informasi', 'alamat' => 'Jl. Raya Bogor No.17, Depok', 'kontak_person' => 'Ibu Sari', 'no_hp' => '02187654321', 'kuota' => 4],
                ['nama' => 'Bank Mandiri KCP Selatan', 'bidang_usaha' => 'Perbankan', 'alamat' => 'Jl. Fatmawati No.22, Jakarta Selatan', 'kontak_person' => 'Bpk. Hendra', 'no_hp' => '02178563421', 'kuota' => 3],
                ['nama' => 'PT Gojek Indonesia', 'bidang_usaha' => 'Teknologi & Transportasi', 'alamat' => 'Jl. Kemang Raya No.9, Jakarta Selatan', 'kontak_person' => 'Ibu Dewi', 'no_hp' => '02198765432', 'kuota' => 6],
                ['nama' => 'Dinas Kominfo Kota', 'bidang_usaha' => 'Pemerintahan / Kominfo', 'alamat' => 'Jl. Kebon Sirih No.18, Jakarta Pusat', 'kontak_person' => 'Bpk. Suryadi', 'no_hp' => '02134567890', 'kuota' => 4],
            ];
            $tempatList = collect($tempatPkls)->map(fn($t) => TempatPkl::create($t));
            $this->info('✓ Tempat PKL');

            // ── 4. PENEMPATAN PKL ───────────────────────────
            if (PenempatanPkl::count() === 0 && $siswas->count() > 0) {
                foreach ($siswas->take(3) as $i => $siswa) {
                    $tempat = $tempatList->get($i % $tempatList->count());
                    $pen = PenempatanPkl::create([
                        'siswa_id'           => $siswa->id,
                        'tempat_pkl_id'      => $tempat->id,
                        'guru_pembimbing_id' => ($i % 2 === 0 ? $guru1->id : $guru2->id),
                        'semester_id'        => $semester->id,
                        'periode_mulai'      => Carbon::now()->subMonths(3)->toDateString(),
                        'periode_selesai'    => Carbon::now()->subMonths(1)->toDateString(),
                        'status'             => 'selesai',
                    ]);

                    // ── 5. JURNAL PKL ────────────────────────
                    for ($d = 1; $d <= 5; $d++) {
                        JurnalPkl::create([
                            'penempatan_pkl_id' => $pen->id,
                            'tanggal'           => Carbon::now()->subMonths(3)->addDays($d)->toDateString(),
                            'kegiatan'          => 'Melaksanakan kegiatan PKL hari ke-' . $d . ': membantu tim teknis, instalasi perangkat, dan dokumentasi pekerjaan lapangan.',
                            'foto'              => null,
                            'keterangan'        => 'Berjalan lancar',
                        ]);
                    }

                    // ── 6. ABSENSI PKL ───────────────────────
                    for ($d = 1; $d <= 5; $d++) {
                        AbsensiPkl::create([
                            'penempatan_pkl_id' => $pen->id,
                            'tanggal'           => Carbon::now()->subMonths(3)->addDays($d)->toDateString(),
                            'jam_masuk'         => '08:00:00',
                            'jam_pulang'        => '16:00:00',
                            'latitude'          => -6.2088 + (rand(-100, 100) / 10000),
                            'longitude'         => 106.8456 + (rand(-100, 100) / 10000),
                        ]);
                    }

                    // ── 7. NILAI PKL ─────────────────────────
                    NilaiPkl::create([
                        'penempatan_pkl_id' => $pen->id,
                        'nilai_pembimbing'  => rand(75, 95),
                        'nilai_du_di'       => rand(78, 95),
                        'nilai_sikap'       => rand(80, 98),
                        'catatan'           => 'Siswa menunjukkan dedikasi dan tanggung jawab yang baik selama PKL.',
                        'dicatat_oleh'      => $guru1->user_id,
                    ]);
                }
                $this->info('✓ Penempatan, Jurnal, Absensi & Nilai PKL');
            }
        }

        // ── 8. PELANGGARAN ──────────────────────────────────
        if (Pelanggaran::count() === 0 && $siswas->count() > 0) {
            $jenisList = ['Terlambat masuk sekolah', 'Tidak memakai seragam lengkap', 'Membawa HP saat pelajaran', 'Tidak mengerjakan tugas'];
            $poinList  = [5, 10, 15, 10];
            foreach ($siswas->take(2) as $siswa) {
                foreach ([0, 1] as $k) {
                    Pelanggaran::create([
                        'siswa_id'    => $siswa->id,
                        'tanggal'     => Carbon::now()->subDays(rand(7, 60))->toDateString(),
                        'jenis'       => $jenisList[$k],
                        'deskripsi'   => 'Siswa ' . $jenisList[$k] . ' tanpa keterangan.',
                        'poin'        => $poinList[$k],
                        'dicatat_oleh'=> $admin->id,
                    ]);
                }
            }
            $this->info('✓ Pelanggaran');
        }

        // ── 9. PRESTASI ─────────────────────────────────────
        if (Prestasi::count() === 0 && $siswas->count() > 0) {
            $daftarPrestasi = [
                [$siswas->get(0)->id, 'Juara 1 LKS Bidang TKJ Tingkat Kota', 'Kabupaten/Kota', '2025-03-15'],
                [$siswas->get(1)->id ?? $siswas->get(0)->id, 'Juara 2 Olimpiade Matematika Tingkat Provinsi', 'Provinsi', '2025-05-20'],
                [$siswas->get(2)->id ?? $siswas->get(0)->id, 'Finalis LKS Bidang Web Design Tingkat Nasional', 'Nasional', '2024-11-10'],
            ];
            foreach ($daftarPrestasi as [$siswaId, $nama, $tingkat, $tgl]) {
                Prestasi::create([
                    'siswa_id'       => $siswaId,
                    'tanggal'        => $tgl,
                    'nama_prestasi'  => $nama,
                    'tingkat'        => $tingkat,
                    'keterangan'     => 'Mewakili sekolah dalam ' . $nama . '.',
                    'foto'           => null,
                    'dicatat_oleh'   => $admin->id,
                ]);
            }
            $this->info('✓ Prestasi');
        }

        // ── 10. EKSKUL ──────────────────────────────────────
        if (Ekskul::count() === 0) {
            $daftarEkskul = [
                ['nama' => 'Pramuka', 'pembina_id' => $guru1->id, 'deskripsi' => 'Kegiatan kepanduan untuk membentuk karakter disiplin dan kemandirian siswa.'],
                ['nama' => 'OSIS', 'pembina_id' => $guru2->id, 'deskripsi' => 'Organisasi intra sekolah sebagai wadah pengembangan kepemimpinan siswa.'],
                ['nama' => 'English Club', 'pembina_id' => $guru1->id, 'deskripsi' => 'Klub bahasa Inggris untuk meningkatkan kemampuan komunikasi internasional.'],
                ['nama' => 'Futsal', 'pembina_id' => $guru2->id, 'deskripsi' => 'Kegiatan olahraga futsal untuk pembinaan bakat dan kebugaran siswa.'],
                ['nama' => 'Robotika', 'pembina_id' => $guru1->id, 'deskripsi' => 'Club robotika untuk mengembangkan kreativitas dan kemampuan teknologi siswa.'],
            ];
            $ekskuls = collect($daftarEkskul)->map(fn($e) => Ekskul::create($e));

            // ── 11. ANGGOTA EKSKUL ───────────────────────────
            if (AnggotaEkskul::count() === 0 && $siswas->count() > 0) {
                foreach ($ekskuls as $ekskul) {
                    foreach ($siswas->take(3) as $siswa) {
                        AnggotaEkskul::firstOrCreate([
                            'ekskul_id'      => $ekskul->id,
                            'siswa_id'       => $siswa->id,
                            'tahun_ajaran_id'=> $tahunAjaran->id,
                        ]);
                    }
                }
            }

            // ── 12. ABSENSI EKSKUL ───────────────────────────
            if (AbsensiEkskul::count() === 0 && $siswas->count() > 0) {
                $statuses = ['Hadir', 'Hadir', 'Hadir', 'Izin', 'Alpa'];
                foreach ($ekskuls->take(2) as $ekskul) {
                    for ($w = 1; $w <= 3; $w++) {
                        $tgl = Carbon::now()->subWeeks($w)->startOfWeek()->addDays(4)->toDateString();
                        foreach ($siswas->take(3) as $siswa) {
                            AbsensiEkskul::firstOrCreate(
                                ['ekskul_id' => $ekskul->id, 'siswa_id' => $siswa->id, 'tanggal' => $tgl],
                                ['status' => $statuses[array_rand($statuses)], 'dicatat_oleh' => $ekskul->pembina_id]
                            );
                        }
                    }
                }
            }
            $this->info('✓ Ekskul, Anggota & Absensi Ekskul');
        }

        // ── 13. SURAT KETERANGAN ────────────────────────────
        if (SuratKeterangan::count() === 0 && $siswas->count() > 0) {
            $jenisList = ['aktif', 'aktif', 'pindah'];
            foreach ($siswas->take(3) as $i => $siswa) {
                SuratKeterangan::create([
                    'siswa_id'      => $siswa->id,
                    'jenis_surat'   => $jenisList[$i],
                    'nomor_surat'   => '421/' . str_pad($i + 1, 3, '0', STR_PAD_LEFT) . '/SK/' . date('Y'),
                    'tanggal_surat' => Carbon::now()->subDays(rand(1, 30))->toDateString(),
                    'keterangan'    => 'Diterbitkan untuk keperluan administrasi ' . $jenisList[$i] . '.',
                    'dicatat_oleh'  => $admin->id,
                ]);
            }
            $this->info('✓ Surat Keterangan');
        }

        // ── 14. TARIF SPP ───────────────────────────────────
        if (TarifSpp::count() === 0) {
            $kelas = Kelas::all();
            foreach ($kelas as $k) {
                TarifSpp::firstOrCreate(
                    ['kelas_id' => $k->id, 'tahun_ajaran_id' => $tahunAjaran->id],
                    ['nominal' => 300000]
                );
            }
            $this->info('✓ Tarif SPP');
        }

        // ── 15. TAGIHAN SPP ─────────────────────────────────
        if (TagihanSpp::count() === 0 && $siswas->count() > 0) {
            $bulanList = ['2025-08', '2025-09', '2025-10', '2025-11', '2025-12', '2026-01', '2026-02', '2026-03', '2026-04', '2026-05'];
            foreach ($siswas as $siswa) {
                foreach ($bulanList as $bulan) {
                    TagihanSpp::create([
                        'siswa_id'    => $siswa->id,
                        'bulan'       => $bulan . '-01',
                        'nominal'     => 300000,
                        'status'      => 'belum',
                        'keterangan'  => null,
                        'dicatat_oleh'=> $admin->id,
                    ]);
                }
            }

            // ── 16. PEMBAYARAN SPP ───────────────────────────
            // Bayar beberapa tagihan untuk siswa pertama
            if (PembayaranSpp::count() === 0) {
                $tagihans = TagihanSpp::where('siswa_id', $siswas->first()->id)
                    ->orderBy('bulan')->take(6)->get();
                foreach ($tagihans as $tagihan) {
                    $bayar = PembayaranSpp::create([
                        'tagihan_spp_id' => $tagihan->id,
                        'tanggal_bayar'  => Carbon::parse($tagihan->bulan)->addDays(rand(1, 20))->toDateString(),
                        'nominal_bayar'  => 300000,
                        'metode'         => ['tunai', 'transfer', 'qris'][rand(0, 2)],
                        'bukti'          => null,
                        'catatan'        => null,
                        'dicatat_oleh'   => $admin->id,
                    ]);
                    $tagihan->update(['status' => 'lunas']);
                }
            }
            $this->info('✓ Tagihan & Pembayaran SPP');
        }

        // ── 17. GURU PERMISSIONS ────────────────────────────
        if (GuruPermission::count() === 0) {
            foreach ($gurus as $guru) {
                GuruPermission::firstOrCreate(['guru_id' => $guru->id], [
                    'menu_jadwal'  => true, 'menu_mapel'   => true,
                    'menu_absensi' => true, 'menu_nilai'   => true,
                    'menu_pkl'     => true, 'menu_ekskul'  => true,
                ]);
            }
            $this->info('✓ Guru Permissions');
        }

        // ── 18. GELOMBANG PPDB (harus sebelum pendaftar) ────
        if (\App\Models\GelombangPpdb::count() === 0) {
            $gel1 = GelombangPpdb::create([
                'nama' => 'Gelombang 1', 'tahun_ajaran' => '2025/2026',
                'tanggal_buka' => '2025-06-01', 'tanggal_tutup' => '2025-06-30',
                'kuota' => 120, 'keterangan' => 'Pendaftaran gelombang pertama.', 'is_aktif' => false,
            ]);
            GelombangPpdb::create([
                'nama' => 'Gelombang 2', 'tahun_ajaran' => '2025/2026',
                'tanggal_buka' => '2025-07-01', 'tanggal_tutup' => '2025-07-20',
                'kuota' => 60, 'keterangan' => 'Pendaftaran gelombang kedua.', 'is_aktif' => false,
            ]);
            $this->info('✓ Gelombang PPDB');
        } else {
            $gel1 = \App\Models\GelombangPpdb::first();
        }

        // ── 19. PENDAFTAR PPDB ───────────────────────────────
        if (PendaftarPpdb::count() === 0) {
            $jurusans = \App\Models\Jurusan::all();
            $pendaftarData = [
                ['Aditya Ramadhan Putra', 'aditya.r@example.com', '0813001', 'L', 'pending'],
                ['Bunga Citra Lestari', 'bunga.c@example.com', '0813002', 'P', 'diterima'],
                ['Candra Wijaya', 'candra.w@example.com', '0813003', 'L', 'diterima'],
                ['Dina Maulida', 'dina.m@example.com', '0813004', 'P', 'ditolak'],
                ['Eka Prasetyo', 'eka.p@example.com', '0813005', 'L', 'verifikasi'],
                ['Fauzi Hakim', 'fauzi.h@example.com', '0813006', 'L', 'pending'],
                ['Gita Pertiwi', 'gita.p@example.com', '0813007', 'P', 'diterima'],
            ];
            foreach ($pendaftarData as $i => [$nama, $email, $hp, $jk, $status]) {
                $jurusan = $jurusans->get($i % max(1, $jurusans->count()));
                PendaftarPpdb::create([
                    'no_pendaftaran'     => 'PPDB-' . date('Y') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'nama'               => $nama,
                    'email'              => $email,
                    'no_hp'              => $hp,
                    'jenis_kelamin'      => $jk,
                    'tempat_lahir'       => 'Jakarta',
                    'tanggal_lahir'      => '2009-0' . ($i + 1) . '-15',
                    'alamat'             => 'Jl. Contoh No.' . ($i + 1) . ', Jakarta',
                    'asal_sekolah'       => 'SMP Negeri ' . ($i + 1) . ' Jakarta',
                    'jurusan_pilihan_id' => $jurusan?->id,
                    'gelombang'          => $gel1->id,
                    'tahun_ajaran'       => '2025/2026',
                    'status'             => $status,
                    'catatan_admin'      => $status === 'ditolak' ? 'Kuota penuh.' : null,
                ]);
            }
            $this->info('✓ Pendaftar PPDB');
        }

        // ── 19. GALERI ──────────────────────────────────────
        if (Galeri::count() === 0) {
            $daftarGaleri = [
                ['Kegiatan LKS 2025', 'Dokumentasi pelaksanaan Lomba Kompetensi Siswa tingkat kota.', '2025-03-10'],
                ['Pensi & Perpisahan Kelas XII', 'Momen kenangan perpisahan angkatan 2025.', '2025-06-05'],
                ['Pramuka Camping', 'Kegiatan perkemahan Pramuka di Bumi Perkemahan Cibubur.', '2025-02-20'],
                ['Prakerin Angkatan 2025', 'Dokumentasi kegiatan PKL siswa di berbagai instansi.', '2025-01-15'],
            ];
            foreach ($daftarGaleri as [$nama, $desc, $tgl]) {
                Galeri::create([
                    'nama'         => $nama,
                    'deskripsi'    => $desc,
                    'cover'        => null,
                    'tanggal'      => $tgl,
                    'is_published' => true,
                ]);
            }
            $this->info('✓ Galeri');
        }

        // ── 20. ARTIKEL ─────────────────────────────────────
        if (Artikel::count() === 0) {
            $artikelData = [
                ['SMK Raih Juara LKS Tingkat Kota', 'lks-juara-kota', 'Selamat kepada tim LKS SMK yang berhasil meraih juara 1 bidang TKJ tingkat kota/kabupaten.', 'berita', '2025-03-16'],
                ['Penerimaan Peserta Didik Baru 2025/2026 Dibuka', 'ppdb-2025-2026', 'PPDB tahun ajaran 2025/2026 resmi dibuka. Pendaftaran dapat dilakukan secara online melalui portal PPDB.', 'pengumuman', '2025-06-01'],
                ['Kegiatan Ujian Akhir Semester Berjalan Lancar', 'uas-berjalan-lancar', 'Ujian Akhir Semester (UAS) berjalan dengan tertib dan lancar. Seluruh siswa mengikuti ujian dengan penuh semangat.', 'berita', '2025-12-10'],
                ['Tips Mempersiapkan PKL yang Sukses', 'tips-pkl-sukses', 'Berikut beberapa tips dari pembimbing PKL agar kegiatan Prakerin berjalan lancar dan memberikan pengalaman maksimal.', 'berita', '2025-01-05'],
                ['Jadwal Libur Semester Ganjil 2025', 'jadwal-libur-semester', 'Pengumuman jadwal libur semester ganjil tahun ajaran 2025/2026. Harap diperhatikan oleh seluruh warga sekolah.', 'pengumuman', '2025-12-20'],
            ];
            $adminUser = User::where('role', 0)->first();
            foreach ($artikelData as [$judul, $slug, $ringkasan, $kat, $pubDate]) {
                Artikel::create([
                    'judul'        => $judul,
                    'slug'         => $slug,
                    'ringkasan'    => $ringkasan,
                    'konten'       => '<p>' . $ringkasan . '</p><p>Selengkapnya dapat dilihat langsung di sekolah.</p>',
                    'kategori'     => $kat,
                    'is_published' => true,
                    'published_at' => $pubDate,
                    'dibuat_oleh'  => $adminUser->id,
                ]);
            }
            $this->info('✓ Artikel');
        }

        // ── 21. HALAMAN STATIS ──────────────────────────────
        if (HalamanStatis::count() === 0) {
            $halamanData = [
                ['tentang', 'Tentang Kami', '<h4>Selamat Datang di SMK</h4><p>SMK kami adalah sekolah kejuruan yang berdedikasi mencetak lulusan siap kerja, terampil, dan berkarakter. Didirikan sejak tahun 1975, kami telah menghasilkan ribuan alumni yang berkarir di berbagai bidang industri dan teknologi.</p><p>Dengan fasilitas laboratorium modern, tenaga pengajar berpengalaman, dan program keahlian yang relevan dengan dunia industri, kami berkomitmen memberikan pendidikan berkualitas terbaik.</p>'],
                ['visi-misi', 'Visi & Misi', '<h4>Visi</h4><p>Menjadi sekolah kejuruan terdepan yang menghasilkan lulusan kompeten, berkarakter, dan siap bersaing di era global.</p><h4>Misi</h4><ul><li>Menyelenggarakan pendidikan dan pelatihan vokasi berkualitas tinggi</li><li>Menjalin kemitraan strategis dengan dunia usaha dan industri</li><li>Membentuk karakter siswa yang berintegritas dan bertanggung jawab</li><li>Mengembangkan inovasi pembelajaran berbasis teknologi</li></ul>'],
                ['fasilitas', 'Fasilitas Sekolah', '<h4>Fasilitas Unggulan</h4><ul><li>Laboratorium Komputer dengan 40 unit PC terbaru</li><li>Laboratorium Jaringan & Server</li><li>Laboratorium Multimedia</li><li>Perpustakaan Digital</li><li>Lapangan Olahraga multifungsi</li><li>Aula serba guna berkapasitas 500 orang</li><li>Koperasi Sekolah</li><li>Kantin bersih dan sehat</li></ul>'],
            ];
            foreach ($halamanData as [$slug, $judul, $konten]) {
                HalamanStatis::firstOrCreate(['slug' => $slug], [
                    'judul'        => $judul,
                    'konten'       => $konten,
                    'meta_desc'    => $judul . ' - ' . setting('sekolah_nama', 'SMK'),
                    'is_published' => true,
                    'urutan'       => 0,
                ]);
            }
            $this->info('✓ Halaman Statis');
        }

        // ── 23. PERSYARATAN PPDB ────────────────────────────
        if (PersyaratanPpdb::count() === 0) {
            $syaratList = [
                [1, 'Fotokopi Ijazah / SKL SMP/MTs', 'Fotokopi ijazah atau Surat Keterangan Lulus yang telah dilegalisir, sebanyak 2 lembar.'],
                [2, 'Fotokopi Rapor SMP/MTs', 'Fotokopi rapor 5 semester (kelas VII s.d. IX semester 1), telah dilegalisir.'],
                [3, 'Fotokopi Akta Kelahiran', 'Fotokopi akta kelahiran sebanyak 2 lembar.'],
                [4, 'Fotokopi Kartu Keluarga', 'Fotokopi Kartu Keluarga (KK) yang masih berlaku.'],
                [5, 'Pas Foto Berwarna', 'Pas foto berwarna terbaru ukuran 3×4 sebanyak 4 lembar dengan latar belakang merah.'],
                [6, 'Surat Keterangan Sehat', 'Surat keterangan sehat dari dokter atau puskesmas setempat.'],
            ];
            foreach ($syaratList as [$urutan, $judul, $desc]) {
                PersyaratanPpdb::create(['judul' => $judul, 'deskripsi' => $desc, 'urutan' => $urutan, 'is_aktif' => true]);
            }
            $this->info('✓ Persyaratan PPDB');
        }

        // ── 24. FAQ PPDB ────────────────────────────────────
        if (FaqPpdb::count() === 0) {
            $faqList = [
                [1, 'Bagaimana cara mendaftar?', 'Pendaftaran dapat dilakukan secara online melalui portal PPDB di website ini, atau datang langsung ke sekolah pada jam kerja (08.00–14.00 WIB).'],
                [2, 'Berapa biaya pendaftaran?', 'Pendaftaran PPDB tidak dipungut biaya (gratis).'],
                [3, 'Apa saja jurusan yang tersedia?', 'Kami memiliki 3 program keahlian: Teknik Komputer & Jaringan (TKJ), Rekayasa Perangkat Lunak (RPL), dan Multimedia (MM).'],
                [4, 'Kapan pengumuman kelulusan PPDB?', 'Pengumuman kelulusan disampaikan paling lambat 7 hari kerja setelah batas akhir pendaftaran melalui website dan papan pengumuman sekolah.'],
                [5, 'Apakah ada jalur prestasi?', 'Ya, tersedia jalur prestasi untuk siswa berprestasi di bidang akademik maupun non-akademik. Kuota jalur prestasi adalah 20% dari total penerimaan.'],
                [6, 'Bagaimana cara mengecek status pendaftaran?', 'Status pendaftaran dapat dicek melalui menu "Cek Status Pendaftaran" di portal PPDB dengan memasukkan nomor pendaftaran yang diterima saat mendaftar.'],
            ];
            foreach ($faqList as [$urutan, $pert, $jwb]) {
                FaqPpdb::create(['pertanyaan' => $pert, 'jawaban' => $jwb, 'urutan' => $urutan, 'is_aktif' => true]);
            }
            $this->info('✓ FAQ PPDB');
        }

        // ── 25. PENGUMUMAN TAMBAHAN ─────────────────────────
        if (Pengumuman::count() < 3) {
            $pengumumanData = [
                ['Libur Nasional Hari Kemerdekaan', 'Diberitahukan kepada seluruh warga sekolah bahwa pada tanggal 17 Agustus 2025 sekolah diliburkan dalam rangka Hari Kemerdekaan Republik Indonesia ke-80.', null, '2025-08-15'],
                ['Jadwal Ujian Tengah Semester', 'Ujian Tengah Semester (UTS) Semester Genap akan dilaksanakan mulai tanggal 10–15 Maret 2026. Harap seluruh siswa mempersiapkan diri dengan baik.', 2, '2026-03-01'],
                ['Rapat Wali Kelas Semester Genap', 'Guru diundang untuk menghadiri rapat koordinasi wali kelas semester genap pada hari Sabtu, 7 Februari 2026 pukul 08.00 WIB di ruang rapat.', 1, '2026-02-03'],
            ];
            foreach ($pengumumanData as [$judul, $isi, $targetRole, $pubDate]) {
                Pengumuman::create([
                    'judul'           => $judul,
                    'isi'             => $isi,
                    'target_role'     => $targetRole,
                    'target_kelas_id' => null,
                    'dibuat_oleh'     => $admin->id,
                    'published_at'    => $pubDate,
                ]);
            }
            $this->info('✓ Pengumuman tambahan');
        }
    }

    private function info(string $msg): void
    {
        $this->command?->info($msg);
    }
}
