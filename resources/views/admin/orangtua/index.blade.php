@extends('layouts.admin')
@section('title', 'Akun Orang Tua / Wali')
@section('page_title', 'Akun Orang Tua / Wali')

@section('content')

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">Daftar Orang Tua / Wali</h3>
    <a href="{{ route('admin.orangtua.create') }}" class="btn btn-primary btn-sm">
      <i class="fas fa-plus"></i> Tambah Akun
    </a>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <th>#</th><th>Nama</th><th>Username</th><th>Telepon</th><th>Anak Terhubung</th><th class="text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orangTuas as $i => $ot)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $ot->nama }}</td>
            <td>{{ $ot->user->username ?? '—' }}</td>
            <td>{{ $ot->telepon ?? '—' }}</td>
            <td>
              @forelse($ot->siswas as $s)
                <span class="badge badge-info">{{ $s->nama }} ({{ ucfirst($s->pivot->hubungan) }})</span>
              @empty
                <span class="text-muted">Belum ada</span>
              @endforelse
            </td>
            <td class="text-right">
              <a href="{{ route('admin.orangtua.edit', $ot) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
              <form action="{{ route('admin.orangtua.destroy', $ot) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus akun ini? Akun login orang tua juga terhapus.')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-3">Belum ada akun orang tua.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
