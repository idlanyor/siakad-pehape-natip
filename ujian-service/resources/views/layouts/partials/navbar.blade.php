<!-- Navbar -->
@php $isDark = request()->cookie('dark_mode') === 'on'; @endphp
<nav class="main-header navbar navbar-expand {{ $isDark ? 'navbar-dark navbar-secondary' : 'navbar-white navbar-light' }}">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">

    {{-- Maintenance Mode Indicator (admin only) --}}
    @if(auth()->user() && auth()->user()->isAdmin() && app()->isDownForMaintenance())
    <li class="nav-item">
      <span class="nav-link">
        <span class="badge badge-warning">
          <i class="fas fa-tools mr-1"></i> MAINTENANCE
        </span>
      </span>
    </li>
    @endif

    {{-- Toggle Dark Mode --}}
    <li class="nav-item">
      <a href="#" class="nav-link" id="dark-mode-toggle" role="button" title="Mode Gelap/Terang">
        <i class="fas {{ $isDark ? 'fa-sun' : 'fa-moon' }}"></i>
      </a>
    </li>

    {{-- Pengumuman --}}
    <li class="nav-item">
      <a href="{{ route('pengumuman.index') }}" class="nav-link" title="Pengumuman">
        <i class="fas fa-bullhorn"></i>
      </a>
    </li>

    {{-- Notifikasi Bell --}}
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" title="Notifikasi">
        <i class="far fa-bell"></i>
        @if(!empty($unreadCount) && $unreadCount > 0)
          <span class="badge badge-warning navbar-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-header">
          {{ !empty($unreadCount) && $unreadCount > 0 ? $unreadCount . ' Notifikasi Baru' : 'Notifikasi' }}
        </span>
        <div class="dropdown-divider"></div>
        @forelse($notifikasiTerbaru ?? [] as $n)
          <a href="{{ $n->url ?? route('notifikasi.index') }}" class="dropdown-item {{ !$n->is_read ? 'font-weight-bold' : '' }}">
            <i class="fas fa-circle mr-2 {{ !$n->is_read ? 'text-primary' : 'text-muted' }}" style="font-size:.5rem;vertical-align:middle"></i>
            <div class="d-inline-block" style="max-width:220px;vertical-align:middle">
              <span class="d-block" style="white-space:normal;font-size:.85rem">{{ Str::limit($n->judul, 45) }}</span>
              <span class="text-muted" style="font-size:.75rem">{{ $n->created_at->diffForHumans() }}</span>
            </div>
          </a>
          <div class="dropdown-divider"></div>
        @empty
          <span class="dropdown-item text-muted text-center">Tidak ada notifikasi</span>
          <div class="dropdown-divider"></div>
        @endforelse
        <div class="dropdown-footer d-flex justify-content-between px-3 py-1">
          <a href="{{ route('notifikasi.index') }}" class="text-sm">Lihat Semua</a>
          <form action="{{ route('notifikasi.readAll') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-link p-0 text-sm">Tandai Semua Dibaca</button>
          </form>
        </div>
      </div>
    </li>

    {{-- Keamanan 2FA --}}
    <li class="nav-item">
      <a href="{{ route('profile.2fa') }}" class="nav-link" title="Autentikasi Dua Faktor">
        <i class="fas fa-shield-alt"></i>
        @if(auth()->user() && auth()->user()->hasTwoFactorEnabled())
          <span class="badge badge-success badge-pill">on</span>
        @endif
      </a>
    </li>

    {{-- Profil --}}
    <li class="nav-item">
      <a href="{{ route('profile.edit') }}" class="nav-link" title="Profil Saya">
        <img src="{{ auth()->user() ? auth()->user()->avatar_url : asset('dist/img/avatar5.png') }}" class="img-circle" alt="Foto Profil" style="width:24px;height:24px;object-fit:cover;vertical-align:middle">
      </a>
    </li>

    {{-- Logout --}}
    <li class="nav-item">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-link nav-link">
          <i class="fas fa-sign-out-alt"></i> Log Out
        </button>
      </form>
    </li>

  </ul>
</nav>
<!-- /.navbar -->
