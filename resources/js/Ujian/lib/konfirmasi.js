/**
 * Dialog konfirmasi memakai SweetAlert2 dari shell AdminLTE, dengan
 * fallback ke window.confirm.
 */
export async function konfirmasi(pesan, { judul = 'Konfirmasi', tombol = 'Ya, lanjutkan', bahaya = false } = {}) {
    if (!window.Swal) return window.confirm(pesan);

    const hasil = await window.Swal.fire({
        title: judul,
        text: pesan,
        icon: bahaya ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: tombol,
        cancelButtonText: 'Batal',
        confirmButtonColor: bahaya ? '#dc3545' : undefined,
        reverseButtons: true,
    });

    return hasil.isConfirmed;
}

/**
 * Minta alasan teks. Mengembalikan string (boleh kosong bila tidak wajib),
 * atau null bila dibatalkan.
 */
export async function mintaAlasan(judul, { teks = '', wajib = false, tombol = 'Lanjutkan', bahaya = false } = {}) {
    if (!window.Swal) {
        const jawab = window.prompt(`${judul}\n${teks}`);
        if (jawab === null || (wajib && !jawab.trim())) return null;
        return jawab.trim();
    }

    const hasil = await window.Swal.fire({
        title: judul,
        text: teks,
        input: 'text',
        inputPlaceholder: wajib ? 'Alasan (wajib)' : 'Alasan (opsional)',
        inputValidator: (v) => (wajib && !v?.trim() ? 'Alasan wajib diisi.' : undefined),
        icon: bahaya ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: tombol,
        cancelButtonText: 'Batal',
        confirmButtonColor: bahaya ? '#dc3545' : undefined,
        reverseButtons: true,
    });

    return hasil.isConfirmed ? (hasil.value ?? '').trim() : null;
}
