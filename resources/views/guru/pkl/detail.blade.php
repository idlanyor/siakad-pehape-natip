@extends('layouts.admin')
@section('title', 'Detail PKL Siswa')
@section('page_title', 'Detail PKL — ' . $penempatan->siswa->nama)

@section('content')
<div class="row">
  <div class="col-md-4">
    <div class="card card-primary card-outline">
      <div class="card-header"><h3 class="card-title">Info Siswa</h3></div>
      <div class="card-body">
        <table class="table table-sm table-borderless">
          <tr><td><strong>Nama</strong></td><td>: {{ $penempatan->siswa->nama }}</td></tr>
          <tr><td><strong>NISN</strong></td><td>: {{ $penempatan->siswa->nisn }}</td></tr>
          <tr><td><strong>Kelas</strong></td><td>: {{ $penempatan->siswa->kelas?->nama_kelas ?? '-' }}</td></tr>
          <tr><td><strong>Tempat PKL</strong></td><td>: {{ $penempatan->tempatPkl->nama }}</td></tr>
          <tr><td><strong>Periode</strong></td><td>:
            {{ $penempatan->periode_mulai?->format('d/m/Y') ?? '-' }} –
            {{ $penempatan->periode_selesai?->format('d/m/Y') ?? '...' }}
          </td></tr>
        </table>
        <a href="{{ route('guru.pkl.nilai', $penempatan) }}" class="btn btn-primary btn-block">
          <i class="fas fa-star"></i> Input Nilai PKL
        </a>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    {{-- Absensi --}}
    <div class="card card-info card-outline">
      <div class="card-header"><h3 class="card-title">Absensi PKL ({{ $penempatan->absensis->count() }} hari)</h3></div>
      <div class="card-body p-0 table-responsive" style="max-height:250px;overflow-y:auto">
        <table class="table table-sm table-bordered mb-0">
          <thead class="thead-light">
            <tr><th>Tanggal</th><th>Masuk</th><th>Pulang</th><th>Lokasi</th><th>Foto</th></tr>
          </thead>
          <tbody>
            @forelse($penempatan->absensis->sortByDesc('tanggal') as $ab)
            <tr>
              <td>{{ $ab->tanggal->format('d/m/Y') }}</td>
              <td>{{ $ab->jam_masuk ?? '-' }}</td>
              <td>{{ $ab->jam_pulang ?? '-' }}</td>
              <td>
                @if($ab->latitude)
                  <a href="https://maps.google.com/?q={{ $ab->latitude }},{{ $ab->longitude }}" target="_blank">
                    <small><i class="fas fa-map-marker-alt"></i> Lihat</small>
                  </a>
                @else - @endif
              </td>
              <td>
                @if($ab->foto_masuk)
                  <a href="{{ asset('storage/' . $ab->foto_masuk) }}" target="_blank">
                    <img src="{{ asset('storage/' . $ab->foto_masuk) }}" width="40" class="rounded">
                  </a>
                @else - @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Belum ada absensi</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Jurnal --}}
    <div class="card card-warning card-outline">
      <div class="card-header"><h3 class="card-title">Jurnal Kegiatan ({{ $penempatan->jurnals->count() }} entri)</h3></div>
      <div class="card-body p-0 table-responsive" style="max-height:300px;overflow-y:auto">
        <table class="table table-sm table-bordered mb-0">
          <thead class="thead-light">
            <tr><th>Tanggal</th><th>Kegiatan</th><th>Foto</th></tr>
          </thead>
          <tbody>
            @forelse($penempatan->jurnals->sortByDesc('tanggal') as $j)
            <tr>
              <td class="text-nowrap">{{ $j->tanggal->format('d/m/Y') }}</td>
              <td>{{ $j->kegiatan }}</td>
              <td>
                @if($j->foto)
                  <a href="{{ asset('storage/' . $j->foto) }}" target="_blank">
                    <img src="{{ asset('storage/' . $j->foto) }}" width="40" class="rounded">
                  </a>
                @else - @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center text-muted">Belum ada jurnal</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<a href="{{ route('guru.pkl.index') }}" class="btn btn-secondary">
  <i class="fas fa-arrow-left"></i> Kembali
</a>
@endsection
