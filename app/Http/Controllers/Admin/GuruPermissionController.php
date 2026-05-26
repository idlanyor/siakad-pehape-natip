<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\GuruPermission;
use Illuminate\Http\Request;

class GuruPermissionController extends Controller
{
    public function edit(Guru $guru)
    {
        $permission = $guru->permission ?? new GuruPermission(['guru_id' => $guru->id]);
        return view('admin.guru.permission', compact('guru', 'permission'));
    }

    public function update(Request $request, Guru $guru)
    {
        GuruPermission::updateOrCreate(
            ['guru_id' => $guru->id],
            [
                'menu_jadwal'  => $request->boolean('menu_jadwal'),
                'menu_mapel'   => $request->boolean('menu_mapel'),
                'menu_absensi' => $request->boolean('menu_absensi'),
                'menu_nilai'   => $request->boolean('menu_nilai'),
                'menu_pkl'     => $request->boolean('menu_pkl'),
                'menu_ekskul'  => $request->boolean('menu_ekskul'),
            ]
        );

        return redirect()->route('admin.guru.index')
            ->with('success', 'Hak akses guru ' . $guru->nama . ' berhasil disimpan.');
    }
}
