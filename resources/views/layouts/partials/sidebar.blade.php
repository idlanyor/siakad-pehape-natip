<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="#" class="brand-link">
    <img src="{{ asset('dist/img/halal.jpeg') }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">Simakad SMK 48</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">

    {{-- User Panel: avatar + nama, link ke profil --}}
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="{{ auth()->user()->avatar_url }}" class="img-circle elevation-2" alt="Foto Profil" style="width:34px;height:34px;object-fit:cover">
      </div>
      <div class="info">
        <a href="{{ route('profile.edit') }}" class="d-block">{{ auth()->user()->name }}</a>
      </div>
    </div>

    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        {{-- Dashboard --}}
        <li class="nav-item">
          <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('*.dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        {{-- Pengumuman (semua role) --}}
        <li class="nav-item">
          <a href="{{ route('pengumuman.index') }}" class="nav-link {{ request()->routeIs('pengumuman.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-bullhorn"></i>
            <p>Pengumuman</p>
          </a>
        </li>

        {{-- Notifikasi (semua role) --}}
        <li class="nav-item">
          <a href="{{ route('notifikasi.index') }}" class="nav-link {{ request()->routeIs('notifikasi.*') ? 'active' : '' }}">
            <i class="nav-icon far fa-bell"></i>
            <p>Notifikasi</p>
          </a>
        </li>

        {{-- ===== ADMIN ===== --}}
        @if(auth()->user()->role == 0)

        <li class="nav-header">PENGGUNA</li>

        <li class="nav-item {{ request()->routeIs('admin.siswa.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('admin.siswa.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p>Data Siswa <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('admin.siswa.index') }}" class="nav-link {{ request()->routeIs('admin.siswa.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Daftar Siswa</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.siswa.create') }}" class="nav-link {{ request()->routeIs('admin.siswa.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Tambah Siswa</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item {{ request()->routeIs('admin.guru.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('admin.guru.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-chalkboard-teacher"></i>
            <p>Data Guru <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('admin.guru.index') }}" class="nav-link {{ request()->routeIs('admin.guru.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Daftar Guru</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.guru.create') }}" class="nav-link {{ request()->routeIs('admin.guru.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Tambah Guru</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item">
          <a href="{{ route('admin.orangtua.index') }}" class="nav-link {{ request()->routeIs('admin.orangtua.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-friends"></i>
            <p>Orang Tua / Wali</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('admin.audit-log.index') }}" class="nav-link {{ request()->routeIs('admin.audit-log.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-history"></i>
            <p>Audit Log</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('admin.laporan.index') }}" class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-contract"></i>
            <p>Laporan</p>
          </a>
        </li>

        <li class="nav-header">AKADEMIK</li>

        <li class="nav-item {{ request()->routeIs('admin.jadwal.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-week"></i>
            <p>Jadwal <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('admin.jadwal.index') }}" class="nav-link {{ request()->routeIs('admin.jadwal.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Lihat Jadwal</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.jadwal.create') }}" class="nav-link {{ request()->routeIs('admin.jadwal.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Tambah Jadwal</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item {{ request()->routeIs('admin.jurusan.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('admin.jurusan.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-graduation-cap"></i>
            <p>Jurusan <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('admin.jurusan.index') }}" class="nav-link {{ request()->routeIs('admin.jurusan.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Daftar Jurusan</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.jurusan.create') }}" class="nav-link {{ request()->routeIs('admin.jurusan.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Tambah Jurusan</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item {{ request()->routeIs('admin.kelas.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('admin.kelas.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-door-open"></i>
            <p>Kelas <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('admin.kelas.index') }}" class="nav-link {{ request()->routeIs('admin.kelas.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Daftar Kelas</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.kelas.create') }}" class="nav-link {{ request()->routeIs('admin.kelas.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Tambah Kelas</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item {{ request()->routeIs('admin.mapel.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('admin.mapel.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book"></i>
            <p>Mata Pelajaran <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('admin.mapel.index') }}" class="nav-link {{ request()->routeIs('admin.mapel.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Daftar Mapel</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.mapel.create') }}" class="nav-link {{ request()->routeIs('admin.mapel.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Tambah Mapel</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item">
          <a href="{{ route('admin.absensi.index') }}" class="nav-link {{ request()->routeIs('admin.absensi.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-clipboard-list"></i>
            <p>Absensi</p>
          </a>
        </li>

        <li class="nav-item {{ request()->routeIs('admin.nilai.*') || request()->routeIs('admin.raport') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('admin.nilai.*') || request()->routeIs('admin.raport') ? 'active' : '' }}">
            <i class="nav-icon fas fa-star-half-alt"></i>
            <p>Penilaian <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('admin.nilai.index') }}" class="nav-link {{ request()->routeIs('admin.nilai.*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Data Nilai</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.raport') }}" class="nav-link {{ request()->routeIs('admin.raport') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Raport</p>
              </a>
            </li>
          </ul>
        </li>

        @include('layouts.partials.sidebar-ujian', ['ikon' => 'fa-clipboard-list'])

        <li class="nav-item {{ request()->routeIs('admin.pkl*') || request()->routeIs('admin.pkl-tempat.*') || request()->routeIs('admin.pkl-penempatan.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('admin.pkl*') || request()->routeIs('admin.pkl-tempat.*') || request()->routeIs('admin.pkl-penempatan.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-briefcase"></i>
            <p>PKL <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('admin.pkl.index') }}" class="nav-link {{ request()->routeIs('admin.pkl.index') || request()->routeIs('admin.pkl.detail') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Overview PKL</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.pkl-tempat.index') }}" class="nav-link {{ request()->routeIs('admin.pkl-tempat.*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Tempat PKL</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.pkl-penempatan.index') }}" class="nav-link {{ request()->routeIs('admin.pkl-penempatan.*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Penempatan</p>
              </a>
            </li>
          </ul>
        </li>

        {{-- Fase 8: Kelola Pengumuman (admin only) --}}
        <li class="nav-item">
          <a href="{{ route('admin.pengumuman.index') }}" class="nav-link {{ request()->routeIs('admin.pengumuman.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-bullhorn"></i>
            <p>Kelola Pengumuman</p>
          </a>
        </li>

        <li class="nav-header">KESISWAAN</li>

        <li class="nav-item {{ request()->routeIs('admin.pelanggaran.*') ? 'menu-open' : '' }}">
          <a href="{{ route('admin.pelanggaran.index') }}" class="nav-link {{ request()->routeIs('admin.pelanggaran.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-exclamation-triangle"></i>
            <p>Pelanggaran</p>
          </a>
        </li>

        <li class="nav-item {{ request()->routeIs('admin.prestasi.*') ? 'menu-open' : '' }}">
          <a href="{{ route('admin.prestasi.index') }}" class="nav-link {{ request()->routeIs('admin.prestasi.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-trophy"></i>
            <p>Prestasi</p>
          </a>
        </li>

        <li class="nav-item {{ request()->routeIs('admin.ekskul.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('admin.ekskul.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i>
            <p>Ekstrakurikuler <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('admin.ekskul.index') }}" class="nav-link {{ request()->routeIs('admin.ekskul.index') || request()->routeIs('admin.ekskul.create') || request()->routeIs('admin.ekskul.edit') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Daftar Ekskul</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.ekskul.create') }}" class="nav-link {{ request()->routeIs('admin.ekskul.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Tambah Ekskul</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item {{ request()->routeIs('admin.surat.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('admin.surat.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>Surat Keterangan <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('admin.surat.index') }}" class="nav-link {{ request()->routeIs('admin.surat.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Log Surat</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.surat.create') }}" class="nav-link {{ request()->routeIs('admin.surat.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Buat Surat Baru</p>
              </a>
            </li>
          </ul>
        </li>

        {{-- Fase 11: CMS Website --}}
        <li class="nav-header">WEBSITE</li>

        <li class="nav-item">
          <a href="{{ route('admin.cms.pengaturan') }}" class="nav-link {{ request()->routeIs('admin.cms.pengaturan*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-sliders-h"></i>
            <p>Pengaturan</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('admin.cms.halaman.index') }}" class="nav-link {{ request()->routeIs('admin.cms.halaman*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>Halaman Statis</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('admin.jurusan.index') }}" class="nav-link {{ request()->routeIs('admin.jurusan.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-graduation-cap"></i>
            <p>Program Keahlian</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('admin.cms.artikel.index') }}" class="nav-link {{ request()->routeIs('admin.cms.artikel*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-newspaper"></i>
            <p>Artikel / Berita</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('admin.cms.galeri.index') }}" class="nav-link {{ request()->routeIs('admin.cms.galeri*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-images"></i>
            <p>Galeri Foto</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('admin.organisasi.index') }}" class="nav-link {{ request()->routeIs('admin.organisasi.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-sitemap"></i>
            <p>Struktur Organisasi</p>
          </a>
        </li>

        {{-- Fase 15: Pesan Kontak --}}
        <li class="nav-item">
          <a href="{{ route('admin.kontak.index') }}" class="nav-link {{ request()->routeIs('admin.kontak.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-envelope"></i>
            <p>
              Pesan Kontak
              @if(!empty($unreadKontakCount) && $unreadKontakCount > 0)
                <span class="badge badge-danger badge-pill right">{{ $unreadKontakCount }}</span>
              @endif
            </p>
          </a>
        </li>

        {{-- Fase 9: PPDB --}}
        <li class="nav-header">PPDB</li>
        <li class="nav-item {{ request()->routeIs('admin.ppdb.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('admin.ppdb.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-school"></i>
            <p>Penerimaan Siswa <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('admin.ppdb.index') }}" class="nav-link {{ request()->routeIs('admin.ppdb.index') || request()->routeIs('admin.ppdb.show') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Data Pendaftar</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.ppdb.laporan') }}" class="nav-link {{ request()->routeIs('admin.ppdb.laporan') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Laporan PPDB</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.ppdb.gelombang.index') }}" class="nav-link {{ request()->routeIs('admin.ppdb.gelombang*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Gelombang</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.ppdb.persyaratan.index') }}" class="nav-link {{ request()->routeIs('admin.ppdb.persyaratan*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Persyaratan</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.ppdb.faq.index') }}" class="nav-link {{ request()->routeIs('admin.ppdb.faq*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>FAQ</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-header">KONFIGURASI</li>

        <li class="nav-item {{ request()->routeIs('admin.tahun-ajaran.*') || request()->routeIs('admin.semester.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('admin.tahun-ajaran.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>Tahun Ajaran <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('admin.tahun-ajaran.index') }}" class="nav-link {{ request()->routeIs('admin.tahun-ajaran.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Kelola Tahun Ajaran</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.tahun-ajaran.create') }}" class="nav-link {{ request()->routeIs('admin.tahun-ajaran.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Tambah Tahun Ajaran</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item">
          <a href="{{ route('admin.import.index') }}" class="nav-link {{ request()->routeIs('admin.import.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-excel"></i>
            <p>Import Data Excel</p>
          </a>
        </li>

        @endif
        {{-- ===== END ADMIN ===== --}}

        {{-- ===== GURU ===== --}}
        @if(auth()->user()->role == 1)
        @php $guruPerms = auth()->user()->guru; @endphp

        <li class="nav-header">AKADEMIK</li>

        @if($guruPerms?->canAccess('menu_jadwal'))
        <li class="nav-item">
          <a href="{{ route('guru.jadwal') }}" class="nav-link {{ request()->routeIs('guru.jadwal') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-week"></i>
            <p>Jadwal Mengajar</p>
          </a>
        </li>
        @endif

        @if($guruPerms?->canAccess('menu_mapel'))
        <li class="nav-item">
          <a href="{{ route('guru.mapel') }}" class="nav-link {{ request()->routeIs('guru.mapel') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book"></i>
            <p>Mata Pelajaran</p>
          </a>
        </li>
        @endif

        @if($guruPerms?->canAccess('menu_absensi'))
        <li class="nav-item {{ request()->routeIs('guru.absensi.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('guru.absensi.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-clipboard-list"></i>
            <p>Absensi <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('guru.absensi.index') }}" class="nav-link {{ request()->routeIs('guru.absensi.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Input Absensi</p>
              </a>
            </li>
          </ul>
        </li>
        @endif

        @if($guruPerms?->canAccess('menu_nilai'))
        <li class="nav-item {{ request()->routeIs('guru.nilai.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('guru.nilai.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-star-half-alt"></i>
            <p>Penilaian <i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('guru.nilai.index') }}" class="nav-link {{ request()->routeIs('guru.nilai.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i><p>Input Nilai</p>
              </a>
            </li>
          </ul>
        </li>
        @endif

        @if($guruPerms?->canAccess('menu_pkl'))
        <li class="nav-item">
          <a href="{{ route('guru.pkl.index') }}" class="nav-link {{ request()->routeIs('guru.pkl.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-briefcase"></i>
            <p>PKL Bimbingan</p>
          </a>
        </li>
        @endif

        {{-- Fase 6: Ekskul Binaan --}}
        @if($guruPerms?->canAccess('menu_ekskul'))
        <li class="nav-item {{ request()->routeIs('guru.ekskul.*') ? 'menu-open' : '' }}">
          <a href="{{ route('guru.ekskul.index') }}" class="nav-link {{ request()->routeIs('guru.ekskul.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i>
            <p>Ekskul Binaan</p>
          </a>
        </li>
        @endif

        <li class="nav-header">E-LEARNING</li>

        @if($guruPerms?->canAccess('menu_mapel'))
        <li class="nav-item {{ request()->routeIs('guru.materi.*') ? 'menu-open' : '' }}">
          <a href="{{ route('guru.materi.index') }}" class="nav-link {{ request()->routeIs('guru.materi.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book-open"></i>
            <p>Materi Pelajaran</p>
          </a>
        </li>
        @endif

        @if($guruPerms?->canAccess('menu_nilai'))
        <li class="nav-item {{ request()->routeIs('guru.tugas.*') ? 'menu-open' : '' }}">
          <a href="{{ route('guru.tugas.index') }}" class="nav-link {{ request()->routeIs('guru.tugas.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tasks"></i>
            <p>Tugas Online</p>
          </a>
        </li>
        @endif

        @if($guruPerms?->canAccess('menu_nilai'))
        @include('layouts.partials.sidebar-ujian', ['ikon' => 'fa-file-alt'])
        @endif

        @endif
        {{-- ===== END GURU ===== --}}

        {{-- ===== SISWA ===== --}}
        @if(auth()->user()->role == 2)

        <li class="nav-header">AKADEMIK</li>

        <li class="nav-item">
          <a href="{{ route('siswa.jadwal') }}" class="nav-link {{ request()->routeIs('siswa.jadwal') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-week"></i>
            <p>Jadwal Pelajaran</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('siswa.mapel') }}" class="nav-link {{ request()->routeIs('siswa.mapel') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>Mata Pelajaran</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('siswa.absensi') }}" class="nav-link {{ request()->routeIs('siswa.absensi') ? 'active' : '' }}">
            <i class="nav-icon fas fa-clipboard-check"></i>
            <p>Absensi Saya</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('siswa.nilai') }}" class="nav-link {{ request()->routeIs('siswa.nilai') ? 'active' : '' }}">
            <i class="nav-icon fas fa-star-half-alt"></i>
            <p>Nilai Saya</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('siswa.raport') }}" class="nav-link {{ request()->routeIs('siswa.raport') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>Raport</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('siswa.pkl') }}" class="nav-link {{ request()->routeIs('siswa.pkl*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-briefcase"></i>
            <p>PKL Saya</p>
          </a>
        </li>

        {{-- Fase 6: Kesiswaan Siswa --}}
        <li class="nav-item">
          <a href="{{ route('siswa.kesiswaan') }}" class="nav-link {{ request()->routeIs('siswa.kesiswaan') ? 'active' : '' }}">
            <i class="nav-icon fas fa-id-card"></i>
            <p>Kesiswaan Saya</p>
          </a>
        </li>

        <li class="nav-header">E-LEARNING</li>

        <li class="nav-item">
          <a href="{{ route('siswa.materi.index') }}" class="nav-link {{ request()->routeIs('siswa.materi.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book-open"></i>
            <p>Materi Pelajaran</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('siswa.tugas.index') }}" class="nav-link {{ request()->routeIs('siswa.tugas.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tasks"></i>
            <p>Tugas Online</p>
          </a>
        </li>

        <li class="nav-item" data-ujian-nav>
          <a href="{{ route('ujian.siswa.index') }}" class="nav-link {{ request()->routeIs('ujian.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>Ujian Online</p>
          </a>
        </li>

        @endif
        {{-- ===== END SISWA ===== --}}

        {{-- ===== PENDAFTAR (role=3) ===== --}}
        @if(auth()->user()->role == 3)

        <li class="nav-header">PENDAFTARAN SAYA</li>

        <li class="nav-item">
          <a href="{{ route('pendaftar.dashboard') }}" class="nav-link {{ request()->routeIs('pendaftar.dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-clipboard-list"></i>
            <p>Status Pendaftaran</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('pendaftar.cetak') }}" target="_blank" class="nav-link">
            <i class="nav-icon fas fa-print"></i>
            <p>Cetak Bukti</p>
          </a>
        </li>

        @endif
        {{-- ===== END PENDAFTAR ===== --}}

        {{-- ===== ORANG TUA / WALI (role=4) ===== --}}
        @if(auth()->user()->role == 4)

        <li class="nav-header">PORTAL ORANG TUA</li>

        <li class="nav-item">
          <a href="{{ route('orangtua.dashboard') }}" class="nav-link {{ request()->routeIs('orangtua.dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-home"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('orangtua.nilai') }}" class="nav-link {{ request()->routeIs('orangtua.nilai') ? 'active' : '' }}">
            <i class="nav-icon fas fa-star-half-alt"></i>
            <p>Nilai Anak</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('orangtua.absensi') }}" class="nav-link {{ request()->routeIs('orangtua.absensi') ? 'active' : '' }}">
            <i class="nav-icon fas fa-clipboard-check"></i>
            <p>Absensi Anak</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('orangtua.kesiswaan') }}" class="nav-link {{ request()->routeIs('orangtua.kesiswaan') ? 'active' : '' }}">
            <i class="nav-icon fas fa-id-card"></i>
            <p>Kesiswaan Anak</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('orangtua.spp') }}" class="nav-link {{ request()->routeIs('orangtua.spp*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-money-bill-wave"></i>
            <p>Pembayaran SPP</p>
          </a>
        </li>

        @endif
        {{-- ===== END ORANG TUA ===== --}}

      </ul>
    </nav>
  </div>
</aside>
