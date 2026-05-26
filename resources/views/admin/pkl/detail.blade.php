@extends('layouts.admin')
@section('title', 'Detail PKL — ' . $penempatan->siswa->nama)
@section('page_title', 'Detail PKL')

@section('content')
<div class="row">
  {{-- Info Penempatan --}}
  <div class="col-md-5">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">Informasi Penempatan</h3>
        <div class="card-tools">
          <a href="{{ route('admin.pkl-penempatan.edit', $penempatan) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i> Edit
          </a>
        </div>
      </div>
      <div class="card-body">
        <table class="table table-sm table-borderless">
          <tr><td style="width:130px"><strong>Siswa</strong></td><td>: {{ $penempatan->siswa->nama }}</td></tr>
          <tr><td><strong>NISN</strong></td><td>: {{ $penempatan->siswa->nisn }}</td></tr>
          <tr><td><strong>Kelas</strong></td><td>: {{ $penempatan->siswa->kelas?->nama_kelas ?? '-' }}</td></tr>
          <tr><td><strong>Tempat PKL</strong></td><td>: {{ $penempatan->tempatPkl->nama }}</td></tr>
          <tr><td><strong>Pembimbing</strong></td><td>: {{ $penempatan->guruPembimbing?->nama ?? '-' }}</td></tr>
          <tr><td><strong>Periode</strong></td><td>:
            {{ $penempatan->periode_mulai?->format('d/m/Y') ?? '-' }} –
            {{ $penempatan->periode_selesai?->format('d/m/Y') ?? '...' }}
          </td></tr>
          <tr><td><strong>Status</strong></td><td>:
            <span class="badge badge-{{ $penempatan->status === 'aktif' ? 'success' : 'secondary' }}">
              {{ ucfirst($penempatan->status) }}
            </span>
          </td></tr>
        </table>
      </div>
    </div>

    {{-- Nilai PKL --}}
    <div class="card card-success card-outline">
      <div class="card-header"><h3 class="card-title">Nilai PKL</h3></div>
      <div class="card-body">
        @if($penempatan->nilai)
          @php $n = $penempatan->nilai; @endphp
          <table class="table table-sm table-borderless">
            <tr><td>Nilai Pembimbing Sekolah</td><td class="text-right"><strong>{{ $n->nilai_pembimbing ?? '-' }}</strong></td></tr>
            <tr><td>Nilai DU/DI</td><td class="text-right"><strong>{{ $n->nilai_du_di ?? '-' }}</strong></td></tr>
            <tr><td>Nilai Sikap</td><td class="text-right"><strong>{{ $n->nilai_sikap ?? '-' }}</strong></td></tr>
            <tr class="font-weight-bold"><td>Nilai Akhir</td>
              <td class="text-right">
                <span class="badge badge-{{ $n->predikat_color }}">{{ $n->nilai_akhir ?? '-' }} ({{ $n->predikat }})</span>
              </td>
            </tr>
          </table>
          @if($n->catatan)<p class="text-muted small">{{ $n->catatan }}</p>@endif
        @else
          <p class="text-muted">Belum ada nilai PKL.</p>
        @endif
      </div>
    </div>
  </div>

  {{-- Jurnal & Absensi --}}
  <div class="col-md-7">
    {{-- Absensi PKL --}}
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
                  <small>{{ number_format($ab->latitude,4) }}, {{ number_format($ab->longitude,4) }}</small>
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

    {{-- Jurnal PKL --}}
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
              <td>{{ Str::limit($j->kegiatan, 80) }}</td>
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

<a href="{{ route('admin.pkl.index') }}" class="btn btn-secondary">
  <i class="fas fa-arrow-left"></i> Kembali
</a>
@endsection
