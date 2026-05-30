<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 0]);
    }

    private function kelas(): Kelas
    {
        $j = Jurusan::create(['nama_jurusan' => 'RPL']);
        return Kelas::create(['nama_kelas' => 'XII RPL', 'jurusan_id' => $j->id, 'tingkat' => 'XII']);
    }

    public function test_menu_laporan_terbuka(): void
    {
        $this->actingAs($this->admin())->get(route('admin.laporan.index'))->assertOk();
    }

    public function test_laporan_kehadiran_html_dan_pdf(): void
    {
        $admin = $this->admin();
        $kelas = $this->kelas();

        $this->actingAs($admin)
            ->get(route('admin.laporan.kehadiran', ['kelas_id' => $kelas->id]))
            ->assertOk();

        $pdf = $this->actingAs($admin)
            ->get(route('admin.laporan.kehadiran', ['kelas_id' => $kelas->id, 'export' => 'pdf']));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('Content-Type'));
    }

    public function test_laporan_nilai_html(): void
    {
        $kelas = $this->kelas();
        $this->actingAs($this->admin())
            ->get(route('admin.laporan.nilai', ['kelas_id' => $kelas->id]))
            ->assertOk();
    }

    public function test_laporan_keuangan_dan_ppdb(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.laporan.keuangan', ['bulan' => now()->format('Y-m')]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.laporan.ppdb'))
            ->assertOk();
    }

    public function test_laporan_kehadiran_butuh_kelas(): void
    {
        // Tanpa kelas_id → validasi gagal (redirect back).
        $this->actingAs($this->admin())
            ->get(route('admin.laporan.kehadiran'))
            ->assertSessionHasErrors('kelas_id');
    }
}
