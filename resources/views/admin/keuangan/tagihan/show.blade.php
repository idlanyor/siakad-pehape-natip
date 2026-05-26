@extends('layouts.admin')
@section('title', 'Detail Tagihan SPP')
@section('page_title', 'Detail Tagihan SPP')

@section('content')
<div class="row">

  {{-- ── Kolom kiri: Info tagihan + form pembayaran ── --}}
  <div class="col-md-5">

    {{-- Info Tagihan --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-2"></i>Info Tagihan</h3>
        <div class="card-tools">
          <span class="badge badge-{{ $tagihan->status_color }} badge-lg px-3 py-2">
            {{ $tagihan->status_label }}
          </span>
        </div>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
          <tr><th class="pl-3 w-40" style="width:40%">Siswa</th><td>{{ $tagihan->siswa->nama }}</td></tr>
          <tr><th class="pl-3">Kelas</th><td>{{ $tagihan->siswa->kelas?->nama_kelas ?? '-' }}</td></tr>
          <tr><th class="pl-3">Jurusan</th><td>{{ $tagihan->siswa->jurusan?->nama ?? '-' }}</td></tr>
          <tr><th class="pl-3">Bulan</th><td>{{ $tagihan->bulan_label }}</td></tr>
          <tr><th class="pl-3">Nominal</th><td>{{ $tagihan->nominal_rp }}</td></tr>
          <tr><th class="pl-3">Total Dibayar</th>
            <td class="text-success font-weight-bold">Rp {{ number_format($tagihan->total_dibayar, 0, ',', '.') }}</td></tr>
          <tr><th class="pl-3">Sisa Tagihan</th>
            <td class="{{ $tagihan->sisa_tagihan > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
              Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
            </td>
          </tr>
        </table>
      </div>
      <div class="card-footer">
        <a href="{{ route('admin.keuangan.tagihan.index') }}" class="btn btn-sm btn-secondary">
          <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        @if($tagihan->pembayarans->isEmpty())
          <form action="{{ route('admin.keuangan.tagihan.destroy', $tagihan) }}" method="POST" class="d-inline">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger"
                    onclick="return confirm('Hapus tagihan ini?')">
              <i class="fas fa-trash mr-1"></i> Hapus Tagihan
            </button>
          </form>
        @endif
      </div>
    </div>

    {{-- Form Input Pembayaran --}}
    @if($tagihan->status !== 'lunas')
    <div class="card card-success">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>Catat Pembayaran</h3>
      </div>
      <form action="{{ route('admin.keuangan.tagihan.bayar', $tagihan) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger py-2">
              <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
          @endif

          <div class="row">
            <div class="col-6">
              <div class="form-group">
                <label>Tanggal Bayar <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_bayar"
                       class="form-control @error('tanggal_bayar') is-invalid @enderror"
                       value="{{ old('tanggal_bayar', today()->toDateString()) }}">
                @error('tanggal_bayar') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label>Nominal <span class="text-danger">*</span></label>
                <input type="number" name="nominal_bayar"
                       class="form-control @error('nominal_bayar') is-invalid @enderror"
                       value="{{ old('nominal_bayar', $tagihan->sisa_tagihan) }}"
                       min="1000" step="1000"
                       placeholder="Rp">
                @error('nominal_bayar') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Metode Pembayaran <span class="text-danger">*</span></label>
            <select name="metode" class="form-control @error('metode') is-invalid @enderror">
              @foreach($metodeList as $key => $label)
                <option value="{{ $key }}" {{ old('metode') == $key ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            @error('metode') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label>Bukti Pembayaran <small class="text-muted">(opsional, maks 2MB)</small></label>
            <input type="file" name="bukti" class="form-control-file @error('bukti') is-invalid @enderror"
                   accept="image/*">
            @error('bukti') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group mb-0">
            <label>Catatan</label>
            <input type="text" name="catatan" class="form-control"
                   value="{{ old('catatan') }}" placeholder="Opsional">
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-success btn-block">
            <i class="fas fa-save mr-1"></i> Simpan Pembayaran
          </button>
        </div>
      </form>
    </div>
    @else
    <div class="alert alert-success">
      <i class="fas fa-check-circle mr-1"></i> Tagihan ini sudah <strong>LUNAS</strong>.
    </div>
    @endif

  </div>

  {{-- ── Kolom kanan: Riwayat pembayaran ── --}}
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-history mr-2"></i>Riwayat Pembayaran
          <span class="badge badge-secondary ml-2">{{ $tagihan->pembayarans->count() }} transaksi</span>
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
                <th>Nominal</th>
                <th>Metode</th>
                <th>Bukti</th>
                <th>Dicatat</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach($tagihan->pembayarans->sortBy('tanggal_bayar') as $p)
              <tr>
                <td>{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
                <td class="font-weight-bold text-success">{{ $p->nominal_rp }}</td>
                <td><span class="badge badge-secondary">{{ $p->metode_label }}</span></td>
                <td>
                  @if($p->bukti_url)
                    <a href="{{ $p->bukti_url }}" target="_blank" class="btn btn-xs btn-outline-info">
                      <i class="fas fa-image"></i>
                    </a>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td class="text-muted small">{{ $p->pencatat?->name ?? '-' }}</td>
                <td>
                  <a href="{{ route('admin.keuangan.kwitansi', $p) }}" target="_blank" class="btn btn-xs btn-outline-success mr-1" title="Cetak Kwitansi">
                    <i class="fas fa-print"></i>
                  </a>
                  <form action="{{ route('admin.keuangan.pembayaran.destroy', $p) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-xs btn-danger"
                            onclick="return confirm('Hapus catatan pembayaran ini?')">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
            <tfoot class="thead-light">
              <tr>
                <th colspan="1">Total</th>
                <th class="text-success">Rp {{ number_format($tagihan->total_dibayar, 0, ',', '.') }}</th>
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
