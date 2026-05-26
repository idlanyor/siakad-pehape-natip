@extends('layouts.admin')
@section('title', 'Input Nilai PKL')
@section('page_title', 'Nilai PKL — ' . $penempatan->siswa->nama)

@section('content')
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card card-primary card-outline">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Input Nilai PKL</h3>
        <a href="{{ route('guru.pkl.detail', $penempatan) }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-3">
          <tr><td style="width:140px"><strong>Siswa</strong></td><td>: {{ $penempatan->siswa->nama }}</td></tr>
          <tr><td><strong>Tempat PKL</strong></td><td>: {{ $penempatan->tempatPkl->nama }}</td></tr>
        </table>

        @if($penempatan->nilai)
        <div class="alert alert-info py-2"><i class="fas fa-info-circle"></i> Nilai sudah pernah diisi — data lama akan diperbarui.</div>
        @endif

        <form action="{{ route('guru.pkl.nilai.simpan', $penempatan) }}" method="POST">
          @csrf
          <div class="form-group">
            <label>Nilai Pembimbing Sekolah <small class="text-muted">(0-100)</small></label>
            <input type="number" name="nilai_pembimbing" step="0.01" min="0" max="100"
                   class="form-control @error('nilai_pembimbing') is-invalid @enderror"
                   value="{{ old('nilai_pembimbing', $penempatan->nilai?->nilai_pembimbing) }}"
                   style="width:150px" placeholder="-">
            @error('nilai_pembimbing') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Nilai DU/DI <small class="text-muted">(dari industri)</small></label>
            <input type="number" name="nilai_du_di" step="0.01" min="0" max="100"
                   class="form-control @error('nilai_du_di') is-invalid @enderror"
                   value="{{ old('nilai_du_di', $penempatan->nilai?->nilai_du_di) }}"
                   style="width:150px" placeholder="-">
            @error('nilai_du_di') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Nilai Sikap <small class="text-muted">(kedisiplinan, etika)</small></label>
            <input type="number" name="nilai_sikap" step="0.01" min="0" max="100"
                   class="form-control @error('nilai_sikap') is-invalid @enderror"
                   value="{{ old('nilai_sikap', $penempatan->nilai?->nilai_sikap) }}"
                   style="width:150px" placeholder="-">
            @error('nilai_sikap') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Catatan</label>
            <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3"
                      placeholder="Catatan opsional">{{ old('catatan', $penempatan->nilai?->catatan) }}</textarea>
            @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Simpan Nilai
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
