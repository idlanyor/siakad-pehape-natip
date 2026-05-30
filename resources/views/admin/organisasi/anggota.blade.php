@extends('layouts.admin')
@section('title', 'Anggota — ' . $organisasi->nama_jabatan)
@section('page_title', 'Kelola Anggota Jabatan')

@section('content')
<div class="row">

  {{-- Kolom Kiri: Form Tambah --}}
  <div class="col-md-5">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-sitemap mr-1"></i> {{ $organisasi->nama_jabatan }}
          @if($organisasi->parent)
            <small class="text-muted ml-1">← {{ $organisasi->parent->nama_jabatan }}</small>
          @endif
        </h3>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-plus mr-1"></i> Tambah Anggota</h3>
      </div>
      <form action="{{ route('admin.organisasi.anggota.store', $organisasi) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
          @endif

          <div class="form-group">
            <label>Nama <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                   value="{{ old('nama') }}" placeholder="Nama lengkap">
            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label>Guru Internal <small class="text-muted">(opsional, isi jika ada di data guru)</small></label>
            <select name="guru_id" class="form-control" id="selectGuru">
              <option value="">-- Bukan guru internal --</option>
              @foreach($gurus as $g)
                <option value="{{ $g->id }}" data-nama="{{ $g->nama }}" data-nip="{{ $g->nip }}">
                  {{ $g->nama }} {{ $g->nip ? '(' . $g->nip . ')' : '' }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>NIP</label>
            <input type="text" name="nip" id="inputNip" class="form-control"
                   value="{{ old('nip') }}" placeholder="Nomor Induk Pegawai">
          </div>

          <div class="form-group">
            <label>Periode Menjabat</label>
            <input type="text" name="periode" class="form-control"
                   value="{{ old('periode') }}" placeholder="Contoh: 2024–2027">
          </div>

          <div class="form-group">
            <label>Foto <small class="text-muted">(opsional, max 1MB)</small></label>
            <input type="file" name="foto" class="form-control-file @error('foto') is-invalid @enderror" accept="image/*">
            @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.organisasi.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Tambah Anggota
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Kolom Kanan: Daftar Anggota --}}
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users mr-1"></i> Daftar Anggota</h3>
      </div>
      <div class="card-body p-0">

        @forelse($anggota as $a)
        <div class="d-flex align-items-center p-3 border-bottom {{ !$a->is_aktif ? 'bg-light' : '' }}">
          <div class="mr-3">
            @if($a->foto)
              <img src="{{ asset('storage/' . $a->foto) }}" alt="{{ $a->nama }}"
                   class="img-circle" style="width:48px;height:48px;object-fit:cover">
            @else
              <div class="bg-secondary img-circle d-flex align-items-center justify-content-center"
                   style="width:48px;height:48px">
                <i class="fas fa-user text-white"></i>
              </div>
            @endif
          </div>
          <div class="flex-grow-1">
            <div class="font-weight-bold">
              {{ $a->nama }}
              @if(!$a->is_aktif) <span class="badge badge-secondary ml-1">Tidak aktif</span> @endif
            </div>
            <small class="text-muted">
              {{ $a->nip ? 'NIP: ' . $a->nip . ' · ' : '' }}
              {{ $a->periode ? 'Periode: ' . $a->periode : '' }}
              @if($a->guru_id) <span class="badge badge-info">Guru Internal</span> @endif
            </small>
          </div>
          <div>
            <form action="{{ route('admin.organisasi.anggota.destroy', [$organisasi, $a]) }}"
                  method="POST" onsubmit="return confirm('Hapus {{ $a->nama }}?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </div>
        @empty
          <div class="p-4 text-center text-muted">
            <i class="fas fa-user-slash fa-2x mb-2"></i>
            <p>Belum ada anggota untuk jabatan ini.</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  document.getElementById('selectGuru').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (opt.value) {
      document.querySelector('input[name="nama"]').value = opt.dataset.nama || '';
      document.getElementById('inputNip').value = opt.dataset.nip || '';
    }
  });
</script>
@endpush
