<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: index.php');
}

include 'config/koneksi.php';
$stmt = $koneksi->prepare("SELECT * FROM mahasiswa WHERE id_user=?");
$stmt->bind_param('i', $_SESSION['id_user']);
$stmt->execute();
$mahasiswa = $stmt->get_result();
$stmt->close();
$stmt = $koneksi->prepare("SELECT nama_prodi FROM nama_tabel_prodi WHERE id_prodi = ?");
$stmt->bind_param('i',$mahasiswa['id_prodi']);
$stmt->execute();
$prodi = $stmt->get_result();
if ($mahasiswa->num_rows > 0) {
  var_dump($mahasiswa->fetch_assoc());
}


?>
<!DOCTYPE html>

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

          <!-- /.row -->
          <div class="row pt-3">
            <div class="col-md-4">
              <?php foreach($mahasiswa as $mhs): ?>
              <!-- Widget: user widget style 2 -->
              <div class="card card-widget widget-user-2">
                <!-- Add the bg color to the header using any of the bg-* classes -->
                <div class="widget-user-header bg-warning">
                  <div class="widget-user-image">
                    <img class="img-circle elevation-2" src="../dist/img/user7-128x128.jpg" alt="User Avatar">
                  </div>
                  <!-- /.widget-user-image -->
                  <h3 class="widget-user-username"><?= $mhs['nama'] ?></h3>
                  <h5 class="widget-user-desc"><?= $mhs['nim'] ?></h5>
                </div>
                <div class="card-footer p-0">
                  <ul class="nav flex-column">
                    <li class="nav-item">
                      <a href="#" class="nav-link">
                        Kelas <span class="float-right badge bg-primary">C3.4</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="#" class="nav-link">
                        Prodi <span class="float-right badge bg-info"><?= $mhs['prodi'] ?></span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="#" class="nav-link">
                        Tahun Masuk <span class="float-right badge bg-success">2022</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="#" class="nav-link">
                        Jumlah Mata Kuliah <span class="float-right badge bg-danger">10</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <?php endforeach ?>
              <!-- /.widget-user -->
            </div>
            <!-- /.col -->
            <div class="col-md-8">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title text-bold">Kuliah hari ini</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body p-0">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th style="width: 10px">Kode</th>
                        <th>Mata Kuliah</th>
                        <th>Waktu</th>
                        <th>#</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>#43</td>
                        <td>Sistem Basis Data</td>
                        <td>19.30 - 20.30 WIB</td>
                        <td><a class="badge bg-success">Link Zoom</a></td>
                      </tr>
                      <tr>
                        <td>#33</td>
                        <td>Struktur Data</td>
                        <td>20.30 - 21.30 WIB</td>
                        <td><span class="badge bg-danger">Link Zoom</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <!-- /.card-body -->
              </div>
            </div>
          </div>
          <!-- /.row -->
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
                    <a href="#" class="list-group-item list-group-item-action text-primary">Handling class through mySWU</a>
                    <a href="#" class="list-group-item list-group-item-action text-primary">SOP Perkuliahan</a>
                  </div>
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