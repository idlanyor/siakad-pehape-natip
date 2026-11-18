import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Deteksi pelanggaran di browser siswa dan laporkan lewat `lapor(tipe)`.
 *
 * - tab_switch: halaman tersembunyi (pindah tab/aplikasi)
 * - blur: jendela kehilangan fokus tanpa tersembunyi (mis. jendela lain di atasnya)
 * - copy / paste: diblokir dan dilaporkan
 * - right_click: menu konteks diblokir dan dilaporkan
 * - fullscreen_exit: keluar dari layar penuh setelah masuk
 *
 * Tipe yang sama dalam 3 detik dihitung sekali.
 *
 * @param {(tipe: string) => void} lapor
 * @param {() => boolean} aktif pengawasan hanya berjalan bila mengembalikan true
 */
export function usePengawasanBrowser(lapor, aktif) {
    const terakhir = {};
    const layarPenuh = ref(false);

    function catat(tipe) {
        if (!aktif()) return;
        const sekarang = Date.now();
        if (terakhir[tipe] && sekarang - terakhir[tipe] < 3000) return;
        terakhir[tipe] = sekarang;
        lapor(tipe);
    }

    const onVisibility = () => { if (document.hidden) catat('tab_switch'); };
    // Blur mendahului visibilitychange saat pindah tab; tunggu sebentar agar tidak dobel.
    const onBlur = () => setTimeout(() => { if (!document.hidden) catat('blur'); }, 300);
    const blokir = (tipe) => (e) => { e.preventDefault(); catat(tipe); };
    const onCopy = blokir('copy');
    const onCut = blokir('copy');
    const onPaste = blokir('paste');
    const onContext = blokir('right_click');
    const onFullscreen = () => {
        const masuk = Boolean(document.fullscreenElement);
        if (layarPenuh.value && !masuk) catat('fullscreen_exit');
        layarPenuh.value = masuk;
    };

    async function masukLayarPenuh() {
        try {
            await document.documentElement.requestFullscreen?.();
        } catch {
            // Browser menolak (mis. iOS Safari); ujian tetap bisa berjalan.
        }
    }

    onMounted(() => {
        document.addEventListener('visibilitychange', onVisibility);
        window.addEventListener('blur', onBlur);
        document.addEventListener('copy', onCopy);
        document.addEventListener('cut', onCut);
        document.addEventListener('paste', onPaste);
        document.addEventListener('contextmenu', onContext);
        document.addEventListener('fullscreenchange', onFullscreen);
    });

    onBeforeUnmount(() => {
        document.removeEventListener('visibilitychange', onVisibility);
        window.removeEventListener('blur', onBlur);
        document.removeEventListener('copy', onCopy);
        document.removeEventListener('cut', onCut);
        document.removeEventListener('paste', onPaste);
        document.removeEventListener('contextmenu', onContext);
        document.removeEventListener('fullscreenchange', onFullscreen);
        if (document.fullscreenElement) document.exitFullscreen?.();
    });

    return { layarPenuh, masukLayarPenuh };
}
