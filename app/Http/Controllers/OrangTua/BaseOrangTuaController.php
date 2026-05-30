<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\OrangTua;
use App\Models\Siswa;

/**
 * Base controller untuk portal Orang Tua / Wali (role=4).
 *
 * Menyediakan resolusi "anak aktif" — orang tua dapat memiliki lebih dari
 * satu anak; anak yang sedang dilihat disimpan di session dan selalu
 * divalidasi kepemilikannya untuk mencegah IDOR.
 */
abstract class BaseOrangTuaController extends Controller
{
    protected function orangTua(): OrangTua
    {
        return OrangTua::with('siswas.kelas')
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    /**
     * Ambil anak yang sedang aktif dipilih. Default ke anak pertama.
     * Selalu pastikan anak benar-benar milik orang tua ini.
     */
    protected function anakAktif(OrangTua $ortu): ?Siswa
    {
        $anakIds = $ortu->siswas->pluck('id');

        if ($anakIds->isEmpty()) {
            return null;
        }

        $aktifId = session('ortu_anak_aktif');

        if (! $aktifId || ! $anakIds->contains($aktifId)) {
            $aktifId = $anakIds->first();
            session(['ortu_anak_aktif' => $aktifId]);
        }

        return $ortu->siswas->firstWhere('id', $aktifId);
    }
}
