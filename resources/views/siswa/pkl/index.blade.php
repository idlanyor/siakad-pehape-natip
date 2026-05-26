@extends('layouts.admin')
@section('title', 'PKL Saya')
@section('page_title', 'PKL Saya')

@section('content')
@if(!$penempatan)
  <div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    Kamu belum memiliki penempatan PKL aktif. Hubungi admin/wali kelas.
  </div>
@else
{{-- Info Penempatan --}}
<div class="row">
  <div class="col-md-6">
    <div class="card card-primary card-outline">
      <div class="card-header"><h3 class="card-title">Penempatan PKL Kamu</h3></div>
      <div class="card-body">
        <table class="table table-sm table-borderless">
          <tr><td style="width:130px"><strong>Tempat PKL</strong></td><td>: <strong>{{ $penempatan->tempatPkl->nama }}</strong></td></tr>
          <tr><td><strong>Bidang Usaha</strong></td><td>: {{ $penempatan->tempatPkl->bidang_usaha ?? '-' }}</td></tr>
          <tr><td><strong>Alamat</strong></td><td>: {{ $penempatan->tempatPkl->alamat ?? '-' }}</td></tr>
          <tr><td><strong>Pembimbing</strong></td><td>: {{ $penempatan->guruPembimbing?->nama ?? '-' }}</td></tr>
          <tr><td><strong>Periode</strong></td><td>:
            {{ $penempatan->periode_mulai?->format('d M Y') ?? '-' }} –
            {{ $penempatan->periode_selesai?->format('d M Y') ?? '...' }}
          </td></tr>
          <tr><td><strong>Status</strong></td><td>:
            <span class="badge badge-{{ $penempatan->status === 'aktif' ? 'success' : 'secondary' }}">
              {{ ucfirst($penempatan->status) }}
            </span>
          </td></tr>
        </table>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    {{-- Quick Stats --}}
    <div class="row">
      <div class="col-6">
        <div class="info-box">
          <span class="info-box-icon bg-warning"><i class="fas fa-book-open"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Jurnal</span>
            <span class="info-box-number">{{ $stats['total_jurnal'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="info-box">
          <span class="info-box-icon bg-info"><i class="fas fa-calendar-check"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Hari Absensi</span>
            <span class="info-box-number">{{ $stats['total_absensi'] }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Nilai PKL --}}
    @if($stats['nilai'])
    @php $n = $stats['nilai']; @endphp
    <div class="card card-success card-outline">
      <div class="card-header"><h3 class="card-title">Nilai PKL Kamu</h3></div>
      <div class="card-body">
        <div class="row text-center">
          <div class="col-4">
            <div class="h4">{{ $n->nilai_pembimbing ?? '-' }}</div>
            <small class="text-muted">Pembimbing</small>
          </div>
          <div class="col-4">
            <div class="h4">{{ $n->nilai_du_di ?? '-' }}</div>
            <small class="text-muted">DU/DI</small>
          </div>
          <div class="col-4">
            <div class="h4">{{ $n->nilai_sikap ?? '-' }}</div>
            <small class="text-muted">Sikap</small>
          </div>
        </div>
        <hr class="my-2">
        <div class="text-center">
          <span class="badge badge-{{ $n->predikat_color }}" style="font-size:1.2rem;padding:.4rem .8rem">
            {{ $n->nilai_akhir ?? '-' }} — {{ $n->predikat }}
          </span>
        </div>
      </div>
    </div>
    @endif
  </div>
</div>

{{-- Action Buttons --}}
<div class="row">
  <div class="col-md-6">
    <a href="{{ route('siswa.pkl.jurnal') }}" class="btn btn-warning btn-lg btn-block mb-2">
      <i class="fas fa-book-open"></i> Kelola Jurnal Harian
    </a>
  </div>
  <div class="col-md-6">
    <a href="{{ route('siswa.pkl.absensi') }}" class="btn btn-info btn-lg btn-block mb-2">
      <i class="fas fa-camera"></i> Absensi PKL
    </a>
  </div>
</div>
@endif
@endsection
