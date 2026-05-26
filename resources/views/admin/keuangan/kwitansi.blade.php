<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Kwitansi Pembayaran SPP</title>
  <style>
    @page { size: A5 landscape; margin: 15mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }
    body { padding: 20px; color: #333; }

    .kwitansi { border: 2px solid #333; padding: 20px 28px; max-width: 650px; margin: 0 auto; position: relative; }
    .kwitansi::after { content: 'KWITANSI'; position: absolute; top: 12px; right: 20px; font-size: .7rem; font-weight: 700; color: #999; letter-spacing: 2px; }

    .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #333; padding-bottom: 12px; }
    .header h2 { font-size: 1.1rem; margin-bottom: 2px; }
    .header p { font-size: .78rem; color: #555; }
    .header .title { font-size: .85rem; font-weight: 700; margin-top: 8px; text-transform: uppercase; letter-spacing: 1px; }

    table.detail { width: 100%; margin-bottom: 16px; }
    table.detail td { padding: 3px 0; font-size: .82rem; vertical-align: top; }
    table.detail td.label { width: 140px; font-weight: 600; }
    table.detail td.sep { width: 10px; text-align: center; }

    .nominal-box { background: #f4f4f4; border: 1px solid #ccc; border-radius: 6px; padding: 12px 16px; text-align: center; margin: 12px 0; }
    .nominal-box .amount { font-size: 1.4rem; font-weight: 800; color: #1a3a6b; }
    .nominal-box .method { font-size: .75rem; color: #666; margin-top: 2px; }

    .footer-row { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 24px; }
    .footer-row .left { font-size: .75rem; color: #888; }
    .footer-row .right { text-align: center; min-width: 160px; }
    .footer-row .right .date { font-size: .78rem; margin-bottom: 48px; }
    .footer-row .right .name { font-size: .82rem; font-weight: 600; border-top: 1px solid #333; padding-top: 4px; }

    .status-badge { display: inline-block; padding: 3px 12px; border-radius: 4px; font-size: .72rem; font-weight: 700; color: #fff; }
    .status-lunas { background: #28a745; }
    .status-cicil { background: #ffc107; color: #333; }

    @media print {
      body { padding: 0; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>
  <div class="no-print" style="text-align:center;margin-bottom:16px">
    <button onclick="window.print()" style="padding:8px 24px;background:#1a3a6b;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:.9rem">
      <i class="fas fa-print"></i> Cetak Kwitansi
    </button>
    <button onclick="window.close()" style="padding:8px 24px;background:#6c757d;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:.9rem;margin-left:8px">
      Tutup
    </button>
  </div>

  @php
    $tagihan = $pembayaran->tagihan;
    $siswa   = $tagihan->siswa;
  @endphp

  <div class="kwitansi">
    <div class="header">
      <h2>{{ setting('sekolah_nama', 'SMK') }}</h2>
      <p>{{ setting('sekolah_alamat', '-') }} &middot; Telp: {{ setting('sekolah_no_telp', '-') }}</p>
      <div class="title">Kwitansi Pembayaran SPP</div>
    </div>

    <table class="detail">
      <tr>
        <td class="label">No. Kwitansi</td>
        <td class="sep">:</td>
        <td>KW-{{ str_pad($pembayaran->id, 6, '0', STR_PAD_LEFT) }}</td>
      </tr>
      <tr>
        <td class="label">Tanggal Bayar</td>
        <td class="sep">:</td>
        <td>{{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->isoFormat('D MMMM YYYY') }}</td>
      </tr>
      <tr>
        <td class="label">Nama Siswa</td>
        <td class="sep">:</td>
        <td><strong>{{ $siswa->nama }}</strong></td>
      </tr>
      <tr>
        <td class="label">NISN</td>
        <td class="sep">:</td>
        <td>{{ $siswa->nisn ?? '-' }}</td>
      </tr>
      <tr>
        <td class="label">Kelas / Jurusan</td>
        <td class="sep">:</td>
        <td>{{ $siswa->kelas?->nama_kelas ?? '-' }} / {{ $siswa->jurusan?->nama_jurusan ?? $siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</td>
      </tr>
      <tr>
        <td class="label">Periode / Bulan</td>
        <td class="sep">:</td>
        <td>{{ \Carbon\Carbon::parse($tagihan->bulan)->isoFormat('MMMM YYYY') }}</td>
      </tr>
    </table>

    <div class="nominal-box">
      <div class="amount">Rp {{ number_format($pembayaran->nominal_bayar, 0, ',', '.') }}</div>
      <div class="method">
        Metode: {{ $pembayaran->metode_label ?? ucfirst($pembayaran->metode) }}
        @if($tagihan->status === 'lunas')
          &middot; <span class="status-badge status-lunas">LUNAS</span>
        @elseif($tagihan->status === 'cicil')
          &middot; <span class="status-badge status-cicil">CICILAN</span>
          (Sisa: Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }})
        @endif
      </div>
    </div>

    @if($pembayaran->catatan)
    <p style="font-size:.78rem;color:#666;margin-bottom:8px"><em>Catatan: {{ $pembayaran->catatan }}</em></p>
    @endif

    <div class="footer-row">
      <div class="left">
        Dicetak: {{ now()->isoFormat('D MMM YYYY HH:mm') }}<br>
        Dicatat oleh: {{ $pembayaran->pencatat?->name ?? '-' }}
      </div>
      <div class="right">
        <div class="date">{{ setting('sekolah_kota', 'Jakarta') }}, {{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->isoFormat('D MMMM YYYY') }}</div>
        <div class="name">Bendahara Sekolah</div>
      </div>
    </div>
  </div>
</body>
</html>
