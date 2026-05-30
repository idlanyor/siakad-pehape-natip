@extends('layouts.admin')
@section('title', 'WhatsApp Log')
@section('page_title', 'WhatsApp Notification Log')

@section('content')
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fab fa-whatsapp mr-2 text-success"></i>Riwayat Pengiriman WhatsApp</h3>
  </div>
  <div class="card-body">
    <form method="GET" class="row mb-3">
      <div class="col-md-3">
        <input type="text" name="search" class="form-control" placeholder="Cari no HP / pesan..." value="{{ request('search') }}">
      </div>
      <div class="col-md-2">
        <select name="status" class="form-control">
          <option value="">— Status —</option>
          <option value="sent" @selected(request('status')=='sent')>Sent</option>
          <option value="failed" @selected(request('status')=='failed')>Failed</option>
          <option value="error" @selected(request('status')=='error')>Error</option>
        </select>
      </div>
      <div class="col-md-2">
        <select name="event" class="form-control">
          <option value="">— Event —</option>
          @foreach($events as $ev)
            <option value="{{ $ev }}" @selected(request('event')==$ev)>{{ $ev }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search mr-1"></i>Filter</button>
      </div>
      @if(request()->hasAny(['search','status','event']))
      <div class="col-md-2">
        <a href="{{ route('admin.whatsapp.log') }}" class="btn btn-secondary btn-block">Reset</a>
      </div>
      @endif
    </form>

    @if($logs->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="fab fa-whatsapp fa-3x mb-3 d-block"></i>Belum ada log pengiriman WA.
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-hover table-striped mb-0">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>Waktu</th>
              <th>No. Tujuan</th>
              <th>Event</th>
              <th>Pesan</th>
              <th class="text-center">Status</th>
              <th>Response</th>
            </tr>
          </thead>
          <tbody>
            @foreach($logs as $log)
            <tr>
              <td>{{ $log->id }}</td>
              <td class="text-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
              <td>{{ $log->phone }}</td>
              <td><span class="badge badge-info">{{ $log->event ?? '-' }}</span></td>
              <td><small>{{ Str::limit($log->message, 80) }}</small></td>
              <td class="text-center">
                @if($log->status === 'sent')
                  <span class="badge badge-success">Sent</span>
                @elseif($log->status === 'failed')
                  <span class="badge badge-danger">Failed</span>
                @else
                  <span class="badge badge-warning">{{ ucfirst($log->status) }}</span>
                @endif
              </td>
              <td><small class="text-muted">{{ Str::limit($log->response, 60) }}</small></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="mt-3">
        {{ $logs->withQueryString()->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
