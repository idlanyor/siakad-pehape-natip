<?php

namespace App\Http\Controllers\Proktor;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\UserSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Controller untuk proktor mengelola akun siswa
 */
class SiswaController extends Controller
{
    /**
     * Display list of siswa accounts
     */
    public function index(Request $request)
    {
        $query = UserSiswa::with('siswa');

        // Filter by kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search by name or NIS
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $userSiswa = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get kelas list for filter
        $kelasList = Siswa::select('kelas_id', 'kelas_nama')
            ->distinct()
            ->orderBy('kelas_nama')
            ->get();

        return view('proktor.siswa.index', compact('userSiswa', 'kelasList'));
    }

    /**
     * Show detail of siswa account
     */
    public function show($id)
    {
        $userSiswa = UserSiswa::with('siswa')->findOrFail($id);

        return view('proktor.siswa.show', compact('userSiswa'));
    }

    /**
     * Reset password siswa to default (NIS)
     */
    public function resetPassword($id)
    {
        $userSiswa = UserSiswa::with('siswa')->findOrFail($id);
        
        $userSiswa->resetPasswordToDefault();

        return back()->with('success', "Password siswa {$userSiswa->siswa->nama_lengkap} berhasil direset ke default (NIS).");
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        $userSiswa = UserSiswa::with('siswa')->findOrFail($id);
        
        $userSiswa->update([
            'is_active' => !$userSiswa->is_active,
            'login_attempts' => 0,
            'locked_until' => null
        ]);

        $status = $userSiswa->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun siswa {$userSiswa->siswa->nama_lengkap} berhasil {$status}.");
    }

    /**
     * Unlock account
     */
    public function unlock($id)
    {
        $userSiswa = UserSiswa::with('siswa')->findOrFail($id);
        
        $userSiswa->resetLoginAttempts();

        return back()->with('success', "Akun siswa {$userSiswa->siswa->nama_lengkap} berhasil dibuka kembali.");
    }

    /**
     * Bulk reset passwords
     */
    public function bulkResetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:user_siswa,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $count = 0;
        foreach ($request->siswa_ids as $id) {
            $userSiswa = UserSiswa::find($id);
            if ($userSiswa) {
                $userSiswa->resetPasswordToDefault();
                $count++;
            }
        }

        return back()->with('success', "Berhasil mereset password {$count} siswa ke default (NIS).");
    }

    /**
     * Bulk activate/deactivate
     */
    public function bulkToggleActive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:user_siswa,id',
            'is_active' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $count = UserSiswa::whereIn('id', $request->siswa_ids)
            ->update([
                'is_active' => $request->is_active,
                'login_attempts' => 0,
                'locked_until' => null
            ]);

        $status = $request->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Berhasil {$status} {$count} akun siswa.");
    }

    /**
     * Change password for specific siswa
     */
    public function changePassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $userSiswa = UserSiswa::with('siswa')->findOrFail($id);
        
        $userSiswa->update([
            'password' => Hash::make($request->new_password),
            'login_attempts' => 0,
            'locked_until' => null
        ]);

        return back()->with('success', "Password siswa {$userSiswa->siswa->nama_lengkap} berhasil diubah.");
    }

    /**
     * Export siswa accounts to CSV
     */
    public function export(Request $request)
    {
        $query = UserSiswa::with('siswa');

        // Apply same filters as index
        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $userSiswa = $query->get();

        $filename = 'akun-siswa-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($userSiswa) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['NIS', 'Nama', 'Kelas', 'Username', 'Password Default', 'Status', 'Login Terakhir']);

            // Data
            foreach ($userSiswa as $user) {
                fputcsv($file, [
                    $user->siswa->nis,
                    $user->siswa->nama_lengkap,
                    $user->siswa->kelas_nama,
                    $user->username,
                    $user->siswa->nis, // Password default = NIS
                    $user->is_active ? 'Aktif' : 'Nonaktif',
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get statistics
     */
    public function statistics()
    {
        $stats = [
            'total' => UserSiswa::count(),
            'active' => UserSiswa::where('is_active', true)->count(),
            'inactive' => UserSiswa::where('is_active', false)->count(),
            'locked' => UserSiswa::whereNotNull('locked_until')
                ->where('locked_until', '>', now())
                ->count(),
            'never_login' => UserSiswa::whereNull('last_login_at')->count(),
            'logged_in_today' => UserSiswa::whereDate('last_login_at', today())->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
