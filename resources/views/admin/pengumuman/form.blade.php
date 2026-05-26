@extends('layouts.admin')
@section('title', $pengumuman ? 'Edit Pengumuman' : 'Buat Pengumuman')
@section('page_title', $pengumuman ? 'Edit Pengumuman' : 'Buat Pengumuman')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-9">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-bullhorn text-primary mr-2"></i>
          {{ $pengumuman ? 'Edit' : 'Buat' }} Pengumuman
        </h3>
      </div>
      <form action="{{ $pengumuman ? route('admin.pengumuman.update', $pengumuman) : route('admin.pengumuman.store') }}" method="POST">
        @csrf
        @if($pengumuman) @method('PUT') @endif
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
          @endif

          <div class="form-group">
            <label>Judul <span class="text-danger">*</span></label>
            <input type="text" name="judul"
                   class="form-control @error('judul') is-invalid @enderror"
                   value="{{ old('judul', $pengumuman?->judul) }}"
                   placeholder="Judul pengumuman...">
            @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label>Isi Pengumuman <span class="text-danger">*</span></label>
            <textarea name="isi" rows="8"
                      class="form-control @error('isi') is-invalid @enderror"
                      placeholder="Tulis isi pengumuman di sini...">{{ old('isi', $pengumuman?->isi) }}</textarea>
            @error('isi') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Target Penerima</label>
                <select name="target_role" id="targetRole"
                        class="form-control @error('target_role') is-invalid @enderror"
                        onchange="toggleKelas()">
                  @foreach($targetRoles as $val => $label)
                    <option value="{{ $val }}"
                      {{ old('target_role', $pengumuman?->target_role ?? '') == $val ? 'selected' : '' }}>
                      {{ $label }}
                    </option>
                  @endforeach
                </select>
                @error('target_role') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-4" id="kelasGroup" style="display:none">
              <div class="form-group">
                <label>Khusus Kelas <small class="text-muted">(opsional)</small></label>
                <select name="target_kelas_id" class="form-control">
                  <option value="">Semua Kelas</option>
                  @foreach($kelas as $k)
                    <option value="{{ $k->id }}"
                      {{ old('target_kelas_id', $pengumuman?->target_kelas_id) == $k->id ? 'selected' : '' }}>
                      {{ $k->nama_kelas }} — {{ $k->jurusan?->nama }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Tanggal Publish <small class="text-muted">(kosong = draft)</small></label>
                <input type="datetime-local" name="published_at"
                       class="form-control @error('published_at') is-invalid @enderror"
                       value="{{ old('published_at', $pengumuman?->published_at?->format('Y-m-d\TH:i')) }}">
                @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-secondary">Batal</a>
          <div>
            <button type="submit" name="published_at" value="" class="btn btn-secondary mr-2">
              <i class="fas fa-save mr-1"></i> Simpan Draft
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-paper-plane mr-1"></i> Simpan & Publish
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
function toggleKelas() {
  const role = document.getElementById('targetRole').value;
  document.getElementById('kelasGroup').style.display = role == '2' ? 'block' : 'none';
}
// Run on load untuk edit page
document.addEventListener('DOMContentLoaded', toggleKelas);
</script>
@endpush
@endsection
