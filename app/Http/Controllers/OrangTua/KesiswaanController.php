<?php

namespace App\Http\Controllers\OrangTua;

use App\Models\TahunAjaran;

class KesiswaanController extends BaseOrangTuaController
{
    public function index()
    {
        $ortu = $this->orangTua();
        $anak = $this->anakAktif($ortu);
        abort_if(! $anak, 404, 'Belum ada data anak.');

        $pelanggarans = $anak->pelanggarans()->orderBy('tanggal', 'desc')->get();
        $totalPoin = $pelanggarans->sum('poin');

        $prestasis = $anak->prestasis()->orderBy('tanggal', 'desc')->get();

        return view('orangtua.kesiswaan', compact('ortu', 'anak', 'pelanggarans', 'totalPoin', 'prestasis'));
    }
}
