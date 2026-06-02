<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="{{ route('admin.dashboard') }}" class="brand-link">
    <img src="{{ asset('dist/img/AdminLTELogo.png') }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">Ujian Online</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="{{ Auth::user()->avatar_url ?? asset('dist/img/avatar.png') }}" class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="{{ route('profile.edit') }}" class="d-block">{{ Auth::user()->nama }}</a>
        <small class="text-muted">{{ Auth::user()->role_label }}</small>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <!-- Dashboard -->
        <li class="nav-item">
          <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        @if(Auth::user()->isAdmin())
        <!-- Periode Ujian -->
        <li class="nav-item {{ request()->routeIs('admin.periode.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('admin.periode.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>
              Periode Ujian
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('admin.periode.index') }}" class="nav-link {{ request()->routeIs('admin.periode.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Daftar Periode</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.periode.create') }}" class="nav-link {{ request()->routeIs('admin.periode.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Tambah Periode</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Fase Periode -->
        <li class="nav-item">
          <a href="{{ route('admin.fase.index') }}" class="nav-link {{ request()->routeIs('admin.fase.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tasks"></i>
            <p>Fase Periode</p>
          </a>
        </li>
        @endif

        <!-- Ujian -->
        <li class="nav-item {{ request()->routeIs('ujian.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('ujian.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>
              Manajemen Ujian
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('ujian.index') }}" class="nav-link {{ request()->routeIs('ujian.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Daftar Ujian</p>
              </a>
            </li>
            @if(Auth::user()->isGuru() || Auth::user()->isAdmin())
            <li class="nav-item">
              <a href="{{ route('ujian.create') }}" class="nav-link {{ request()->routeIs('ujian.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Buat Ujian Baru</p>
              </a>
            </li>
            @endif
          </ul>
        </li>

        @if(Auth::user()->isGuru() || Auth::user()->isAdmin())
        <!-- Bank Soal -->
        <li class="nav-item">
          <a href="{{ route('bank-soal.index') }}" class="nav-link {{ request()->routeIs('bank-soal.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-database"></i>
            <p>Bank Soal</p>
          </a>
        </li>

        <!-- Monitoring Proktor -->
        <li class="nav-item">
          <a href="{{ route('proktor.dashboard') }}" class="nav-link {{ request()->routeIs('proktor.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-video"></i>
            <p>
              Monitoring Proktor
              <span class="badge badge-danger right" id="active-sessions-badge">0</span>
            </p>
          </a>
        </li>

        <!-- Hasil & Statistik -->
        <li class="nav-item {{ request()->routeIs('hasil.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('hasil.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>
              Hasil & Statistik
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('hasil.index') }}" class="nav-link {{ request()->routeIs('hasil.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Daftar Hasil</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('hasil.statistik') }}" class="nav-link {{ request()->routeIs('hasil.statistik') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Statistik Ujian</p>
              </a>
            </li>
          </ul>
        </li>
        @endif

        @if(Auth::user()->isSiswa())
        <!-- Ujian Saya (Siswa) -->
        <li class="nav-item">
          <a href="{{ route('siswa.ujian.index') }}" class="nav-link {{ request()->routeIs('siswa.ujian.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-clipboard-list"></i>
            <p>Ujian Tersedia</p>
          </a>
        </li>

        <!-- Riwayat Ujian -->
        <li class="nav-item">
          <a href="{{ route('siswa.riwayat.index') }}" class="nav-link {{ request()->routeIs('siswa.riwayat.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-history"></i>
            <p>Riwayat Ujian</p>
          </a>
        </li>
        @endif

        <!-- Divider -->
        <li class="nav-header">PENGATURAN</li>

        <!-- Profil -->
        <li class="nav-item">
          <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user"></i>
            <p>Profil Saya</p>
          </a>
        </li>

        @if(Auth::user()->isAdmin())
        <!-- Pengaturan Sistem -->
        <li class="nav-item">
          <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
            <i class="nav-icon fas fa-cog"></i>
            <p>Pengaturan Sistem</p>
          </a>
        </li>

        <!-- Audit Log -->
        <li class="nav-item">
          <a href="{{ route('admin.audit-log.index') }}" class="nav-link {{ request()->routeIs('admin.audit-log.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-clipboard-list"></i>
            <p>Audit Log</p>
          </a>
        </li>
        @endif

        <!-- Logout -->
        <li class="nav-item">
          <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
          </a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
        </li>

      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>