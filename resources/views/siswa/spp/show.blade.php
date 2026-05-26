@extends('layouts.admin')
@section('title', 'Detail Tagihan SPP')
@section('page_title', 'Detail Tagihan SPP')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">

    {{-- Info Tagihan --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-file-invoice-dollar mr-2"></i>Info Tagihan
        </h3>
        <div class="card-tools">
          <span class="badge badge-{{ $tagihan->status_color }} badge-lg px-3 py-2" style="font-size:.9rem">
            {{ $tagihan->status_label }}
          </span>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr><th>Nama</th><td>{{ $siswa->nama }}</td></tr>
              <tr><th>Kelas</th><td>{{ $siswa->kelas?->nama_kelas ?? '-' }}</td></tr>
              <tr><th>Bulan</th><td class="font-weight-bold">{{ $tagihan->bulan_label }}</td></tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr><th>Nominal</th><td>{{ $tagihan->nominal_rp }}</td></tr>
              <tr><th>Sudah Dibayar</th>
                <td class="text-success font-weight-bold">
                  Rp {{ number_format($tagihan->total_dibayar, 0, ',', '.') }}
                </td>
              </tr>
              <tr><th>Sisa</th>
                <td class="{{ $tagihan->sisa_tagihan > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                  Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                </td>
              </tr>
            </table>
          </div>
        </div>

        @if($tagihan->sisa_tagihan > 0)
          <div class="alert alert-warning mb-0">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Masih ada tunggakan sebesar <strong>Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}</strong>.
            Segera lakukan pembayaran ke bagian TU.
          </div>
        @else
          <div class="alert alert-success mb-0">
            <i class="fas fa-check-circle mr-1"></i>
            Tagihan bulan ini sudah <strong>LUNAS</strong>. Terima kasih!
          </div>
        @endif
      </div>
      <div class="card-footer">
        <a href="{{ route('siswa.spp') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
      </div>
    </div>

    {{-- Riwayat Pembayaran --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-history mr-2"></i>Riwayat Pembayaran
        </h3>
      </div>
      <div class="card-body p-0">
        @if($tagihan->pembayarans->isEmpty())
          <div class="text-center py-4 text-muted">
            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Belum ada riwayat pembayaran.
          </div>
        @else
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>Tanggal</th>
                <th class="text-right">Nominal</th>
                <th>Metode</th>
                <th>Bukti</th>
                <th>Catatan</th>
              </tr>
            </thead>
            <tbody>
              @foreach($tagihan->pembayarans->sortBy('tanggal_bayar') as $p)
              <tr>
                <td>{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
                <td class="text-right font-weight-bold text-success">{{ $p->nominal_rp }}</td>
                <td><span class="badge badge-secondary">{{ $p->metode_label }}</span></td>
                <td>
                  @if($p->bukti_url)
                    <a href="{{ $p->bukti_url }}" target="_blank" class="btn btn-xs btn-outline-info">
                      <i class="fas fa-image mr-1"></i>Lihat
                    </a>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td class="text-muted small">{{ $p->catatan ?? '—' }}</td>
              </tr>
              @endforeach
            </tbody>
            <tfoot class="thead-light">
              <tr>
                <th>Total Dibayar</th>
                <th class="text-right text-success">Rp {{ number_format($tagihan->total_dibayar, 0, ',', '.') }}</th>
                <th colspan="3"></th>
              </tr>
            </tfoot>
          </table>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection
