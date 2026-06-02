<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\PembayaranSpp;
use App\Models\PendaftarPpdb;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\Ujian;
use App\Models\SesiUjian;

class WaTemplates
{
    public static function sppJatuhTempo(Siswa $siswa, TagihanSpp $tagihan): string
    {
        $sekolah = setting('sekolah_nama', 'Sekolah');
        return "Yth. Orang Tua/Wali {$siswa->nama},\n\n"
            . "Mengingatkan bahwa tagihan SPP bulan *{$tagihan->bulan_label}* "
            . "sebesar *Rp " . number_format($tagihan->sisa_tagihan, 0, ',', '.') . "* "
            . "akan segera jatuh tempo.\n\n"
            . "Mohon segera melakukan pembayaran.\n\n"
            . "Terima kasih,\n{$sekolah}";
    }

    public static function sppBerhasil(Siswa $siswa, PembayaranSpp $pembayaran): string
    {
        $sekolah = setting('sekolah_nama', 'Sekolah');
        $tagihan = $pembayaran->tagihan;
        return "Yth. Orang Tua/Wali {$siswa->nama},\n\n"
            . "Pembayaran SPP bulan *{$tagihan->bulan_label}* "
            . "sebesar *Rp " . number_format($pembayaran->nominal_bayar, 0, ',', '.') . "* "
            . "telah *dikonfirmasi*.\n\n"
            . "Status tagihan: " . strtoupper($tagihan->status) . "\n\n"
            . "Terima kasih,\n{$sekolah}";
    }

    public static function tagihanBaru(Siswa $siswa, TagihanSpp $tagihan): string
    {
        $sekolah = setting('sekolah_nama', 'Sekolah');
        return "Yth. Orang Tua/Wali {$siswa->nama},\n\n"
            . "Tagihan SPP bulan *{$tagihan->bulan_label}* telah diterbitkan "
            . "sebesar *Rp " . number_format($tagihan->nominal, 0, ',', '.') . "*.\n\n"
            . "Mohon segera melakukan pembayaran sebelum jatuh tempo.\n\n"
            . "Terima kasih,\n{$sekolah}";
    }

    public static function siswaAlpa(Siswa $siswa, Absensi $absensi): string
    {
        $sekolah = setting('sekolah_nama', 'Sekolah');
        $mapel = $absensi->jadwal->mapel->nama_mapel ?? 'Tidak diketahui';
        return "Yth. Orang Tua/Wali {$siswa->nama},\n\n"
            . "Kami informasikan bahwa *{$siswa->nama}* tercatat *ALPA* (tidak hadir tanpa keterangan) "
            . "pada:\n"
            . "- Tanggal: {$absensi->tanggal->format('d/m/Y')}\n"
            . "- Mata Pelajaran: {$mapel}\n\n"
            . "Mohon perhatiannya.\n\n"
            . "Terima kasih,\n{$sekolah}";
    }

    public static function pengumumanBaru(string $judul, string $ringkasan): string
    {
        $sekolah = setting('sekolah_nama', 'Sekolah');
        return "📢 *Pengumuman {$sekolah}*\n\n"
            . "*{$judul}*\n\n"
            . $ringkasan . "\n\n"
            . "Selengkapnya dapat dilihat di portal sekolah.";
    }

    public static function ppdbDiterima(PendaftarPpdb $pendaftar, string $username, string $password): string
    {
        $sekolah = setting('sekolah_nama', 'Sekolah');
        return "Halo *{$pendaftar->nama}*,\n\n"
            . "Selamat! Pendaftaran Anda di *{$sekolah}* telah *DITERIMA*. 🎉\n\n"
            . "Berikut akun login Anda:\n"
            . "- Username: *{$username}*\n"
            . "- Password: *{$password}*\n\n"
            . "Silakan login di portal sekolah dan segera ganti password Anda.\n\n"
            . "Terima kasih.";
    }

    public static function ujianBaru(Siswa $siswa, Ujian $ujian): string
    {
        $sekolah = setting('sekolah_nama', 'Sekolah');
        $mapel = $ujian->jadwal->mapel->nama_mapel ?? 'Mata Pelajaran';
        return "Yth. {$siswa->nama},\n\n"
            . "📝 *Ujian Baru Tersedia*\n\n"
            . "- Mata Pelajaran: *{$mapel}*\n"
            . "- Judul: *{$ujian->judul}*\n"
            . "- Tipe: {$ujian->tipe_label}\n"
            . "- Waktu: {$ujian->waktu_mulai->format('d/m/Y H:i')} - {$ujian->waktu_selesai->format('H:i')}\n"
            . "- Durasi: {$ujian->durasi_menit} menit\n\n"
            . "Silakan login ke portal untuk mengerjakan ujian.\n\n"
            . "Terima kasih,\n{$sekolah}";
    }

    public static function ujianReminder(Siswa $siswa, Ujian $ujian): string
    {
        $sekolah = setting('sekolah_nama', 'Sekolah');
        $mapel = $ujian->jadwal->mapel->nama_mapel ?? 'Mata Pelajaran';
        return "Yth. {$siswa->nama},\n\n"
            . "⏰ *Reminder: Ujian Besok*\n\n"
            . "- Mata Pelajaran: *{$mapel}*\n"
            . "- Judul: *{$ujian->judul}*\n"
            . "- Waktu: {$ujian->waktu_mulai->format('d/m/Y H:i')}\n"
            . "- Durasi: {$ujian->durasi_menit} menit\n\n"
            . "Jangan lupa persiapkan diri dan login tepat waktu.\n\n"
            . "Terima kasih,\n{$sekolah}";
    }

    public static function ujianDikoreksi(Siswa $siswa, SesiUjian $sesi): string
    {
        $sekolah = setting('sekolah_nama', 'Sekolah');
        $ujian = $sesi->ujian;
        $mapel = $ujian->jadwal->mapel->nama_mapel ?? 'Mata Pelajaran';
        return "Yth. {$siswa->nama},\n\n"
            . "✅ *Hasil Ujian Sudah Keluar*\n\n"
            . "- Mata Pelajaran: *{$mapel}*\n"
            . "- Judul: *{$ujian->judul}*\n"
            . "- Nilai: *" . number_format($sesi->nilai_akhir, 2) . "*\n"
            . "- Status: *{$sesi->predikat}*\n\n"
            . "Silakan login ke portal untuk melihat detail hasil ujian.\n\n"
            . "Terima kasih,\n{$sekolah}";
    }
}
