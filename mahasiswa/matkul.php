<?php include_once '../template/head.php' ?>
<div class="row">
  <div class="col-md-12 mt-3">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Daftar Jadwal &  Mata Kuliah <strong>S1 Sistem Informasi</strong></h3>
      </div>
      <!-- /.card-header -->
      <div class="card-body">
        <table id="tabel-matkul" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Mata Kuliah</th>
              <th>Dosen</th>
              <th>Waktu</th>
              <th>Hari</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#44</td>
              <td>Basis Data</td>
              <td>Siti Delimasari,S.Kom</td>
              <td>18:30 - 19:30</td>
              <td>Selasa</td>
            </tr>
            <tr>
              <td>#33</td>
              <td>Struktur Data</td>
              <td>Rahmam Manto,S.Kom</td>
              <td>19:30 - 20:30</td>
              <td>Rabu</td>
            </tr>
            
          </tbody>
        </table>
      </div>
      <!-- /.card-body -->
    </div>
  </div>
</div>
<!-- DataTables  & Plugins -->
<script src="plugins/datatables/jquery.dataTables.min.js" defer></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js" defer></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js" defer></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js" defer></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js" defer></script>
<script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js" defer></script>
<script src="plugins/jszip/jszip.min.js" defer></script>
<script src="plugins/pdfmake/pdfmake.min.js" defer></script>
<script src="plugins/pdfmake/vfs_fonts.js"></script>
<script src="plugins/datatables-buttons/js/buttons.html5.min.js" defer></script>
<script src="plugins/datatables-buttons/js/buttons.print.min.js" defer></script>
<script src="plugins/datatables-buttons/js/buttons.colVis.min.js" defer></script>
<script defer>
  $(function () {
    $('#tabel-matkul').DataTable({
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true,
      "responsive": true,
    });
  });
</script>
<?php include_once '../template/foot.php' ?>