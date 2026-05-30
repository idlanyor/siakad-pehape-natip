@extends('layouts.admin')
@section('title', 'Audit Log')
@section('page_title', 'Audit Log — Riwayat Perubahan Data')

@section('content')
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-history mr-1"></i> Riwayat Aktivitas</h3>
    <div class="float-right">
      <a href="{{ route('admin.audit-log.export', request()->query()) }}" class="btn btn-sm btn-success">
        <i class="fas fa-file-excel mr-1"></i> Export Excel
      </a>
    </div>
  </div>

  <div class="card-body">
    {{-- Filter --}}
    <form method="GET" class="form-row mb-3">
      <div class="col-md-2">
        <select name="user_id" class="form-control form-control-sm">
          <option value="">Semua User</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <select name="model" class="form-control form-control-sm">
          <option value="">Semua Model</option>
          @foreach($models as $m)
            <option value="{{ $m }}" {{ request('model') == $m ? 'selected' : '' }}>{{ $m }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <select name="action" class="form-control form-control-sm">
          <option value="">Semua Aksi</option>
          @foreach(['created'=>'Tambah','updated'=>'Ubah','deleted'=>'Hapus'] as $k=>$v)
            <option value="{{ $k }}" {{ request('action') == $k ? 'selected' : '' }}>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2"><input type="date" name="dari" value="{{ request('dari') }}" class="form-control form-control-sm"></div>
      <div class="col-md-2"><input type="date" name="sampai" value="{{ request('sampai') }}" class="form-control form-control-sm"></div>
      <div class="col-md-2">
        <button class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filter</button>
        <a href="{{ route('admin.audit-log.index') }}" class="btn btn-sm btn-secondary">Reset</a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-sm table-hover">
        <thead>
          <tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Model</th><th>ID</th><th>Perubahan</th><th>IP</th></tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
          <tr>
            <td><small>{{ $log->created_at->format('d/m/Y H:i:s') }}</small></td>
            <td>{{ $log->user_name }}</td>
            <td><span class="badge badge-{{ $log->action_color }}">{{ $log->action_label }}</span></td>
            <td>{{ $log->model }}</td>
            <td>{{ $log->model_id }}</td>
            <td>
              @if($log->action === 'updated' && $log->new_values)
                <details>
                  <summary class="text-primary" style="cursor:pointer">{{ count($log->new_values) }} field</summary>
                  <small>
                    @foreach($log->new_values as $k => $v)
                      <div><strong>{{ $k }}:</strong> <span class="text-muted">{{ $log->old_values[$k] ?? '—' }}</span> → {{ is_array($v) ? json_encode($v) : $v }}</div>
                    @endforeach
                  </small>
                </details>
              @elseif($log->new_values || $log->old_values)
                <details>
                  <summary class="text-primary" style="cursor:pointer">Lihat</summary>
                  <small>
                    @foreach(($log->new_values ?? $log->old_values) as $k => $v)
                      <div><strong>{{ $k }}:</strong> {{ is_array($v) ? json_encode($v) : $v }}</div>
                    @endforeach
                  </small>
                </details>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td><small>{{ $log->ip_address }}</small></td>
          </tr>
          @empty
          <tr><td colspan="7" class="text-center text-muted py-3">Belum ada aktivitas tercatat.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $logs->links() }}
  </div>
</div>
@endsection
