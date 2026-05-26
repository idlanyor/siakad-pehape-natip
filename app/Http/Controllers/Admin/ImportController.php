<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\GuruImport;
use App\Imports\MapelImport;
use App\Imports\SiswaImport;
use App\Imports\TagihanSppImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    private const TYPES = [
        'guru'    => ['label' => 'Data Guru',          'import' => GuruImport::class],
        'siswa'   => ['label' => 'Data Siswa',         'import' => SiswaImport::class],
        'mapel'   => ['label' => 'Mata Pelajaran',     'import' => MapelImport::class],
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
        if (!array_key_exists($type, self::TYPES) || !file_exists($file)) {
            abort(404, 'Template tidak ditemukan.');
        }

        return response()->download($file, "template_{$type}.xlsx");
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(self::TYPES)),
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
                ->with('error', 'Import gagal: ' . $e->getMessage())
                ->with('active_tab', $type);
        }

        $result = [
            'type'     => $type,
            'label'    => $meta['label'],
            'imported' => $importClass->imported ?? 0,
            'updated'  => $importClass->updated ?? 0,
            'skipped'  => $importClass->skipped ?? [],
        ];

        return back()
            ->with('import_result', $result)
            ->with('active_tab', $type);
    }
}
