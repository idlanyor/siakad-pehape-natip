<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotifikasiResource;
use App\Models\Notifikasi;

class NotifikasiController extends Controller
{
    public function index()
    {
        $notifikasis = Notifikasi::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return NotifikasiResource::collection($notifikasis);
    }

    public function markRead(Notifikasi $notifikasi)
    {
        abort_if($notifikasi->user_id !== auth()->id(), 403, 'Akses ditolak.');

        $notifikasi->markAsRead();

        return response()->json(['message' => 'Notifikasi ditandai sudah dibaca.']);
    }
}
