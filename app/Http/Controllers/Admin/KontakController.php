<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PesanKontak;
use Illuminate\Http\Request;

class KontakController extends Controller
{
    public function index(Request $request)
    {
        $query = PesanKontak::orderBy('created_at', 'desc');

        if ($request->filter === 'unread') {
            $query->unread();
        }

        $pesanList   = $query->paginate(25)->withQueryString();
        $unreadCount = PesanKontak::unread()->count();

        return view('admin.kontak.index', compact('pesanList', 'unreadCount'));
    }

    public function show(PesanKontak $kontak)
    {
        $kontak->markAsRead();
        return view('admin.kontak.show', compact('kontak'));
    }

    public function destroy(PesanKontak $kontak)
    {
        $kontak->delete();
        return redirect()->route('admin.kontak.index')
            ->with('success', 'Pesan berhasil dihapus.');
    }

    public function markAllRead()
    {
        PesanKontak::unread()->update(['is_read' => true, 'read_at' => now()]);
        return back()->with('success', 'Semua pesan ditandai sudah dibaca.');
    }
}
