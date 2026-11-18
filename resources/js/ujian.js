/**
 * Entry Inertia untuk modul Ujian Online.
 *
 * Shell (navbar, sidebar, toastr, SweetAlert) disediakan layouts/admin.blade.php,
 * jadi file ini hanya me-mount halaman Vue di area konten.
 */
import './bootstrap';
import '../css/ujian.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

createInertiaApp({
    title: (title) => (title ? `${title} - Ujian Online` : 'Ujian Online'),
    resolve: (name) =>
        resolvePageComponent(`./Ujian/Pages/${name}.vue`, import.meta.glob('./Ujian/Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        tampilkanFlash(props.initialPage.props.flash);

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: { color: '#3c8dbc' },
});

/**
 * Flash message ditampilkan lewat toastr (layout Blade tidak merender flash
 * di halaman Inertia): sekali saat halaman pertama dimuat, lalu setiap
 * kunjungan sukses. Event "success" dipakai, bukan "navigate", karena
 * redirect kembali ke URL yang sama (mis. simpan nilai) tidak memicu navigate.
 */
function tampilkanFlash(flash) {
    if (!window.toastr || !flash) return;
    for (const type of ['success', 'error', 'warning', 'info']) {
        if (flash[type]) window.toastr[type](flash[type]);
    }
}

router.on('success', (event) => tampilkanFlash(event.detail.page.props.flash));
router.on('navigate', (event) => syncSidebarActive(event.detail.page.props.rute));

/**
 * Sidebar dirender Blade sekali saat full load; sinkronkan class "active"
 * ketika navigasi Inertia terjadi di sisi klien. Setiap link menu ujian
 * membawa data-pola berisi pola nama route (sama dengan routeIs() di Blade).
 */
function syncSidebarActive(rute) {
    if (!rute) return;
    const cocok = (pola) => pola.split('|').some((p) => new RegExp(`^${p.replace(/\./g, '\\.').replace(/\*/g, '.*')}$`).test(rute));

    document.querySelectorAll('[data-ujian-nav] a.nav-link[data-pola]').forEach((link) => {
        link.classList.toggle('active', cocok(link.dataset.pola));
    });
}
