@extends('layouts.admin')
@section('title', 'Anggota Ekskul — ' . $ekskul->nama)
@section('page_title', 'Anggota Ekskul: ' . $ekskul->nama)

@section('content')
<div class="row">
  {{-- Tambah Anggota --}}
  <div class="col-md-4">
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Daftarkan Siswa</h3>
      </div>
      <form action="{{ route('admin.ekskul.anggota.store', $ekskul) }}" method="POST">
        @csrf
        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif

          <div class="form-group">
            <label>Tahun Ajaran Aktif</label>
            <p class="form-control-plaintext">
              <span class="badge badge-success">{{ $tahunAjaran?->nama ?? '—' }}</span>
            </p>
          </div>

          <div class="form-group">
            <label>Pilih Siswa <span class="text-danger">*</span></label>
            @if($siswas->isEmpty())
              <p class="text-muted">Semua siswa sudah terdaftar di ekskul ini.</p>
            @else
              <select name="siswa_id" class="form-control" required>
                <option value="">-- Pilih Siswa --</option>
                @foreach($siswas as $s)
                  <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->kelas?->nama_kelas ?? '-' }})</option>
                @endforeach
              </select>
            @endif
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary btn-block" {{ $siswas->isEmpty() ? 'disabled' : '' }}>
            <i class="fas fa-plus mr-1"></i> Daftarkan
          </button>
        </div>
      </form>
    </div>

    <a href="{{ route('admin.ekskul.index') }}" class="btn btn-secondary btn-block">
      <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Ekskul
    </a>
  </div>

  {{-- Daftar Anggota --}}
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-users mr-2"></i>Anggota Aktif
          <span class="badge badge-info ml-2">{{ $anggotas->count() }} siswa</span>
        </h3>
      </div>
      <div class="card-body p-0">
        @if($anggotas->isEmpty())
          <div class="p-4 text-center text-muted">
            <i class="fas fa-user-slash fa-2x mb-2"></i>
            <p>Belum ada anggota untuk ekskul ini.</p>
          </div>
        @else
          <table class="table table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>#</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Jurusan</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach($anggotas as $i => $a)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $a->siswa->nama }}</td>
                <td>{{ $a->siswa->kelas?->nama_kelas ?? '-' }}</td>
                <td>{{ $a->siswa->jurusan?->nama_jurusan ?? '-' }}</td>
                <td class="text-center">
                  <form action="{{ route('admin.ekskul.anggota.destroy', [$ekskul, $a]) }}" method="POST"
                        onsubmit="return confirm('Keluarkan {{ $a->siswa->nama }} dari ekskul ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-xs"><i class="fas fa-user-minus"></i> Keluarkan</button>
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
</div>
@endsection
