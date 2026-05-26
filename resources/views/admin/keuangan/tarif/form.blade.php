@extends('layouts.admin')
@section('title', $tarif ? 'Edit Tarif SPP' : 'Tambah Tarif SPP')
@section('page_title', $tarif ? 'Edit Tarif SPP' : 'Tambah Tarif SPP')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-money-bill-wave text-success mr-2"></i>{{ $tarif ? 'Edit' : 'Tambah' }} Tarif SPP</h3>
      </div>
      <form action="{{ $tarif ? route('admin.keuangan.tarif.update', $tarif) : route('admin.keuangan.tarif.store') }}" method="POST">
        @csrf
        @if($tarif) @method('PUT') @endif
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
          @endif

          <div class="form-group">
            <label>Kelas <span class="text-danger">*</span></label>
            @if($tarif)
              {{-- Kelas tidak bisa diubah saat edit --}}
              <input type="text" class="form-control" value="{{ $tarif->kelas->nama_kelas }} — {{ $tarif->kelas->jurusan?->nama_jurusan }}" readonly>
            @else
              <select name="kelas_id" class="form-control @error('kelas_id') is-invalid @enderror" required>
                <option value="">-- Pilih Kelas --</option>
                @foreach($kelas as $k)
                  <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                    {{ $k->nama_kelas }} — {{ $k->jurusan?->nama_jurusan ?? '-' }} ({{ $k->siswas->count() }} siswa)
                  </option>
                @endforeach
              </select>
              @error('kelas_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            @endif
          </div>

          <div class="form-group">
            <label>Tahun Ajaran <span class="text-danger">*</span></label>
            @if($tarif)
              <input type="text" class="form-control" value="{{ $tarif->tahunAjaran->nama }}" readonly>
            @else
              <select name="tahun_ajaran_id" class="form-control @error('tahun_ajaran_id') is-invalid @enderror" required>
                <option value="">-- Pilih Tahun Ajaran --</option>
                @foreach($tahunAjarans as $ta)
                  <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id') == $ta->id ? 'selected' : '' }}>
                    {{ $ta->nama }} {{ $ta->aktif ? '(Aktif)' : '' }}
                  </option>
                @endforeach
              </select>
              @error('tahun_ajaran_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            @endif
          </div>

          <div class="form-group">
            <label>Nominal SPP (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
              <input type="number" name="nominal" class="form-control @error('nominal') is-invalid @enderror"
                     value="{{ old('nominal', $tarif?->nominal) }}"
                     placeholder="Contoh: 250000" min="1000" step="1000" required>
              @error('nominal') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <small class="text-muted">Nominal SPP bulanan untuk kelas ini.</small>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.keuangan.tarif.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
