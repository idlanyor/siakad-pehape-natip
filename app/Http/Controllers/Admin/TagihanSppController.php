<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SppExport;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TagihanSpp;
use App\Models\TahunAjaran;
use App\Models\TarifSpp;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TagihanSppController extends Controller
{
    public function index(Request $request)
    {
        $query = TagihanSpp::with(['siswa.kelas', 'siswa.jurusan'])
            ->orderBy('bulan', 'desc')
            ->orderBy('siswa_id');

        if ($request->filled('bulan')) {
            $query->whereYear('bulan', substr($request->bulan, 0, 4))
                  ->whereMonth('bulan', substr($request->bulan, 5, 2));
        }
        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $request->kelas_id));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tagihans   = $query->get();
        $kelas      = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $statusList = TagihanSpp::$STATUS_LABELS;

        // Ringkasan
        $summary = [
            'total_nominal'    => $tagihans->sum('nominal'),
            'jumlah_lunas'     => $tagihans->where('status', 'lunas')->count(),
            'jumlah_cicil'     => $tagihans->where('status', 'cicil')->count(),
            'jumlah_belum'     => $tagihans->where('status', 'belum')->count(),
        ];

        return view('admin.keuangan.tagihan.index', compact('tagihans', 'kelas', 'statusList', 'summary'));
    }

    public function create()
    {
        $kelas        = Kelas::with(['jurusan', 'siswas'])->orderBy('nama_kelas')->get();
        $tahunAjarans = TahunAjaran::orderBy('nama', 'desc')->get();
        $tarifAktif   = TarifSpp::with(['kelas', 'tahunAjaran'])
            ->whereHas('tahunAjaran', fn($q) => $q->where('aktif', true))
            ->get()
            ->keyBy('kelas_id');

        return view('admin.keuangan.tagihan.create', compact('kelas', 'tahunAjarans', 'tarifAktif'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'bulan'    => 'required|date_format:Y-m',
            'nominal'  => 'required|numeric|min:1000',
        ]);

        $kelas   = Kelas::findOrFail($request->kelas_id);
        $dibuat  = TagihanSpp::generateBatch($kelas, $request->bulan, (float) $request->nominal);
        $total   = $kelas->siswas()->count();
        $dilewati = $total - $dibuat;

        $msg = "Generate tagihan selesai: {$dibuat} tagihan baru dibuat";
        if ($dilewati > 0) {
            $msg .= ", {$dilewati} sudah ada (dilewati)";
        }
        $msg .= '.';

        return redirect()->route('admin.keuangan.tagihan.index')
            ->with('success', $msg);
    }

    public function show(TagihanSpp $tagihan)
    {
        $tagihan->load(['siswa.kelas.jurusan', 'siswa.jurusan', 'pembayarans.pencatat']);
        $metodeList = \App\Models\PembayaranSpp::METODE_LIST;

        return view('admin.keuangan.tagihan.show', compact('tagihan', 'metodeList'));
    }

    public function destroy(TagihanSpp $tagihan)
    {
        if ($tagihan->pembayarans()->exists()) {
            return back()->with('error', 'Tagihan tidak dapat dihapus karena sudah ada riwayat pembayaran.');
        }

        $siswaName = $tagihan->siswa->nama;
        $bulan     = $tagihan->bulan_label;
        $tagihan->delete();

        return redirect()->route('admin.keuangan.tagihan.index')
            ->with('success', "Tagihan SPP {$bulan} untuk {$siswaName} berhasil dihapus.");
    }

    public function export(Request $request)
    {
        $query = TagihanSpp::with(['siswa.kelas', 'siswa.jurusan'])->orderBy('bulan', 'desc');

        if ($request->filled('bulan')) {
            $query->whereYear('bulan', substr($request->bulan, 0, 4))
                  ->whereMonth('bulan', substr($request->bulan, 5, 2));
        }
        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $request->kelas_id));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tagihans = $query->get();
        $bulanLabel = $request->bulan ? str_replace('-', '_', $request->bulan) : 'Semua';
        return Excel::download(new SppExport($tagihans), "SPP_{$bulanLabel}.xlsx");
    }
}
