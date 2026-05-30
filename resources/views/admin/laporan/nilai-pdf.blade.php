@extends('admin.laporan.pdf-layout')
@section('judul', 'LAPORAN REKAP NILAI')
@section('isi')
<div class="meta">
  Kelas: <strong>{{ $kelas->nama_kelas }}</strong> &nbsp;|&nbsp;
  Jurusan: {{ $kelas->jurusan->nama_jurusan ?? '-' }}
</div>
<table>
  <thead><tr><th>No</th><th>Nama</th><th>NISN</th><th class="text-center">Jml Mapel</th><th class="text-center">Rata-rata</th></tr></thead>
  <tbody>
    @foreach($rows as $i => $r)
    <tr>
      <td>{{ $i + 1 }}</td><td>{{ $r['nama'] }}</td><td>{{ $r['nisn'] }}</td>
      <td class="text-center">{{ $r['jml_mapel'] }}</td>
      <td class="text-center">{{ $r['rata'] ?? '-' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection
