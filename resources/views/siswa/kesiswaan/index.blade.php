@extends('layouts.admin')
@section('title', 'Kesiswaan Saya')
@section('page_title', 'Kesiswaan Saya')

@push('styles')
<style>
  .kesiswaan-card { border-radius: 12px; overflow: hidden; }
  .stat-poin-badge {
    font-size: 2rem;
    font-weight: bold;
    padding: 10px 20px;
    border-radius: 50px;
  }
  .tingkat-badge {
    font-size: 0.75rem;
    padding: 4px 10px;
    border-radius: 12px;
  }
  .progress-bar-text { font-size: 0.85rem; }
  .foto-prestasi {
    width: 70px;
    height: 55px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s;
  }
  .foto-prestasi:hover { transform: scale(1.08); }
</style>
@endpush

@section('content')
<div class="row">
  {{-- INFO SISWA --}}
  <div class="col-12">
    <div class="callout callout-info">
      <h5 class="font-weight-bold">{{ $siswa->nama }}</h5>
      <p class="mb-0">
        <i class="fas fa-id-badge mr-1"></i> NISN: {{ $siswa->nisn }}
        &nbsp;&bull;&nbsp;
        <i class="fas fa-door-open mr-1"></i> Kelas: {{ $siswa->kelas?->nama_kelas ?? '-' }}
        &nbsp;&bull;&nbsp;
        <i class="fas fa-graduation-cap mr-1"></i> Jurusan: {{ $siswa->jurusan?->nama_jurusan ?? '-' }}
        @if($tahunAjaran)
          &nbsp;&bull;&nbsp;
          <i class="fas fa-calendar mr-1"></i> TA: {{ $tahunAjaran->nama }}
        @endif
      </p>
    </div>
  </div>
</div>

<div class="row">
  {{-- PELANGGARAN --}}
  <div class="col-md-12">
    <div class="card kesiswaan-card">
      <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">
          <i class="fas fa-exclamation-triangle mr-2"></i>Pelanggaran
        </h4>
        <div>
          <span class="text-white-50 mr-2">Total Poin:</span>
          <span class="stat-poin-badge bg-white {{ $totalPoin >= 100 ? 'text-danger' : ($totalPoin >= 50 ? 'text-warning' : 'text-success') }}">
            {{ $totalPoin }}
          </span>
        </div>
      </div>
      <div class="card-body p-0">
        @if($pelanggarans->isEmpty())
          <div class="py-4 text-center text-muted">
            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
            <p>Belum ada catatan pelanggaran. Pertahankan! 🎉</p>
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Tanggal</th>
                  <th>Jenis Pelanggaran</th>
                  <th>Deskripsi</th>
                  <th class="text-center">Poin</th>
                </tr>
              </thead>
              <tbody>
                @foreach($pelanggarans as $i => $p)
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                  <td><strong>{{ $p->jenis }}</strong></td>
                  <td>{{ $p->deskripsi ?: '—' }}</td>
                  <td class="text-center">
                    <span class="badge badge-{{ $p->poin >= 50 ? 'danger' : ($p->poin >= 25 ? 'warning' : 'info') }}">
                      {{ $p->poin }}
                    </span>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<div class="row mt-0">
  {{-- PRESTASI --}}
  <div class="col-md-6">
    <div class="card kesiswaan-card">
      <div class="card-header bg-warning d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">
          <i class="fas fa-trophy mr-2"></i>Prestasi
        </h4>
        <span class="badge badge-dark">{{ $prestasis->count() }} prestasi</span>
      </div>
      <div class="card-body p-0">
        @if($prestasis->isEmpty())
          <div class="py-4 text-center text-muted">
            <i class="fas fa-medal fa-2x mb-2"></i>
            <p>Belum ada catatan prestasi.</p>
          </div>
        @else
          <div class="list-group list-group-flush">
            @foreach($prestasis as $p)
            <div class="list-group-item">
              <div class="d-flex align-items-center">
                @if($p->foto_url)
                  <img src="{{ $p->foto_url }}" class="foto-prestasi mr-3"
                       onclick="window.open(this.src, '_blank')"
                       alt="Bukti {{ $p->nama_prestasi }}">
                @else
                  <div class="mr-3 text-muted" style="width:70px;text-align:center;">
                    <i class="fas fa-image fa-2x"></i>
                  </div>
                @endif
                <div class="flex-fill">
                  <div class="font-weight-bold">{{ $p->nama_prestasi }}</div>
                  <div class="text-muted" style="font-size:0.85rem;">
                    {{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}
                  </div>
                  @php
                    $badgeColor = match($p->tingkat) {
                      'Internasional' => 'danger', 'Nasional' => 'primary', 'Provinsi' => 'info',
                      'Kabupaten/Kota' => 'success', 'Kecamatan' => 'warning', default => 'secondary',
                    };
                  @endphp
                  <span class="badge badge-{{ $badgeColor }} tingkat-badge mt-1">{{ $p->tingkat }}</span>
                </div>
              </div>
              @if($p->keterangan)
                <small class="text-muted d-block mt-1">{{ $p->keterangan }}</small>
              @endif
            </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- EKSKUL --}}
  <div class="col-md-6">
    <div class="card kesiswaan-card">
      <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">
          <i class="fas fa-users mr-2"></i>Ekskul
        </h4>
        <span class="badge badge-light text-dark">{{ $ekskulStats->count() }} ekskul</span>
      </div>
      <div class="card-body p-0">
        @if($ekskulStats->isEmpty())
          <div class="py-4 text-center text-muted">
            <i class="fas fa-user-friends fa-2x mb-2"></i>
            <p>Belum terdaftar di ekskul manapun pada tahun ajaran ini.</p>
          </div>
        @else
          <div class="list-group list-group-flush">
            @foreach($ekskulStats as $es)
            <div class="list-group-item">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <div>
                  <strong>{{ $es['ekskul']->nama }}</strong>
                  @if($es['ekskul']->pembina)
                    <small class="text-muted d-block">Pembina: {{ $es['ekskul']->pembina->nama }}</small>
                  @endif
                </div>
                <span class="badge badge-{{ $es['persen_hadir'] >= 75 ? 'success' : ($es['persen_hadir'] >= 50 ? 'warning' : 'danger') }} ml-2">
                  {{ $es['persen_hadir'] }}%
                </span>
              </div>
              <div class="progress" style="height:8px;">
                <div class="progress-bar bg-{{ $es['persen_hadir'] >= 75 ? 'success' : ($es['persen_hadir'] >= 50 ? 'warning' : 'danger') }}"
                     style="width: {{ $es['persen_hadir'] }}%"></div>
              </div>
              <small class="text-muted progress-bar-text">
                Hadir {{ $es['jumlah_hadir'] }} dari {{ $es['total_pertemuan'] }} pertemuan
              </small>
            </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
