@extends('layouts.admin')
@section('title', 'Generate Tagihan SPP')
@section('page_title', 'Generate Tagihan SPP')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-magic mr-2"></i>Generate Tagihan SPP (Batch per Kelas)</h3>
      </div>
      <form action="{{ route('admin.keuangan.tagihan.store') }}" method="POST">
        @csrf
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
          @endif

          <div class="callout callout-info">
            <h5><i class="fas fa-info-circle"></i> Cara Kerja</h5>
            <p class="mb-0">
              Sistem akan membuat tagihan SPP untuk <strong>semua siswa</strong> di kelas yang dipilih pada bulan tersebut.
              Jika tagihan untuk siswa tertentu sudah ada, akan <strong>dilewati</strong> (tidak duplikat).
            </p>
          </div>

          <div class="row">
            <div class="col-md-7">
              <div class="form-group">
                <label>Kelas <span class="text-danger">*</span></label>
                <select name="kelas_id" id="kelas_id" class="form-control @error('kelas_id') is-invalid @enderror" required>
                  <option value="">-- Pilih Kelas --</option>
                  @foreach($kelas as $k)
                    <option value="{{ $k->id }}"
                            data-siswa="{{ $k->siswas->count() }}"
                            data-tarif="{{ $tarifAktif->get($k->id)?->nominal ?? '' }}"
                            {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                      {{ $k->nama_kelas }} — {{ $k->jurusan?->nama_jurusan ?? '-' }}
                      ({{ $k->siswas->count() }} siswa)
                    </option>
                  @endforeach
                </select>
                @error('kelas_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-5">
              <div class="form-group">
                <label>Bulan <span class="text-danger">*</span></label>
                <input type="month" name="bulan" id="bulan" class="form-control @error('bulan') is-invalid @enderror"
                       value="{{ old('bulan', now()->format('Y-m')) }}" required>
                @error('bulan') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Nominal SPP (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
              <input type="number" name="nominal" id="nominal"
                     class="form-control @error('nominal') is-invalid @enderror"
                     value="{{ old('nominal') }}"
                     placeholder="Otomatis dari tarif aktif kelas ini"
                     min="1000" step="1000" required>
              @error('nominal') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <small id="tarif-info" class="text-muted"></small>
          </div>

          {{-- Preview --}}
          <div id="preview-box" class="callout callout-success d-none">
            <h5><i class="fas fa-eye"></i> Preview Generate</h5>
            <p class="mb-0">
              Akan membuat tagihan untuk <strong id="prev-siswa">0</strong> siswa,
              masing-masing <strong id="prev-nominal">Rp 0</strong>/bulan.
              Total potensi: <strong id="prev-total">Rp 0</strong>
            </p>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.keuangan.tagihan.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary" id="btn-generate">
            <i class="fas fa-magic mr-1"></i> Generate Tagihan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function formatRp(val) {
  return 'Rp ' + parseInt(val || 0).toLocaleString('id-ID');
}
function updatePreview() {
  const kelasSelect = document.getElementById('kelas_id');
  const opt = kelasSelect.options[kelasSelect.selectedIndex];
  const nominal = parseFloat(document.getElementById('nominal').value) || 0;
  const jumlahSiswa = parseInt(opt.dataset.siswa || 0);
  const tarifAktif = opt.dataset.tarif;

  if (kelasSelect.value && nominal > 0) {
    document.getElementById('preview-box').classList.remove('d-none');
    document.getElementById('prev-siswa').textContent = jumlahSiswa;
    document.getElementById('prev-nominal').textContent = formatRp(nominal);
    document.getElementById('prev-total').textContent = formatRp(nominal * jumlahSiswa);
  } else {
    document.getElementById('preview-box').classList.add('d-none');
  }

  // Auto-fill nominal dari tarif
  if (tarifAktif) {
    document.getElementById('tarif-info').textContent = '💡 Tarif aktif kelas ini: ' + formatRp(tarifAktif) + ' (terisi otomatis)';
    if (!document.getElementById('nominal').value) {
      document.getElementById('nominal').value = tarifAktif;
    }
  } else {
    document.getElementById('tarif-info').textContent = '⚠️ Belum ada tarif SPP untuk kelas ini di tahun ajaran aktif. Isi nominal manual.';
  }
}

document.getElementById('kelas_id').addEventListener('change', function() {
  const opt = this.options[this.selectedIndex];
  const tarif = opt.dataset.tarif;
  document.getElementById('nominal').value = tarif || '';
  updatePreview();
});
document.getElementById('nominal').addEventListener('input', updatePreview);
document.getElementById('bulan').addEventListener('change', updatePreview);
</script>
@endpush
