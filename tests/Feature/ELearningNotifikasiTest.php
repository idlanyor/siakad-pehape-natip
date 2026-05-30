<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Notifikasi;
use App\Models\PengumpulanTugas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ELearningNotifikasiTest extends TestCase
{
    use RefreshDatabase;

    private Guru $guru;
    private Siswa $siswaA;
    private Siswa $siswaB;
    private Jadwal $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $jurusan  = Jurusan::create(['nama_jurusan' => 'RPL']);
        $kelas    = Kelas::create(['nama_kelas' => 'X RPL 1', 'jurusan_id' => $jurusan->id, 'tingkat' => 'X']);
        $mapel    = Mapel::create(['kode' => 'PRO', 'nama_mapel' => 'Pemrograman', 'jurusan_id' => $jurusan->id]);
        $ta       = TahunAjaran::create(['nama' => '2025/2026', 'aktif' => true]);
        $semester = Semester::create(['tahun_ajaran_id' => $ta->id, 'semester' => 'ganjil', 'aktif' => true]);

        $guruUser   = User::factory()->create(['role' => 1]);
        $this->guru = Guru::create(['user_id' => $guruUser->id, 'nip' => '199001', 'nama' => 'Pak Guru']);

        $this->jadwal = Jadwal::create([
            'mapel_id' => $mapel->id, 'kelas_id' => $kelas->id, 'guru_id' => $this->guru->id,
            'semester_id' => $semester->id, 'hari' => 'Senin', 'jam_mulai' => '07:00', 'jam_selesai' => '08:30',
        ]);

        $this->siswaA = $this->buatSiswa($jurusan->id, $kelas->id, '1111111111');
        $this->siswaB = $this->buatSiswa($jurusan->id, $kelas->id, '2222222222');
    }

    private function buatSiswa(int $jurusanId, int $kelasId, string $nisn): Siswa
    {
        $u = User::factory()->create(['role' => 2]);
        return Siswa::create([
            'user_id' => $u->id, 'jurusan_id' => $jurusanId, 'kelas_id' => $kelasId,
            'nama' => 'Siswa ' . $nisn, 'nisn' => $nisn, 'tanggal_lahir' => '2008-01-01',
        ]);
    }

    public function test_materi_terbit_menotifikasi_semua_siswa_kelas(): void
    {
        Materi::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guru->id,
            'judul' => 'Bab 1', 'tipe' => 'teks', 'konten' => 'isi', 'is_published' => true,
        ]);

        // Kedua siswa kelas dapat notifikasi.
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->siswaA->user_id, 'judul' => 'Materi baru: Bab 1',
        ]);
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->siswaB->user_id, 'judul' => 'Materi baru: Bab 1',
        ]);
        $this->assertEquals(2, Notifikasi::count());
    }

    public function test_materi_draft_tidak_menotifikasi(): void
    {
        Materi::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guru->id,
            'judul' => 'Draft', 'tipe' => 'teks', 'konten' => 'isi', 'is_published' => false,
        ]);

        $this->assertEquals(0, Notifikasi::count());
    }

    public function test_materi_diterbitkan_kemudian_menotifikasi(): void
    {
        $m = Materi::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guru->id,
            'judul' => 'Bab 2', 'tipe' => 'teks', 'konten' => 'isi', 'is_published' => false,
        ]);
        $this->assertEquals(0, Notifikasi::count());

        $m->update(['is_published' => true]);
        $this->assertEquals(2, Notifikasi::count());
    }

    public function test_tugas_baru_menotifikasi_siswa_kelas(): void
    {
        Tugas::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guru->id,
            'judul' => 'Tugas 1', 'instruksi' => 'x', 'deadline' => now()->addDays(3),
            'nilai_maksimal' => 100, 'tipe_pengumpulan' => 'teks',
        ]);

        $this->assertEquals(2, Notifikasi::count());
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->siswaA->user_id, 'judul' => 'Tugas baru: Tugas 1',
        ]);
    }

    public function test_penilaian_menotifikasi_hanya_siswa_bersangkutan(): void
    {
        $tugas = Tugas::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guru->id,
            'judul' => 'Tugas 1', 'instruksi' => 'x', 'deadline' => now()->addDays(3),
            'nilai_maksimal' => 100, 'tipe_pengumpulan' => 'teks',
        ]);
        Notifikasi::truncate(); // buang notif "tugas baru"

        $p = PengumpulanTugas::create([
            'tugas_id' => $tugas->id, 'siswa_id' => $this->siswaA->id, 'konten' => 'jawab',
        ]);
        $this->assertEquals(0, Notifikasi::count()); // mengumpulkan ≠ notif

        $p->update(['nilai' => 88, 'dinilai_at' => now()]);

        $this->assertEquals(1, Notifikasi::count());
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->siswaA->user_id, 'judul' => 'Tugas dinilai: Tugas 1',
        ]);
        // Siswa B (tak mengumpulkan) tak dapat notif penilaian.
        $this->assertDatabaseMissing('notifikasis', [
            'user_id' => $this->siswaB->user_id, 'judul' => 'Tugas dinilai: Tugas 1',
        ]);
    }
}

