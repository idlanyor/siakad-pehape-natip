<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OrangTuaController extends Controller
{
    public function index()
    {
        $orangTuas = OrangTua::with(['user', 'siswas'])->get();
        return view('admin.orangtua.index', compact('orangTuas'));
    }

    public function create()
    {
        $siswas = Siswa::with('kelas')->get();
        return view('admin.orangtua.form', ['orangTua' => null, 'siswas' => $siswas]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'username'  => 'required|string|max:50|unique:users,username',
            'email'     => 'nullable|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'telepon'   => 'nullable|string|max:20',
            'pekerjaan' => 'nullable|string|max:100',
            'alamat'    => 'nullable|string|max:500',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 4,
        ]);

        OrangTua::create([
            'user_id'   => $user->id,
            'nama'      => $request->name,
            'telepon'   => $request->telepon,
            'pekerjaan' => $request->pekerjaan,
            'alamat'    => $request->alamat,
        ]);

        return redirect()->route('admin.orangtua.index')->with('success', 'Akun orang tua berhasil dibuat.');
    }

    public function edit(OrangTua $orangtua)
    {
        $orangtua->load('siswas');
        $siswas = Siswa::with('kelas')->get();
        return view('admin.orangtua.form', ['orangTua' => $orangtua, 'siswas' => $siswas]);
    }

    public function update(Request $request, OrangTua $orangtua)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'username'  => 'required|string|max:50|unique:users,username,' . $orangtua->user_id,
            'email'     => 'nullable|email|unique:users,email,' . $orangtua->user_id,
            'password'  => 'nullable|string|min:6',
            'telepon'   => 'nullable|string|max:20',
            'pekerjaan' => 'nullable|string|max:100',
            'alamat'    => 'nullable|string|max:500',
        ]);

        $userData = [
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
        ];
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        $orangtua->user->update($userData);

        $orangtua->update([
            'nama'      => $request->name,
            'telepon'   => $request->telepon,
            'pekerjaan' => $request->pekerjaan,
            'alamat'    => $request->alamat,
        ]);

        return redirect()->route('admin.orangtua.index')->with('success', 'Akun orang tua berhasil diperbarui.');
    }

    public function destroy(OrangTua $orangtua)
    {
        // Hapus user terkait → cascade hapus orang_tua & pivot.
        $orangtua->user->delete();
        return redirect()->route('admin.orangtua.index')->with('success', 'Akun orang tua berhasil dihapus.');
    }

    /**
     * Assign anak (siswa) ke orang tua dengan jenis hubungan.
     */
    public function assignAnak(Request $request, OrangTua $orangtua)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'hubungan' => 'required|in:ayah,ibu,wali',
        ]);

        $orangtua->siswas()->syncWithoutDetaching([
            $request->siswa_id => ['hubungan' => $request->hubungan],
        ]);

        return back()->with('success', 'Anak berhasil dihubungkan.');
    }

    public function detachAnak(OrangTua $orangtua, Siswa $siswa)
    {
        $orangtua->siswas()->detach($siswa->id);
        return back()->with('success', 'Hubungan anak berhasil dilepas.');
    }
}
