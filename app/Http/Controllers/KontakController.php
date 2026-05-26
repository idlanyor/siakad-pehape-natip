<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\PesanKontak;
use App\Models\User;
use Illuminate\Http\Request;

class KontakController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama'   => 'required|string|max:255',
            'email'  => 'required|email|max:255',
            'no_hp'  => 'nullable|string|max:20',
            'subjek' => 'required|string|max:255',
            'pesan'  => 'required|string|min:10|max:2000',
        ]);

        $pesan = PesanKontak::create($request->only(['nama', 'email', 'no_hp', 'subjek', 'pesan']));

        // Kirim notifikasi in-app ke semua admin
        User::where('role', 0)->each(function ($admin) use ($pesan) {
            Notifikasi::create([
                'user_id' => $admin->id,
                'judul'   => 'Pesan Kontak Baru: ' . $pesan->subjek,
                'pesan'   => 'Dari ' . $pesan->nama . ' (' . $pesan->email . '): ' .
                             \Str::limit($pesan->pesan, 100),
                'url'     => route('admin.kontak.show', $pesan->id),
            ]);
        });

        return back()->with('kontak_success', 'Pesan Anda berhasil dikirim! Kami akan segera menghubungi Anda.');
    }
}
