<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: index.php');
}
?>
<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard <?= $_SESSION['username']; ?> | Simakad</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">

    <?php include 'navbar.php' ?>
    <?php include 'sidebar.php' ?>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Main content -->
      <div class="content">
        <div class="container-fluid">
          <!-- Info boxes -->
          <div class="row pt-3">
            <div class="col-12 col-sm-6 col-md-3">
              <div class="info-box">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">CPU Traffic</span>
                  <span class="info-box-number">
                    10
                    <small>%</small>
                  </span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-3">
              <div class="info-box mb-3">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-thumbs-up"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Likes</span>
                  <span class="info-box-number">41,410</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix hidden-md-up"></div>

            <div class="col-12 col-sm-6 col-md-3">
              <div class="info-box mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-shopping-cart"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Sales</span>
                  <span class="info-box-number">760</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-3">
              <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">New Members</span>
                  <span class="info-box-number">2,000</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
          <div class="row">
            <div class="col-lg-12 mt-3">
              <div class="card card-primary card-outline">
                <div class="card-header">
                  <h2 class="card-title text-bold">Pengumuman</h2>
                </div>
                <div class="card-body">
                  <div class="list-group">
                    <div class="list-group-item list-group-item-action">
                      Selamat menempuh Ujian Akhir Semester Genap 2022/2023 Semoga diberikan kelancaran dalam mengerjakan soal. Silahkan isi kuisioner di : https://forms.gle/DVxCB3EMJJeetaZ49
                    </div>
                    <div class="list-group-item list-group-item-action">
                      Pengumuman, diberitahukan kepada mahasiswa semester 5, jurusan S1 Teknik Informatika dan S1 Sistem Informasi untuk mengikuti sosialisasi MBKM, pada Hari Sabtu Tanggal 27 Agustus 2022, jam 13:00. Link ( https://us06web.zoom.us/j/88245555456?pwd=dnU1VTFQNzU3dFdGa1FCT1g2R3RGdz09 )
                    </div>
                    <div class="list-group-item list-group-item-action">
                      Pengumuman diberitahukan kepada seluruh Mahasiswa STMIK Widya Utama, Untuk segera mengisi peminatan matakuliah semester Ganjil, dimulai dari tanggal 23 Agustus 2022 sampai dengan 27 Agustus 2022
                    </div>
                    <div class="list-group-item list-group-item-action">
                      Pengumuman Diberitahukan kepada seluruh mahasiswa STMIK Widya Utama berdasarkan surat edaran dari LLDIKTI Wilayah VI Jawa tengah bahwa Pusat Layanan Pembiayaan Pendidikan akan melakukan penyaluran dan bantuan Bidikmisi/KIP Kuliah Semester Gasal Tahun 2022/2023 sehingga untuk memperlancar penilaian hasil UAS dimohon setiap mahasiswa pada saat mengerjakan Ujian Akhir Semester (UAS) sesuai dengan jadwal dihari tersebut , waktu pengerjakan UAS hanya satu hari dimulai hari jam 00.00 -23.59 WIB
                    </div>
                  </div>
                  <a href="#" class="list-group-item list-group-item-action text-info">Handling class through mySWU</a>
                  <a href="#" class="list-group-item list-group-item-action text-info">SOP Perkuliahan</a>
                </div>
              </div><!-- /.card -->
            </div>
          </div>
          <!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
      <!-- Control sidebar content goes here -->
      <div class="p-3">
        <h5>Title</h5>
        <p>Sidebar content</p>
      </div>
    </aside>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <footer class="main-footer">
      <!-- To the right -->
      <div class="float-right d-none d-sm-inline">
        Anything you want
      </div>
      <!-- Default to the left -->
      <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights reserved.
    </footer>
  </div>
  <!-- ./wrapper -->

  <!-- REQUIRED SCRIPTS -->

  <!-- jQuery -->
  <script src="plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="dist/js/adminlte.min.js"></script>
</body>

</html>