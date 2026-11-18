{{-- Menu modul Ujian Online untuk admin dan guru. data-ujian-nav dipakai
     resources/js/ujian.js untuk menyinkronkan class active saat navigasi Inertia. --}}
@php
    $user = auth()->user();
    $perluKoreksi = $user->isGuru() && $user->guru
        ? \App\Modules\Ujian\Services\PenilaianService::jumlahPerluKoreksi($user->guru)
        : 0;
    $menu = [
        ['ujian.dashboard', 'ujian.dashboard', 'Ringkasan'],
        ['ujian.ujian.index', 'ujian.ujian.*|ujian.soal.*|ujian.hasil.*', $user->isAdmin() ? 'Semua Ujian' : 'Ujian Saya'],
        ['ujian.bank-soal.index', 'ujian.bank-soal.*', 'Bank Soal'],
        ['ujian.periode.index', 'ujian.periode.*', 'Periode'],
    ];
@endphp
<li class="nav-item {{ request()->routeIs('ujian.*') ? 'menu-open' : '' }}" data-ujian-nav>
  <a href="#" class="nav-link {{ request()->routeIs('ujian.*') ? 'active' : '' }}">
    <i class="nav-icon fas {{ $ikon }}"></i>
    <p>
      Ujian Online
      <i class="fas fa-angle-left right"></i>
      @if($perluKoreksi > 0)
        <span class="badge badge-warning right" title="Jawaban essay belum dinilai">{{ $perluKoreksi }}</span>
      @endif
    </p>
  </a>
  <ul class="nav nav-treeview">
    @foreach ($menu as [$route, $pola, $label])
      <li class="nav-item">
        <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs(...explode('|', $pola)) ? 'active' : '' }}">
          <i class="far fa-circle nav-icon"></i><p>{{ $label }}</p>
        </a>
      </li>
    @endforeach
  </ul>
</li>
