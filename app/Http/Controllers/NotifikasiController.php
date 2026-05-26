<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index()
    {
        $notifikasis = Notifikasi::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Mark semua yang dibuka sebagai read
        Notifikasi::where('user_id', auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        return view('notifikasi.index', compact('notifikasis'));
    }

    public function markRead(Notifikasi $notifikasi)
    {
        abort_if($notifikasi->user_id !== auth()->id(), 403);
        $notifikasi->markAsRead();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return $notifikasi->url
            ? redirect($notifikasi->url)
            : back();
    }

    public function markAllRead()
    {
        Notifikasi::where('user_id', auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}
