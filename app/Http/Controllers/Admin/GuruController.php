<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    public function index()
    {
        $gurus = Guru::with('user')->get();
        return view('admin.guru.index', compact('gurus'));
    }

    public function create()
    {
        return view('admin.guru.form', ['guru' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'username'      => 'required|string|max:50|unique:users,username',
            'email'         => 'nullable|email|unique:users,email',
            'password'      => 'required|string|min:6',
            'nip'           => 'nullable|string|max:30|unique:gurus,nip',
            'bidang_studi'  => 'nullable|string|max:100',
            'no_hp'         => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 1,
        ]);

        Guru::create([
            'user_id'      => $user->id,
            'nip'          => $request->nip,
            'nama'         => $request->name,
            'bidang_studi' => $request->bidang_studi,
            'no_hp'        => $request->no_hp,
        ]);

        return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit(Guru $guru)
    {
        return view('admin.guru.form', compact('guru'));
    }

    public function update(Request $request, Guru $guru)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'username'      => 'required|string|max:50|unique:users,username,' . $guru->user_id,
            'email'         => 'nullable|email|unique:users,email,' . $guru->user_id,
            'password'      => 'nullable|string|min:6',
            'nip'           => 'nullable|string|max:30|unique:gurus,nip,' . $guru->id,
            'bidang_studi'  => 'nullable|string|max:100',
            'no_hp'         => 'nullable|string|max:20',
        ]);

        $userData = [
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
        ];
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        $guru->user->update($userData);

        $guru->update([
            'nip'          => $request->nip,
            'nama'         => $request->name,
            'bidang_studi' => $request->bidang_studi,
            'no_hp'        => $request->no_hp,
        ]);

        return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $guru->user->delete(); // cascade deletes guru too
        return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil dihapus.');
    }
}
