<?php
function activeSidebar($comparator): string
{
  return strstr($_SERVER['PHP_SELF'], $comparator) ? 'active' : '';
}
?>
<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="index3.html" class="brand-link">
    <img src="dist/img/halal.jpeg" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">Simakad Roi</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class
              with font-awesome or any other icon font library -->
        <li class="nav-item ">
          <a href="index.php" class="nav-link <?= activeSidebar('index') ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              Dashboard
            </p>
          </a>
        </li>
        <li class="nav-item ">
          <a href="matkul.php" class="nav-link <?= activeSidebar('matkul') ?>">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>
              Mata Kuliah
            </p>
          </a>
        </li>
        <li class="nav-item ">
          <a href="jadwal.php" class="nav-link <?= activeSidebar('jadwal') ?>">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>
              Jadwal Kuliah
            </p>
          </a>
        </li>
        <li class="nav-item ">
          <a href="penugasan.php" class="nav-link <?= activeSidebar('penugasan') ?>">
            <i class="nav-icon fas fa-book-open"></i>
            <p>
              Penugasan
            </p>
          </a>
        </li>
        <li class="nav-item ">
          <a href="peminatan.php" class="nav-link <?= activeSidebar('peminatan') ?>">
            <i class="nav-icon fas fa-layer-group"></i>
            <p>
              Peminatan MK
            </p>
          </a>
        </li>
        <li class="nav-item ">
          <a href="daftar-nilai.php" class="nav-link <?= activeSidebar('daftar-nilai') ?>">
            <i class="nav-icon fas fa-list-check"></i>
            <p>
              Daftar Nilai
            </p>
          </a>
        </li>
        <li class="nav-item ">
          <a href="account.php" class="nav-link <?= activeSidebar('account') ?>">
            <i class="nav-icon fas fa-user-cog"></i>
            <p>
              Akun Saya
            </p>
          </a>
        </li>
        <hr>
        <li class="nav-item">
          <a href="logout.php" class="nav-link">
            <i class="nav-icon fa fa-sign-out"></i>
            <p>
              Log Out
            </p>
          </a>
        </li>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>