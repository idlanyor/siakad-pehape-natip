@extends('layouts.admin')

@section('title', 'Tambah Soal')

@section('page_title', 'Tambah Soal - ' . $ujian->judul)

@push('styles')
<!-- Summernote -->
<link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
<!-- iCheck -->
<link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Informasi Ujian</h3>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <strong>Judul:</strong> {{ $ujian->judul }}<br>
            <strong>Kelas:</strong> {{ $ujian->kelas_nama }}<br>
            <strong>Mapel:</strong> {{ $ujian->mapel_nama }}
          </div>
          <div class="col-md-6">
            <strong>Jumlah Soal Target:</strong> {{ $ujian->jumlah_soal }}<br>
            <strong>Soal Saat Ini:</strong> 
            <span class="badge {{ $ujian->soals_count >= $ujian->jumlah_soal ? 'badge-success' : 'badge-warning' }}">
              {{ $ujian->soals_count }} / {{ $ujian->jumlah_soal }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <form action="{{ route('soal.store', $ujian->id) }}" method="POST" id="soal-form" enctype="multipart/form-data">
      @csrf
      
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Informasi Soal</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="tipe">Tipe Soal <span class="text-danger">*</span></label>
                <select name="tipe" id="tipe" class="form-control @error('tipe') is-invalid @enderror" required>
                  <option value="">Pilih Tipe</option>
                  <option value="pilihan_ganda" {{ old('tipe') == 'pilihan_ganda' ? 'selected' : '' }}>Pilihan Ganda</option>
                  <option value="benar_salah" {{ old('tipe') == 'benar_salah' ? 'selected' : '' }}>Benar/Salah</option>
                  <option value="essay" {{ old('tipe') == 'essay' ? 'selected' : '' }}>Essay</option>
                </select>
                @error('tipe')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="bobot_nilai">Bobot Nilai <span class="text-danger">*</span></label>
                <input type="number" name="bobot_nilai" id="bobot_nilai" class="form-control @error('bobot_nilai') is-invalid @enderror" value="{{ old('bobot_nilai', 1) }}" min="0" step="0.01" required>
                @error('bobot_nilai')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted">Bobot penilaian untuk soal ini</small>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="pertanyaan">Pertanyaan <span class="text-danger">*</span></label>
            <textarea name="pertanyaan" id="pertanyaan" class="form-control summernote @error('pertanyaan') is-invalid @enderror" required>{{ old('pertanyaan') }}</textarea>
            @error('pertanyaan')
            <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <label for="media">Media Pendukung (Opsional)</label>
            <div class="custom-file">
              <input type="file" class="custom-file-input @error('media') is-invalid @enderror" id="media" name="media" accept="image/*,video/*,audio/*,.pdf">
              <label class="custom-file-label" for="media">Pilih file...</label>
            </div>
            @error('media')
            <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Format: gambar, video, audio, atau PDF. Maksimal 10MB</small>
          </div>
        </div>
      </div>

      <!-- Opsi Pilihan Ganda -->
      <div class="card" id="opsi-pg-card" style="display: none;">
        <div class="card-header">
          <h3 class="card-title">Opsi Jawaban</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label for="opsi_a">Opsi A <span class="text-danger">*</span></label>
            <textarea name="opsi_a" id="opsi_a" rows="2" class="form-control @error('opsi_a') is-invalid @enderror">{{ old('opsi_a') }}</textarea>
            @error('opsi_a')
            <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <label for="opsi_b">Opsi B <span class="text-danger">*</span></label>
            <textarea name="opsi_b" id="opsi_b" rows="2" class="form-control @error('opsi_b') is-invalid @enderror">{{ old('opsi_b') }}</textarea>
            @error('opsi_b')
            <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <label for="opsi_c">Opsi C <span class="text-danger">*</span></label>
            <textarea name="opsi_c" id="opsi_c" rows="2" class="form-control @error('opsi_c') is-invalid @enderror">{{ old('opsi_c') }}</textarea>
            @error('opsi_c')
            <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <label for="opsi_d">Opsi D <span class="text-danger">*</span></label>
            <textarea name="opsi_d" id="opsi_d" rows="2" class="form-control @error('opsi_d') is-invalid @enderror">{{ old('opsi_d') }}</textarea>
            @error('opsi_d')
            <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <label for="opsi_e">Opsi E (Opsional)</label>
            <textarea name="opsi_e" id="opsi_e" rows="2" class="form-control @error('opsi_e') is-invalid @enderror">{{ old('opsi_e') }}</textarea>
            @error('opsi_e')
            <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <label for="kunci_jawaban_pg">Kunci Jawaban <span class="text-danger">*</span></label>
            <select name="kunci_jawaban" id="kunci_jawaban_pg" class="form-control @error('kunci_jawaban') is-invalid @enderror">
              <option value="">Pilih Kunci Jawaban</option>
              <option value="A" {{ old('kunci_jawaban') == 'A' ? 'selected' : '' }}>A</option>
              <option value="B" {{ old('kunci_jawaban') == 'B' ? 'selected' : '' }}>B</option>
              <option value="C" {{ old('kunci_jawaban') == 'C' ? 'selected' : '' }}>C</option>
              <option value="D" {{ old('kunci_jawaban') == 'D' ? 'selected' : '' }}>D</option>
              <option value="E" {{ old('kunci_jawaban') == 'E' ? 'selected' : '' }}>E</option>
            </select>
            @error('kunci_jawaban')
            <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
        </div>
      </div>

      <!-- Opsi Benar/Salah -->
      <div class="card" id="opsi-bs-card" style="display: none;">
        <div class="card-header">
          <h3 class="card-title">Kunci Jawaban</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label for="kunci_jawaban_bs">Kunci Jawaban <span class="text-danger">*</span></label>
            <select name="kunci_jawaban_bs" id="kunci_jawaban_bs" class="form-control @error('kunci_jawaban') is-invalid @enderror">
              <option value="">Pilih Kunci Jawaban</option>
              <option value="benar" {{ old('kunci_jawaban') == 'benar' ? 'selected' : '' }}>Benar</option>
              <option value="salah" {{ old('kunci_jawaban') == 'salah' ? 'selected' : '' }}>Salah</option>
            </select>
            @error('kunci_jawaban')
            <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
        </div>
      </div>

      <!-- Pembahasan -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Pembahasan (Opsional)</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label for="pembahasan">Pembahasan</label>
            <textarea name="pembahasan" id="pembahasan" class="form-control summernote @error('pembahasan') is-invalid @enderror">{{ old('pembahasan') }}</textarea>
            @error('pembahasan')
            <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Pembahasan akan ditampilkan setelah siswa selesai ujian</small>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-footer">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan Soal
          </button>
          <button type="submit" name="action" value="save_and_new" class="btn btn-success">
            <i class="fas fa-plus"></i> Simpan & Tambah Lagi
          </button>
          <a href="{{ route('ujian.show', $ujian->id) }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Batal
          </a>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<!-- Summernote -->
<script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<!-- bs-custom-file-input -->
<script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>

<script>
$(function() {
  // Initialize Summernote
  $('.summernote').summernote({
    height: 200,
    toolbar: [
      ['style', ['style']],
      ['font', ['bold', 'italic', 'underline', 'clear']],
      ['fontname', ['fontname']],
      ['color', ['color']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['table', ['table']],
      ['insert', ['link', 'picture']],
      ['view', ['fullscreen', 'codeview', 'help']]
    ]
  });

  // Initialize file input
  bsCustomFileInput.init();

  // Toggle opsi based on tipe
  $('#tipe').on('change', function() {
    var tipe = $(this).val();
    
    $('#opsi-pg-card').hide();
    $('#opsi-bs-card').hide();
    
    // Reset required
    $('#opsi_a, #opsi_b, #opsi_c, #opsi_d, #kunci_jawaban_pg, #kunci_jawaban_bs').prop('required', false);
    
    if (tipe === 'pilihan_ganda') {
      $('#opsi-pg-card').show();
      $('#opsi_a, #opsi_b, #opsi_c, #opsi_d, #kunci_jawaban_pg').prop('required', true);
    } else if (tipe === 'benar_salah') {
      $('#opsi-bs-card').show();
      $('#kunci_jawaban_bs').prop('required', true);
    }
  });

  // Trigger on load if old value exists
  @if(old('tipe'))
  $('#tipe').trigger('change');
  @endif

  // Form validation
  $('#soal-form').on('submit', function(e) {
    var tipe = $('#tipe').val();
    
    if (tipe === 'pilihan_ganda') {
      var kunci = $('#kunci_jawaban_pg').val();
      if (!kunci) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'Validasi Gagal',
          text: 'Silakan pilih kunci jawaban'
        });
        return false;
      }
    } else if (tipe === 'benar_salah') {
      var kunci = $('#kunci_jawaban_bs').val();
      if (!kunci) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'Validasi Gagal',
          text: 'Silakan pilih kunci jawaban'
        });
        return false;
      }
    }
  });
});
</script>
@endpush
