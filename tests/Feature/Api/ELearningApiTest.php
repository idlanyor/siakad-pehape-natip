<?php

namespace Tests\Feature\Api;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ELearningApiTest extends TestCase
{
    use RefreshDatabase;

    private Guru $guru;
    private Siswa $siswa;
    private Jadwal $jadwal;
    private Jadwal $jadwalLain;

    protected function setUp(): void
    {
        parent::setUp();

        $jurusan = Jurusan::create(['nama_jurusan' => 'RPL']);
        $kelas   = Kelas::create(['nama_kelas' => 'X RPL 1', 'jurusan_id' => $jurusan->id, 'tingkat' => 'X']);
        $mapel   = Mapel::create(['kode' => 'PRO', 'nama_mapel' => 'Pemrograman', 'jurusan_id' => $jurusan->id]);
        $ta      = TahunAjaran::create(['nama' => '2025/2026', 'aktif' => true]);
        $semester = Semester::create(['tahun_ajaran_id' => $ta->id, 'semester' => 'ganjil', 'aktif' => true]);

        $guruUser = User::factory()->create(['role' => 1]);
        $this->guru = Guru::create(['user_id' => $guruUser->id, 'nip' => '199001', 'nama' => 'Pak Guru']);

        $guruLainUser = User::factory()->create(['role' => 1]);
        $guruLain = Guru::create(['user_id' => $guruLainUser->id, 'nip' => '199002', 'nama' => 'Guru Lain']);

        $this->jadwal = Jadwal::create([
            'mapel_id' => $mapel->id, 'kelas_id' => $kelas->id, 'guru_id' => $this->guru->id,
            'semester_id' => $semester->id, 'hari' => 'Senin', 'jam_mulai' => '07:00', 'jam_selesai' => '08:30',
        ]);

        // Jadwal milik guru lain & kelas lain → untuk uji IDOR.
        $kelasLain = Kelas::create(['nama_kelas' => 'X RPL 2', 'jurusan_id' => $jurusan->id, 'tingkat' => 'X']);
        $this->jadwalLain = Jadwal::create([
            'mapel_id' => $mapel->id, 'kelas_id' => $kelasLain->id, 'guru_id' => $guruLain->id,
            'semester_id' => $semester->id, 'hari' => 'Selasa', 'jam_mulai' => '07:00', 'jam_selesai' => '08:30',
        ]);

        $siswaUser = User::factory()->create(['role' => 2]);
        $this->siswa = Siswa::create([
            'user_id' => $siswaUser->id, 'jurusan_id' => $jurusan->id, 'kelas_id' => $kelas->id,
            'nama' => 'Budi', 'nisn' => '1234567890', 'tanggal_lahir' => '2008-01-01',
        ]);
    }

    private function guruUser(): User { return $this->guru->user; }
    private function siswaUser(): User { return $this->siswa->user; }

    public function test_guru_buat_materi_lalu_siswa_melihatnya(): void
    {
        Sanctum::actingAs($this->guruUser());

        $this->postJson("/api/v1/guru/materi/{$this->jadwal->id}", [
            'judul' => 'Bab 1: Variabel',
            'tipe'  => 'teks',
            'teks'  => 'Isi materi variabel.',
        ])->assertCreated()->assertJsonPath('data.judul', 'Bab 1: Variabel');

        // Siswa di kelas jadwal ini bisa lihat materi terbit.
        Sanctum::actingAs($this->siswaUser());
        $this->getJson('/api/v1/siswa/materi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.judul', 'Bab 1: Variabel');
    }

    public function test_guru_tidak_bisa_buat_materi_di_jadwal_orang_lain(): void
    {
        Sanctum::actingAs($this->guruUser());

        $this->postJson("/api/v1/guru/materi/{$this->jadwalLain->id}", [
            'judul' => 'Nyusup', 'tipe' => 'teks', 'teks' => 'x',
        ])->assertForbidden();
    }

    public function test_materi_belum_terbit_tak_tampil_ke_siswa(): void
    {
        Materi::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guru->id,
            'judul' => 'Draft', 'tipe' => 'teks', 'konten' => 'draft', 'is_published' => false,
        ]);

        Sanctum::actingAs($this->siswaUser());
        $this->getJson('/api/v1/siswa/materi')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_siswa_kumpul_tugas_lalu_guru_menilai(): void
    {
        $tugas = Tugas::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guru->id,
            'judul' => 'Tugas 1', 'instruksi' => 'Kerjakan', 'deadline' => now()->addDays(3),
            'nilai_maksimal' => 100, 'tipe_pengumpulan' => 'teks',
        ]);

        Sanctum::actingAs($this->siswaUser());
        $this->postJson("/api/v1/siswa/tugas/{$tugas->id}/kumpul", [
            'teks' => 'Jawaban saya',
        ])->assertCreated();

        $this->assertDatabaseHas('pengumpulan_tugas', [
            'tugas_id' => $tugas->id, 'siswa_id' => $this->siswa->id,
        ]);

        $pengumpulan = $tugas->pengumpulans()->first();

        // Guru menilai.
        Sanctum::actingAs($this->guruUser());
        $this->postJson("/api/v1/guru/pengumpulan/{$pengumpulan->id}/nilai", [
            'nilai' => 90, 'feedback' => 'Bagus',
        ])->assertOk()->assertJsonPath('data.nilai', 90);

        // Siswa lihat nilainya di detail tugas.
        Sanctum::actingAs($this->siswaUser());
        $this->getJson("/api/v1/siswa/tugas/{$tugas->id}")
            ->assertOk()
            ->assertJsonPath('data.pengumpulan.nilai', 90)
            ->assertJsonPath('data.pengumpulan.is_dinilai', true);
    }

    public function test_kumpul_tugas_lewat_deadline_ditolak(): void
    {
        $tugas = Tugas::create([
            'jadwal_id' => $this->jadwal->id, 'guru_id' => $this->guru->id,
            'judul' => 'Telat', 'instruksi' => 'x', 'deadline' => now()->subDay(),
            'nilai_maksimal' => 100, 'tipe_pengumpulan' => 'teks',
        ]);

        Sanctum::actingAs($this->siswaUser());
        $this->postJson("/api/v1/siswa/tugas/{$tugas->id}/kumpul", ['teks' => 'telat'])
            ->assertStatus(422);
    }

    public function test_siswa_tak_bisa_kumpul_tugas_kelas_lain(): void
    {
        $tugasLain = Tugas::create([
            'jadwal_id' => $this->jadwalLain->id, 'guru_id' => $this->guru->id,
            'judul' => 'Kelas lain', 'instruksi' => 'x', 'deadline' => now()->addDay(),
            'nilai_maksimal' => 100, 'tipe_pengumpulan' => 'teks',
        ]);

        Sanctum::actingAs($this->siswaUser());
        $this->postJson("/api/v1/siswa/tugas/{$tugasLain->id}/kumpul", ['teks' => 'x'])
            ->assertForbidden();
    }

    public function test_butuh_autentikasi(): void
    {
        $this->getJson('/api/v1/siswa/materi')->assertUnauthorized();
    }

    public function test_role_siswa_tak_bisa_akses_endpoint_guru(): void
    {
        Sanctum::actingAs($this->siswaUser());
        $this->postJson("/api/v1/guru/materi/{$this->jadwal->id}", [
            'judul' => 'x', 'tipe' => 'teks', 'teks' => 'x',
        ])->assertForbidden();
    }
}

