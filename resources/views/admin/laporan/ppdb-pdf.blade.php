@extends('admin.laporan.pdf-layout')
@section('judul', 'LAPORAN PPDB' . ($gelombang ? ' — ' . $gelombang->nama : ''))
@section('isi')
<table class="summary" style="margin-bottom:10px">
  <tr>
    <td>Total: <strong>{{ $summary['total'] }}</strong></td>
    <td>Pending: <strong>{{ $summary['pending'] }}</strong></td>
    <td>Diterima: <strong>{{ $summary['diterima'] }}</strong></td>
    <td>Ditolak: <strong>{{ $summary['ditolak'] }}</strong></td>
  </tr>
</table>
<table>
  <thead><tr><th>No</th><th>No. Daftar</th><th>Nama</th><th>Asal Sekolah</th><th>Pilihan 1</th><th>Status</th></tr></thead>
  <tbody>
    @foreach($rows as $i => $r)
    <tr>
      <td>{{ $i + 1 }}</td><td>{{ $r->no_pendaftaran }}</td><td>{{ $r->nama }}</td>
      <td>{{ $r->asal_sekolah }}</td><td>{{ $r->jurusanPilihan->nama_jurusan ?? '-' }}</td>
      <td>{{ $r->status_label }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection
