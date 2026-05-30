@extends('layouts.admin')
@section('title', 'Detail Tagihan SPP')
@section('page_title', 'Detail Tagihan SPP')

@section('content')
<div class="row">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Tagihan {{ $tagihan->bulan_label }} — {{ $anak->nama }}</h3>
        <span class="float-right badge badge-{{ $tagihan->status_color }}">{{ $tagihan->status_label }}</span>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <tr><th class="pl-3" style="width:40%">Nominal Tagihan</th><td>Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</td></tr>
          <tr><th class="pl-3">Sudah Dibayar</th><td>Rp {{ number_format($tagihan->total_dibayar, 0, ',', '.') }}</td></tr>
          <tr><th class="pl-3">Sisa</th><td class="font-weight-bold text-danger">Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}</td></tr>
        </table>
      </div>
      <div class="card-footer">
        <a href="{{ route('orangtua.spp') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
        @if($tagihan->status !== 'lunas' && $tagihan->sisa_tagihan > 0 && config('midtrans.client_key'))
          <button id="btn-bayar-online" class="btn btn-primary float-right"
                  data-snap-url="{{ route('orangtua.spp.snap-token', $tagihan) }}">
            <i class="fas fa-credit-card mr-2"></i> Bayar Online Sekarang
          </button>
        @endif
      </div>
    </div>
  </div>

  <div class="col-md-5">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Riwayat Pembayaran</h3></div>
      <div class="card-body table-responsive p-0">
        <table class="table table-sm">
          <thead><tr><th>Tanggal</th><th>Nominal</th><th>Metode</th></tr></thead>
          <tbody>
            @forelse($tagihan->pembayarans as $p)
              <tr>
                <td>{{ $p->tanggal_bayar?->format('d/m/Y') ?? '—' }}</td>
                <td>Rp {{ number_format($p->nominal_bayar, 0, ',', '.') }}</td>
                <td>{{ $p->metode_label ?? $p->metode }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted py-3">Belum ada pembayaran.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@if(config('midtrans.client_key'))
@push('scripts')
<script src="https://app.{{ config('midtrans.is_production') ? '' : 'sandbox.' }}midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
document.getElementById('btn-bayar-online')?.addEventListener('click', function () {
  const btn = this;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memuat...';
  fetch(btn.dataset.snapUrl, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
  })
  .then(res => res.json())
  .then(data => {
    if (data.error) {
      alert('Error: ' + data.error);
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-credit-card mr-2"></i> Bayar Online Sekarang';
      return;
    }
    snap.pay(data.snap_token, {
      onSuccess: () => window.location.reload(),
      onPending: () => window.location.reload(),
      onError: () => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-credit-card mr-2"></i> Bayar Online Sekarang'; },
      onClose: () => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-credit-card mr-2"></i> Bayar Online Sekarang'; },
    });
  })
  .catch(() => {
    alert('Gagal menghubungi server. Coba lagi.');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-credit-card mr-2"></i> Bayar Online Sekarang';
  });
});
</script>
@endpush
@endif
