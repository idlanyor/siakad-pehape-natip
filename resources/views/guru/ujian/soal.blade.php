@extends('layouts.admin')
@section('title', 'Kelola Soal - ' . $ujian->judul)
@section('page_title', 'Kelola Soal Ujian')

@push('styles')
<style>
  .soal-item {
    transition: all 0.3s ease;
    cursor: move;
  }
  .soal-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  .soal-item.sortable-ghost {
    opacity: 0.4;
    background: #f8f9fa;
  }
  .drag-handle {
    cursor: grab;
    color: #6c757d;
  }
  .drag-handle:active {
    cursor: grabbing;
  }
  .image-preview {
    max-width: 200px;
    max-height: 200px;
    border-radius: 4px;
    margin-top: 10px;
  }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    {{-- Header Card --}}
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-list-check"></i> {{ $ujian->judul }}
          <span class="badge badge-{{ $ujian->tipe_color }}">{{ $ujian->tipe_label }}</span>
        </h3>
        <div class="card-tools">
          <span class="badge badge-info badge-lg mr-2">
            <i class="fas fa-question-circle"></i> <span id="total-soal">{{ $ujian->soalUjians->count() }}</span> Soal
          </span>
          <span class="badge badge-warning badge-lg mr-2">
            <i class="fas fa-star"></i> Total Bobot: <span id="total-bobot">{{ $ujian->soalUjians->sum('bobot_nilai') }}</span>
          </span>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-8">
            <p class="mb-1"><strong>Kelas:</strong> {{ $ujian->jadwal->kelas->nama_kelas }} | <strong>Mapel:</strong> {{ $ujian->jadwal->mapel->nama_mapel }}</p>
            <p class="mb-0"><strong>Waktu:</strong> {{ $ujian->waktu_mulai->format('d M Y H:i') }} - {{ $ujian->waktu_selesai->format('d M Y H:i') }}</p>
          </div>
          <div class="col-md-4 text-right">
            @if($ujian->is_published)
              <span class="badge badge-success badge-lg"><i class="fas fa-check-circle"></i> Published</span>
            @else
              <form action="{{ route('guru.ujian.publish', $ujian) }}" method="POST" class="d-inline" id="formPublish">
                @csrf
                <button type="submit" class="btn btn-success btn-sm" id="btnPublish"
                        {{ $ujian->soalUjians->count() < 1 ? 'disabled' : '' }}>
                  <i class="fas fa-paper-plane"></i> Publish Ujian
                </button>
              </form>
            @endif
            <a href="{{ route('guru.ujian.show', $ujian->jadwal) }}" class="btn btn-secondary btn-sm ml-1">
              <i class="fas fa-arrow-left"></i> Kembali
            </a>
          </div>
        </div>
      </div>
    </div>

    {{-- Form Tambah Soal --}}
    <div class="card card-success card-outline collapsed-card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus-circle"></i> Tambah Soal Baru</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-plus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <form action="{{ route('guru.ujian.soal.store', $ujian) }}" method="POST" enctype="multipart/form-data" id="formTambahSoal">
          @csrf

          <div class="form-group">
            <label>Tipe Soal <span class="text-danger">*</span></label>
            <select name="tipe_soal" class="form-control @error('tipe_soal') is-invalid @enderror" id="tipe-soal" required>
              <option value="pilihan_ganda" selected>Pilihan Ganda</option>
              <option value="essay">Essay</option>
              <option value="benar_salah">Benar / Salah</option>
              <option value="isian_singkat">Isian Singkat</option>
            </select>
            @error('tipe_soal')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="form-group">
            <label>Pertanyaan <span class="text-danger">*</span></label>
            <textarea name="pertanyaan" rows="3" class="form-control @error('pertanyaan') is-invalid @enderror" placeholder="Tuliskan pertanyaan soal..." required>{{ old('pertanyaan') }}</textarea>
            @error('pertanyaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="form-group">
            <label>Gambar Soal (opsional)</label>
            <div class="custom-file">
              <input type="file" name="gambar" id="gambar" class="custom-file-input @error('gambar') is-invalid @enderror" accept="image/*">
              <label class="custom-file-label" for="gambar">Pilih gambar...</label>
            </div>
            <img id="preview-gambar" class="image-preview" style="display:none;">
            <small class="form-text text-muted">Max 2MB. Format: JPG, PNG, GIF</small>
            @error('gambar')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>

          {{-- Opsi Pilihan Ganda --}}
          <div id="opsi-pilihan-ganda" class="form-group">
            <label>Opsi Jawaban <span class="text-danger">*</span></label>
            <div id="opsi-container">
              @for($i = 0; $i < 2; $i++)
              <div class="input-group mb-2 opsi-item">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <input type="radio" name="opsi_benar" value="{{ $i }}" {{ $i === 0 ? 'checked' : '' }} required>
                  </span>
                  <span class="input-group-text">{{ chr(65 + $i) }}</span>
                </div>
                <input type="text" name="opsi[{{ $i }}][teks]" class="form-control" placeholder="Opsi {{ chr(65 + $i) }}" required>
              </div>
              @endfor
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-opsi">
              <i class="fas fa-plus"></i> Tambah Opsi
            </button>
            <small class="form-text text-muted">Minimal 2 opsi, maksimal 5 opsi. Pilih radio untuk jawaban benar.</small>
          </div>

          {{-- Opsi Benar/Salah --}}
          <div id="opsi-benar-salah" class="form-group" style="display: none;">
            <label>Jawaban Benar <span class="text-danger">*</span></label>
            <div>
              <div class="custom-control custom-radio custom-control-inline">
                <input class="custom-control-input" type="radio" name="opsi_benar_salah" id="benar" value="0" checked>
                <label class="custom-control-label" for="benar">Benar</label>
              </div>
              <div class="custom-control custom-radio custom-control-inline">
                <input class="custom-control-input" type="radio" name="opsi_benar_salah" id="salah" value="1">
                <label class="custom-control-label" for="salah">Salah</label>
              </div>
            </div>
          </div>

          {{-- Kunci Jawaban --}}
          <div id="kunci-jawaban-container" class="form-group" style="display: none;">
            <label>Kunci Jawaban</label>
            <textarea name="kunci_jawaban" class="form-control" rows="3" id="kunci-jawaban-input">{{ old('kunci_jawaban') }}</textarea>
            <small class="form-text text-muted" id="kunci-hint"></small>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Bobot Nilai <span class="text-danger">*</span></label>
                <input type="number" name="bobot_nilai" class="form-control @error('bobot_nilai') is-invalid @enderror" value="{{ old('bobot_nilai', 10) }}" min="1" max="100" required>
                @error('bobot_nilai')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Pembahasan (opsional)</label>
                <textarea name="pembahasan" rows="2" class="form-control" placeholder="Penjelasan jawaban yang benar...">{{ old('pembahasan') }}</textarea>
              </div>
            </div>
          </div>

          <div class="text-right">
            <button type="reset" class="btn btn-secondary">
              <i class="fas fa-redo"></i> Reset
            </button>
            <button type="submit" class="btn btn-success" id="btnSimpanSoal">
              <i class="fas fa-save"></i> Simpan Soal
            </button>
          </div>
        </form>
      </div>
    </div>

    {{-- List Soal --}}
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list"></i> Daftar Soal</h3>
        <div class="card-tools">
          <span class="text-muted small"><i class="fas fa-grip-vertical"></i> Drag untuk mengubah urutan</span>
        </div>
      </div>
      <div class="card-body">
        @if($ujian->soalUjians->isEmpty())
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Belum ada soal. Klik tombol "Tambah Soal Baru" untuk memulai.
          </div>
        @else
          <div id="soal-list">
            @foreach($ujian->soalUjians as $index => $soal)
            <div class="card mb-3 soal-item" data-id="{{ $soal->id }}">
              <div class="card-body">
                <div class="row">
                  <div class="col-auto drag-handle">
                    <i class="fas fa-grip-vertical fa-2x"></i>
                  </div>
                  <div class="col">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <h5 class="mb-0">
                        <span class="badge badge-secondary">{{ $index + 1 }}</span>
                        <span class="badge badge-info"><i class="{{ $soal->tipe_icon }}"></i> {{ $soal->tipe_label }}</span>
                        <span class="badge badge-warning">{{ $soal->bobot_nilai }} poin</span>
                      </h5>
                      <div>
                        <button class="btn btn-sm btn-danger btn-delete-soal" data-id="{{ $soal->id }}">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </div>
                    <p class="mb-2"><strong>{{ $soal->pertanyaan }}</strong></p>
                    @if($soal->gambar)
                      <img src="{{ $soal->gambar_url }}" class="image-preview mb-2">
                    @endif
                    @if($soal->is_objektif)
                      <div class="pl-3">
                        @foreach($soal->opsiJawabans as $opsi)
                          <div class="mb-1">
                            <i class="fas fa-{{ $opsi->is_benar ? 'check-circle text-success' : 'circle text-muted' }}"></i>
                            <strong>{{ $opsi->label }}.</strong> {{ $opsi->teks_opsi }}
                          </div>
                        @endforeach
                      </div>
                    @endif
                    @if($soal->kunci_jawaban)
                      <div class="alert alert-light mt-2 mb-0">
                        <small><strong>Kunci:</strong> {{ Str::limit($soal->kunci_jawaban, 100) }}</small>
                      </div>
                    @endif
                    @if($soal->pembahasan)
                      <div class="alert alert-light mt-2 mb-0">
                        <small><strong>Pembahasan:</strong> {{ $soal->pembahasan }}</small>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
$(function() {
  const ujianId = {{ $ujian->id }};
  let opsiCount = 2;
  const opsiLabels = ['A', 'B', 'C', 'D', 'E'];

  // Sortable.js - Drag & Drop
  const soalList = document.getElementById('soal-list');
  if (soalList) {
    new Sortable(soalList, {
      animation: 150,
      handle: '.drag-handle',
      ghostClass: 'sortable-ghost',
      onEnd: function(evt) {
        const order = [];
        $('#soal-list .soal-item').each(function(index) {
          order.push($(this).data('id'));
        });

        $.ajax({
          url: `/guru/ujian/${ujianId}/soal/reorder`,
          method: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
            order: order
          },
          success: function() {
            toastr.success('Urutan soal berhasil diperbarui');
            updateNomorUrut();
          },
          error: function() {
            toastr.error('Gagal mengubah urutan soal');
          }
        });
      }
    });
  }

  // Update nomor urut setelah reorder
  function updateNomorUrut() {
    $('#soal-list .soal-item').each(function(index) {
      $(this).find('.badge-secondary').first().text(index + 1);
    });
  }

  // Tipe soal change handler
  $('#tipe-soal').on('change', function() {
    const tipe = $(this).val();
    const opsiPG = $('#opsi-pilihan-ganda');
    const opsiBS = $('#opsi-benar-salah');
    const kunciContainer = $('#kunci-jawaban-container');
    const kunciInput = $('#kunci-jawaban-input');
    const kunciHint = $('#kunci-hint');

    // Hide all
    opsiPG.hide();
    opsiBS.hide();
    kunciContainer.hide();

    // Remove required
    $('#opsi-container input[type="text"]').removeAttr('required');
    $('#opsi-container input[type="radio"]').removeAttr('required');
    kunciInput.removeAttr('required');

    if (tipe === 'pilihan_ganda') {
      opsiPG.show();
      $('#opsi-container input[type="text"]').attr('required', 'required');
      $('#opsi-container input[type="radio"]').attr('required', 'required');
    } else if (tipe === 'benar_salah') {
      opsiBS.show();
    } else if (tipe === 'essay') {
      kunciContainer.show();
      kunciHint.text('Panduan koreksi untuk guru (opsional)');
    } else if (tipe === 'isian_singkat') {
      kunciContainer.show();
      kunciInput.attr('required', 'required');
      kunciHint.text('Jawaban yang benar (untuk auto-grading, case-insensitive)');
    }
  });

  // Preview gambar
  $('#gambar').on('change', function() {
    const file = this.files[0];
    const preview = $('#preview-gambar');

    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.attr('src', e.target.result).show();
      };
      reader.readAsDataURL(file);
      $(this).next('.custom-file-label').text(file.name);
    } else {
      preview.hide();
      $(this).next('.custom-file-label').text('Pilih gambar...');
    }
  });

  // Tambah opsi jawaban
  $('#btn-tambah-opsi').on('click', function() {
    if (opsiCount >= 5) {
      toastr.warning('Maksimal 5 opsi jawaban');
      return;
    }

    const label = opsiLabels[opsiCount];
    const html = `
      <div class="input-group mb-2 opsi-item">
        <div class="input-group-prepend">
          <span class="input-group-text">
            <input type="radio" name="opsi_benar" value="${opsiCount}" required>
          </span>
          <span class="input-group-text">${label}</span>
        </div>
        <input type="text" name="opsi[${opsiCount}][teks]" class="form-control" placeholder="Opsi ${label}" required>
        <div class="input-group-append">
          <button type="button" class="btn btn-danger btn-remove-opsi">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
    `;
    $('#opsi-container').append(html);
    opsiCount++;
  });

  // Hapus opsi jawaban
  $(document).on('click', '.btn-remove-opsi', function() {
    if ($('.opsi-item').length <= 2) {
      toastr.warning('Minimal 2 opsi jawaban');
      return;
    }
    $(this).closest('.opsi-item').remove();
    opsiCount--;

    // Reindex
    $('#opsi-container .opsi-item').each(function(idx) {
      const label = opsiLabels[idx];
      $(this).find('.input-group-text').last().text(label);
      $(this).find('input[type="text"]').attr('name', `opsi[${idx}][teks]`).attr('placeholder', `Opsi ${label}`);
      $(this).find('input[type="radio"]').val(idx);
    });
  });

  // Submit form tambah soal
  $('#formTambahSoal').on('submit', function(e) {
    const tipe = $('#tipe-soal').val();

    // Validasi jawaban benar untuk pilihan ganda
    if (tipe === 'pilihan_ganda' && !$('input[name="opsi_benar"]:checked').length) {
      e.preventDefault();
      toastr.error('Pilih jawaban yang benar');
      return false;
    }

    // Handle benar/salah submission
    if (tipe === 'benar_salah') {
      const benarSalah = $('input[name="opsi_benar_salah"]:checked').val();

      // Create hidden inputs
      $('<input>').attr({type: 'hidden', name: 'opsi[0][teks]', value: 'Benar'}).appendTo(this);
      $('<input>').attr({type: 'hidden', name: 'opsi[1][teks]', value: 'Salah'}).appendTo(this);
      $('<input>').attr({type: 'hidden', name: 'opsi_benar', value: benarSalah}).appendTo(this);
    }

    $('#btnSimpanSoal').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
  });

  // Delete soal
  $(document).on('click', '.btn-delete-soal', function() {
    const soalId = $(this).data('id');

    Swal.fire({
      title: 'Hapus Soal?',
      text: 'Soal yang dihapus tidak dapat dikembalikan',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `/guru/ujian/${ujianId}/soal/${soalId}`,
          method: 'DELETE',
          data: { _token: '{{ csrf_token() }}' },
          success: function() {
            toastr.success('Soal berhasil dihapus');
            setTimeout(() => location.reload(), 1000);
          },
          error: function() {
            toastr.error('Gagal menghapus soal');
          }
        });
      }
    });
  });

  // Publish ujian confirmation
  $('#formPublish').on('submit', function(e) {
    e.preventDefault();

    Swal.fire({
      title: 'Publish Ujian?',
      text: 'Ujian akan terlihat oleh siswa setelah dipublish',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, Publish',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $('#btnPublish').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Publishing...');
        this.submit();
      }
    });
  });

  // Reset form
  $('#formTambahSoal').on('reset', function() {
    opsiCount = 2;
    $('#preview-gambar').hide();
    $('.custom-file-label').text('Pilih gambar...');

    // Reset to 2 opsi
    $('#opsi-container .opsi-item').slice(2).remove();
  });
});
</script>
@endpush
