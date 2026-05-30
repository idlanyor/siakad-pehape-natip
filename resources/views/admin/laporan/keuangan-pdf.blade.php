@extends('admin.laporan.pdf-layout')
@section('judul', 'LAPORAN KEUANGAN SPP — ' . $bulan)
@section('isi')
<table class="summary" style="margin-bottom:10px">
  <tr>
    <td>Total Tagihan: <strong>{{ $summary['total'] }}</strong></td>
    <td>Lunas: <strong>{{ $summary['lunas'] }}</strong></td>
    <td>Belum: <strong>{{ $summary['belum'] }}</strong></td>
    <td>Cicil: <strong>{{ $summary['cicil'] }}</strong></td>
  </tr>
  <tr>
    <td colspan="2">Total Nominal: <strong>Rp {{ number_format($summary['nominal'], 0, ',', '.') }}</strong></td>
    <td colspan="2">Terkumpul: <strong>Rp {{ number_format($summary['terkumpul'], 0, ',', '.') }}</strong></td>
  </tr>
</table>
<table>
  <thead><tr><th>No</th><th>Siswa</th><th>Kelas</th><th class="text-right">Nominal</th><th class="text-right">Dibayar</th><th>Status</th></tr></thead>
  <tbody>
    @foreach($tagihans as $i => $t)
    <tr>
      <td>{{ $i + 1 }}</td><td>{{ $t->siswa->nama ?? '-' }}</td><td>{{ $t->siswa->kelas->nama_kelas ?? '-' }}</td>
      <td class="text-right">{{ number_format($t->nominal, 0, ',', '.') }}</td>
      <td class="text-right">{{ number_format($t->total_dibayar, 0, ',', '.') }}</td>
      <td>{{ $t->status_label }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection
