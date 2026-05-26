<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $surat->jenis_label }} — {{ $surat->siswa->nama }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Times New Roman', Times, serif;
      font-size: 12pt;
      color: #000;
      background: #fff;
    }
    .container {
      max-width: 720px;
      margin: 30px auto;
      padding: 30px 40px;
      border: 1px solid #ccc;
    }
    /* KOP SURAT */
    .kop {
      display: flex;
      align-items: center;
      border-bottom: 3px solid #000;
      padding-bottom: 12px;
      margin-bottom: 20px;
    }
    .kop-logo {
      width: 80px;
      height: 80px;
      object-fit: contain;
      margin-right: 16px;
    }
    .kop-text { flex: 1; text-align: center; }
    .kop-text h1 { font-size: 16pt; text-transform: uppercase; margin-bottom: 2px; }
    .kop-text h2 { font-size: 13pt; margin-bottom: 4px; }
    .kop-text p { font-size: 9pt; }

    /* JUDUL SURAT */
    .judul { text-align: center; margin: 24px 0 6px; }
    .judul h3 { font-size: 14pt; text-transform: uppercase; text-decoration: underline; }
    .nomor-surat { text-align: center; font-size: 11pt; margin-bottom: 20px; }

    /* PEMBUKA */
    .pembuka { margin-bottom: 16px; line-height: 1.8; }

    /* DATA SISWA */
    .data-table { width: 100%; margin-bottom: 20px; }
    .data-table td { padding: 3px 6px; font-size: 12pt; vertical-align: top; }
    .data-table td:first-child { width: 160px; font-weight: normal; }
    .data-table td:nth-child(2) { width: 14px; }

    /* ISI */
    .isi { line-height: 1.9; text-align: justify; margin-bottom: 16px; }

    /* PENUTUP */
    .penutup { margin-bottom: 30px; line-height: 1.8; }

    /* TANDA TANGAN */
    .ttd { margin-top: 20px; }
    .ttd-row { display: flex; justify-content: flex-end; }
    .ttd-box { text-align: center; min-width: 200px; }
    .ttd-box .tempat-tanggal { margin-bottom: 4px; }
    .ttd-box .jabatan { margin-bottom: 70px; }
    .ttd-box .nama { font-weight: bold; text-decoration: underline; }
    .ttd-box .nip { font-size: 10pt; }

    /* CETAK — sembunyikan tombol */
    .no-print { text-align: center; margin-top: 30px; }
    @media print {
      .no-print { display: none !important; }
      .container { border: none; margin: 0; padding: 20px 30px; }
      body { font-size: 12pt; }
    }
  </style>
</head>
<body>
  <div class="container">
    {{-- KOP SURAT --}}
    <div class="kop">
      <img src="{{ asset('dist/img/halal.jpeg') }}" alt="Logo" class="kop-logo">
      <div class="kop-text">
        <p style="font-size:10pt;">PEMERINTAH DAERAH</p>
        <h2>DINAS PENDIDIKAN DAN KEBUDAYAAN</h2>
        <h1>SMK NEGERI 48</h1>
        <p>Jl. Contoh No. 48, Kota, Provinsi &bull; Telp. (021) 1234567 &bull; smk48.sch.id</p>
      </div>
    </div>

    {{-- JUDUL --}}
    <div class="judul">
      <h3>{{ $surat->jenis_label }}</h3>
    </div>
    <div class="nomor-surat">Nomor: {{ $surat->nomor_surat }}</div>

    {{-- PEMBUKA --}}
    <div class="pembuka">
      Yang bertanda tangan di bawah ini, Kepala SMK Negeri 48, dengan ini menerangkan bahwa:
    </div>

    {{-- DATA SISWA --}}
    <table class="data-table">
      <tr>
        <td>Nama</td><td>:</td><td><strong>{{ $surat->siswa->nama }}</strong></td>
      </tr>
      <tr>
        <td>NISN</td><td>:</td><td>{{ $surat->siswa->nisn }}</td>
      </tr>
      <tr>
        <td>Kelas</td><td>:</td><td>{{ $surat->siswa->kelas?->nama_kelas ?? '-' }}</td>
      </tr>
      <tr>
        <td>Jurusan</td><td>:</td><td>{{ $surat->siswa->jurusan?->nama_jurusan ?? '-' }}</td>
      </tr>
      <tr>
        <td>Tempat / Tgl. Lahir</td><td>:</td>
        <td>{{ $surat->siswa->tanggal_lahir ? \Carbon\Carbon::parse($surat->siswa->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</td>
      </tr>
    </table>

    {{-- ISI SURAT --}}
    <div class="isi">
      @if($surat->jenis_surat === 'aktif')
        adalah benar siswa aktif di SMK Negeri 48 pada tahun pelajaran yang sedang berjalan.
        Surat keterangan ini diberikan untuk keperluan yang bersangkutan dan dapat dipergunakan sebagaimana mestinya.
      @elseif($surat->jenis_surat === 'pindah')
        adalah benar terdaftar sebagai siswa di SMK Negeri 48 dan yang bersangkutan telah mengajukan permohonan pindah sekolah.
        Surat keterangan ini diberikan untuk keperluan perpindahan ke sekolah yang dituju.
      @else
        telah menyelesaikan seluruh program pendidikan di SMK Negeri 48 dan dinyatakan <strong>LULUS</strong>.
        Surat keterangan ini diberikan sebagai bukti kelulusan sementara menunggu penerbitan ijazah resmi.
      @endif

      @if($surat->keterangan)
        <br><br>{{ $surat->keterangan }}
      @endif
    </div>

    {{-- PENUTUP --}}
    <div class="penutup">
      Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.
    </div>

    {{-- TANDA TANGAN --}}
    <div class="ttd">
      <div class="ttd-row">
        <div class="ttd-box">
          <div class="tempat-tanggal">
            Jakarta, {{ $surat->tanggal_surat->translatedFormat('d F Y') }}
          </div>
          <div class="jabatan">Kepala Sekolah,</div>
          <div class="nama">Drs. H. Kepala Sekolah, M.Pd</div>
          <div class="nip">NIP. 19700101 199501 1 001</div>
        </div>
      </div>
    </div>
  </div>

  {{-- TOMBOL CETAK --}}
  <div class="no-print">
    <button onclick="window.print()" class="btn btn-primary" style="padding:10px 30px;font-size:14px;cursor:pointer;background:#007bff;color:#fff;border:none;border-radius:5px;">
      🖨️ Cetak Surat
    </button>
    <a href="{{ route('admin.surat.index') }}" style="margin-left:12px;padding:10px 20px;font-size:14px;text-decoration:none;background:#6c757d;color:#fff;border-radius:5px;">
      ← Kembali
    </a>
  </div>

  <script>
    // Auto-trigger print setelah halaman dimuat
    window.addEventListener('load', function () {
      // Hanya auto-print jika tidak ada query param 'noprint'
      if (!window.location.search.includes('noprint')) {
        setTimeout(() => window.print(), 600);
      }
    });
  </script>
</body>
</html>
