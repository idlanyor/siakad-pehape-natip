@extends('layouts.admin')
@section('title', isset($materi) ? 'Edit Materi' : 'Tambah Materi')
@section('page_title', isset($materi) ? 'Edit Materi' : 'Tambah Materi')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          {{ isset($materi) ? 'Edit' : 'Tambah' }} Materi &mdash;
          <span class="badge badge-primary">{{ $jadwal->mapel->nama_mapel }}</span>
          {{ $jadwal->kelas?->nama_kelas }}
        </h3>
      </div>
      <form action="{{ isset($materi) ? route('guru.materi.update', $materi) : route('guru.materi.store', $jadwal) }}"
            method="POST" enctype="multipart/form-data">
        @csrf
        @isset($materi) @method('PUT') @endisset
        <div class="card-body">
          <div class="form-group">
            <label>Judul Materi <span class="text-danger">*</span></label>
            <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
                   value="{{ old('judul', $materi->judul ?? '') }}" required>
            @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="3"
                      class="form-control @error('deskripsi') is-invalid @enderror"
                      placeholder="Penjelasan singkat tentang materi ini...">{{ old('deskripsi', $materi->deskripsi ?? '') }}</textarea>
            @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="form-group">
            <label>Tipe Materi <span class="text-danger">*</span></label>
            <div class="d-flex gap-3">
              @foreach(['file' => ['label' => 'Upload File', 'icon' => 'fa-file-alt', 'color' => 'primary'],
                         'link' => ['label' => 'Tautan URL', 'icon' => 'fa-link', 'color' => 'info'],
                         'teks' => ['label' => 'Teks / Artikel', 'icon' => 'fa-align-left', 'color' => 'success']] as $val => $opt)
              <div class="custom-control custom-radio mr-3">
                <input type="radio" id="tipe_{{ $val }}" name="tipe" value="{{ $val }}"
                       class="custom-control-input tipe-radio"
                       {{ old('tipe', ($materi ?? null)?->tipe ?? 'file') === $val ? 'checked' : '' }}>
                <label class="custom-control-label" for="tipe_{{ $val }}">
                  <i class="fas {{ $opt['icon'] }} text-{{ $opt['color'] }}"></i> {{ $opt['label'] }}
                </label>
              </div>
              @endforeach
            </div>
          </div>

          {{-- Input File --}}
          <div id="input-file" class="form-group tipe-input">
            <label>File <span class="text-danger">*</span></label>
            <div class="custom-file">
              <input type="file" name="file" id="file"
                     class="custom-file-input @error('file') is-invalid @enderror"
                     accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.jpg,.jpeg,.png">
              <label class="custom-file-label" for="file">Pilih file...</label>
            </div>
            @isset($materi)
              @if($materi->tipe === 'file' && $materi->konten)
                <small class="text-muted mt-1 d-block">
                  File saat ini: <a href="{{ $materi->download_url }}" target="_blank">{{ $materi->file_name }}</a>
                  (kosongkan jika tidak ingin mengganti)
                </small>
              @endif
            @endisset
            <small class="form-text text-muted">PDF, Word, PPT, Excel, ZIP, Gambar. Maks 10 MB.</small>
            @error('file')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>

          {{-- Input Link --}}
          <div id="input-link" class="form-group tipe-input" style="display:none">
            <label>URL Tautan <span class="text-danger">*</span></label>
            <input type="url" name="link" class="form-control @error('link') is-invalid @enderror"
                   value="{{ old('link', (($materi ?? null)?->tipe === 'link') ? ($materi->konten ?? '') : '') }}"
                   placeholder="https://...">
            @error('link')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Input Teks --}}
          <div id="input-teks" class="form-group tipe-input" style="display:none">
            <label>Isi Materi <span class="text-danger">*</span></label>
            <textarea name="teks" rows="10"
                      class="form-control @error('teks') is-invalid @enderror"
                      placeholder="Tuliskan isi materi di sini...">{{ old('teks', (($materi ?? null)?->tipe === 'teks') ? ($materi->konten ?? '') : '') }}</textarea>
            @error('teks')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="form-group mb-0">
            <div class="custom-control custom-switch">
              <input type="checkbox" name="is_published" id="is_published" value="1"
                     class="custom-control-input"
                     {{ old('is_published', (($materi ?? null)?->is_published ?? true) ? '1' : '') ? 'checked' : '' }}>
              <label class="custom-control-label" for="is_published">Publikasikan (langsung terlihat siswa)</label>
            </div>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('guru.materi.show', $jadwal) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> {{ isset($materi) ? 'Perbarui' : 'Simpan' }} Materi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
  function updateTipeInput() {
    var tipe = $('input[name="tipe"]:checked').val();
    $('#input-file').toggle(tipe === 'file');
    $('#input-link').toggle(tipe === 'link');
    $('#input-teks').toggle(tipe === 'teks');
  }
  $('input[name="tipe"]').on('change', updateTipeInput);
  updateTipeInput();

  // Custom file label
  $('#file').on('change', function() {
    var name = this.files[0]?.name || 'Pilih file...';
    $(this).next('.custom-file-label').text(name);
  });
});
</script>
@endpush
