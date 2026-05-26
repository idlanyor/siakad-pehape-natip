@extends('layouts.admin')
@section('title', $halaman ? 'Edit Halaman' : 'Tambah Halaman')
@section('page_title', $halaman ? 'Edit Halaman Statis' : 'Tambah Halaman Statis')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
@endpush

@section('content')
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-file-alt text-primary mr-2"></i>
      {{ $halaman ? 'Edit' : 'Tambah' }} Halaman
    </h3>
  </div>
  <form action="{{ $halaman ? route('admin.cms.halaman.update', $halaman) : route('admin.cms.halaman.store') }}"
        method="POST">
    @csrf
    @if($halaman) @method('PUT') @endif
    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <div class="row">
        <div class="col-md-8">
          <div class="form-group">
            <label>Judul Halaman <span class="text-danger">*</span></label>
            <input type="text" name="judul" id="judulInput"
                   class="form-control @error('judul') is-invalid @enderror"
                   value="{{ old('judul', $halaman?->judul) }}"
                   placeholder="e.g. Tentang Sekolah" required>
            @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Slug (URL) <span class="text-danger">*</span></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text text-muted">/halaman/</span>
              </div>
              <input type="text" name="slug" id="slugInput"
                     class="form-control @error('slug') is-invalid @enderror"
                     value="{{ old('slug', $halaman?->slug) }}"
                     placeholder="tentang-sekolah" required>
            </div>
            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <small class="text-muted">Huruf kecil, angka, dan tanda hubung saja.</small>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label>Konten</label>
        <textarea name="konten" id="kontenEditor" class="form-control">{{ old('konten', $halaman?->konten) }}</textarea>
      </div>

      <div class="row">
        <div class="col-md-8">
          <div class="form-group">
            <label>Meta Description <small class="text-muted">(SEO — maks 255 karakter)</small></label>
            <input type="text" name="meta_desc"
                   class="form-control" maxlength="255"
                   value="{{ old('meta_desc', $halaman?->meta_desc) }}"
                   placeholder="Deskripsi singkat untuk mesin pencari...">
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Urutan</label>
            <input type="number" name="urutan" class="form-control"
                   value="{{ old('urutan', $halaman?->urutan ?? 0) }}" min="0">
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Status</label>
            <div class="custom-control custom-switch mt-2">
              <input type="checkbox" class="custom-control-input" id="isPublished"
                     name="is_published" value="1"
                     {{ old('is_published', $halaman?->is_published) ? 'checked' : '' }}>
              <label class="custom-control-label" for="isPublished">Published</label>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
      <a href="{{ route('admin.cms.halaman.index') }}" class="btn btn-secondary">Batal</a>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> Simpan
      </button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<script>
  $('#kontenEditor').summernote({
    height: 350,
    toolbar: [
      ['style', ['style']],
      ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
      ['color', ['color']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['table', ['table']],
      ['insert', ['link', 'picture', 'hr']],
      ['view', ['fullscreen', 'codeview']],
    ]
  });

  // Auto-generate slug dari judul
  @if(!$halaman)
  document.getElementById('judulInput').addEventListener('input', function () {
    const slug = this.value
      .toLowerCase()
      .replace(/[^a-z0-9\s\-]/g, '')
      .trim()
      .replace(/\s+/g, '-');
    document.getElementById('slugInput').value = slug;
  });
  @endif
</script>
@endpush
