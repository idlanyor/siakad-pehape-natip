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
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: { color: '#3c8dbc' },
});

/**
 * Flash message: layout Blade hanya merender flash pada full page load,
 * jadi di halaman Inertia flash ditampilkan lewat toastr setiap navigasi.
 */
router.on('navigate', (event) => {
    const flash = event.detail.page.props.flash ?? {};
    if (window.toastr) {
        for (const type of ['success', 'error', 'warning', 'info']) {
            if (flash[type]) window.toastr[type](flash[type]);
        }
    }
    syncSidebarActive(window.location.pathname);
});

/**
 * Sidebar dirender Blade sekali saat full load; sinkronkan class "active"
 * secara manual ketika navigasi Inertia terjadi di sisi klien.
 */
function syncSidebarActive(pathname) {
    const links = document.querySelectorAll('.nav-sidebar a.nav-link[href]');
    let best = null;
    let bestLength = -1;

    links.forEach((link) => {
        const path = new URL(link.href, window.location.origin).pathname;
        const matches = pathname === path || pathname.startsWith(path.replace(/\/$/, '') + '/');
        if (matches && path.length > bestLength) {
            best = link;
            bestLength = path.length;
        }
    });

    if (!best || !best.closest('[data-ujian-nav]')) return;

    document
        .querySelectorAll('[data-ujian-nav] a.nav-link.active')
        .forEach((link) => link.classList.remove('active'));
    best.classList.add('active');
}
