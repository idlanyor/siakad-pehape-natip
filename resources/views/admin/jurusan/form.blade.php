@extends('layouts.admin')
@section('title', $jurusan ? 'Edit Jurusan' : 'Tambah Jurusan')
@section('page_title', $jurusan ? 'Edit Jurusan' : 'Tambah Jurusan')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $jurusan ? 'Edit' : 'Tambah' }} Jurusan</h3>
      </div>
      <form action="{{ $jurusan ? route('admin.jurusan.update', $jurusan) : route('admin.jurusan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($jurusan) @method('PUT') @endif
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
          @endif

          <div class="form-group">
            <label>Nama Jurusan <span class="text-danger">*</span></label>
            <input type="text" name="nama_jurusan" class="form-control @error('nama_jurusan') is-invalid @enderror"
                   value="{{ old('nama_jurusan', $jurusan?->nama_jurusan) }}" placeholder="Contoh: Teknik Komputer dan Jaringan">
            @error('nama_jurusan') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label>Deskripsi Publik <small class="text-muted">(tampil di website)</small></label>
            <textarea name="deskripsi_publik" rows="3" class="form-control"
                      placeholder="Deskripsi singkat program keahlian ini untuk calon siswa...">{{ old('deskripsi_publik', $jurusan?->deskripsi_publik) }}</textarea>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Icon <small class="text-muted">(Font Awesome class)</small></label>
                <input type="text" name="icon" class="form-control"
                       value="{{ old('icon', $jurusan?->icon) }}"
                       placeholder="fas fa-laptop-code">
                <small class="text-muted">Cari di <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com</a></small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Gambar Program</label>
                @if($jurusan?->gambar)
                  <div class="mb-2">
                    <img src="{{ asset('storage/' . $jurusan->gambar) }}" style="height:80px;border-radius:6px;object-fit:cover" alt="">
                  </div>
                @endif
                <input type="file" name="gambar" class="form-control-file" accept="image/*">
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="isAktifPpdb"
                     name="is_aktif_ppdb" value="1"
                     {{ old('is_aktif_ppdb', $jurusan?->is_aktif_ppdb ?? true) ? 'checked' : '' }}>
              <label class="custom-control-label" for="isAktifPpdb">Buka pendaftaran PPDB untuk jurusan ini</label>
            </div>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.jurusan.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
