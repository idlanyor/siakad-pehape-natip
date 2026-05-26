@extends('layouts.admin')
@section('title', 'Generate Tagihan SPP')
@section('page_title', 'Generate Tagihan SPP')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-9">
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-magic mr-2"></i>Generate Tagihan SPP Batch</h3>
      </div>
      <form action="{{ route('admin.keuangan.tagihan.store') }}" method="POST" id="formGenerate">
        @csrf
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
          @endif
          @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif

          <div class="callout callout-info">
            <h5><i class="fas fa-info-circle"></i> Cara Kerja</h5>
            <p class="mb-0">
              Pilih satu/beberapa kelas dan rentang bulan. Sistem akan membuat tagihan SPP untuk <strong>semua siswa</strong>
              di kelas terpilih untuk setiap bulan dalam rentang. Tagihan yang sudah ada akan <strong>dilewati</strong> (tidak duplikat).
              Nominal otomatis dari tarif kelas (bisa di-override manual).
            </p>
          </div>

          {{-- Mode pilihan --}}
          <div class="form-group">
            <label>Pilih Kelas <span class="text-danger">*</span></label>
            <div class="mb-2">
              <div class="custom-control custom-checkbox d-inline mr-3">
                <input type="checkbox" class="custom-control-input" id="checkAll">
                <label class="custom-control-label font-weight-bold" for="checkAll">Semua Kelas</label>
              </div>
            </div>
            <div class="row">
              @foreach($kelas as $k)
              <div class="col-md-4 col-6">
                <div class="custom-control custom-checkbox mb-2">
                  <input type="checkbox" class="custom-control-input kelas-check"
                         name="kelas_ids[]" value="{{ $k->id }}" id="kelas-{{ $k->id }}"
                         data-siswa="{{ $k->siswas->count() }}"
                         data-tarif="{{ $tarifAktif->get($k->id)?->nominal ?? '' }}"
                         {{ is_array(old('kelas_ids')) && in_array($k->id, old('kelas_ids')) ? 'checked' : '' }}>
                  <label class="custom-control-label" for="kelas-{{ $k->id }}">
                    {{ $k->nama_kelas }}
                    <small class="text-muted d-block">{{ $k->siswas->count() }} siswa · {{ $tarifAktif->get($k->id) ? 'Rp ' . number_format($tarifAktif->get($k->id)->nominal, 0, ',', '.') : 'tanpa tarif' }}</small>
                  </label>
                </div>
              </div>
              @endforeach
            </div>
            @error('kelas_ids') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          <hr>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Bulan Mulai <span class="text-danger">*</span></label>
                <input type="month" name="bulan_mulai" id="bulan_mulai" class="form-control @error('bulan_mulai') is-invalid @enderror"
                       value="{{ old('bulan_mulai', now()->format('Y-m')) }}" required>
                @error('bulan_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Bulan Selesai <span class="text-danger">*</span></label>
                <input type="month" name="bulan_selesai" id="bulan_selesai" class="form-control @error('bulan_selesai') is-invalid @enderror"
                       value="{{ old('bulan_selesai', now()->format('Y-m')) }}" required>
                @error('bulan_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="text-muted">Samakan dengan bulan mulai untuk 1 bulan saja.</small>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Override Nominal <small class="text-muted">(opsional)</small></label>
                <div class="input-group">
                  <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                  <input type="number" name="nominal_override" id="nominal_override"
                         class="form-control @error('nominal_override') is-invalid @enderror"
                         value="{{ old('nominal_override') }}"
                         placeholder="Kosong = pakai tarif"
                         min="1000" step="1000">
                  @error('nominal_override') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <small class="text-muted">Kosongkan untuk otomatis pakai tarif masing-masing kelas.</small>
              </div>
            </div>
          </div>

          {{-- Preview --}}
          <div id="preview-box" class="callout callout-success d-none">
            <h5><i class="fas fa-eye"></i> Preview</h5>
            <p class="mb-0">
              <strong id="prev-kelas">0</strong> kelas terpilih ·
              <strong id="prev-siswa">0</strong> siswa ·
              <strong id="prev-bulan">0</strong> bulan ·
              Estimasi <strong id="prev-total">0</strong> tagihan baru
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
  const checkAll    = document.getElementById('checkAll');
  const kelasChecks = document.querySelectorAll('.kelas-check');

  checkAll.addEventListener('change', function() {
    kelasChecks.forEach(c => c.checked = this.checked);
    updatePreview();
  });
  kelasChecks.forEach(c => c.addEventListener('change', updatePreview));
  document.getElementById('bulan_mulai').addEventListener('change', updatePreview);
  document.getElementById('bulan_selesai').addEventListener('change', updatePreview);
  document.getElementById('nominal_override').addEventListener('input', updatePreview);

  function updatePreview() {
    const checked = document.querySelectorAll('.kelas-check:checked');
    const jumlahKelas = checked.length;
    let jumlahSiswa = 0;
    checked.forEach(c => jumlahSiswa += parseInt(c.dataset.siswa || 0));

    const mulai = document.getElementById('bulan_mulai').value;
    const selesai = document.getElementById('bulan_selesai').value;
    let jumlahBulan = 0;
    if (mulai && selesai && selesai >= mulai) {
      const [y1,m1] = mulai.split('-').map(Number);
      const [y2,m2] = selesai.split('-').map(Number);
      jumlahBulan = (y2 - y1) * 12 + (m2 - m1) + 1;
    }

    const box = document.getElementById('preview-box');
    if (jumlahKelas > 0 && jumlahBulan > 0) {
      box.classList.remove('d-none');
      document.getElementById('prev-kelas').textContent = jumlahKelas;
      document.getElementById('prev-siswa').textContent = jumlahSiswa;
      document.getElementById('prev-bulan').textContent = jumlahBulan;
      document.getElementById('prev-total').textContent = (jumlahSiswa * jumlahBulan).toLocaleString('id-ID');
    } else {
      box.classList.add('d-none');
    }
  }
</script>
@endpush
