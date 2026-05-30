<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaLog;
use Illuminate\Http\Request;

class WaLogController extends Controller
{
    public function index(Request $request)
    {
        $query = WaLog::orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('phone', 'like', "%{$request->search}%")
                  ->orWhere('message', 'like', "%{$request->search}%");
            });
        }

        $logs = $query->paginate(50);
        $events = WaLog::select('event')->distinct()->whereNotNull('event')->pluck('event');

        return view('admin.whatsapp.index', compact('logs', 'events'));
    }
}
