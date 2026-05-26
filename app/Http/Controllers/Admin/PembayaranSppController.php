<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PembayaranSpp;
use App\Models\TagihanSpp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembayaranSppController extends Controller
{
    public function store(Request $request, TagihanSpp $tagihan)
    {
        // Cegah pencatatan jika sudah lunas
        if ($tagihan->status === 'lunas') {
            return back()->with('error', 'Tagihan ini sudah berstatus LUNAS, tidak perlu pembayaran lagi.');
        }

        $request->validate([
            'tanggal_bayar' => 'required|date',
            'nominal_bayar' => 'required|numeric|min:1000',
            'metode'        => 'required|in:tunai,transfer,qris',
            'bukti'         => 'nullable|image|max:2048',
            'catatan'       => 'nullable|string|max:255',
        ]);

        // Cegah over-payment
        $sisa = $tagihan->sisa_tagihan;
        if ($request->nominal_bayar > $sisa) {
            return back()
                ->withErrors(['nominal_bayar' => "Nominal melebihi sisa tagihan (Rp " . number_format($sisa, 0, ',', '.') . ")."])
                ->withInput();
        }

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('spp/bukti', 'public');
        }

        PembayaranSpp::create([
            'tagihan_spp_id' => $tagihan->id,
            'tanggal_bayar'  => $request->tanggal_bayar,
            'nominal_bayar'  => $request->nominal_bayar,
            'metode'         => $request->metode,
            'bukti'          => $buktiPath,
            'catatan'        => $request->catatan,
            'dicatat_oleh'   => auth()->id(),
        ]);

        // Recalculate status tagihan
        $tagihan->refresh();
        $tagihan->recalculateStatus();

        return back()->with('success', 'Pembayaran berhasil dicatat. Status tagihan diperbarui.');
    }

    public function destroy(PembayaranSpp $pembayaran)
    {
        $tagihan = $pembayaran->tagihan;

        if ($pembayaran->bukti) {
            Storage::disk('public')->delete($pembayaran->bukti);
        }

        $pembayaran->delete();

        // Recalculate status tagihan setelah hapus pembayaran
        $tagihan->refresh();
        $tagihan->recalculateStatus();

        return back()->with('success', 'Catatan pembayaran berhasil dihapus.');
    }
}
