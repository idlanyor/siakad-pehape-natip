@extends('layouts.admin')
@section('title', 'Pengaturan Website')
@section('page_title', 'Pengaturan Website Sekolah')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
<style>
  .setting-img-preview { max-height: 100px; border-radius: 6px; border: 1px solid #dee2e6; }
  .nav-tabs .nav-link { font-weight: 500; }
</style>
@endpush

@section('content')
<form action="{{ route('admin.cms.pengaturan.update') }}" method="POST" enctype="multipart/form-data">
  @csrf @method('PUT')


  {{-- Tab Navigation --}}
  <ul class="nav nav-tabs mb-3" id="settingTabs">
    @foreach($grupan as $key => $label)
      <li class="nav-item">
        <a class="nav-link {{ $loop->first ? 'active' : '' }}"
           data-toggle="tab" href="#tab-{{ $key }}">
          {{ $label }}
        </a>
      </li>
    @endforeach
  </ul>

  {{-- Tab Content --}}
  <div class="tab-content">
    @foreach($grupan as $grupKey => $grupLabel)
    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $grupKey }}">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-cog mr-2"></i>{{ $grupLabel }}</h3>
        </div>
        <div class="card-body">
          @forelse($settings[$grupKey] ?? [] as $s)
          <div class="form-group row">
            <label class="col-md-3 col-form-label font-weight-bold">{{ $s->label }}</label>
            <div class="col-md-9">

              @if($s->tipe === 'text')
                <input type="text" name="{{ $s->key }}"
                       class="form-control" value="{{ old($s->key, $s->value) }}">

              @elseif($s->tipe === 'textarea')
                <textarea name="{{ $s->key }}" rows="3"
                          class="form-control">{{ old($s->key, $s->value) }}</textarea>

              @elseif($s->tipe === 'richtext')
                <textarea name="{{ $s->key }}" class="form-control summernote"
                          id="summernote-{{ $s->key }}">{{ old($s->key, $s->value) }}</textarea>

              @elseif($s->tipe === 'image')
                <div class="d-flex align-items-center gap-3" style="gap:.75rem">
                  @if($s->value)
                    <img src="{{ asset('storage/' . $s->value) }}" class="setting-img-preview mr-3" alt="{{ $s->label }}">
                  @endif
                  <input type="file" name="{{ $s->key }}" class="form-control-file" accept="image/*">
                </div>
                @if($s->value)
                  <small class="text-muted">Upload baru untuk mengganti gambar lama.</small>
                @endif

              @elseif($s->tipe === 'boolean')
                <div class="custom-control custom-switch mt-2">
                  <input type="hidden" name="{{ $s->key }}" value="0">
                  <input type="checkbox" class="custom-control-input"
                         id="bool-{{ $s->key }}" name="{{ $s->key }}" value="1"
                         {{ old($s->key, $s->value) == '1' ? 'checked' : '' }}>
                  <label class="custom-control-label" for="bool-{{ $s->key }}">
                    {{ old($s->key, $s->value) == '1' ? 'Aktif' : 'Tidak Aktif' }}
                  </label>
                </div>

              @endif

            </div>
          </div>
          @empty
            <p class="text-muted">Tidak ada pengaturan dalam grup ini.</p>
          @endforelse
        </div>
      </div>
    </div>
    @endforeach
  </div>

  <div class="d-flex justify-content-end mt-3">
    <button type="submit" class="btn btn-primary btn-lg">
      <i class="fas fa-save mr-1"></i> Simpan Semua Pengaturan
    </button>
  </div>

</form>
@endsection

@push('scripts')
<script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<script>
  $('.summernote').summernote({
    height: 250,
    toolbar: [
      ['style', ['style']],
      ['font', ['bold', 'italic', 'underline', 'clear']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['insert', ['link']],
      ['view', ['codeview']],
    ]
  });

  // Update boolean label on toggle
  document.querySelectorAll('.custom-control-input[type=checkbox]').forEach(cb => {
    cb.addEventListener('change', function () {
      this.nextElementSibling.textContent = this.checked ? 'Aktif' : 'Tidak Aktif';
    });
  });
</script>
@endpush
