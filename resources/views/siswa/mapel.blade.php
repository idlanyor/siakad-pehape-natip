@extends('layouts.admin')

@section('title', 'Daftar Mata Pelajaran')
@section('page_title', 'Mata Pelajaran')

@push('styles')
  <!-- DataTables -->
  <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-md-12 mt-3">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Daftar Jadwal & Mata Pelajaran</h3>
      </div>
      <!-- /.card-header -->
      <div class="card-body">
        <table id="tabel-mapel" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Mata Pelajaran</th>
              <th>Guru</th>
              <th>Waktu</th>
              <th>Hari</th>
            </tr>
          </thead>
          <tbody>
            @foreach($mapels as $mapel)
                @foreach($mapel->jadwals as $jadwal)
                <tr>
                  <td>{{ $mapel->kode }}</td>
                  <td>{{ $mapel->nama_mapel }}</td>
                  <td>{{ $mapel->dosen_pengampu }}</td>
                  <td>{{ $jadwal->jam_mulai }}-{{ $jadwal->jam_selesai }}</td>
                  <td>{{ $jadwal->hari }}</td>
                </tr>
                @endforeach
            @endforeach
          </tbody>
        </table>
      </div>
      <!-- /.card-body -->
    </div>
  </div>
</div>
@endsection

@push('scripts')
<!-- DataTables  & Plugins -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script>
  $(function () {
    $('#tabel-mapel').DataTable({
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
@endpush
