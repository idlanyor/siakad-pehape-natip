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
