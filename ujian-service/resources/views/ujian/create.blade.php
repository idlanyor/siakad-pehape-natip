@extends('layouts.admin')

@section('title', 'Buat Ujian Baru')

@section('page_title', 'Buat Ujian Baru')

@push('styles')
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<!-- Tempusdominus Bootstrap 4 -->
<link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
<!-- iCheck -->
<link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <form action="{{ route('ujian.store') }}" method="POST" id="ujian-form">
      @csrf
      
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Informasi Dasar</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="periode_id">Periode Ujian <span class="text-danger">*</span></label>
                <select name="periode_id" id="periode_id" class="form-control select2 @error('periode_id') is-invalid @enderror" required>
                  <option value="">Pilih Periode</option>
                  @foreach($periodes as $periode)
                  <option value="{{ $periode->id }}" {{ old('periode_id') == $periode->id ? 'selected' : '' }}>
                    {{ $periode->nama }} ({{ $periode->tahun_ajaran }})
                  </option>
                  @endforeach
                </select>
                @error('periode_id')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="judul">Judul Ujian <span class="text-danger">*</span></label>
                <input type="text" name="judul" id="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul') }}" placeholder="Contoh: Ujian Tengah Semester Matematika" required>
                @error('judul')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="kelas_id">Kelas <span class="text-danger">*</span></label>
                <select name="kelas_id" id="kelas_id" class="form-control select2 @error('kelas_id') is-invalid @enderror" required>
                  <option value="">Pilih Kelas</option>
                  <!-- Will be populated via AJAX from SIAKAD -->
                </select>
                @error('kelas_id')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="mapel_id">Mata Pelajaran <span class="text-danger">*</span></label>
                <select name="mapel_id" id="mapel_id" class="form-control select2 @error('mapel_id') is-invalid @enderror" required>
                  <option value="">Pilih Mata Pelajaran</option>
                  <!-- Will be populated via AJAX from SIAKAD -->
                </select>
                @error('mapel_id')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="jumlah_soal">Jumlah Soal <span class="text-danger">*</span></label>
                <input type="number" name="jumlah_soal" id="jumlah_soal" class="form-control @error('jumlah_soal') is-invalid @enderror" value="{{ old('jumlah_soal', 30) }}" min="1" max="100" required>
                @error('jumlah_soal')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted">Jumlah soal yang akan ditampilkan dalam ujian</small>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" rows="3" class="form-control @error('deskripsi') is-invalid @enderror" placeholder="Deskripsi singkat tentang ujian ini">{{ old('deskripsi') }}</textarea>
            @error('deskripsi')
            <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Waktu & Durasi</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="waktu_mulai">Waktu Mulai <span class="text-danger">*</span></label>
                <div class="input-group date" id="waktu_mulai_picker" data-target-input="nearest">
                  <input type="text" name="waktu_mulai" id="waktu_mulai" class="form-control datetimepicker-input @error('waktu_mulai') is-invalid @enderror" data-target="#waktu_mulai_picker" value="{{ old('waktu_mulai') }}" required>
                  <div class="input-group-append" data-target="#waktu_mulai_picker" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
                </div>
                @error('waktu_mulai')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="waktu_selesai">Waktu Selesai <span class="text-danger">*</span></label>
                <div class="input-group date" id="waktu_selesai_picker" data-target-input="nearest">
                  <input type="text" name="waktu_selesai" id="waktu_selesai" class="form-control datetimepicker-input @error('waktu_selesai') is-invalid @enderror" data-target="#waktu_selesai_picker" value="{{ old('waktu_selesai') }}" required>
                  <div class="input-group-append" data-target="#waktu_selesai_picker" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
                </div>
                @error('waktu_selesai')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="durasi_menit">Durasi (Menit) <span class="text-danger">*</span></label>
                <input type="number" name="durasi_menit" id="durasi_menit" class="form-control @error('durasi_menit') is-invalid @enderror" value="{{ old('durasi_menit', 90) }}" min="1" max="300" required>
                @error('durasi_menit')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted">Durasi pengerjaan ujian per siswa</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Pengaturan Penilaian</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="passing_grade">Passing Grade <span class="text-danger">*</span></label>
                <input type="number" name="passing_grade" id="passing_grade" class="form-control @error('passing_grade') is-invalid @enderror" value="{{ old('passing_grade', 75) }}" min="0" max="100" step="0.01" required>
                @error('passing_grade')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted">Nilai minimum untuk lulus (0-100)</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>&nbsp;</label>
                <div class="icheck-primary">
                  <input type="checkbox" name="tampilkan_nilai" id="tampilkan_nilai" value="1" {{ old('tampilkan_nilai', true) ? 'checked' : '' }}>
                  <label for="tampilkan_nilai">
                    Tampilkan nilai setelah ujian selesai
                  </label>
                </div>
                <small class="form-text text-muted">Jika dicentang, siswa dapat melihat nilai langsung setelah submit</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Pengaturan Soal</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="icheck-primary">
                <input type="checkbox" name="acak_soal" id="acak_soal" value="1" {{ old('acak_soal', true) ? 'checked' : '' }}>
                <label for="acak_soal">
                  Acak urutan soal untuk setiap siswa
                </label>
              </div>
              <small class="form-text text-muted">Setiap siswa akan mendapat urutan soal yang berbeda</small>
            </div>
            <div class="col-md-6">
              <div class="icheck-primary">
                <input type="checkbox" name="acak_opsi" id="acak_opsi" value="1" {{ old('acak_opsi', true) ? 'checked' : '' }}>
                <label for="acak_opsi">
                  Acak urutan opsi jawaban
                </label>
              </div>
              <small class="form-text text-muted">Urutan A, B, C, D akan diacak untuk setiap siswa</small>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-footer">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan Ujian
          </button>
          <a href="{{ route('ujian.index') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Batal
          </a>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<!-- Select2 -->
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<!-- Moment -->
<script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>

<script>
$(function() {
  // Initialize Select2
  $('.select2').select2({
    theme: 'bootstrap4'
  });

  // Initialize Datetimepicker
  $('#waktu_mulai_picker').datetimepicker({
    format: 'YYYY-MM-DD HH:mm:ss',
    sideBySide: true,
    icons: {
      time: 'far fa-clock'
    }
  });

  $('#waktu_selesai_picker').datetimepicker({
    format: 'YYYY-MM-DD HH:mm:ss',
    sideBySide: true,
    icons: {
      time: 'far fa-clock'
    },
    useCurrent: false
  });

  // Link pickers
  $('#waktu_mulai_picker').on('change.datetimepicker', function(e) {
    $('#waktu_selesai_picker').datetimepicker('minDate', e.date);
  });

  $('#waktu_selesai_picker').on('change.datetimepicker', function(e) {
    $('#waktu_mulai_picker').datetimepicker('maxDate', e.date);
  });

  // Load reference data from SIAKAD
  loadReferenceData();

  // Form validation
  $('#ujian-form').on('submit', function(e) {
    var waktuMulai = moment($('#waktu_mulai').val());
    var waktuSelesai = moment($('#waktu_selesai').val());
    
    if (waktuSelesai.isBefore(waktuMulai)) {
      e.preventDefault();
      Swal.fire({
        icon: 'error',
        title: 'Validasi Gagal',
        text: 'Waktu selesai harus lebih besar dari waktu mulai'
      });
      return false;
    }
  });
});

function loadReferenceData() {
  // TODO: Implement AJAX call to SIAKAD for kelas and mapel data
  // This will be implemented when JWT token handoff is ready
  console.log('Loading reference data from SIAKAD...');
  
  // Temporary mock data for development
  var mockKelas = [
    {id: 1, nama: 'X RPL 1'},
    {id: 2, nama: 'X RPL 2'},
    {id: 3, nama: 'XI RPL 1'},
    {id: 4, nama: 'XI RPL 2'},
    {id: 5, nama: 'XII RPL 1'}
  ];
  
  var mockMapel = [
    {id: 1, nama: 'Matematika'},
    {id: 2, nama: 'Bahasa Indonesia'},
    {id: 3, nama: 'Bahasa Inggris'},
    {id: 4, nama: 'Pemrograman Web'},
    {id: 5, nama: 'Basis Data'}
  ];
  
  // Populate kelas
  $.each(mockKelas, function(i, kelas) {
    $('#kelas_id').append($('<option>', {
      value: kelas.id,
      text: kelas.nama
    }));
  });
  
  // Populate mapel
  $.each(mockMapel, function(i, mapel) {
    $('#mapel_id').append($('<option>', {
      value: mapel.id,
      text: mapel.nama
    }));
  });
}
</script>
@endpush
