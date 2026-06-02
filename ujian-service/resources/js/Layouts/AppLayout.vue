<template>
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <Link :href="route('dashboard')" class="nav-link">Home</Link>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-user"></i>
                        <span class="ml-2">{{ $page.props.auth.user?.name }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">{{ $page.props.auth.user?.name }}</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item" @click.prevent="logout">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <Link :href="route('dashboard')" class="brand-link">
                <span class="brand-text font-weight-light">Ujian Service</span>
            </Link>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <i class="fas fa-user-circle fa-2x text-white"></i>
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">{{ $page.props.auth.user?.name }}</a>
                        <small class="text-muted">{{ $page.props.auth.user?.role }}</small>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <Link :href="route('dashboard')" class="nav-link" :class="{ active: isRoute('dashboard') }">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </Link>
                        </li>

                        <!-- Admin/Guru Menu -->
                        <template v-if="isAdmin || isGuru">
                            <!-- Periode Ujian -->
                            <li class="nav-item">
                                <Link :href="route('periode.index')" class="nav-link" :class="{ active: isRoute('periode.*') }">
                                    <i class="nav-icon fas fa-calendar-alt"></i>
                                    <p>Periode Ujian</p>
                                </Link>
                            </li>

                            <!-- Ujian -->
                            <li class="nav-item">
                                <Link :href="route('ujian.index')" class="nav-link" :class="{ active: isRoute('ujian.*') }">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p>Ujian</p>
                                </Link>
                            </li>

                            <!-- Bank Soal -->
                            <li class="nav-item">
                                <Link :href="route('bank-soal.index')" class="nav-link" :class="{ active: isRoute('bank-soal.*') }">
                                    <i class="nav-icon fas fa-database"></i>
                                    <p>Bank Soal</p>
                                </Link>
                            </li>

                            <!-- Hasil Ujian -->
                            <li class="nav-item">
                                <Link :href="route('hasil.index')" class="nav-link" :class="{ active: isRoute('hasil.*') }">
                                    <i class="nav-icon fas fa-chart-bar"></i>
                                    <p>Hasil Ujian</p>
                                </Link>
                            </li>
                        </template>

                        <!-- Siswa Menu -->
                        <template v-if="isSiswa">
                            <!-- Ujian Tersedia -->
                            <li class="nav-item">
                                <Link :href="route('sesi.index')" class="nav-link" :class="{ active: isRoute('sesi.*') }">
                                    <i class="nav-icon fas fa-clipboard-list"></i>
                                    <p>Ujian Tersedia</p>
                                </Link>
                            </li>

                            <!-- Riwayat Ujian -->
                            <li class="nav-item">
                                <Link :href="route('riwayat.index')" class="nav-link" :class="{ active: isRoute('riwayat.*') }">
                                    <i class="nav-icon fas fa-history"></i>
                                    <p>Riwayat Ujian</p>
                                </Link>
                            </li>
                        </template>

                        <!-- Admin Only -->
                        <template v-if="isAdmin">
                            <li class="nav-header">ADMINISTRASI</li>
                            
                            <!-- Proktor Monitoring -->
                            <li class="nav-item">
                                <Link :href="route('proktor.index')" class="nav-link" :class="{ active: isRoute('proktor.*') }">
                                    <i class="nav-icon fas fa-video"></i>
                                    <p>Proktor Monitoring</p>
                                </Link>
                            </li>
                        </template>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header" v-if="$slots.header">
                <div class="container-fluid">
                    <slot name="header" />
                </div>
            </div>

            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                    <!-- Flash Messages -->
                    <div v-if="$page.props.flash.success" class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ $page.props.flash.success }}
                    </div>
                    <div v-if="$page.props.flash.error" class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ $page.props.flash.error }}
                    </div>
                    <div v-if="$page.props.flash.warning" class="alert alert-warning alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ $page.props.flash.warning }}
                    </div>
                    <div v-if="$page.props.flash.info" class="alert alert-info alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ $page.props.flash.info }}
                    </div>

                    <!-- Page Content -->
                    <slot />
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                Version 1.0.0
            </div>
            <strong>Copyright &copy; 2024 <a href="#">Ujian Service</a>.</strong> All rights reserved.
        </footer>

        <!-- Toast Notifications -->
        <Toast />
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Toast } from '@/Components';

const isRoute = (routeName) => {
    return route().current(routeName);
};

const isAdmin = computed(() => {
    return ['admin', 'superadmin'].includes($page.props.auth.user?.role);
});

const isGuru = computed(() => {
    return $page.props.auth.user?.role === 'guru';
});

const isSiswa = computed(() => {
    return $page.props.auth.user?.role === 'siswa';
});

const logout = () => {
    window.tokenManager.remove();
    window.location.href = $page.props.app.siakad_url + '/logout';
};
</script>

<style scoped>
/* Additional custom styles if needed */
</style>
