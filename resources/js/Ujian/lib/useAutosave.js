import { onBeforeUnmount, ref } from 'vue';
import axios from 'axios';

/**
 * Antrean autosave jawaban.
 *
 * - Satu entri per soal; perubahan terbaru menimpa yang belum terkirim.
 * - Dikirim berurutan (satu request berjalan) supaya urutan tidak tertukar.
 * - Gagal jaringan: entri tetap di antrean dan dicoba lagi tiap 5 detik
 *   atau saat browser kembali online.
 * - Respons 422 (waktu habis, sesi diakhiri) diteruskan ke onDitolak.
 * - Respons 409 (sesi diambil alih perangkat lain) menghentikan pengiriman
 *   dan memanggil onPerangkatLain; antrean dibiarkan supaya tidak hilang.
 *
 * @param {string} url endpoint jawab
 * @param {{ headers: object, onStatus: Function, onDitolak: Function, onPerangkatLain: Function }} opsi
 */
export function useAutosave(url, { headers = {}, onStatus, onDitolak, onPerangkatLain }) {
    const antrean = new Map();
    const status = ref('tersimpan'); // tersimpan | menyimpan | offline
    let berjalan = null;
    let berhenti = false; // true setelah sesi diambil alih perangkat lain

    function simpan(payload) {
        antrean.set(payload.soal_id, payload);
        status.value = 'menyimpan';
        return kirim();
    }

    function kirim() {
        if (berhenti) return Promise.resolve(false);
        berjalan ??= proses().finally(() => { berjalan = null; });
        return berjalan;
    }

    async function proses() {
        while (antrean.size) {
            const [soalId, payload] = antrean.entries().next().value;
            try {
                const { data } = await axios.post(url, payload, { headers });
                // Hapus hanya bila tidak ada perubahan lebih baru selama request berjalan.
                if (antrean.get(soalId) === payload) antrean.delete(soalId);
                onStatus(data);
            } catch (error) {
                if (error.response?.status === 409) {
                    berhenti = true;
                    status.value = 'offline';
                    onPerangkatLain?.();
                    return false;
                }
                if (error.response?.status === 422 || error.response?.status === 403) {
                    antrean.delete(soalId);
                    onDitolak(error.response.data?.message ?? 'Jawaban ditolak server.');
                    continue;
                }
                status.value = 'offline';
                return false;
            }
        }
        status.value = 'tersimpan';
        return true;
    }

    /** Kirim semua yang tertunda; true bila antrean kosong. */
    async function kosongkan() {
        await kirim();
        return antrean.size === 0;
    }

    const tertunda = () => antrean.size > 0;

    const coba = () => { if (antrean.size) kirim(); };
    const interval = setInterval(coba, 5000);
    window.addEventListener('online', coba);
    onBeforeUnmount(() => {
        clearInterval(interval);
        window.removeEventListener('online', coba);
    });

    return { simpan, kosongkan, tertunda, status };
}
