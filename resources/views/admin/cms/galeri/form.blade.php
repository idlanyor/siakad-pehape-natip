@extends('layouts.admin')
@section('title', $galeri ? 'Edit Galeri' : 'Buat Galeri')
@section('page_title', $galeri ? 'Edit Galeri' : 'Buat Galeri Baru')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-images text-primary mr-2"></i>{{ $galeri ? 'Edit' : 'Buat' }} Galeri</h3>
      </div>
      <form action="{{ $galeri ? route('admin.cms.galeri.update', $galeri) : route('admin.cms.galeri.store') }}"
            method="POST" enctype="multipart/form-data">
        @csrf
        @if($galeri) @method('PUT') @endif
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
          @endif

          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Nama Galeri <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                       value="{{ old('nama', $galeri?->nama) }}" placeholder="e.g. Kegiatan Pramuka 2026" required>
                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Tanggal Kegiatan <span class="text-danger">*</span></label>
                <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                       value="{{ old('tanggal', $galeri?->tanggal?->format('Y-m-d') ?? today()->toDateString()) }}" required>
                @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="2" class="form-control"
                      placeholder="Deskripsi singkat kegiatan...">{{ old('deskripsi', $galeri?->deskripsi) }}</textarea>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Foto Cover</label>
                @if($galeri?->cover_url)
                  <div class="mb-2">
                    <img src="{{ $galeri->cover_url }}" style="max-height:80px;border-radius:6px" alt="">
                  </div>
                @endif
                <input type="file" name="cover" class="form-control-file" accept="image/*">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Status</label>
                <div class="custom-control custom-switch mt-2">
                  <input type="checkbox" class="custom-control-input" id="isPublished"
                         name="is_published" value="1"
                         {{ old('is_published', $galeri?->is_published) ? 'checked' : '' }}>
                  <label class="custom-control-label" for="isPublished">Published</label>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Upload Foto <small class="text-muted">(bisa multiple, maks 3MB per foto)</small></label>
            <input type="file" name="fotos[]" class="form-control-file" accept="image/*" multiple
                   id="fotosInput">
            <div id="fotoPreview" class="d-flex flex-wrap mt-2" style="gap:.5rem"></div>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.cms.galeri.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('fotosInput').addEventListener('change', function() {
  const preview = document.getElementById('fotoPreview');
  preview.innerHTML = '';
  Array.from(this.files).forEach(file => {
    const reader = new FileReader();
    reader.onload = e => {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style = 'height:80px;width:80px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6';
      preview.appendChild(img);
    };
    reader.readAsDataURL(file);
  });
});
</script>
@endpush
