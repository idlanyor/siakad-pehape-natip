<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Database\Factories\Ujian\DataSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class ImportAkunTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 0]);
    }

    private function fileExcel(array $baris): UploadedFile
    {
        $sheet = new Spreadsheet;
        $sheet->getActiveSheet()->fromArray($baris);
        $path = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        IOFactory::createWriter($sheet, 'Xlsx')->save($path);

        return new UploadedFile($path, 'data.xlsx', null, null, true);
    }

    /** @return array<int, array<int, string>> isi file unduhan */
    private function bacaUnduhan($res): array
    {
        $path = $res->baseResponse->getFile()->getPathname();

        return IOFactory::load($path)->getActiveSheet()->toArray();
    }

    /** Regresi: password akun impor dulu = NISN/NIP (atau 'password'), mudah ditebak. */
    public function test_impor_siswa_memakai_password_acak_dan_daftar_akun_bisa_diunduh_sekali(): void
    {
        $kelas = DataSekolah::kelas();
        $file = $this->fileExcel([
            ['nisn', 'nama_lengkap', 'kelas', 'tanggal_lahir'],
            ['0012345678', 'Budi Santoso', $kelas->nama_kelas, '2010-02-03'],
            ['', 'Tanpa NISN', $kelas->nama_kelas, '2010-04-05'],
            ['0099999999', 'Kelas Salah', 'KELAS-TIDAK-ADA', '2010-01-01'],
            ['0088888888', 'Tanpa Tanggal', $kelas->nama_kelas, ''],
        ]);

        $res = $this->actingAs($this->admin)->post(route('admin.import.store'), ['type' => 'siswa', 'file' => $file]);
        $this->assertNull($res->getSession()->get('error'));
        $hasil = $res->assertSessionHasNoErrors()->getSession()->get('import_result');
        $this->assertSame(1, $hasil['imported']);
        $this->assertSame(1, $hasil['akun_count']);
        // Regresi: baris dengan kelas tak dikenal dulu menggagalkan seluruh impor (jurusan_id NOT NULL)
        $this->assertSame([
            'Baris 3: kolom NISN kosong, dilewati.',
            'Baris 4: kelas "KELAS-TIDAK-ADA" tidak ditemukan, dilewati.',
            'Baris 5: tanggal lahir kosong atau formatnya tidak dikenali, dilewati.',
        ], $hasil['skipped']);

        $budi = Siswa::where('nisn', '0012345678')->firstOrFail()->user;
        $this->assertFalse(Hash::check('0012345678', $budi->password));

        // Unduh: berisi username + password yang benar-benar berlaku
        $unduh = $this->get(route('admin.import.akun', $hasil['akun_token']))->assertOk();
        $this->assertStringContainsString('no-store', $unduh->headers->get('Cache-Control'));
        $isi = $this->bacaUnduhan($unduh);
        $this->assertSame(['Nama', 'Keterangan', 'Username', 'Password awal'], $isi[0]);
        $barisBudi = collect($isi)->firstWhere(0, 'Budi Santoso');
        $this->assertSame('0012345678', $barisBudi[2]);
        $this->assertTrue(Hash::check($barisBudi[3], $budi->password));

        // Hanya sekali
        $this->get(route('admin.import.akun', $hasil['akun_token']))->assertNotFound();
    }

    public function test_daftar_akun_hanya_untuk_admin_yang_mengimpor(): void
    {
        $file = $this->fileExcel([['nip', 'nama_lengkap'], ['198001012005011001', 'Bu Sari']]);
        $hasil = $this->actingAs($this->admin)->post(route('admin.import.store'), ['type' => 'guru', 'file' => $file])
            ->getSession()->get('import_result');

        $guru = Guru::where('nip', '198001012005011001')->firstOrFail()->user;
        $this->assertFalse(Hash::check('198001012005011001', $guru->password));

        $adminLain = User::factory()->create(['role' => 0]);
        $this->actingAs($adminLain)->get(route('admin.import.akun', $hasil['akun_token']))->assertNotFound();
        // Percobaan admin lain tidak menghanguskan token milik pengimpor
        $this->actingAs($this->admin)->get(route('admin.import.akun', $hasil['akun_token']))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.import.akun', 'token-ngawur'))->assertNotFound();
    }
}
