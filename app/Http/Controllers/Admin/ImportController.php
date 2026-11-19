<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AkunImportExport;
use App\Http\Controllers\Controller;
use App\Imports\GuruImport;
use App\Imports\MapelImport;
use App\Imports\SiswaImport;
use App\Imports\TagihanSppImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    private const TYPES = [
        'guru' => ['label' => 'Data Guru',          'import' => GuruImport::class],
        'siswa' => ['label' => 'Data Siswa',         'import' => SiswaImport::class],
        'mapel' => ['label' => 'Mata Pelajaran',     'import' => MapelImport::class],
        'tagihan' => ['label' => 'Tagihan SPP',        'import' => TagihanSppImport::class],
    ];

    public function index()
    {
        $types = self::TYPES;

        return view('admin.import.index', compact('types'));
    }

    public function template(string $type)
    {
        $file = resource_path("templates/template_{$type}.xlsx");
        if (! array_key_exists($type, self::TYPES) || ! file_exists($file)) {
            abort(404, 'Template tidak ditemukan.');
        }

        return response()->download($file, "template_{$type}.xlsx");
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:'.implode(',', array_keys(self::TYPES)),
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        $type = $request->type;
        $meta = self::TYPES[$type];
        $importClass = new ($meta['import']);

        try {
            DB::beginTransaction();
            Excel::import($importClass, $request->file('file'));
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Import gagal: '.$e->getMessage())
                ->with('active_tab', $type);
        }

        $result = [
            'type' => $type,
            'label' => $meta['label'],
            'imported' => $importClass->imported ?? 0,
            'updated' => $importClass->updated ?? 0,
            'skipped' => $importClass->skipped ?? [],
        ];

        // Akun baru beserta password awalnya: simpan terenkripsi sebentar agar admin bisa
        // mengunduhnya sekali untuk dibagikan. Password tidak pernah ditampilkan di halaman.
        if (! empty($importClass->akun ?? [])) {
            $token = Str::random(40);
            Cache::put(self::kunciAkun($token), Crypt::encrypt([
                'user_id' => $request->user()->id,
                'type' => $type,
                'akun' => $importClass->akun,
            ]), now()->addMinutes(30));
            $result['akun_token'] = $token;
            $result['akun_count'] = count($importClass->akun);
        }

        return back()
            ->with('import_result', $result)
            ->with('active_tab', $type);
    }

    /** Unduh daftar akun hasil impor. Sekali pakai, hanya oleh admin yang mengimpor, 30 menit. */
    public function unduhAkun(Request $request, string $token)
    {
        $simpan = Cache::get(self::kunciAkun($token));
        $data = $simpan ? Crypt::decrypt($simpan) : null;
        abort_unless($data && $data['user_id'] === $request->user()->id, 404);
        Cache::forget(self::kunciAkun($token));

        $nama = 'akun-'.$data['type'].'-'.now()->format('Ymd-His').'.xlsx';

        $respons = Excel::download(new AkunImportExport($data['akun']), $nama);
        $respons->headers->set('Cache-Control', 'no-store, private');

        return $respons;
    }

    private static function kunciAkun(string $token): string
    {
        return 'import-akun:'.hash('sha256', $token);
    }
}
