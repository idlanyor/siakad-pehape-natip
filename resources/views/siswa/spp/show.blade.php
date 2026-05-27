@extends('layouts.admin')
@section('title', 'Detail Tagihan SPP')
@section('page_title', 'Detail Tagihan SPP')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">

    @if(session('success'))
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
      </div>
    @endif

    {{-- Info Tagihan --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-2"></i>Info Tagihan</h3>
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
                <td class="text-success font-weight-bold">Rp {{ number_format($tagihan->total_dibayar, 0, ',', '.') }}</td>
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
          {{-- Cek apakah ada pending yang belum dikonfirmasi --}}
          @php $adaPending = $tagihan->pembayarans->where('status_konfirmasi','pending')->count() > 0; @endphp
          @if($adaPending)
            <div class="alert alert-info mb-0">
              <i class="fas fa-clock mr-1"></i>
              Bukti transfer sudah dikirim dan sedang menunggu konfirmasi dari petugas TU.
            </div>
          @else
            <div class="alert alert-warning mb-0">
              <i class="fas fa-exclamation-triangle mr-1"></i>
              Masih ada tunggakan <strong>Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}</strong>.
              Bayar via transfer/QRIS lalu upload bukti di bawah, atau langsung ke TU.
            </div>
          @endif
        @else
          <div class="alert alert-success mb-0">
            <i class="fas fa-check-circle mr-1"></i>
            Tagihan bulan ini sudah <strong>LUNAS</strong>. Terima kasih!
          </div>
        @endif
      </div>
      <div class="card-footer">
        <a href="{{ route('siswa.spp') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
      </div>
    </div>

    {{-- Form Upload Bukti (hanya jika belum lunas & belum ada pending) --}}
    @if($tagihan->sisa_tagihan > 0 && $tagihan->pembayarans->where('status_konfirmasi','pending')->count() === 0)
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-upload mr-2 text-primary"></i>Upload Bukti Transfer / QRIS</h3>
      </div>
      <form action="{{ route('siswa.spp.upload', $tagihan) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
          @endif

          <div class="callout callout-info">
            <p class="mb-0 small">
              <i class="fas fa-info-circle mr-1"></i>
              Upload foto/screenshot bukti transfer atau QRIS. Pembayaran akan dikonfirmasi oleh petugas TU.
              Sisa tagihan yang harus dibayar: <strong>Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}</strong>
            </p>
          </div>

          <div class="row">
            <div class="col-md-5">
              <div class="form-group">
                <label>Nominal Dibayar <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                  <input type="number" name="nominal_bayar" class="form-control @error('nominal_bayar') is-invalid @enderror"
                         value="{{ old('nominal_bayar', $tagihan->sisa_tagihan) }}"
                         min="1000" max="{{ $tagihan->sisa_tagihan }}" step="1000">
                  @error('nominal_bayar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Metode <span class="text-danger">*</span></label>
                <select name="metode" class="form-control @error('metode') is-invalid @enderror">
                  <option value="transfer" {{ old('metode') === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                  <option value="qris" {{ old('metode') === 'qris' ? 'selected' : '' }}>QRIS</option>
                </select>
                @error('metode') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Bukti Pembayaran <span class="text-danger">*</span> <small class="text-muted">(foto/screenshot, maks 2MB)</small></label>
            <input type="file" name="bukti" class="form-control-file @error('bukti') is-invalid @enderror"
                   accept="image/*" required>
            @error('bukti') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group mb-0">
            <label>Catatan <small class="text-muted">(opsional)</small></label>
            <input type="text" name="catatan" class="form-control"
                   value="{{ old('catatan') }}" placeholder="Contoh: Transfer BCA a.n. Budi Santoso">
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane mr-1"></i> Kirim Bukti Pembayaran
          </button>
        </div>
      </form>
    </div>
    @endif

    {{-- Riwayat Pembayaran --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history mr-2"></i>Riwayat Pembayaran</h3>
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
                <th>Status</th>
                <th>Bukti</th>
                <th>Catatan</th>
              </tr>
            </thead>
            <tbody>
              @foreach($tagihan->pembayarans->sortBy('tanggal_bayar') as $p)
              <tr class="{{ $p->isPending() ? 'table-warning' : '' }}">
                <td>{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
                <td class="text-right font-weight-bold text-success">{{ $p->nominal_rp }}</td>
                <td><span class="badge badge-secondary">{{ $p->metode_label }}</span></td>
                <td>
                  @if($p->isPending())
                    <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Menunggu</span>
                  @else
                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Dikonfirmasi</span>
                  @endif
                </td>
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
                <th>Total Dikonfirmasi</th>
                <th class="text-right text-success">Rp {{ number_format($tagihan->total_dibayar, 0, ',', '.') }}</th>
                <th colspan="4"></th>
              </tr>
            </tfoot>
          </table>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection
