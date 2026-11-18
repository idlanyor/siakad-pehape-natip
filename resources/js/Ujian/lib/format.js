const opsiTanggal = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };

export function tanggalWaktu(nilai) {
    if (!nilai) return '-';
    return new Date(nilai).toLocaleString('id-ID', opsiTanggal);
}

export function angka(nilai, digit = 2) {
    if (nilai === null || nilai === undefined || nilai === '') return '-';
    return Number(nilai).toLocaleString('id-ID', { maximumFractionDigits: digit });
}

export function potong(teks, panjang = 120) {
    if (!teks) return '';
    return teks.length > panjang ? `${teks.slice(0, panjang)}…` : teks;
}

export const labelStatusSesi = {
    belum: ['Belum mulai', 'secondary'],
    berlangsung: ['Berlangsung', 'primary'],
    selesai: ['Selesai', 'success'],
    timeout: ['Waktu habis', 'warning'],
    diskualifikasi: ['Diskualifikasi', 'danger'],
};
