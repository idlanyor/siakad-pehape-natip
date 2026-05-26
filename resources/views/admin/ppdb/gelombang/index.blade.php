@extends('layouts.admin')
@section('title', 'Gelombang PPDB')
@section('page_title', 'Gelombang Pendaftaran PPDB')

@section('content')
<div class="row">
  {{-- Tabel --}}
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Daftar Gelombang</h3>
        <a href="{{ route('admin.ppdb.gelombang.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus mr-1"></i> Tambah Gelombang
        </a>
      </div>
      <div class="card-body p-0">
        @if($gelombangList->isEmpty())
          <div class="text-center py-5 text-muted">
            <i class="fas fa-calendar-alt fa-3x mb-3 d-block"></i>Belum ada gelombang PPDB.
          </div>
        @else
          <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
              <tr>
                <th>Nama</th>
                <th>Tahun Ajaran</th>
                <th>Buka — Tutup</th>
                <th class="text-center">Kuota</th>
                <th class="text-center">Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach($gelombangList as $g)
              <tr>
                <td class="font-weight-bold">{{ $g->nama }}</td>
                <td>{{ $g->tahun_ajaran }}</td>
                <td class="small">
                  {{ $g->tanggal_buka->format('d/m/Y H:i') }}<br>
                  <span class="text-muted">s.d. {{ $g->tanggal_tutup->format('d/m/Y H:i') }}</span>
                </td>
                <td class="text-center">{{ $g->kuota > 0 ? $g->kuota : '∞' }}</td>
                <td class="text-center">
                  <span class="badge badge-{{ $g->status_color }}">{{ $g->status_label }}</span>
                </td>
                <td class="text-right" style="white-space:nowrap">
                  <a href="{{ route('admin.ppdb.gelombang.edit', $g) }}" class="btn btn-xs btn-warning">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="{{ route('admin.ppdb.gelombang.destroy', $g) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-danger" onclick="return confirm('Hapus gelombang ini?')">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>
  </div>

  {{-- Info --}}
  <div class="col-lg-4">
    <div class="card card-info card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Info</h3>
      </div>
      <div class="card-body small text-muted">
        <p><strong>Status otomatis</strong> berdasarkan tanggal:</p>
        <ul class="pl-3">
          <li><span class="badge badge-warning">Belum Dibuka</span> — sebelum tanggal buka</li>
          <li><span class="badge badge-success">Dibuka</span> — dalam rentang buka–tutup</li>
          <li><span class="badge badge-secondary">Ditutup</span> — setelah tanggal tutup</li>
        </ul>
        <p class="mb-0">Kuota <strong>0</strong> = tidak terbatas.</p>
      </div>
    </div>
  </div>
</div>
@endsection
