<?php

namespace App\Http\Controllers\OrangTua;

use App\Models\Jadwal;
use App\Models\Semester;
use App\Models\TagihanSpp;
use Illuminate\Http\Request;

class AnakController extends BaseOrangTuaController
{
    /**
     * Ganti anak aktif (jika orang tua punya >1 anak).
     */
    public function switch(Request $request)
    {
        $ortu = $this->orangTua();
        $siswaId = (int) $request->input('siswa_id');

        // Validasi kepemilikan — cegah switch ke anak orang lain.
        abort_unless($ortu->siswas->pluck('id')->contains($siswaId), 403);

        session(['ortu_anak_aktif' => $siswaId]);

        return back()->with('success', 'Anak aktif berhasil diganti.');
    }
}
