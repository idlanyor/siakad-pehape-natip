# Login Siswa Independen + Flow Ujian — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Siswa login mandiri (session, tanpa SSO) lalu melihat daftar ujian & mengerjakan ujian penuh, dengan reuse engine `Api\SesiController` yang sudah ada.

**Architecture:** Middleware `InjectSiswaAuthContext` menjembatani guard session `siswa` ke kontrak `_auth_user` yang dipakai engine. Halaman HTML (daftar/kerjakan/hasil) disajikan web controller tipis; aksi AJAX (start/answer/submit/violation) didelegasikan ke `Api\SesiController` via route web (session+CSRF). Penyiapan payload soal diekstrak ke presenter bersama agar tak duplikasi. Entry SIAKAD untuk siswa diarahkan ke `/siswa/login` ujian-service.

**Tech Stack:** Laravel 11 (ujian-service), guard `siswa` (Eloquent `UserSiswa`), Blade + AdminLTE + jQuery AJAX, PHPUnit feature tests.

**Spec:** `ujian-service/docs/superpowers/specs/2026-06-02-siswa-independent-login-design.md`

**Konvensi path:** semua path relatif ke `ujian-service/` kecuali Task 9 (repo SIAKAD root `/home/roy/siakad-pehape-natip`).

---

## File Structure

**Buat baru:**
- `app/Http/Middleware/InjectSiswaAuthContext.php` — inject `_auth_user` dari guard `siswa`.
- `app/Support/SesiPresenter.php` — penyiapan payload soal & progress (dipakai web + API).
- `app/Http/Controllers/Siswa/UjianController.php` — halaman HTML daftar/kerjakan/hasil.
- `tests/Feature/SiswaUjianFlowTest.php` — feature test flow siswa.
- `tests/Unit/InjectSiswaAuthContextTest.php` — unit test middleware.

**Modifikasi:**
- `app/Models/Ujian.php` — tambah relasi `soals()`, `sesis()`, accessor `status_*`.
- `bootstrap/app.php` — daftarkan alias `siswa.context`.
- `routes/web.php` — grup siswa: ganti placeholder, tambah route HTML + AJAX.
- `app/Http/Controllers/Api/SesiController.php` — pakai `SesiPresenter` di `show()`.
- `resources/views/siswa/ujian/index.blade.php` — pakai data controller (bukan `Auth::id()`).
- `resources/views/siswa/sesi/show.blade.php` — URL fetch → route web siswa + CSRF.
- `resources/views/layouts/admin.blade.php` — tambah `<meta name="csrf-token">`.

**Repo SIAKAD (Task 9):**
- `resources/views/siswa/dashboard.blade.php`, `resources/views/layouts/partials/sidebar.blade.php` — link siswa → `/siswa/login` ujian-service.

---

## Task 1: Middleware jembatan `InjectSiswaAuthContext`

**Files:**
- Create: `app/Http/Middleware/InjectSiswaAuthContext.php`
- Modify: `bootstrap/app.php`
- Test: `tests/Unit/InjectSiswaAuthContextTest.php`

- [ ] **Step 1: Tulis test gagal**

```php
<?php
// tests/Unit/InjectSiswaAuthContextTest.php
namespace Tests\Unit;

use App\Http\Middleware\InjectSiswaAuthContext;
use App\Models\Siswa;
use App\Models\UserSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class InjectSiswaAuthContextTest extends TestCase
{
    public function test_injects_auth_user_for_logged_in_siswa(): void
    {
        $siswa = Siswa::create([
            'siakad_id' => 1, 'nis' => '12345', 'nisn' => '999',
            'nama_lengkap' => 'Budi', 'kelas_id' => 7, 'kelas_nama' => 'X-A',
            'tingkat' => 10, 'status' => 'aktif',
        ]);
        $user = UserSiswa::create([
            'siswa_id' => $siswa->id, 'username' => '12345',
            'password' => bcrypt('12345'), 'is_active' => true,
        ]);
        Auth::guard('siswa')->login($user);

        $request = Request::create('/siswa/ujian', 'GET');
        $request->setLaravelSession($this->app['session']->driver());

        $captured = null;
        (new InjectSiswaAuthContext())->handle($request, function ($req) use (&$captured) {
            $captured = $req->input('_auth_user');
            return response('ok');
        });

        $this->assertSame($siswa->id, $captured['user_id']);
        $this->assertSame('siswa', $captured['role']);
        $this->assertSame(7, $captured['kelas_id']);
        $this->assertSame('Budi', $captured['nama']);
        $this->assertSame('12345', $captured['nis']);
    }

    public function test_noop_when_not_logged_in(): void
    {
        $request = Request::create('/siswa/ujian', 'GET');
        $captured = 'unset';
        (new InjectSiswaAuthContext())->handle($request, function ($req) use (&$captured) {
            $captured = $req->input('_auth_user');
            return response('ok');
        });
        $this->assertNull($captured);
    }
}
```

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --filter=InjectSiswaAuthContextTest`
Expected: FAIL — class `InjectSiswaAuthContext` not found.

- [ ] **Step 3: Implementasi middleware**

```php
<?php
// app/Http/Middleware/InjectSiswaAuthContext.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adaptor: ubah identitas guard session `siswa` menjadi kontrak `_auth_user`
 * yang dipakai Api\SesiController. Memungkinkan reuse engine ujian tanpa JWT/SSO.
 */
class InjectSiswaAuthContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('siswa')->user();

        if ($user && $user->siswa) {
            $siswa = $user->siswa;
            $request->merge([
                '_auth_user' => [
                    'user_id'  => $siswa->id,
                    'role'     => 'siswa',
                    'kelas_id' => $siswa->kelas_id,
                    'nama'     => $siswa->nama_lengkap,
                    'nis'      => $siswa->nis,
                ],
            ]);
        }

        return $next($request);
    }
}
```

- [ ] **Step 4: Daftarkan alias**

Di `bootstrap/app.php`, dalam `->withMiddleware(...)`, tambah ke array `alias`:

```php
$middleware->alias([
    'auth.siswa' => \App\Http\Middleware\AuthenticateSiswa::class,
    'auth.jwt' => \App\Http\Middleware\AuthenticateJWT::class,
    'siswa.context' => \App\Http\Middleware\InjectSiswaAuthContext::class,
]);
```

- [ ] **Step 5: Jalankan, pastikan lulus**

Run: `php artisan test --filter=InjectSiswaAuthContextTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Middleware/InjectSiswaAuthContext.php bootstrap/app.php tests/Unit/InjectSiswaAuthContextTest.php
git commit -m "feat(ujian-service): bridge session siswa auth ke kontrak _auth_user"
```

---

## Task 2: Lengkapi model `Ujian` (relasi & accessor untuk view)

**Konteks:** `store()` memanggil `$ujian->soals()` (belum ada — bug). `index.blade` memakai `$ujian->sesis()` dan accessor `status_color/status_badge/status_label` (belum ada).

**Files:**
- Modify: `app/Models/Ujian.php`
- Test: `tests/Unit/UjianModelTest.php`

- [ ] **Step 1: Tulis test gagal**

```php
<?php
// tests/Unit/UjianModelTest.php
namespace Tests\Unit;

use App\Models\Ujian;
use Tests\TestCase;

class UjianModelTest extends TestCase
{
    public function test_status_accessors_for_published(): void
    {
        $ujian = new Ujian(['status' => 'published', 'is_published' => true]);
        $this->assertSame('card-success', $ujian->status_color);
        $this->assertSame('success', $ujian->status_badge);
        $this->assertSame('Tersedia', $ujian->status_label);
    }

    public function test_status_accessors_for_draft(): void
    {
        $ujian = new Ujian(['status' => 'draft', 'is_published' => false]);
        $this->assertSame('secondary', $ujian->status_badge);
        $this->assertSame('Belum dibuka', $ujian->status_label);
    }

    public function test_soals_and_sesis_relations_exist(): void
    {
        $ujian = new Ujian();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class, $ujian->soals());
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class, $ujian->sesis());
    }
}
```

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --filter=UjianModelTest`
Expected: FAIL — `status_color` null / `soals()` not defined.

- [ ] **Step 3: Tambah relasi & accessor di `app/Models/Ujian.php`**

Tambahkan method berikut di dalam class (setelah `sesi()`):

```php
    /** Alias jamak agar konsisten dgn pemanggilan store() & view */
    public function soals()
    {
        return $this->hasMany(SoalUjian::class, 'ujian_id');
    }

    public function sesis()
    {
        return $this->hasMany(SesiUjian::class, 'ujian_id');
    }

    public function getStatusColorAttribute(): string
    {
        return ($this->status === 'published' || $this->is_published)
            ? 'card-success' : 'card-secondary';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match (true) {
            $this->status === 'published' || $this->is_published => 'success',
            $this->status === 'selesai' => 'dark',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match (true) {
            $this->status === 'published' || $this->is_published => 'Tersedia',
            $this->status === 'selesai' => 'Selesai',
            default => 'Belum dibuka',
        };
    }
```

- [ ] **Step 4: Jalankan, pastikan lulus**

Run: `php artisan test --filter=UjianModelTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Models/Ujian.php tests/Unit/UjianModelTest.php
git commit -m "feat(ujian-service): relasi soals/sesis + accessor status pada model Ujian"
```

---

## Task 3: Presenter payload sesi (`SesiPresenter`) + refactor `Api\SesiController::show`

**Konteks:** `show.blade` butuh `$soals`, `$progress`, `$sesi->sisa_waktu_detik`. Logika ini sudah ada di `Api\SesiController::show` (baris ~245-322). Ekstrak ke presenter agar web & API berbagi satu sumber.

**Files:**
- Create: `app/Support/SesiPresenter.php`
- Modify: `app/Http/Controllers/Api/SesiController.php` (method `show`)
- Test: `tests/Unit/SesiPresenterTest.php`

- [ ] **Step 1: Tulis test gagal**

```php
<?php
// tests/Unit/SesiPresenterTest.php
namespace Tests\Unit;

use App\Models\{Ujian, SesiUjian, SoalUjian, JawabanSiswa};
use App\Support\SesiPresenter;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SesiPresenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_builds_soals_progress_and_remaining_time(): void
    {
        $ujian = Ujian::create([
            'periode_id' => 1, 'judul' => 'UH1', 'tipe' => 'quiz',
            'kelas_id' => 7, 'waktu_mulai' => now()->subMinute(),
            'waktu_selesai' => now()->addHour(), 'durasi_menit' => 60,
            'passing_grade' => 70, 'status' => 'published', 'is_published' => true,
        ]);
        $soal = SoalUjian::create([
            'ujian_id' => $ujian->id, 'nomor' => 1, 'tipe' => 'pilihan_ganda',
            'pertanyaan' => '2+2?', 'opsi_a' => '3', 'opsi_b' => '4',
            'kunci_jawaban' => 'B', 'bobot_nilai' => 1,
        ]);
        $sesi = SesiUjian::create([
            'ujian_id' => $ujian->id, 'siswa_id' => 1, 'siswa_nama' => 'Budi',
            'kelas_id' => 7, 'status' => 'berlangsung', 'is_resmi' => true,
            'waktu_mulai' => now()->subMinute(), 'durasi_detik' => 3600,
        ]);
        JawabanSiswa::create([
            'sesi_ujian_id' => $sesi->id, 'soal_ujian_id' => $soal->id,
            'nomor_urut' => 1, 'jawaban' => 'B', 'is_ragu' => false,
        ]);

        $payload = (new SesiPresenter())->forPengerjaan($sesi->fresh());

        $this->assertCount(1, $payload['soals']);
        $this->assertSame('2+2?', $payload['soals'][0]['pertanyaan']);
        $this->assertNull($payload['soals'][0]['kunci_jawaban']); // disembunyikan saat berlangsung
        $this->assertSame(1, $payload['progress']['terjawab']);
        $this->assertGreaterThan(0, $payload['sisa_waktu_detik']);
    }
}
```

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --filter=SesiPresenterTest`
Expected: FAIL — class `SesiPresenter` not found.

- [ ] **Step 3: Implementasi presenter**

```php
<?php
// app/Support/SesiPresenter.php
namespace App\Support;

use App\Models\SesiUjian;
use Carbon\Carbon;

class SesiPresenter
{
    private const STATUS_BERLANGSUNG = ['berlangsung', 'sedang_mengerjakan'];

    /**
     * Susun data soal + progress + sisa waktu untuk halaman pengerjaan.
     * Kunci jawaban hanya disertakan jika sesi sudah selesai.
     */
    public function forPengerjaan(SesiUjian $sesi): array
    {
        $sesi->loadMissing(['ujian', 'jawabans.soal']);
        $berlangsung = in_array($sesi->status, self::STATUS_BERLANGSUNG, true);

        $soals = $sesi->jawabans->map(function ($jawaban) use ($sesi, $berlangsung) {
            $soal = $jawaban->soal;

            $opsi = [
                'A' => $soal->opsi_a, 'B' => $soal->opsi_b, 'C' => $soal->opsi_c,
                'D' => $soal->opsi_d, 'E' => $soal->opsi_e,
            ];
            if ($soal->tipe === 'pilihan_ganda' && $sesi->ujian->acak_opsi) {
                $opsi = collect($opsi)->filter()->shuffle()->toArray();
            }

            return [
                'id' => $soal->id,
                'nomor' => $soal->nomor,
                'tipe' => $soal->tipe,
                'pertanyaan' => $soal->pertanyaan,
                'opsi' => $opsi,
                'media_url' => $soal->media_url ?? null,
                'jawaban_id' => $jawaban->id,
                'jawaban' => $jawaban->jawaban,
                'is_ragu' => (bool) $jawaban->is_ragu,
                'kunci_jawaban' => $berlangsung ? null : $soal->kunci_jawaban,
                'is_benar' => $jawaban->is_benar,
                'nilai' => $jawaban->nilai,
            ];
        })->values()->all();

        $durasi = $sesi->durasi_detik > 0
            ? (int) $sesi->durasi_detik
            : (int) ($sesi->ujian->durasi_menit * 60);
        $waktuBerakhir = Carbon::parse($sesi->waktu_mulai)->addSeconds($durasi);
        $sisa = $berlangsung ? max(0, (int) now()->diffInSeconds($waktuBerakhir, false)) : 0;

        return [
            'soals' => $soals,
            'progress' => [
                'terjawab' => $sesi->jawabans->whereNotNull('jawaban')->count(),
                'ragu' => $sesi->jawabans->where('is_ragu', true)->count(),
                'total' => $sesi->jawabans->count(),
            ],
            'sisa_waktu_detik' => $sisa,
        ];
    }
}
```

- [ ] **Step 4: Pakai presenter di `Api\SesiController::show`**

Di `app/Http/Controllers/Api/SesiController.php`, dalam `show()`, ganti blok penyiapan `$soals`/`$sisaWaktu`/progress menjadi:

```php
        $payload = (new \App\Support\SesiPresenter())->forPengerjaan($sesi);
        $soals = $payload['soals'];
        $sisaWaktu = $payload['sisa_waktu_detik'];
```

Lalu pada array response, ganti blok `'progress' => [...]` menjadi `'progress' => $payload['progress'],`. Biarkan struktur response lain tetap.

- [ ] **Step 5: Jalankan test**

Run: `php artisan test --filter=SesiPresenterTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Support/SesiPresenter.php app/Http/Controllers/Api/SesiController.php tests/Unit/SesiPresenterTest.php
git commit -m "refactor(ujian-service): ekstrak SesiPresenter, reuse di Api show"
```

---

## Task 4: Web controller siswa (daftar/kerjakan/hasil)

**Files:**
- Create: `app/Http/Controllers/Siswa/UjianController.php`
- Test: (ditambah di Task 8)

- [ ] **Step 1: Implementasi controller**

```php
<?php
// app/Http/Controllers/Siswa/UjianController.php
namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SesiUjian;
use App\Models\Ujian;
use App\Support\SesiPresenter;
use Illuminate\Http\Request;

class UjianController extends Controller
{
    /** Daftar ujian aktif untuk kelas siswa */
    public function index(Request $request)
    {
        $ctx = $request->input('_auth_user');
        $now = now();

        $ujians = Ujian::where('is_published', true)
            ->where('kelas_id', $ctx['kelas_id'])
            ->where('waktu_mulai', '<=', $now)
            ->where('waktu_selesai', '>=', $now)
            ->orderBy('waktu_mulai')
            ->get();

        // Status sesi siswa per ujian (untuk tombol di view)
        $sesiByUjian = SesiUjian::where('siswa_id', $ctx['user_id'])
            ->where('is_resmi', true)
            ->get()->keyBy('ujian_id');

        return view('siswa.ujian.index', compact('ujians', 'sesiByUjian'));
    }

    /** Halaman pengerjaan */
    public function show(Request $request, SesiUjian $sesi)
    {
        $this->authorizeSesi($request, $sesi);

        $payload = (new SesiPresenter())->forPengerjaan($sesi);
        $sesi->setAttribute('sisa_waktu_detik', $payload['sisa_waktu_detik']);
        $soals = $payload['soals'];
        $progress = $payload['progress'];

        return view('siswa.sesi.show', compact('sesi', 'soals', 'progress'));
    }

    /** Halaman hasil */
    public function result(Request $request, SesiUjian $sesi)
    {
        $this->authorizeSesi($request, $sesi);
        abort_unless(
            in_array($sesi->status, ['selesai', 'timeout', 'diskualifikasi'], true),
            422, 'Sesi ujian belum selesai'
        );

        $sesi->load(['ujian', 'jawabans.soal']);

        $jawabans = $sesi->jawabans->map(function ($j) {
            $soal = $j->soal;
            return (object) [
                'nomor_urut' => $j->nomor_urut,
                'tipe' => $soal->tipe,
                'pertanyaan' => $soal->pertanyaan,
                'opsi' => [
                    'A' => $soal->opsi_a, 'B' => $soal->opsi_b, 'C' => $soal->opsi_c,
                    'D' => $soal->opsi_d, 'E' => $soal->opsi_e,
                ],
                'kunci_jawaban' => $soal->kunci_jawaban,
                'jawaban' => $j->jawaban,
                'is_benar' => $j->is_benar,
                'nilai' => $j->nilai,
                'feedback' => $j->feedback ?? null,
                'pembahasan' => $soal->pembahasan ?? null,
            ];
        });

        $statistik = [
            'benar' => $sesi->jawabans->where('is_benar', true)->count(),
            'salah' => $sesi->jawabans->where('is_benar', false)->count(),
            'belum_dinilai' => $sesi->jawabans->whereNull('is_benar')->count(),
        ];

        return view('siswa.sesi.result', compact('sesi', 'jawabans', 'statistik'));
    }

    private function authorizeSesi(Request $request, SesiUjian $sesi): void
    {
        $ctx = $request->input('_auth_user');
        abort_if(!$ctx || $sesi->siswa_id != $ctx['user_id'], 403, 'Unauthorized');
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/Siswa/UjianController.php
git commit -m "feat(ujian-service): web controller siswa untuk daftar/kerjakan/hasil ujian"
```

---

## Task 5: Routing web siswa (HTML + AJAX) dengan bridge

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Ganti grup siswa authenticated**

Di `routes/web.php`, ganti blok `Route::middleware('auth.siswa')->group(...)` (di dalam `prefix('siswa')->name('siswa.')`) menjadi:

```php
    // Authenticated routes (session + bridge ke kontrak _auth_user)
    Route::middleware(['auth.siswa', 'siswa.context'])->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('siswa.ujian.index'))->name('dashboard');
        Route::post('/logout', [SiswaAuthController::class, 'logout'])->name('logout');

        // Daftar ujian (HTML)
        Route::get('/ujian', [\App\Http\Controllers\Siswa\UjianController::class, 'index'])->name('ujian.index');

        // Mulai sesi (delegasi ke engine)
        Route::post('/ujian/{ujian}/sesi', [\App\Http\Controllers\Api\SesiController::class, 'store'])->name('sesi.store');

        // Pengerjaan (HTML)
        Route::get('/sesi/{sesi}', [\App\Http\Controllers\Siswa\UjianController::class, 'show'])->name('sesi.show');
        Route::get('/sesi/{sesi}/result', [\App\Http\Controllers\Siswa\UjianController::class, 'result'])->name('sesi.result');

        // Aksi AJAX (delegasi ke engine, JSON)
        Route::post('/sesi/{sesi}/answer', [\App\Http\Controllers\Api\SesiController::class, 'submitAnswer'])->name('sesi.answer');
        Route::post('/sesi/{sesi}/submit', [\App\Http\Controllers\Api\SesiController::class, 'submit'])->name('sesi.submit');
        Route::post('/sesi/{sesi}/violation', [\App\Http\Controllers\Api\SesiController::class, 'violation'])->name('sesi.violation');
    });
```

> Catatan: `siswa.sesi.store` memakai route-model-binding `{ujian}` → `SesiController@store(Request, Ujian $ujian)`. `{sesi}` → `SesiUjian`. Nama param harus `ujian` & `sesi` agar binding cocok.

- [ ] **Step 2: Verifikasi route terdaftar**

Run: `php artisan route:list --name=siswa`
Expected: muncul `siswa.ujian.index`, `siswa.sesi.store`, `siswa.sesi.show`, `siswa.sesi.result`, `siswa.sesi.answer`, `siswa.sesi.submit`, `siswa.sesi.violation`.

- [ ] **Step 3: Commit**

```bash
git add routes/web.php
git commit -m "feat(ujian-service): route web siswa untuk flow ujian via session bridge"
```

---

## Task 6: CSRF meta + perbaiki view (show & index)

**Files:**
- Modify: `resources/views/layouts/admin.blade.php`
- Modify: `resources/views/siswa/sesi/show.blade.php`
- Modify: `resources/views/siswa/ujian/index.blade.php`

- [ ] **Step 1: Tambah meta CSRF di `layouts/admin.blade.php`**

Setelah baris `<meta name="viewport" ...>`, tambah:

```html
  <meta name="csrf-token" content="{{ csrf_token() }}">
```

- [ ] **Step 2: Arahkan jQuery AJAX ke CSRF + URL web siswa di `show.blade.php`**

Di awal blok `@push('scripts')` (sebelum `let currentSoalIndex`), tambah setup global:

```javascript
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
```

Ganti URL fetch:
- `url: '/api/sesi/' + sesiId + '/answer'` → `url: '/siswa/sesi/' + sesiId + '/answer'`
- `url: '/api/sesi/' + sesiId + '/submit'` (kedua kemunculan) → `url: '/siswa/sesi/' + sesiId + '/submit'`
- `url: '/api/proktor/event'` → `url: '/siswa/sesi/' + sesiId + '/violation'`

(Redirect `'/siswa/sesi/' + sesiId + '/result'` sudah benar — biarkan.)

- [ ] **Step 3: Perbaiki `index.blade.php` agar pakai data controller**

Ganti blok PHP inline yang memakai `$ujian->sesis()->where('siswa_id', Auth::id())` menjadi pemakaian `$sesiByUjian` dari controller. Cari baris:

```php
$sesi = $ujian->sesis()->where('siswa_id', Auth::id())->where('is_resmi', true)->first();
```

ganti menjadi:

```php
$sesi = $sesiByUjian[$ujian->id] ?? null;
```

> Alasan: `Auth::id()` pada guard default mengembalikan id `UserSiswa`, bukan `siswa.id`. `$sesiByUjian` sudah di-scope ke `siswa.id` yang benar di controller (Task 4).

- [ ] **Step 4: Verifikasi tidak ada sisa URL `/api/` atau `Auth::id()` di view siswa**

Run: `grep -rn "/api/sesi\|/api/proktor\|Auth::id()" resources/views/siswa/`
Expected: kosong (tidak ada hasil).

- [ ] **Step 5: Commit**

```bash
git add resources/views/layouts/admin.blade.php resources/views/siswa/sesi/show.blade.php resources/views/siswa/ujian/index.blade.php
git commit -m "fix(ujian-service): arahkan AJAX siswa ke route web + CSRF, perbaiki id sesi di daftar"
```

---

## Task 7: Feature test flow siswa end-to-end

**Files:**
- Create: `tests/Feature/SiswaUjianFlowTest.php`

- [ ] **Step 1: Tulis feature test**

```php
<?php
// tests/Feature/SiswaUjianFlowTest.php
namespace Tests\Feature;

use App\Models\{Siswa, UserSiswa, Ujian, SoalUjian};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaUjianFlowTest extends TestCase
{
    use RefreshDatabase;

    private function seedSiswa(int $kelasId = 7): UserSiswa
    {
        $siswa = Siswa::create([
            'siakad_id' => 1, 'nis' => '12345', 'nisn' => '999',
            'nama_lengkap' => 'Budi', 'kelas_id' => $kelasId, 'kelas_nama' => 'X-A',
            'tingkat' => 10, 'status' => 'aktif',
        ]);
        return UserSiswa::create([
            'siswa_id' => $siswa->id, 'username' => '12345',
            'password' => bcrypt('12345'), 'is_active' => true,
        ]);
    }

    private function seedUjian(int $kelasId = 7): Ujian
    {
        $ujian = Ujian::create([
            'periode_id' => 1, 'judul' => 'UH1', 'tipe' => 'quiz', 'kelas_id' => $kelasId,
            'waktu_mulai' => now()->subMinute(), 'waktu_selesai' => now()->addHour(),
            'durasi_menit' => 60, 'passing_grade' => 70,
            'status' => 'published', 'is_published' => true,
        ]);
        SoalUjian::create([
            'ujian_id' => $ujian->id, 'nomor' => 1, 'tipe' => 'pilihan_ganda',
            'pertanyaan' => '2+2?', 'opsi_a' => '3', 'opsi_b' => '4',
            'kunci_jawaban' => 'B', 'bobot_nilai' => 100,
        ]);
        return $ujian;
    }

    public function test_login_redirects_to_ujian_index(): void
    {
        $this->seedSiswa();
        $resp = $this->post('/siswa/login', ['username' => '12345', 'password' => '12345']);
        $resp->assertRedirect(route('siswa.ujian.index'));
        $this->assertAuthenticatedAs(UserSiswa::first(), 'siswa');
    }

    public function test_daftar_ujian_hanya_kelas_siswa(): void
    {
        $user = $this->seedSiswa(7);
        $this->seedUjian(7);
        $this->seedUjian(99); // kelas lain

        $resp = $this->actingAs($user, 'siswa')->get('/siswa/ujian');
        $resp->assertOk()->assertSee('UH1');
        $this->assertCount(1, $resp->viewData('ujians'));
    }

    public function test_full_exam_flow(): void
    {
        $user = $this->seedSiswa();
        $ujian = $this->seedUjian();

        // Start sesi
        $start = $this->actingAs($user, 'siswa')
            ->post("/siswa/ujian/{$ujian->id}/sesi");
        $start->assertStatus(201);
        $sesiId = $start->json('data.sesi_id');

        // Buka halaman pengerjaan
        $this->actingAs($user, 'siswa')->get("/siswa/sesi/{$sesiId}")
            ->assertOk()->assertSee('2+2?');

        // Jawab
        $soalId = SoalUjian::first()->id;
        $this->actingAs($user, 'siswa')
            ->post("/siswa/sesi/{$sesiId}/answer", ['soal_id' => $soalId, 'jawaban' => 'B'])
            ->assertOk();

        // Submit
        $this->actingAs($user, 'siswa')
            ->post("/siswa/sesi/{$sesiId}/submit")->assertOk();

        // Hasil
        $this->actingAs($user, 'siswa')->get("/siswa/sesi/{$sesiId}/result")
            ->assertOk()->assertViewHas('statistik');
    }

    public function test_cannot_access_other_siswa_sesi(): void
    {
        $user = $this->seedSiswa();
        $ujian = $this->seedUjian();
        $sesiId = $this->actingAs($user, 'siswa')
            ->post("/siswa/ujian/{$ujian->id}/sesi")->json('data.sesi_id');

        $other = $this->seedSiswaOther();
        $this->actingAs($other, 'siswa')->get("/siswa/sesi/{$sesiId}")
            ->assertForbidden();
    }

    private function seedSiswaOther(): UserSiswa
    {
        $siswa = Siswa::create([
            'siakad_id' => 2, 'nis' => '67890', 'nisn' => '888',
            'nama_lengkap' => 'Siti', 'kelas_id' => 7, 'kelas_nama' => 'X-A',
            'tingkat' => 10, 'status' => 'aktif',
        ]);
        return UserSiswa::create([
            'siswa_id' => $siswa->id, 'username' => '67890',
            'password' => bcrypt('67890'), 'is_active' => true,
        ]);
    }
}
```

- [ ] **Step 2: Jalankan**

Run: `php artisan test --filter=SiswaUjianFlowTest`
Expected: PASS. Field payload sudah terkonfirmasi sesuai validasi engine: `submitAnswer` menerima `soal_id` + `jawaban` (+ opsional `is_ragu`); `submit` & `violation` tanpa body wajib. Tidak perlu penyesuaian.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/SiswaUjianFlowTest.php
git commit -m "test(ujian-service): feature test flow ujian siswa end-to-end"
```

---

## Task 8: Entry point SIAKAD → login mandiri ujian-service

**Repo:** `/home/roy/siakad-pehape-natip` (SIAKAD)

**Files:**
- Modify: `resources/views/siswa/dashboard.blade.php`
- Modify: `resources/views/layouts/partials/sidebar.blade.php`

- [ ] **Step 1: Dashboard — arahkan ke login ujian-service**

Ganti `<a href="{{ route('ujian.sso') }}" class="alert-link">` menjadi:

```blade
      <a href="{{ rtrim(config('services.ujian.url'), '/') }}/siswa/login" class="alert-link">Klik di sini untuk mengerjakan</a>.
```

- [ ] **Step 2: Sidebar siswa — arahkan ke login ujian-service**

Ganti link menu ujian siswa `<a href="{{ route('ujian.sso') }}" ...>` (blok `role==2`) menjadi:

```blade
          <a href="{{ rtrim(config('services.ujian.url'), '/') }}/siswa/login" class="nav-link" target="_blank" rel="noopener">
```

> Admin/guru (blok `role==0`/`role==1`) tetap memakai `ujian.sso` — jangan diubah.

- [ ] **Step 3: Verifikasi**

Run: `grep -n "services.ujian.url\|ujian.sso" resources/views/siswa/dashboard.blade.php resources/views/layouts/partials/sidebar.blade.php`
Expected: dashboard & menu siswa pakai `services.ujian.url`; `ujian.sso` hanya di blok admin/guru.

- [ ] **Step 4: Commit**

```bash
git add resources/views/siswa/dashboard.blade.php resources/views/layouts/partials/sidebar.blade.php
git commit -m "feat: arahkan siswa ke login mandiri ujian-service (bukan SSO)"
```

---

## Task 9: Verifikasi provisioning + dokumentasi operasional

**Files:**
- Create: `ujian-service/docs/OPERASIONAL-SYNC-SISWA.md`

- [ ] **Step 1: Verifikasi konfigurasi**

Run (di `ujian-service/`): `php artisan tinker --execute="echo config('services.siakad.base_url').PHP_EOL; echo config('services.siakad.service_key');"`
Expected: `base_url` mengarah ke host SIAKAD, `service_key` tidak kosong dan cocok dengan `UJIAN_SERVICE_KEY`/`SIAKAD_SERVICE_KEY` SIAKAD.

- [ ] **Step 2: Jalankan sync (lingkungan dengan SIAKAD aktif)**

Run: `php artisan siakad:sync-siswa`
Expected: "Synced N siswa records" + "User siswa accounts processed". Bila gagal koneksi, catat sebagai blocker lingkungan (bukan kode).

- [ ] **Step 3: Tulis doc operasional**

```markdown
# Operasional: Sinkronisasi & Login Siswa

## Provisioning akun siswa
Siswa login mandiri di ujian-service memakai tabel lokal `user_siswa`.
Akun dibuat dari data SIAKAD via:

    php artisan siakad:sync-siswa            # semua siswa aktif
    php artisan siakad:sync-siswa --kelas=7  # filter kelas

- Username = NIS. Password default = NIS (di-hash).
- Jadwalkan berkala (cron) bila perlu; `.env`: SIAKAD_SYNC_INTERVAL.

## Prasyarat
- `SIAKAD_BASE_URL` & `SIAKAD_SERVICE_KEY` (ujian-service) cocok dengan SIAKAD.
- Endpoint SIAKAD: POST /api/ujian-service/sync/siswa.

## Login
- URL: {UJIAN_SERVICE_URL}/siswa/login
- Independen dari SIAKAD saat runtime (validasi password lokal).
- Akun terkunci 15 menit setelah 5x gagal; reset via proktor.
```

- [ ] **Step 4: Commit**

```bash
git add ujian-service/docs/OPERASIONAL-SYNC-SISWA.md
git commit -m "docs(ujian-service): operasional sync & login siswa mandiri"
```

---

## Task 10: Verifikasi akhir & full test run

- [ ] **Step 1: Clear cache & jalankan seluruh test ujian-service**

Run (di `ujian-service/`):
```bash
php artisan config:clear && php artisan route:clear && php artisan view:clear
php artisan test
```
Expected: seluruh test PASS (atau hanya gagal pada test pra-eksisting yang tak terkait — catat bila ada).

- [ ] **Step 2: Smoke manual**

1. Pastikan ada minimal 1 ujian published untuk kelas siswa uji + siswa ter-sync.
2. Buka `{UJIAN_SERVICE_URL}/siswa/login`, login pakai NIS.
3. Daftar ujian muncul → "Mulai" → kerjakan → submit → hasil tampil.
4. Verifikasi tidak ada error 419 (CSRF) / 403 / 500 di log: `tail -n 50 storage/logs/laravel.log`.

- [ ] **Step 3: Commit (jika ada perubahan kecil dari smoke)**

```bash
git add -A && git commit -m "chore(ujian-service): finalisasi flow login & ujian siswa mandiri"
```

---

## Catatan Implementasi
- **TDD:** Task 1-3 & 7 punya test dulu. Task 4-6 (controller/route/view) diverifikasi via feature test Task 7 — jalankan Task 7 setelah 4-6 selesai.
- **Field jawaban terkonfirmasi:** `submitAnswer` → `soal_id` + `jawaban` (+ opsional `is_ragu`/`jawaban_id`); `submit`/`violation` tanpa body wajib. View & test sudah selaras.
- **Jangan ubah** flow admin/guru (JWT handoff) maupun alias `jwt.verify`/`exam.role` (di luar scope).
