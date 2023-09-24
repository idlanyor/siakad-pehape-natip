<?php
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['id_user'] === NULL || $_SESSION['role'] != 2) {
  header('Location: index.php');
}

include '../config/fungsi.php';
$query = "SELECT 
m.nama, m.nim, m.tanggal_lahir, p.nama_prodi
FROM mahasiswa m
INNER JOIN prodi p ON m.id_prodi = p.id_prodi
WHERE m.id_user = ?;";
//panggil fungsi untuk get query
$mahasiswa = dbGetQuery($query, 'i', $_SESSION['id_user']);

?>
<?php include_once '../template/head.php' ?>

<!-- /.row -->
<div class="row pt-3">
  <div class="col-md-4">
    <?php foreach ($mahasiswa as $mhs) : ?>
      <!-- Widget: user widget style 2 -->
      <div class="card card-widget widget-user-2">
        <!-- Add the bg color to the header using any of the bg-* classes -->
        <div class="widget-user-header bg-info">
          <div class="widget-user-image">
            <img class="img-circle bg-white img-thumbnail elevation-2" src="/dist/img/user7-128x128.png" alt="User Avatar">
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
                Prodi <span class="float-right badge bg-info"><?= $mhs['nama_prodi'] ?></span>
              </a>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                Tanggal Lahir <span class="float-right badge bg-success"><?= date('d M Y', strtotime($mhs['tanggal_lahir'])) ?></span>
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
              <td><a href="#" class="badge bg-success">Link Zoom</a></td>
            </tr>
            <tr>
              <td>#33</td>
              <td>Struktur Data</td>
              <td>20.30 - 21.30 WIB</td>
              <td><a href="#" class="badge bg-danger">Link Zoom</a></td>
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


<?php include_once '../template/foot.php' ?>