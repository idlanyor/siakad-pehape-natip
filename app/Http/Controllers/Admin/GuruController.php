<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GuruRequest;
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

    public function store(GuruRequest $request)
    {
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

    public function update(GuruRequest $request, Guru $guru)
    {
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

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:gurus,id'],
        ]);

        $userIds = Guru::whereIn('id', $data['ids'])->pluck('user_id');
        User::whereIn('id', $userIds)->each(fn ($u) => $u->delete());

        $jumlah = $userIds->count();
        return redirect()->route('admin.guru.index')->with('success', "$jumlah guru berhasil dihapus.");
    }
}
