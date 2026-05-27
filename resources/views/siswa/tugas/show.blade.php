@extends('layouts.admin')
@section('title', $tugas->judul)
@section('page_title', 'Detail Tugas')

@section('content')
<div class="row">
  <div class="col-lg-7">
    {{-- Detail Tugas --}}
    <div class="card card-warning card-outline">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">{{ $tugas->judul }}</h3>
        <a href="{{ route('siswa.tugas.index') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Mata Pelajaran</dt>
          <dd class="col-sm-8">{{ $tugas->jadwal->mapel->nama_mapel }}</dd>
          <dt class="col-sm-4">Guru</dt>
          <dd class="col-sm-8">{{ $tugas->jadwal->guru?->nama ?? '-' }}</dd>
          <dt class="col-sm-4">Deadline</dt>
          <dd class="col-sm-8">
            <span class="text-{{ $tugas->deadline_color }} font-weight-bold">
              {{ $tugas->deadline->format('d M Y H:i') }}
            </span>
            <br><small class="{{ $tugas->is_expired ? 'text-danger' : 'text-muted' }}">{{ $tugas->sisa_waktu }}</small>
          </dd>
          <dt class="col-sm-4">Nilai Maks</dt>
          <dd class="col-sm-8">{{ $tugas->nilai_maksimal }}</dd>
          <dt class="col-sm-4">Tipe Kumpul</dt>
          <dd class="col-sm-8"><span class="badge badge-info">{{ $tugas->tipe_label }}</span></dd>
        </dl>
        <hr>
        <h6>Instruksi:</h6>
        <div class="p-3 bg-light rounded">
          {!! nl2br(e($tugas->instruksi)) !!}
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    {{-- Status Pengumpulan --}}
    @if($pengumpulan)
      <div class="card card-success card-outline">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-check-circle text-success"></i> Sudah Dikumpulkan</h3>
        </div>
        <div class="card-body">
          <p class="text-muted small mb-2">
            Dikumpulkan: <strong>{{ $pengumpulan->created_at->format('d M Y H:i') }}</strong>
            @if($pengumpulan->created_at > $tugas->deadline)
              <span class="badge badge-danger ml-1">Terlambat</span>
            @endif
          </p>

          @if($tugas->tipe_pengumpulan === 'teks')
            <div class="p-2 bg-light rounded small">{{ Str::limit($pengumpulan->konten, 200) }}</div>
          @elseif($tugas->tipe_pengumpulan === 'link')
            <a href="{{ $pengumpulan->konten }}" target="_blank" class="btn btn-sm btn-info btn-block">
              <i class="fas fa-external-link-alt"></i> Buka Link Pengumpulan
            </a>
          @else
            <a href="{{ $pengumpulan->download_url }}" target="_blank" class="btn btn-sm btn-primary btn-block">
              <i class="fas fa-download"></i> {{ $pengumpulan->file_name }}
            </a>
          @endif

          @if($pengumpulan->catatan)
            <p class="small mt-2 text-muted"><i class="fas fa-comment"></i> {{ $pengumpulan->catatan }}</p>
          @endif

          @if($pengumpulan->is_dinilai)
            <hr>
            <div class="text-center">
              <h4 class="text-success mb-0">{{ $pengumpulan->nilai }} / {{ $tugas->nilai_maksimal }}</h4>
              <small class="text-muted">({{ $pengumpulan->nilai_persen }}%)</small>
              @if($pengumpulan->feedback)
                <p class="mt-2 small text-muted"><strong>Feedback:</strong> {{ $pengumpulan->feedback }}</p>
              @endif
            </div>
          @else
            <p class="text-muted small text-center mt-2 mb-0">
              <i class="fas fa-hourglass-half"></i> Menunggu penilaian dari guru.
            </p>
          @endif

          @if(! $pengumpulan->is_dinilai)
            <hr>
            <form action="{{ route('siswa.tugas.hapus', $pengumpulan) }}" method="POST"
                  onsubmit="return confirm('Hapus pengumpulan? Kamu bisa kumpul ulang sebelum deadline.')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger btn-block">
                <i class="fas fa-undo"></i> Tarik Pengumpulan
              </button>
            </form>
          @endif
        </div>
      </div>

    @elseif($tugas->is_expired)
      <div class="card">
        <div class="card-body text-center py-4">
          <i class="fas fa-lock fa-3x text-danger mb-3 d-block"></i>
          <p class="text-danger font-weight-bold">Deadline sudah lewat</p>
          <p class="text-muted small">Kamu tidak mengumpulkan tugas ini tepat waktu.</p>
        </div>
      </div>

    @else
      {{-- Form Kumpul --}}
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-upload"></i> Kumpulkan Tugas</h3>
        </div>
        <form action="{{ route('siswa.tugas.kumpul', $tugas) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="card-body">
            @if($tugas->tipe_pengumpulan === 'file')
              <div class="form-group">
                <label>Upload File <span class="text-danger">*</span></label>
                <div class="custom-file">
                  <input type="file" name="file" id="file"
                         class="custom-file-input @error('file') is-invalid @enderror"
                         accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.jpg,.jpeg,.png">
                  <label class="custom-file-label" for="file">Pilih file...</label>
                </div>
                <small class="form-text text-muted">PDF, Word, PPT, Excel, ZIP, Gambar. Maks 10 MB.</small>
                @error('file')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>

            @elseif($tugas->tipe_pengumpulan === 'link')
              <div class="form-group">
                <label>URL / Tautan <span class="text-danger">*</span></label>
                <input type="url" name="link" class="form-control @error('link') is-invalid @enderror"
                       value="{{ old('link') }}" placeholder="https://...">
                @error('link')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

            @else
              <div class="form-group">
                <label>Jawaban / Essay <span class="text-danger">*</span></label>
                <textarea name="teks" rows="8"
                          class="form-control @error('teks') is-invalid @enderror"
                          placeholder="Tuliskan jawaban kamu di sini...">{{ old('teks') }}</textarea>
                @error('teks')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            @endif

            <div class="form-group mb-0">
              <label>Catatan (opsional)</label>
              <textarea name="catatan" rows="2" class="form-control"
                        placeholder="Catatan untuk guru...">{{ old('catatan') }}</textarea>
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-primary btn-block">
              <i class="fas fa-paper-plane"></i> Kumpulkan Sekarang
            </button>
          </div>
        </form>
      </div>
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
  $('#file').on('change', function() {
    var name = this.files[0]?.name || 'Pilih file...';
    $(this).next('.custom-file-label').text(name);
  });
</script>
@endpush
