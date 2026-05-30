@extends('admin.laporan.pdf-layout')
@section('judul', 'LAPORAN KEHADIRAN SISWA')
@section('isi')
<div class="meta">
  Kelas: <strong>{{ $kelas->nama_kelas }}</strong> &nbsp;|&nbsp;
  Jurusan: {{ $kelas->jurusan->nama_jurusan ?? '-' }} &nbsp;|&nbsp;
  Tahun Ajaran: {{ $semester->tahunAjaran->nama ?? '-' }}
</div>
<table>
  <thead><tr><th>No</th><th>Nama</th><th>NISN</th><th class="text-center">H</th><th class="text-center">I</th><th class="text-center">S</th><th class="text-center">A</th></tr></thead>
  <tbody>
    @foreach($rows as $i => $r)
    <tr>
      <td>{{ $i + 1 }}</td><td>{{ $r['nama'] }}</td><td>{{ $r['nisn'] }}</td>
      <td class="text-center">{{ $r['hadir'] }}</td><td class="text-center">{{ $r['izin'] }}</td>
      <td class="text-center">{{ $r['sakit'] }}</td><td class="text-center">{{ $r['alpa'] }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection
