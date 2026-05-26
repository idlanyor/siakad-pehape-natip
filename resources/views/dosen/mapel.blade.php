@extends('layouts.admin')

@section('title', 'Kelola Mata Pelajaran')
@section('page_title', 'Daftar Mata Pelajaran')

@push('styles')
  <!-- DataTables -->
  <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Semua Mata Pelajaran</h3>
      </div>
      <div class="card-body">
        <table id="tabel-mapel" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Mata Pelajaran</th>
              <th>Guru Pengampu</th>
              <th>Jadwal (Hari & Waktu)</th>
            </tr>
          </thead>
          <tbody>
            @foreach($mapels as $mapel)
            <tr>
              <td>{{ $mapel->kode }}</td>
              <td>{{ $mapel->nama_mapel }}</td>
              <td>{{ $mapel->dosen_pengampu }}</td>
              <td>
                @foreach($mapel->jadwals as $jadwal)
                  <span class="badge badge-info">{{ $jadwal->hari }}, {{ $jadwal->waktu }}</span><br>
                @endforeach
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
  $(function () {
    $('#tabel-mapel').DataTable({
      "responsive": true,
      "autoWidth": false,
    });
  });
</script>
@endpush
