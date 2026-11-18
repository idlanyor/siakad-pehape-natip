<?php

namespace App\Modules\Ujian\Http\Controllers;

use App\Modules\Ujian\Support\TeksSoal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Unggah gambar untuk soal (bank soal maupun soal ujian). Mengembalikan
 * potongan Markdown yang disisipkan editor ke teks soal.
 */
class MediaSoalController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $user->isGuru(), 403);

        $request->validate([
            'gambar' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
        ], [
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
            'gambar.mimes' => 'Format gambar harus JPG, PNG, GIF, atau WebP.',
        ]);

        // Nama file acak: tidak bisa ditebak sebelum soal dipakai.
        $path = $request->file('gambar')->store(TeksSoal::FOLDER, 'public');
        $url = '/storage/'.$path;

        return response()->json(['url' => $url, 'markdown' => "![gambar]({$url})"], 201);
    }
}
