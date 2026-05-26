@extends('layouts.admin')
@section('title', $artikel ? 'Edit Artikel' : 'Tulis Artikel')
@section('page_title', $artikel ? 'Edit Artikel' : 'Tulis Artikel Baru')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
<style>
  .thumbnail-preview { max-height: 120px; border-radius: 8px; border: 1px solid #dee2e6; }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-pen text-primary mr-2"></i>
          {{ $artikel ? 'Edit' : 'Tulis' }} Artikel
        </h3>
      </div>
      <form id="artikelForm"
            action="{{ $artikel ? route('admin.cms.artikel.update', $artikel) : route('admin.cms.artikel.store') }}"
            method="POST" enctype="multipart/form-data">
        @csrf
        @if($artikel) @method('PUT') @endif

        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
          @endif

          <div class="form-group">
            <label>Judul Artikel <span class="text-danger">*</span></label>
            <input type="text" name="judul" id="judulInput"
                   class="form-control form-control-lg @error('judul') is-invalid @enderror"
                   value="{{ old('judul', $artikel?->judul) }}"
                   placeholder="Tulis judul yang menarik..." required>
            @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label>Ringkasan <small class="text-muted">(tampil di halaman daftar, maks 500 karakter)</small></label>
            <textarea name="ringkasan" rows="2" class="form-control" maxlength="500"
                      placeholder="Rangkuman singkat artikel...">{{ old('ringkasan', $artikel?->ringkasan) }}</textarea>
          </div>

          <div class="form-group">
            <label>Konten Artikel</label>
            <textarea name="konten" id="kontenEditor"
                      class="form-control">{{ old('konten', $artikel?->konten) }}</textarea>
          </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.cms.artikel.index') }}" class="btn btn-secondary">Batal</a>
          <div>
            <button type="submit" name="is_published" value="0" class="btn btn-outline-secondary mr-2">
              <i class="fas fa-save mr-1"></i> Simpan Draft
            </button>
            <button type="submit" class="btn btn-primary" onclick="document.getElementById('publishedInput').value='1'">
              <i class="fas fa-paper-plane mr-1"></i> Publish
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Sidebar settings --}}
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Pengaturan Artikel</h3></div>
      <div class="card-body">
        <input type="hidden" name="is_published" id="publishedInput" form="artikelForm"
               value="{{ old('is_published', $artikel?->is_published ? '1' : '0') }}">

        <div class="form-group">
          <label>Kategori <span class="text-danger">*</span></label>
          <select name="kategori" form="artikelForm" class="form-control @error('kategori') is-invalid @enderror">
            @foreach($kategoriList as $k => $v)
              <option value="{{ $k }}" {{ old('kategori', $artikel?->kategori ?? 'berita') == $k ? 'selected' : '' }}>
                {{ $v }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label>Tanggal Publish</label>
          <input type="datetime-local" name="published_at" form="artikelForm" class="form-control"
                 value="{{ old('published_at', $artikel?->published_at?->format('Y-m-d\TH:i')) }}">
          <small class="text-muted">Kosongkan untuk publish sekarang.</small>
        </div>

        <div class="form-group">
          <label>Thumbnail / Cover</label>
          @if($artikel?->thumbnail_url)
            <div class="mb-2">
              <img src="{{ $artikel->thumbnail_url }}" class="thumbnail-preview" alt="Thumbnail">
            </div>
          @endif
          <input type="file" name="thumbnail" form="artikelForm"
                 class="form-control-file" accept="image/*" id="thumbnailInput">
          <div id="thumbnailPreviewNew" class="mt-2"></div>
        </div>

        <div class="form-group mb-0">
          <label>Status Saat Ini</label>
          <div>
            @if($artikel?->is_published)
              <span class="badge badge-success">Published</span>
            @else
              <span class="badge badge-secondary">Draft</span>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<script>
  $('#kontenEditor').summernote({
    height: 400,
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

  // Thumbnail preview
  document.getElementById('thumbnailInput')?.addEventListener('change', function() {
    const preview = document.getElementById('thumbnailPreviewNew');
    if (this.files[0]) {
      const reader = new FileReader();
      reader.onload = e => preview.innerHTML = `<img src="${e.target.result}" class="thumbnail-preview">`;
      reader.readAsDataURL(this.files[0]);
    }
  });
</script>
@endpush
