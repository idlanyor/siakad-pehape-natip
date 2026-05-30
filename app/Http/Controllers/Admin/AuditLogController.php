<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AuditLogExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = $this->filtered($request)->paginate(30)->withQueryString();

        $models = AuditLog::distinct()->orderBy('model')->pluck('model');
        $users  = User::whereIn('id', AuditLog::distinct()->pluck('user_id')->filter())
            ->orderBy('name')->get(['id', 'name']);

        return view('admin.audit-log.index', compact('logs', 'models', 'users'));
    }

    public function export(Request $request)
    {
        $logs = $this->filtered($request)->limit(5000)->get();

        return Excel::download(new AuditLogExport($logs), 'audit-log-' . now()->format('Ymd-His') . '.xlsx');
    }

    /**
     * Query audit log dengan filter user/model/aksi/tanggal.
     */
    private function filtered(Request $request)
    {
        return AuditLog::with('user')
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('model'), fn($q) => $q->where('model', $request->model))
            ->when($request->filled('action'), fn($q) => $q->where('action', $request->action))
            ->when($request->filled('dari'), fn($q) => $q->whereDate('created_at', '>=', $request->dari))
            ->when($request->filled('sampai'), fn($q) => $q->whereDate('created_at', '<=', $request->sampai))
            ->latest();
    }
}
