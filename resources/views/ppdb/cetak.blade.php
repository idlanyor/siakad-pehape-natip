<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bukti Pendaftaran - {{ $pendaftar->no_pendaftaran }}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; background: #fff; }

    .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 15mm 20mm; }

    .header { display: flex; align-items: center; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
    .header .logo { width: 70px; height: 70px; margin-right: 15px; }
    .header .sekolah-info { flex: 1; }
    .header .sekolah-info h2 { font-size: 16px; font-weight: bold; }
    .header .sekolah-info p { font-size: 11px; margin-top: 2px; }

    .judul { text-align: center; margin: 15px 0 5px; }
    .judul h3 { font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
    .judul .no-ppdb { font-size: 20px; font-weight: bold; letter-spacing: 3px; margin: 8px 0; border: 2px solid #000; display: inline-block; padding: 4px 20px; }

    .section-title { font-size: 12px; font-weight: bold; text-transform: uppercase; background: #eee; padding: 4px 8px; margin: 12px 0 6px; border-left: 4px solid #333; }

    table.data { width: 100%; border-collapse: collapse; font-size: 12px; }
    table.data td { padding: 4px 6px; vertical-align: top; }
    table.data td:first-child { width: 38%; font-weight: bold; color: #444; }
    table.data td:nth-child(2) { width: 4%; }

    table.bordered { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 6px; }
    table.bordered th, table.bordered td { border: 1px solid #999; padding: 5px 8px; }
    table.bordered th { background: #f0f0f0; font-weight: bold; }

    .akun-box { border: 2px dashed #555; padding: 10px 15px; margin-top: 12px; background: #fafafa; }
    .akun-box .akun-title { font-weight: bold; font-size: 12px; margin-bottom: 6px; }
    .akun-box table td:first-child { width: 30%; }
    .akun-box .warning { font-size: 10px; color: #c00; margin-top: 6px; font-weight: bold; }

    .ttd-section { display: flex; justify-content: space-between; margin-top: 30px; }
    .ttd-box { text-align: center; width: 40%; }
    .ttd-box .ttd-garis { border-top: 1px solid #000; margin: 50px 0 4px; }

    .footer-note { font-size: 10px; color: #666; text-align: center; margin-top: 20px; border-top: 1px solid #ccc; padding-top: 8px; }

    .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; }
    .badge-secondary { background: #ccc; }
    .badge-info { background: #bee5eb; }
    .badge-success { background: #c3e6cb; }
    .badge-danger { background: #f5c6cb; }

    .no-print { display: block; text-align: center; margin: 20px 0; }
    .no-print button { padding: 8px 24px; font-size: 14px; cursor: pointer; background: #1a73e8; color: #fff; border: none; border-radius: 4px; }

    @media print {
      .no-print { display: none !important; }
      body { background: #fff; }
      .page { padding: 10mm 15mm; }
    }
  </style>
</head>
<body>

<div class="no-print">
  <button onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
  <button onclick="window.close()" style="background:#666;margin-left:8px">Tutup</button>
</div>

<div class="page">

  {{-- Header --}}
  <div class="header">
    <div class="sekolah-info">
      <h2>{{ strtoupper(setting('sekolah_nama', 'Nama Sekolah')) }}</h2>
      <p>{{ collect([setting('sekolah_alamat'), setting('sekolah_no_telp') ? 'Telp. ' . setting('sekolah_no_telp') : null, setting('sekolah_website') ?: setting('sekolah_email')])->filter()->implode(' | ') }}</p>
    </div>
  </div>

  {{-- Judul --}}
  <div class="judul">
    <h3>Bukti Pendaftaran Peserta Didik Baru (PPDB)</h3>
    <div>Tahun Ajaran {{ $pendaftar->tahun_ajaran }}</div>
    <div style="margin-top:8px">Nomor Pendaftaran:</div>
    <div class="no-ppdb">{{ $pendaftar->no_pendaftaran }}</div>
    <div style="font-size:11px;color:#555;margin-top:4px">
      Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB
    </div>
  </div>

  {{-- Data Pribadi --}}
  <div class="section-title">Data Pribadi</div>
  <table class="data">
    <tr><td>Nama Lengkap</td><td>:</td><td>{{ $pendaftar->nama }}</td></tr>
    <tr><td>Jenis Kelamin</td><td>:</td><td>{{ $pendaftar->jk_label }}</td></tr>
    <tr><td>Tempat, Tanggal Lahir</td><td>:</td><td>{{ $pendaftar->tempat_lahir }}, {{ $pendaftar->tanggal_lahir?->format('d F Y') }}</td></tr>
    <tr><td>NISN</td><td>:</td><td>{{ $pendaftar->nisn ?: '-' }}</td></tr>
    <tr><td>Alamat</td><td>:</td><td>{{ $pendaftar->alamat }}</td></tr>
    <tr><td>No. HP</td><td>:</td><td>{{ $pendaftar->no_hp }}</td></tr>
    <tr><td>Email</td><td>:</td><td>{{ $pendaftar->email ?: '-' }}</td></tr>
    <tr><td>Asal Sekolah</td><td>:</td><td>{{ $pendaftar->asal_sekolah }}</td></tr>
  </table>

  {{-- Data Orang Tua --}}
  <div class="section-title">Data Orang Tua / Wali</div>
  <table class="data">
    <tr><td>Nama Ayah</td><td>:</td><td>{{ $pendaftar->nama_ayah ?: '-' }}</td></tr>
    <tr><td>Nama Ibu</td><td>:</td><td>{{ $pendaftar->nama_ibu ?: '-' }}</td></tr>
    <tr><td>No. HP Orang Tua</td><td>:</td><td>{{ $pendaftar->no_hp_ortu ?: '-' }}</td></tr>
    <tr><td>Pekerjaan Orang Tua</td><td>:</td><td>{{ $pendaftar->pekerjaan_ortu ?: '-' }}</td></tr>
  </table>

  {{-- Pilihan Jurusan --}}
  <div class="section-title">Pilihan Program Keahlian</div>
  <table class="data">
    <tr><td>Pilihan 1</td><td>:</td><td>{{ $pendaftar->jurusanPilihan?->nama_jurusan ?? '-' }}</td></tr>
    <tr><td>Pilihan 2</td><td>:</td><td>{{ $pendaftar->jurusanPilihan2?->nama_jurusan ?? '-' }}</td></tr>
    <tr><td>Rata-rata Nilai Rapor</td><td>:</td><td>{{ $pendaftar->nilai_rata_rata ? number_format($pendaftar->nilai_rata_rata, 2) : '-' }}</td></tr>
    <tr><td>Gelombang</td><td>:</td><td>{{ $pendaftar->gelombangPpdb?->nama ?? '-' }}</td></tr>
    <tr><td>Tahun Ajaran</td><td>:</td><td>{{ $pendaftar->tahun_ajaran }}</td></tr>
    <tr>
      <td>Status</td><td>:</td>
      <td><span class="badge badge-{{ $pendaftar->status_color }}">{{ $pendaftar->status_label }}</span></td>
    </tr>
  </table>

  {{-- Info Akun --}}
  @if($pendaftar->akun_username)
  <div class="akun-box">
    <div class="akun-title">&#128273; Akun Portal Pendaftar</div>
    <table class="data">
      <tr><td>Username</td><td>:</td><td><strong>{{ $pendaftar->akun_username }}</strong></td></tr>
      <tr><td>Password</td><td>:</td><td><strong>{{ $pendaftar->akun_password_plain }}</strong></td></tr>
      <tr><td>URL Login</td><td>:</td><td>{{ url('/login') }}</td></tr>
    </table>
    <div class="warning">&#9888; Simpan info akun ini dengan aman. Jangan bagikan kepada orang lain.</div>
  </div>
  @endif

  {{-- Tanda Tangan --}}
  <div class="ttd-section">
    <div class="ttd-box">
      <div>Orang Tua / Wali</div>
      <div class="ttd-garis"></div>
      <div>({{ $pendaftar->nama_ayah ?: '......................' }})</div>
    </div>
    <div class="ttd-box">
      <div>Pendaftar</div>
      <div class="ttd-garis"></div>
      <div>({{ $pendaftar->nama }})</div>
    </div>
  </div>

  <div class="footer-note">
    Dokumen ini dicetak secara otomatis oleh sistem. Harap simpan sebagai bukti pendaftaran yang sah.
  </div>

</div>

</body>
</html>
