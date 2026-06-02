@extends('layouts.admin')

@section('title', 'Daftar Ujian')

@section('page_title', 'Daftar Ujian')

@push('styles')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<!-- Daterangepicker -->
<link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Filter Ujian</h3>
        <div class="card-tools">
          @if(Auth::user()->isGuru() || Auth::user()->isAdmin())
          <a href="{{ route('ujian.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Buat Ujian Baru
          </a>
          @endif
        </div>
      </div>
      <div class="card-body">
        <form id="filter-form" class="form-horizontal">
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label>Periode</label>
                <select name="periode_id" id="periode_id" class="form-control select2">
                  <option value="">Semua Periode</option>
                  @foreach($periodes as $periode)
                  <option value="{{ $periode->id }}" {{ request('periode_id') == $periode->id ? 'selected' : '' }}>
                    {{ $periode->nama }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Kelas</label>
                <select name="kelas_id" id="kelas_id" class="form-control select2">
                  <option value="">Semua Kelas</option>
                  <!-- Akan diisi via AJAX dari SIAKAD -->
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Mata Pelajaran</label>
                <select name="mapel_id" id="mapel_id" class="form-control select2">
                  <option value="">Semua Mapel</option>
                  <!-- Akan diisi via AJAX dari SIAKAD -->
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Status</label>
                <select name="status" id="status" class="form-control">
                  <option value="">Semua Status</option>
                  <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                  <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                  <option value="berlangsung" {{ request('status') == 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                  <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Rentang Tanggal</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                  </div>
                  <input type="text" class="form-control" id="daterange" name="daterange" value="{{ request('daterange') }}">
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>&nbsp;</label>
                <div>
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                  </button>
                  <a href="{{ route('ujian.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                  </a>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Data Ujian</h3>
      </div>
      <div class="card-body">
        <table id="ujian-table" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th width="5%">No</th>
              <th>Judul</th>
              <th>Periode</th>
              <th>Kelas</th>
              <th>Mapel</th>
              <th>Guru</th>
              <th>Waktu</th>
              <th>Status</th>
              <th>Soal</th>
              <th width="15%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <!-- DataTables will populate this -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<!-- DataTables -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}">
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<!-- Select2 -->
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<!-- Daterangepicker -->
<script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>

<script>
$(function() {
  // Initialize Select2
  $('.select2').select2({
    theme: 'bootstrap4'
  });

  // Initialize Daterangepicker
  $('#daterange').daterangepicker({
    autoUpdateInput: false,
    locale: {
      cancelLabel: 'Clear',
      format: 'YYYY-MM-DD'
    }
  });

  $('#daterange').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
  });

  $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
  });

  // Initialize DataTable
  var table = $('#ujian-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '{{ route('ujian.index') }}',
      data: function(d) {
        d.periode_id = $('#periode_id').val();
        d.kelas_id = $('#kelas_id').val();
        d.mapel_id = $('#mapel_id').val();
        d.status = $('#status').val();
        d.daterange = $('#daterange').val();
      }
    },
    columns: [
      { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
      { data: 'judul', name: 'judul' },
      { data: 'periode_nama', name: 'periode.nama' },
      { data: 'kelas_nama', name: 'kelas_nama' },
      { data: 'mapel_nama', name: 'mapel_nama' },
      { data: 'guru_nama', name: 'guru_nama' },
      { data: 'waktu', name: 'waktu_mulai' },
      { data: 'status_badge', name: 'is_published', orderable: false },
      { data: 'jumlah_soal_badge', name: 'jumlah_soal', orderable: false },
      { data: 'action', name: 'action', orderable: false, searchable: false }
    ],
    responsive: true,
    order: [[1, 'desc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
    }
  });

  // Filter form submit
  $('#filter-form').on('submit', function(e) {
    e.preventDefault();
    table.draw();
  });

  // Delete confirmation
  $(document).on('click', '.btn-delete', function(e) {
    e.preventDefault();
    var form = $(this).closest('form');
    
    Swal.fire({
      title: 'Konfirmasi Hapus',
      text: 'Apakah Anda yakin ingin menghapus ujian ini?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then(function(result) {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });

  // Publish confirmation
  $(document).on('click', '.btn-publish', function(e) {
    e.preventDefault();
    var form = $(this).closest('form');
    
    Swal.fire({
      title: 'Konfirmasi Publish',
      text: 'Ujian yang sudah dipublish akan dapat diakses oleh siswa. Lanjutkan?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Publish',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then(function(result) {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });

  // Load reference data from SIAKAD
  loadReferenceData();
});

function loadReferenceData() {
  // TODO: Implement AJAX call to SIAKAD for kelas and mapel data
  // This will be implemented when JWT token handoff is ready
  console.log('Loading reference data from SIAKAD...');
}
</script>
@endpush
