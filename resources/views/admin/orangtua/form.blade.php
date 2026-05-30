@extends('layouts.admin')
@section('title', $orangTua ? 'Edit Akun Orang Tua' : 'Tambah Akun Orang Tua')
@section('page_title', $orangTua ? 'Edit Akun Orang Tua' : 'Tambah Akun Orang Tua')

@section('content')
@if($errors->any())
<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Data Akun</h3></div>
      <form action="{{ $orangTua ? route('admin.orangtua.update', $orangTua) : route('admin.orangtua.store') }}" method="POST">
        @csrf
        @if($orangTua) @method('PUT') @endif
        <div class="card-body">
          <div class="form-group">
            <label>Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $orangTua->nama ?? '') }}" required>
          </div>
          <div class="form-group">
            <label>Username <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" value="{{ old('username', $orangTua->user->username ?? '') }}" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $orangTua->user->email ?? '') }}">
          </div>
          <div class="form-group">
            <label>Password {{ $orangTua ? '(kosongkan jika tidak diubah)' : '' }} @if(!$orangTua)<span class="text-danger">*</span>@endif</label>
            <input type="text" name="password" class="form-control" {{ $orangTua ? '' : 'required' }}>
          </div>
          <div class="form-group">
            <label>Telepon / No. WA</label>
            <input type="text" name="telepon" class="form-control" value="{{ old('telepon', $orangTua->telepon ?? '') }}">
          </div>
          <div class="form-group">
            <label>Pekerjaan</label>
            <input type="text" name="pekerjaan" class="form-control" value="{{ old('pekerjaan', $orangTua->pekerjaan ?? '') }}">
          </div>
          <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $orangTua->alamat ?? '') }}</textarea>
          </div>
        </div>
        <div class="card-footer">
          <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
          <a href="{{ route('admin.orangtua.index') }}" class="btn btn-secondary">Batal</a>
        </div>
      </form>
    </div>
  </div>

  @if($orangTua)
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Anak Terhubung</h3></div>
      <div class="card-body">
        <form action="{{ route('admin.orangtua.anak.assign', $orangTua) }}" method="POST" class="form-row align-items-end mb-3">
          @csrf
          <div class="col-6">
            <label>Siswa</label>
            <select name="siswa_id" class="form-control" required>
              <option value="">-- Pilih Siswa --</option>
              @foreach($siswas as $s)
                <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->kelas->nama_kelas ?? '—' }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-4">
            <label>Hubungan</label>
            <select name="hubungan" class="form-control" required>
              <option value="ayah">Ayah</option>
              <option value="ibu">Ibu</option>
              <option value="wali">Wali</option>
            </select>
          </div>
          <div class="col-2">
            <button class="btn btn-success btn-block"><i class="fas fa-link"></i></button>
          </div>
        </form>

        <table class="table table-sm">
          <thead><tr><th>Nama</th><th>Hubungan</th><th></th></tr></thead>
          <tbody>
            @forelse($orangTua->siswas as $s)
              <tr>
                <td>{{ $s->nama }}</td>
                <td>{{ ucfirst($s->pivot->hubungan) }}</td>
                <td class="text-right">
                  <form action="{{ route('admin.orangtua.anak.detach', [$orangTua, $s]) }}" method="POST" onsubmit="return confirm('Lepas hubungan?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-unlink"></i></button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted">Belum ada anak terhubung.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection
