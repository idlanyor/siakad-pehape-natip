@extends('layouts.admin')
@section('title', $jabatan ? 'Edit Jabatan' : 'Tambah Jabatan')
@section('page_title', 'Struktur Organisasi')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-sitemap mr-1 text-primary"></i>
          {{ $jabatan ? 'Edit Jabatan' : 'Tambah Jabatan' }}
        </h3>
      </div>
      <form action="{{ $jabatan ? route('admin.organisasi.update', $jabatan) : route('admin.organisasi.store') }}" method="POST">
        @csrf
        @if($jabatan) @method('PUT') @endif
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
          @endif

          <div class="form-group">
            <label>Nama Jabatan <span class="text-danger">*</span></label>
            <input type="text" name="nama_jabatan"
                   class="form-control @error('nama_jabatan') is-invalid @enderror"
                   value="{{ old('nama_jabatan', $jabatan?->nama_jabatan) }}"
                   placeholder="Contoh: Kepala Sekolah, Waka Kurikulum, Kaproli TKJ">
            @error('nama_jabatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label>Induk Jabatan</label>
            <select name="parent_id" class="form-control" id="selectParent">
              <option value="">-- Tidak ada (jabatan puncak / root) --</option>
              @foreach($parents as $p)
                <option value="{{ $p->id }}" {{ old('parent_id', $jabatan?->parent_id) == $p->id ? 'selected' : '' }}>
                  {{ $p->nama_jabatan }}
                </option>
              @endforeach
            </select>
            <small class="form-text text-muted">Kosongkan jika ini jabatan paling atas (mis. Kepala Sekolah).</small>
          </div>

          <div class="form-group">
            <label id="labelTipe">Tipe Garis Hubungan <span class="text-danger">*</span></label>
            <select name="tipe_hubungan" class="form-control">
              <option value="komando"    {{ old('tipe_hubungan', $jabatan?->tipe_hubungan ?? 'komando') == 'komando'    ? 'selected' : '' }}>
                Komando — garis solid (─────)
              </option>
              <option value="koordinasi" {{ old('tipe_hubungan', $jabatan?->tipe_hubungan) == 'koordinasi' ? 'selected' : '' }}>
                Koordinasi — garis putus-putus (- - - -)
              </option>
              <option value="konsultasi" {{ old('tipe_hubungan', $jabatan?->tipe_hubungan) == 'konsultasi' ? 'selected' : '' }}>
                Konsultasi — garis titik-titik (· · · ·)
              </option>
            </select>
            <small class="form-text text-muted" id="hintTipe">
              <strong>Komando</strong>: struktural langsung (vertikal ke induk). &nbsp;
              <strong>Koordinasi</strong>: setara/koordinatif — untuk root: garis putus ke node sejajar (mis. Komite ↔ Kepala Sekolah). &nbsp;
              <strong>Konsultasi</strong>: advisory/staf.
            </small>
          </div>

          <div class="form-group">
            <label>Urutan Tampil</label>
            <input type="number" name="urutan" class="form-control" min="0"
                   value="{{ old('urutan', $jabatan?->urutan ?? 0) }}" style="width:120px">
            <small class="form-text text-muted">Angka lebih kecil tampil lebih dulu dalam satu level yang sama.</small>
          </div>

          <div class="form-group">
            <label>Deskripsi / Tugas Pokok</label>
            <textarea name="deskripsi" rows="3" class="form-control"
                      placeholder="Deskripsi singkat tugas jabatan ini...">{{ old('deskripsi', $jabatan?->deskripsi) }}</textarea>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.organisasi.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const selectParent = document.getElementById('selectParent');
  const labelTipe    = document.getElementById('labelTipe');
  const hintTipe     = document.getElementById('hintTipe');

  function updateTipeLabel() {
    if (selectParent.value) {
      labelTipe.innerHTML = 'Tipe Garis ke Induk <span class="text-danger">*</span>';
      hintTipe.innerHTML  = '<strong>Komando</strong>: struktural langsung. &nbsp; <strong>Koordinasi</strong>: setara/koordinatif. &nbsp; <strong>Konsultasi</strong>: advisory/staf.';
    } else {
      labelTipe.innerHTML = 'Tipe Garis ke Node Sejajar <span class="text-danger">*</span>';
      hintTipe.innerHTML  = 'Untuk jabatan <strong>root</strong> (tanpa induk): pilih <strong>Komando</strong> jika node ini berdiri sendiri, atau <strong>Koordinasi</strong> jika terhubung garis putus ke node root lain (mis. Komite Sekolah ↔ Kepala Sekolah).';
    }
  }

  selectParent.addEventListener('change', updateTipeLabel);
  updateTipeLabel();
</script>
@endpush
