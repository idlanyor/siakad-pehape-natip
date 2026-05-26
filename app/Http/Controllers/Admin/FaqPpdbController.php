<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqPpdb;
use Illuminate\Http\Request;

class FaqPpdbController extends Controller
{
    public function index()
    {
        $faqList = FaqPpdb::orderBy('urutan')->get();
        return view('admin.ppdb.faq.index', compact('faqList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pertanyaan' => 'required|string|max:255',
            'jawaban'    => 'required|string',
            'urutan'     => 'nullable|integer',
        ]);

        FaqPpdb::create([
            'pertanyaan' => $request->pertanyaan,
            'jawaban'    => $request->jawaban,
            'urutan'     => $request->urutan ?? (FaqPpdb::max('urutan') + 1),
            'is_aktif'   => $request->boolean('is_aktif', true),
        ]);

        return back()->with('success', 'FAQ berhasil ditambahkan.');
    }

    public function update(Request $request, FaqPpdb $faq)
    {
        $request->validate([
            'pertanyaan' => 'required|string|max:255',
            'jawaban'    => 'required|string',
        ]);

        $faq->update([
            'pertanyaan' => $request->pertanyaan,
            'jawaban'    => $request->jawaban,
            'urutan'     => $request->urutan ?? $faq->urutan,
            'is_aktif'   => $request->boolean('is_aktif'),
        ]);

        return back()->with('success', 'FAQ berhasil diperbarui.');
    }

    public function destroy(FaqPpdb $faq)
    {
        $faq->delete();
        return back()->with('success', 'FAQ berhasil dihapus.');
    }
}
