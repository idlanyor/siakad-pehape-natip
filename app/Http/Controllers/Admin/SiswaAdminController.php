<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiswaRequest;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SiswaAdminController extends Controller
{
    public function index()
    {
        $siswas = Siswa::with(['user', 'jurusan', 'kelas'])->get();
        return view('admin.siswa.index', compact('siswas'));
    }

    public function create()
    {
        $jurusans = Jurusan::all();
        $kelas    = Kelas::with('jurusan')->get();
        return view('admin.siswa.form', ['siswa' => null, 'jurusans' => $jurusans, 'kelas' => $kelas]);
    }

    public function store(SiswaRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 2,
        ]);

        Siswa::create([
            'user_id'       => $user->id,
            'jurusan_id'    => $request->jurusan_id,
            'kelas_id'      => $request->kelas_id,
            'nama'          => $request->name,
            'nisn'          => $request->nisn,
            'tanggal_lahir' => $request->tanggal_lahir,
        ]);

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa)
    {
        $jurusans = Jurusan::all();
        $kelas    = Kelas::with('jurusan')->get();
        return view('admin.siswa.form', compact('siswa', 'jurusans', 'kelas'));
    }

    public function update(SiswaRequest $request, Siswa $siswa)
    {
        $userData = [
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
        ];
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        $siswa->user->update($userData);

        $siswa->update([
            'jurusan_id'    => $request->jurusan_id,
            'kelas_id'      => $request->kelas_id,
            'nama'          => $request->name,
            'nisn'          => $request->nisn,
            'tanggal_lahir' => $request->tanggal_lahir,
        ]);

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->user->delete();
        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:siswas,id'],
        ]);

        // Hapus akun login → siswa ikut terhapus (cascade), seperti destroy tunggal.
        $userIds = Siswa::whereIn('id', $data['ids'])->pluck('user_id');
        User::whereIn('id', $userIds)->each(fn ($u) => $u->delete());

        $jumlah = $userIds->count();
        return redirect()->route('admin.siswa.index')->with('success', "$jumlah siswa berhasil dihapus.");
    }
}
