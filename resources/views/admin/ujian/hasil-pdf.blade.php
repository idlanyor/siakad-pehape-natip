<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hasil Ujian - {{ $ujian->judul }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 16px;
        }
        .header p {
            margin: 3px 0;
            font-size: 10px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .info-table td {
            padding: 3px 5px;
        }
        .info-table .label {
            width: 25%;
            font-weight: bold;
        }
        .stats-box {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .stats-box td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .stats-box .stat-label {
            font-size: 9px;
            color: #666;
        }
        .stats-box .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data th {
            background-color: #f0f0f0;
            border: 1px solid #999;
            padding: 6px 4px;
            font-size: 9px;
            text-align: center;
        }
        table.data td {
            border: 1px solid #ddd;
            padding: 5px 4px;
            font-size: 9px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success { background-color: #28a745; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-secondary { background-color: #6c757d; color: white; }
        .footer {
            margin-top: 20px;
            font-size: 9px;
            color: #666;
            text-align: right;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h2>LAPORAN HASIL UJIAN ONLINE</h2>
        <p>{{ $ujian->judul }}</p>
    </div>

    {{-- Info Ujian --}}
    <table class="info-table">
        <tr>
            <td class="label">Guru</td>
            <td>: {{ $ujian->guru->nama ?? '-' }}</td>
            <td class="label">Kelas</td>
            <td>: {{ $ujian->jadwal->kelas->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Mata Pelajaran</td>
            <td>: {{ $ujian->jadwal->mapel->nama_mapel ?? '-' }}</td>
            <td class="label">Tipe</td>
            <td>: {{ $ujian->tipe_label }}</td>
        </tr>
        <tr>
            <td class="label">Waktu Pelaksanaan</td>
            <td>: {{ $ujian->waktu_mulai->format('d/m/Y H:i') }} - {{ $ujian->waktu_selesai->format('d/m/Y H:i') }}</td>
            <td class="label">Durasi</td>
            <td>: {{ $ujian->durasi_menit }} menit</td>
        </tr>
        <tr>
            <td class="label">Jumlah Soal</td>
            <td>: {{ $ujian->jumlah_soal }}</td>
            <td class="label">Passing Grade</td>
            <td>: {{ $ujian->passing_grade }}</td>
        </tr>
    </table>

    {{-- Statistik --}}
    <table class="stats-box">
        <tr>
            <td>
                <div class="stat-label">Total Peserta</div>
                <div class="stat-value">{{ $stats['total_peserta'] }}</div>
            </td>
            <td>
                <div class="stat-label">Sudah Mengerjakan</div>
                <div class="stat-value">{{ $stats['sudah_mengerjakan'] }}</div>
            </td>
            <td>
                <div class="stat-label">Rata-rata Nilai</div>
                <div class="stat-value">{{ number_format($stats['rata_rata'], 2) }}</div>
            </td>
            <td>
                <div class="stat-label">Nilai Tertinggi</div>
                <div class="stat-value">{{ number_format($stats['tertinggi'], 2) }}</div>
            </td>
            <td>
                <div class="stat-label">Nilai Terendah</div>
                <div class="stat-value">{{ number_format($stats['terendah'], 2) }}</div>
            </td>
            <td>
                <div class="stat-label">Passing Rate</div>
                <div class="stat-value">{{ $stats['passing_rate'] }}%</div>
            </td>
        </tr>
    </table>

    {{-- Tabel Hasil --}}
    <table class="data">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="20%">Nama Siswa</th>
                <th width="10%">NIS</th>
                <th width="12%">Waktu Mulai</th>
                <th width="12%">Waktu Selesai</th>
                <th width="8%">Durasi</th>
                <th width="12%">Status</th>
                <th width="8%">Nilai</th>
                <th width="10%">Predikat</th>
                <th width="4%">Tab</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sesis as $sesi)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $sesi->siswa->nama ?? '-' }}</td>
                <td class="text-center">{{ $sesi->siswa->nis ?? '-' }}</td>
                <td class="text-center">{{ $sesi->waktu_mulai?->format('d/m/Y H:i') ?? '-' }}</td>
                <td class="text-center">{{ $sesi->waktu_selesai?->format('d/m/Y H:i') ?? '-' }}</td>
                <td class="text-center">{{ $sesi->durasi_detik ? round($sesi->durasi_detik / 60, 1) . ' mnt' : '-' }}</td>
                <td class="text-center">{{ $sesi->status_label }}</td>
                <td class="text-center"><strong>{{ $sesi->nilai_akhir !== null ? number_format($sesi->nilai_akhir, 2) : '-' }}</strong></td>
                <td class="text-center">
                    @if($sesi->nilai_akhir !== null)
                        @if($sesi->is_lulus)
                            <span class="badge badge-success">LULUS</span>
                        @else
                            <span class="badge badge-danger">TIDAK LULUS</span>
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">{{ $sesi->tab_switch_count ?? 0 }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Belum ada siswa yang mengerjakan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
