<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hasil Ujian - {{ $ujian->judul }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .header h2 { margin: 4px 0; font-size: 16px; }
        .info td { padding: 2px 6px; font-size: 10px; }
        .info .label { font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.data th, table.data td { border: 1px solid #999; padding: 4px; font-size: 10px; }
        table.data th { background: #1f497d; color: #fff; }
        .tengah { text-align: center; }
        .footer { margin-top: 16px; font-size: 9px; color: #666; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Hasil Ujian: {{ $ujian->judul }}</h2>
        <div>{{ config('app.name') }}</div>
    </div>

    <table class="info">
        <tr>
            <td class="label">Mata Pelajaran</td><td>{{ $ujian->mapel?->nama_mapel ?? '-' }}</td>
            <td class="label">Kelas</td><td>{{ $ujian->kelas?->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Guru</td><td>{{ $ujian->guru?->nama ?? '-' }}</td>
            <td class="label">KKM</td><td>{{ (float) $ujian->passing_grade }}</td>
        </tr>
        <tr>
            <td class="label">Waktu</td>
            <td colspan="3">{{ $ujian->waktu_mulai->format('d/m/Y H:i') }} – {{ $ujian->waktu_selesai->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>No</th><th>Nama Siswa</th><th>NISN</th><th>Status</th>
                <th>Nilai PG</th><th>Nilai Essay</th><th>Nilai Akhir</th><th>Predikat</th><th>Lulus</th><th>Pelanggaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sesi as $i => $s)
                <tr>
                    <td class="tengah">{{ $i + 1 }}</td>
                    <td>{{ $s->siswa?->nama ?? '-' }}{{ $s->is_resmi ? '' : ' (try out)' }}</td>
                    <td>{{ $s->siswa?->nisn ?? '-' }}</td>
                    <td>{{ ucfirst($s->status) }}</td>
                    <td class="tengah">{{ $s->nilai_otomatis ?? '-' }}</td>
                    <td class="tengah">{{ $s->nilai_manual ?? '-' }}</td>
                    <td class="tengah"><strong>{{ $s->nilai_akhir ?? '-' }}</strong></td>
                    <td class="tengah">{{ $s->predikat ?? '-' }}</td>
                    <td class="tengah">
                        {{ $s->nilai_akhir === null ? '-' : ((float) $s->nilai_akhir >= (float) $ujian->passing_grade ? 'Ya' : 'Tidak') }}
                    </td>
                    <td class="tengah">{{ $s->jumlah_pelanggaran }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="tengah">Belum ada peserta yang mengerjakan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Dicetak {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
