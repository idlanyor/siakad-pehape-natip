<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\Galeri;
use App\Models\HalamanStatis;
use App\Models\Jurusan;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = collect();

        // Static pages
        $urls->push(['loc' => route('landing.index'),   'priority' => '1.0', 'changefreq' => 'weekly']);
        $urls->push(['loc' => route('landing.tentang'),  'priority' => '0.8', 'changefreq' => 'monthly']);
        $urls->push(['loc' => route('landing.program'),  'priority' => '0.8', 'changefreq' => 'monthly']);
        $urls->push(['loc' => route('berita.index'),     'priority' => '0.9', 'changefreq' => 'daily']);
        $urls->push(['loc' => route('galeri.index'),     'priority' => '0.7', 'changefreq' => 'weekly']);
        $urls->push(['loc' => route('ppdb.landing'),     'priority' => '0.9', 'changefreq' => 'weekly']);

        // Halaman statis dinamis
        HalamanStatis::published()->get()->each(function ($h) use ($urls) {
            $urls->push(['loc' => route('landing.halaman', $h->slug), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $h->updated_at?->toAtomString()]);
        });

        // Artikel
        Artikel::published()->orderBy('published_at', 'desc')->get()->each(function ($a) use ($urls) {
            $urls->push(['loc' => route('berita.show', $a->slug), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $a->updated_at?->toAtomString()]);
        });

        // Galeri
        Galeri::published()->get()->each(function ($g) use ($urls) {
            $urls->push(['loc' => route('galeri.show', $g->id), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $g->updated_at?->toAtomString()]);
        });

        return response()->view('sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }
}
