@extends('layouts.admin')
@section('title', 'Input Absensi')
@section('page_title', 'Input Absensi — ' . $jadwal->mapel->nama_mapel)

@section('content')
<div class="row justify-content-center">
  <div class="col-md-10">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h3 class="card-title mb-0">
            {{ $jadwal->mapel->nama_mapel }} —
            <span class="badge badge-secondary">{{ $jadwal->kelas?->nama_kelas }}</span>
          </h3>
          <small class="text-muted">{{ $jadwal->hari }}, {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</small>
        </div>
        <a href="{{ route('guru.absensi.index') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>

      <form action="{{ route('guru.absensi.simpan', $jadwal) }}" method="POST">
        @csrf
        <div class="card-body">

          {{-- Pilih tanggal --}}
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="font-weight-bold">Tanggal Pertemuan</label>
              <div class="input-group">
                <input type="date" name="tanggal" id="tanggal" class="form-control"
                       value="{{ $tanggal }}" max="{{ today()->toDateString() }}">
                <div class="input-group-append">
                  <button type="button" class="btn btn-outline-secondary" onclick="gantiTanggal()">
                    <i class="fas fa-sync"></i> Ganti
                  </button>
                </div>
              </div>
              @if($sudahDiisi)
              <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Absensi tanggal ini sudah diisi — akan di-update.</small>
              @endif
            </div>
          </div>

          {{-- Tabel absensi --}}
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead class="thead-light">
                <tr>
                  <th width="40">#</th>
                  <th>Nama Siswa</th>
                  <th>NISN</th>
                  <th width="300">
                    Status
                    <small class="d-block text-muted font-weight-normal">
                      <button type="button" class="btn btn-xs btn-success mr-1" onclick="setAll('Hadir')">Semua Hadir</button>
                      <button type="button" class="btn btn-xs btn-danger" onclick="setAll('Alpa')">Semua Alpa</button>
                    </small>
                  </th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                @foreach($siswas as $i => $s)
                @php $currentStatus = $existing[$s->id] ?? 'Hadir'; @endphp
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td class="font-weight-bold">{{ $s->nama }}</td>
                  <td><small>{{ $s->nisn }}</small></td>
                  <td>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                      @foreach(['Hadir' => 'success', 'Izin' => 'warning', 'Sakit' => 'info', 'Alpa' => 'danger'] as $st => $color)
                      <label class="btn btn-outline-{{ $color }} btn-sm {{ $currentStatus === $st ? 'active' : '' }}">
                        <input type="radio" name="status[{{ $s->id }}]" value="{{ $st }}"
                               {{ $currentStatus === $st ? 'checked' : '' }}> {{ $st }}
                      </label>
                      @endforeach
                    </div>
                  </td>
                  <td>
                    <input type="text" name="keterangan[{{ $s->id }}]" class="form-control form-control-sm"
                           placeholder="Opsional..." value="{{ old("keterangan.{$s->id}") }}">
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('guru.absensi.rekap', $jadwal) }}" class="btn btn-info">
            <i class="fas fa-table"></i> Lihat Rekap
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan Absensi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function gantiTanggal() {
    const tgl = document.getElementById('tanggal').value;
    if (tgl) window.location.href = '{{ route('guru.absensi.form', $jadwal) }}?tanggal=' + tgl;
  }
  function setAll(status) {
    document.querySelectorAll('input[type=radio][value="' + status + '"]').forEach(r => {
      r.checked = true;
      r.closest('label').classList.add('active');
      r.closest('.btn-group').querySelectorAll('label').forEach(l => {
        if (l !== r.closest('label')) l.classList.remove('active');
      });
    });
  }
</script>
@endpush
