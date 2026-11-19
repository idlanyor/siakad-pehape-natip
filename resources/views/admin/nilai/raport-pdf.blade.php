<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Times New Roman', serif; font-size: 11pt; color: #000; }
  .kop { display: flex; align-items: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px; }
  .kop-text h2 { font-size: 14pt; font-weight: bold; text-transform: uppercase; }
  .kop-text p { font-size: 10pt; }
  .judul { text-align: center; margin: 16px 0 12px; }
  .judul h3 { font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; }
  .judul .garis { border-bottom: 2px solid #000; width: 60%; margin: 4px auto 0; }
  .info-siswa { width: 100%; margin-bottom: 14px; }
  .info-siswa td { padding: 2px 6px; font-size: 10.5pt; }
  .info-siswa td:first-child { width: 160px; font-weight: normal; }
  table.nilai { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 10pt; }
  table.nilai th, table.nilai td { border: 1px solid #555; padding: 4px 6px; }
  table.nilai thead th { background: #d0d8e8; text-align: center; font-weight: bold; }
  table.nilai tbody td.center { text-align: center; }
  table.nilai tfoot td { font-weight: bold; background: #f0f0f0; }
  .predikat-A { color: #1a7a1a; font-weight: bold; }
  .predikat-B { color: #1a4f8a; font-weight: bold; }
  .predikat-C { color: #8a6a00; font-weight: bold; }
  .predikat-D { color: #8a1a1a; font-weight: bold; }
  .ttd { margin-top: 30px; display: flex; justify-content: space-between; }
  .ttd-box { text-align: center; width: 200px; }
  .ttd-box .ruang { height: 60px; }
  .ttd-box p { border-top: 1px solid #000; padding-top: 4px; font-size: 10pt; }
  .footer-note { font-size: 9pt; color: #555; border-top: 1px solid #ccc; padding-top: 6px; margin-top: 10px; }
</style>
</head>
<body>

{{-- Kop Surat --}}
<div class="kop">
  <div class="kop-text">
    <h2>{{ setting('sekolah_nama', 'Nama Sekolah') }}</h2>
    <p>{{ collect([setting('sekolah_alamat'), setting('sekolah_no_telp') ? 'Telp. ' . setting('sekolah_no_telp') : null, setting('sekolah_website') ?: setting('sekolah_email')])->filter()->implode(' | ') }}</p>
  </div>
</div>

{{-- Judul --}}
<div class="judul">
  <h3>Laporan Hasil Belajar (Raport)</h3>
  <p>{{ $semester ? 'Semester ' . $semester->semester . ' — ' . ($semester->tahunAjaran->nama ?? '') : '' }}</p>
  <div class="garis"></div>
</div>

{{-- Info Siswa --}}
<table class="info-siswa">
  <tr><td>Nama Siswa</td><td>: <strong>{{ $siswa->nama }}</strong></td><td width="50"></td>
      <td>Kelas</td><td>: <strong>{{ $siswa->kelas?->nama_kelas ?? '-' }}</strong></td></tr>
  <tr><td>NISN</td><td>: {{ $siswa->nisn ?? '-' }}</td><td></td>
      <td>Jurusan</td><td>: {{ $siswa->jurusan?->nama_jurusan ?? '-' }}</td></tr>
  <tr><td>Tahun Pelajaran</td><td>: {{ $semester?->tahunAjaran->nama ?? '-' }}</td><td></td>
      <td>Semester</td><td>: {{ $semester?->semester ?? '-' }}</td></tr>
</table>

{{-- Tabel Nilai --}}
<table class="nilai">
  <thead>
    <tr>
      <th rowspan="2" style="width:5%">No</th>
      <th rowspan="2">Mata Pelajaran</th>
      <th colspan="3">Pengetahuan</th>
      <th colspan="3">Keterampilan</th>
      <th rowspan="2" style="width:8%">Nilai Akhir</th>
      <th rowspan="2" style="width:6%">Pred.</th>
    </tr>
    <tr>
      <th style="width:6%">Tugas</th>
      <th style="width:6%">PTS</th>
      <th style="width:6%">PAS</th>
      <th style="width:7%">Praktik</th>
      <th style="width:7%">Proyek</th>
      <th style="width:8%">Porto.</th>
    </tr>
  </thead>
  <tbody>
    @php $totalNilai = 0; $jumlahMapel = 0; @endphp
    @foreach($jadwals as $i => $j)
      @php $n = $nilais->get($j->id); @endphp
      <tr>
        <td class="center">{{ $i + 1 }}</td>
        <td>{{ $j->mapel->nama_mapel ?? '-' }}</td>
        <td class="center">{{ $n?->tugas ?? '-' }}</td>
        <td class="center">{{ $n?->pts ?? '-' }}</td>
        <td class="center">{{ $n?->pas ?? '-' }}</td>
        <td class="center">{{ $n?->praktik ?? '-' }}</td>
        <td class="center">{{ $n?->proyek ?? '-' }}</td>
        <td class="center">{{ $n?->portofolio ?? '-' }}</td>
        <td class="center"><strong>{{ $n?->nilai_akhir ? number_format($n->nilai_akhir, 1) : '-' }}</strong></td>
        <td class="center {{ $n?->predikat ? 'predikat-' . $n->predikat : '' }}">
          {{ $n?->predikat ?? '-' }}
        </td>
      </tr>
      @if($n?->nilai_akhir) @php $totalNilai += $n->nilai_akhir; $jumlahMapel++; @endphp @endif
    @endforeach
  </tbody>
  <tfoot>
    <tr>
      <td colspan="8" style="text-align:right">Rata-rata Keseluruhan</td>
      <td class="center">{{ $jumlahMapel > 0 ? number_format($totalNilai / $jumlahMapel, 1) : '-' }}</td>
      <td></td>
    </tr>
  </tfoot>
</table>

{{-- Keterangan Predikat --}}
<p style="font-size:9pt; color:#444; margin-bottom:14px">
  Keterangan Predikat: &nbsp;
  <span class="predikat-A">A = 90–100 (Sangat Baik)</span> &nbsp;·&nbsp;
  <span class="predikat-B">B = 75–89 (Baik)</span> &nbsp;·&nbsp;
  <span class="predikat-C">C = 60–74 (Cukup)</span> &nbsp;·&nbsp;
  <span class="predikat-D">D = &lt;60 (Perlu Bimbingan)</span>
</p>

{{-- Tanda Tangan --}}
<div class="ttd">
  <div class="ttd-box">
    <p>Mengetahui,<br>Orang Tua / Wali</p>
    <div class="ruang"></div>
    <p>( _________________________ )</p>
  </div>
  <div class="ttd-box">
    <p>Jakarta, {{ now()->translatedFormat('d F Y') }}<br>Wali Kelas</p>
    <div class="ruang"></div>
    <p>( _________________________ )<br><small>NIP. ___________________</small></p>
  </div>
  <div class="ttd-box">
    <p>Mengetahui,<br>Kepala Sekolah</p>
    <div class="ruang"></div>
    <p>( _________________________ )<br><small>NIP. ___________________</small></p>
  </div>
</div>

<div class="footer-note">
  Dicetak oleh SIAKAD {{ setting('sekolah_nama') }} &nbsp;·&nbsp; {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>
