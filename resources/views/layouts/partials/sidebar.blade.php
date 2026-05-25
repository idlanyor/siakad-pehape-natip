<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="#" class="brand-link">
    <img src="{{ asset('dist/img/halal.jpeg') }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">Simakad SMK 48</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        {{-- Dashboard --}}
        <li class="nav-item">
          <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('*.dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
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

        @endif
        {{-- ===== END ADMIN ===== --}}

        {{-- ===== GURU ===== --}}
        @if(auth()->user()->role == 1)

        <li class="nav-header">AKADEMIK</li>

        <li class="nav-item">
          <a href="{{ route('dosen.jadwal') }}" class="nav-link {{ request()->routeIs('dosen.jadwal') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-week"></i>
            <p>Jadwal Mengajar</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('dosen.matkul') }}" class="nav-link {{ request()->routeIs('dosen.matkul') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book"></i>
            <p>Mata Pelajaran</p>
          </a>
        </li>

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
          <a href="{{ route('siswa.matkul') }}" class="nav-link {{ request()->routeIs('siswa.matkul') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>Mata Pelajaran</p>
          </a>
        </li>

        @endif
        {{-- ===== END SISWA ===== --}}

      </ul>
    </nav>
  </div>
</aside>
