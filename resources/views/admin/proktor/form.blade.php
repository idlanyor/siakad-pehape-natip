@extends('layouts.admin')
@section('title', isset($ujian) ? 'Edit Ujian' : 'Buat Ujian')
@section('page_title', isset($ujian) ? 'Edit Ujian' : 'Buat Ujian Baru')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-9">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          {{ isset($ujian) ? 'Edit' : 'Buat' }} Ujian &mdash;
          <span class="badge badge-primary">{{ $jadwal->mapel->nama_mapel }}</span>
          {{ $jadwal->kelas?->nama_kelas }}
        </h3>
      </div>
      <form action="{{ isset($ujian) ? route('admin.proktor.update', $ujian) : route('admin.proktor.store', $jadwal) }}"
            method="POST" id="formUjian">
        @csrf
        @isset($ujian) @method('PUT') @endisset

        <div class="card-body">
          {{-- Judul --}}
          <div class="form-group">
            <label>Judul Ujian <span class="text-danger">*</span></label>
            <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
                   value="{{ old('judul', ($ujian ?? null)?->judul ?? '') }}"
                   placeholder="Contoh: PTS Matematika Semester Ganjil" required>
            @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Deskripsi --}}
          <div class="form-group">
            <label>Deskripsi / Instruksi</label>
            <textarea name="deskripsi" rows="3"
                      class="form-control @error('deskripsi') is-invalid @enderror"
                      placeholder="Instruksi atau keterangan tambahan untuk siswa...">{{ old('deskripsi', ($ujian ?? null)?->deskripsi ?? '') }}</textarea>
            @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Tipe Ujian --}}
          <div class="form-group">
            <label>Tipe Ujian <span class="text-danger">*</span></label>
            <select name="tipe" class="form-control @error('tipe') is-invalid @enderror" required>
              @foreach(\App\Models\Ujian::TIPE_LABEL as $val => $label)
                <option value="{{ $val }}"
                  {{ old('tipe', ($ujian ?? null)?->tipe ?? 'quiz') === $val ? 'selected' : '' }}>
                  {{ $label }}
                </option>
              @endforeach
            </select>
            @error('tipe')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Waktu Mulai & Selesai --}}
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Waktu Mulai <span class="text-danger">*</span></label>
                <input type="datetime-local" name="waktu_mulai" id="waktu_mulai"
                       class="form-control @error('waktu_mulai') is-invalid @enderror"
                       value="{{ old('waktu_mulai', isset($ujian) ? $ujian->waktu_mulai->format('Y-m-d\TH:i') : '') }}" required>
                @error('waktu_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Waktu Selesai <span class="text-danger">*</span></label>
                <input type="datetime-local" name="waktu_selesai" id="waktu_selesai"
                       class="form-control @error('waktu_selesai') is-invalid @enderror"
                       value="{{ old('waktu_selesai', isset($ujian) ? $ujian->waktu_selesai->format('Y-m-d\TH:i') : '') }}" required>
                @error('waktu_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="form-text text-muted" id="waktu-hint"></small>
              </div>
            </div>
          </div>

          {{-- Durasi & Passing Grade --}}
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Durasi Pengerjaan (menit) <span class="text-danger">*</span></label>
                <input type="number" name="durasi_menit" min="1" max="300"
                       class="form-control @error('durasi_menit') is-invalid @enderror"
                       value="{{ old('durasi_menit', ($ujian ?? null)?->durasi_menit ?? 60) }}" required>
                @error('durasi_menit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="form-text text-muted">Waktu maksimal siswa mengerjakan ujian</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Passing Grade (%) <span class="text-danger">*</span></label>
                <input type="number" name="passing_grade" min="0" max="100"
                       class="form-control @error('passing_grade') is-invalid @enderror"
                       value="{{ old('passing_grade', ($ujian ?? null)?->passing_grade ?? 75) }}" required>
                @error('passing_grade')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="form-text text-muted">Nilai minimal untuk lulus</small>
              </div>
            </div>
          </div>

          {{-- Opsi Tambahan --}}
          <div class="form-group">
            <label class="d-block">Pengaturan Tambahan</label>

            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" name="acak_soal" id="acak_soal" value="1"
                     class="custom-control-input"
                     {{ old('acak_soal', ($ujian ?? null)?->acak_soal ?? false) ? 'checked' : '' }}>
              <label class="custom-control-label" for="acak_soal">
                <i class="fas fa-random text-info"></i> Acak urutan soal untuk setiap siswa
              </label>
            </div>

            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" name="acak_opsi" id="acak_opsi" value="1"
                     class="custom-control-input"
                     {{ old('acak_opsi', ($ujian ?? null)?->acak_opsi ?? false) ? 'checked' : '' }}>
              <label class="custom-control-label" for="acak_opsi">
                <i class="fas fa-shuffle text-warning"></i> Acak urutan opsi jawaban
              </label>
            </div>

            <div class="custom-control custom-checkbox">
              <input type="checkbox" name="tampilkan_nilai" id="tampilkan_nilai" value="1"
                     class="custom-control-input"
                     {{ old('tampilkan_nilai', ($ujian ?? null)?->tampilkan_nilai ?? true) ? 'checked' : '' }}>
              <label class="custom-control-label" for="tampilkan_nilai">
                <i class="fas fa-eye text-success"></i> Tampilkan nilai langsung setelah submit
              </label>
            </div>
          </div>

          {{-- Preview Metadata --}}
          <div class="alert alert-light border mt-4" id="preview-metadata">
            <h6 class="mb-2"><i class="fas fa-info-circle"></i> Preview Ujian</h6>
            <div class="row small">
              <div class="col-md-6">
                <strong>Judul:</strong> <span id="prev-judul" class="text-muted">-</span><br>
                <strong>Tipe:</strong> <span id="prev-tipe" class="text-muted">-</span><br>
                <strong>Durasi:</strong> <span id="prev-durasi" class="text-muted">-</span>
              </div>
              <div class="col-md-6">
                <strong>Waktu:</strong> <span id="prev-waktu" class="text-muted">-</span><br>
                <strong>Passing Grade:</strong> <span id="prev-pg" class="text-muted">-</span><br>
                <strong>Pengaturan:</strong> <span id="prev-settings" class="text-muted">-</span>
              </div>
            </div>
          </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.proktor.show', $jadwal) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
          <button type="submit" class="btn btn-primary" id="btnSubmit">
            <i class="fas fa-save"></i> {{ isset($ujian) ? 'Perbarui' : 'Simpan' }} Ujian
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
  // Validasi waktu selesai > waktu mulai
  function validateWaktu() {
    const mulai = $('#waktu_mulai').val();
    const selesai = $('#waktu_selesai').val();

    if (mulai && selesai) {
      const tMulai = new Date(mulai);
      const tSelesai = new Date(selesai);

      if (tSelesai <= tMulai) {
        $('#waktu_selesai')[0].setCustomValidity('Waktu selesai harus lebih besar dari waktu mulai');
        $('#waktu-hint').text('⚠️ Waktu selesai harus setelah waktu mulai').addClass('text-danger');
        return false;
      } else {
        $('#waktu_selesai')[0].setCustomValidity('');
        const diff = Math.floor((tSelesai - tMulai) / (1000 * 60 * 60 * 24));
        $('#waktu-hint').text(`✓ Rentang waktu: ${diff} hari`).removeClass('text-danger').addClass('text-success');
        return true;
      }
    }
    return true;
  }

  $('#waktu_mulai, #waktu_selesai').on('change', validateWaktu);

  // Preview metadata real-time
  function updatePreview() {
    const judul = $('input[name="judul"]').val() || '-';
    const tipe = $('select[name="tipe"] option:selected').text() || '-';
    const durasi = $('input[name="durasi_menit"]').val() || '-';
    const pg = $('input[name="passing_grade"]').val() || '-';
    const mulai = $('#waktu_mulai').val();
    const selesai = $('#waktu_selesai').val();

    $('#prev-judul').text(judul);
    $('#prev-tipe').text(tipe);
    $('#prev-durasi').text(durasi + ' menit');
    $('#prev-pg').text(pg + '%');

    if (mulai && selesai) {
      const fMulai = new Date(mulai).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
      const fSelesai = new Date(selesai).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
      $('#prev-waktu').text(fMulai + ' s/d ' + fSelesai);
    } else {
      $('#prev-waktu').text('-');
    }

    const settings = [];
    if ($('#acak_soal').is(':checked')) settings.push('Acak Soal');
    if ($('#acak_opsi').is(':checked')) settings.push('Acak Opsi');
    if ($('#tampilkan_nilai').is(':checked')) settings.push('Tampilkan Nilai');
    $('#prev-settings').text(settings.length ? settings.join(', ') : 'Tidak ada');
  }

  $('input, select, textarea').on('input change', updatePreview);
  updatePreview();

  // Submit validation
  $('#formUjian').on('submit', function(e) {
    if (!validateWaktu()) {
      e.preventDefault();
      toastr.error('Periksa kembali waktu mulai dan selesai');
      return false;
    }

    const durasi = parseInt($('input[name="durasi_menit"]').val());
    if (durasi < 1) {
      e.preventDefault();
      toastr.error('Durasi minimal 1 menit');
      return false;
    }

    $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
  });
});
</script>
@endpush
