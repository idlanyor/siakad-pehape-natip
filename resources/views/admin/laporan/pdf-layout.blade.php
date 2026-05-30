<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 11px; color: #000; }
    .head { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
    .head h2 { margin: 0; font-size: 16px; }
    .head p { margin: 2px 0; font-size: 11px; }
    .meta { font-size: 11px; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    th, td { border: 1px solid #555; padding: 4px 6px; }
    th { background: #eee; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .footer { margin-top: 30px; font-size: 11px; }
    .summary td { border: none; padding: 2px 6px; }
  </style>
</head>
<body>
  <div class="head">
    <h2>{{ setting('sekolah_nama', 'SIAKAD SMK') }}</h2>
    <p>{{ setting('sekolah_alamat', '') }}</p>
    <p><strong>@yield('judul')</strong></p>
  </div>
  @yield('isi')
  <div class="footer">
    Dicetak: {{ now()->format('d F Y, H:i') }} WIB
  </div>
</body>
</html>
