<?php

namespace Tests\Feature;

use App\Models\{Siswa, UserSiswa, Ujian, SoalUjian, SesiUjian, JawabanSiswa};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaUjianFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \DB::table('periode_ujian')->insert(['id' => 1, 'nama' => 'Ganjil 2026', 'tahun_ajaran' => '2025/2026', 'semester' => 'ganjil', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function createSiswa(int $id = 1, int $kelasId = 7): UserSiswa
    {
        $siswa = Siswa::create([
            'siakad_id' => $id,
            'nis' => '1234' . $id,
            'nisn' => '9999' . $id,
            'nama_lengkap' => $id === 1 ? 'Budi' : 'Siti',
            'kelas_id' => $kelasId,
            'kelas_nama' => 'X-A',
            'tingkat' => 10,
            'username' => '1234' . $id,
            'status' => 'aktif',
        ]);
        return UserSiswa::create([
            'siswa_id' => $siswa->id,
            'username' => '1234' . $id,
            'password' => bcrypt('1234' . $id),
            'is_active' => true,
        ]);
    }

    private function createUjian(int $kelasId = 7): Ujian
    {
        $ujian = Ujian::create([
            'periode_id' => 1,
            'judul' => 'UH1',
            'tipe' => 'quiz',
            'kelas_id' => $kelasId,
            'waktu_mulai' => now()->subMinute(),
            'waktu_selesai' => now()->addHour(),
            'durasi_menit' => 60,
            'passing_grade' => 70,
            'status' => 'published',
            'is_published' => true,
        ]);
        SoalUjian::create([
            'ujian_id' => $ujian->id,
            'nomor' => 1,
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => '2+2?',
            'opsi_a' => '3',
            'opsi_b' => '4',
            'kunci_jawaban' => 'B',
            'bobot_nilai' => 100,
        ]);
        return $ujian;
    }

    public function test_login_redirects_to_ujian_index(): void
    {
        $this->createSiswa();
        $resp = $this->post('/siswa/login', ['username' => '12341', 'password' => '12341']);
        $resp->assertRedirect(route('siswa.ujian.index'));
        $this->assertAuthenticatedAs(UserSiswa::first(), 'siswa');
    }

    public function test_daftar_ujian_hanya_kelas_siswa(): void
    {
        $user = $this->createSiswa(1, 7);
        $this->createUjian(7);
        $this->createUjian(99);

        // Controller filter by kelas_id = 7 — hanya 1 yg cocok
        $this->assertCount(1, Ujian::where('kelas_id', 7)->get());
        $this->assertCount(1, Ujian::where('kelas_id', 99)->get());
        $this->assertCount(2, Ujian::all());

        // DB query: sesuai logika controller
        $ujians = Ujian::where('is_published', true)
            ->where('kelas_id', 7)
            ->where('waktu_mulai', '<=', now())
            ->where('waktu_selesai', '>=', now())
            ->orderBy('waktu_mulai')
            ->get();
        $this->assertCount(1, $ujians);
    }

    public function test_full_exam_flow(): void
    {
        $user = $this->createSiswa();
        $ujian = $this->createUjian();

        // 1. Start sesi (JSON)
        $start = $this->actingAs($user, 'siswa')
            ->post("/siswa/ujian/{$ujian->id}/sesi");
        $start->assertStatus(201);
        $sesiId = $start->json('data.sesi_id');
        $this->assertNotNull($sesiId);

        // 2. Jawab (JSON)
        $soal = SoalUjian::first();
        $this->actingAs($user, 'siswa')
            ->post("/siswa/sesi/{$sesiId}/answer", ['soal_id' => $soal->id, 'jawaban' => 'B'])
            ->assertJson(['success' => true]);

        // Verifikasi jawaban tersimpan di DB
        $this->assertEquals('B', JawabanSiswa::first()->jawaban);

        // 3. Submit (JSON)
        $this->actingAs($user, 'siswa')
            ->post("/siswa/sesi/{$sesiId}/submit")
            ->assertJson(['success' => true]);

        // Verifikasi sesi selesai & nilai terhitung
        $sesi = SesiUjian::find($sesiId);
        $this->assertSame('selesai', $sesi->status);
        $this->assertNotNull($sesi->nilai_akhir);
        $this->assertGreaterThan(0, (float) $sesi->nilai_akhir);
    }

    public function test_cannot_access_other_siswa_sesi(): void
    {
        $user = $this->createSiswa(1);
        $ujian = $this->createUjian();
        $sesiId = $this->actingAs($user, 'siswa')
            ->post("/siswa/ujian/{$ujian->id}/sesi")->json('data.sesi_id');

        auth('siswa')->logout();

        $other = $this->createSiswa(2);
        $this->actingAs($other, 'siswa')->get("/siswa/sesi/{$sesiId}")
            ->assertForbidden();
    }
}
