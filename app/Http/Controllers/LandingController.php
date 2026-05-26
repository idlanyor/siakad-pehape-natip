<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\Ekskul;
use App\Models\Galeri;
use App\Models\Guru;
use App\Models\HalamanStatis;
use App\Models\JabatanOrganisasi;
use App\Models\Jurusan;
use App\Models\Prestasi;
use App\Models\Siswa;

class LandingController extends Controller
{
    public function index()
    {
        $jurusans  = Jurusan::orderBy('nama_jurusan')->get();
        $ekskuls   = Ekskul::with('pembina')->orderBy('nama')->get();
        $prestasis = Prestasi::with('siswa')
            ->whereIn('tingkat', ['Kabupaten/Kota', 'Provinsi', 'Nasional', 'Internasional'])
            ->orderBy('tanggal', 'desc')
            ->limit(6)
            ->get();

        $stats = [
            'siswa'    => Siswa::count(),
            'guru'     => Guru::count(),
            'jurusan'  => $jurusans->count(),
            'prestasi' => Prestasi::count(),
            'ekskul'   => $ekskuls->count(),
            'tahun_berdiri' => setting('sekolah_tahun_berdiri', '1975'),
        ];

        $artikelTerbaru = Artikel::published()->orderBy('published_at', 'desc')->limit(3)->get();
        $galeriTerbaru  = Galeri::published()->withCount('items')->orderBy('tanggal', 'desc')->limit(4)->get();

        return view('public.landing.index', compact('jurusans', 'ekskuls', 'prestasis', 'stats', 'artikelTerbaru', 'galeriTerbaru'));
    }

    public function tentang()
    {
        $halaman = HalamanStatis::where('slug', 'tentang')->published()->first();
        return view('public.landing.tentang', compact('halaman'));
    }

    public function program()
    {
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        return view('public.landing.program', compact('jurusans'));
    }

    public function halaman(HalamanStatis $halaman)
    {
        abort_if(! $halaman->is_published, 404);
        return view('public.landing.halaman', compact('halaman'));
    }

    public function organisasi()
    {
        $jabatans = JabatanOrganisasi::with([
                'anggota',
                'children.anggota',
                'children.children.anggota',
            ])
            ->whereNull('parent_id')
            ->orderBy('urutan')
            ->get();

        return view('public.landing.organisasi', compact('jabatans'));
    }
}
