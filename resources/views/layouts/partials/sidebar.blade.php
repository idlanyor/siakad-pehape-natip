<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="#" class="brand-link">
    <img src="{{ asset('dist/img/halal.jpeg') }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">Simakad SMK 48</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
          <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') || request()->routeIs('siswa.dashboard') || request()->routeIs('dosen.dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        @if(auth()->user()->role == 1)
        <li class="nav-item">
          <a href="{{ route('dosen.matkul') }}" class="nav-link {{ request()->routeIs('dosen.matkul') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book"></i>
            <p>Mata Pelajaran</p>
          </a>
        </li>
        @endif

        @if(auth()->user()->role == 2)
        <li class="nav-item">
          <a href="{{ route('siswa.matkul') }}" class="nav-link {{ request()->routeIs('siswa.matkul') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>Mata Pelajaran</p>
          </a>
        </li>
        {{-- Anda bisa menambahkan link siswa lainnya di sini --}}
        @endif
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
