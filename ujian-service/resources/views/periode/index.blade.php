@extends('layouts.admin')

@section('title', 'Periode Ujian')

@section('page_title', 'Manajemen Periode Ujian')

@push('styles')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Daftar Periode Ujian</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-create">
            <i class="fas fa-plus"></i> Tambah Periode
          </button>
        </div>
      </div>
      <div class="card-body">
        <table id="periode-table" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th width="5%">No</th>
              <th>Nama Periode</th>
              <th>Tahun Ajaran</th>
              <th>Semester</th>
              <th>Tanggal Mulai</th>
              <th>Tanggal Selesai</th>
              <th>Status</th>
              <th>Fase</th>
              <th width="15%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($periodes as $index => $periode)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>
                <strong>{{ $periode->nama }}</strong>
                @if($periode->deskripsi)
                <br><small class="text-muted">{{ Str::limit($periode->deskripsi, 50) }}</small>
                @endif
              </td>
              <td>{{ $periode->tahun_ajaran }}</td>
              <td>
                <span class="badge badge-{{ $periode->semester == 'ganjil' ? 'info' : 'warning' }}">
                  {{ ucfirst($periode->semester) }}
                </span>
              </td>
              <td>{{ \Carbon\Carbon::parse($periode->tanggal_mulai)->format('d M Y') }}</td>
              <td>{{ \Carbon\Carbon::parse($periode->tanggal_selesai)->format('d M Y') }}</td>
              <td>
                @if($periode->is_active)
                <span class="badge badge-success">
                  <i class="fas fa-check-circle"></i> Aktif
                </span>
                @else
                <span class="badge badge-secondary">
                  <i class="fas fa-times-circle"></i> Tidak Aktif
                </span>
                @endif
              </td>
              <td>
                <span class="badge badge-primary">
                  {{ $periode->fases_count }} Fase
                </span>
              </td>
              <td>
                <div class="btn-group btn-group-sm">
                  <a href="{{ route('periode.show', $periode->id) }}" class="btn btn-info" title="Detail">
                    <i class="fas fa-eye"></i>
                  </a>
                  <button type="button" class="btn btn-warning btn-edit" 
                    data-id="{{ $periode->id }}"
                    data-nama="{{ $periode->nama }}"
                    data-tahun="{{ $periode->tahun_ajaran }}"
                    data-semester="{{ $periode->semester }}"
                    data-mulai="{{ $periode->tanggal_mulai }}"
                    data-selesai="{{ $periode->tanggal_selesai }}"
                    data-deskripsi="{{ $periode->deskripsi }}"
                    data-active="{{ $periode->is_active }}"
                    title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  @if(!$periode->is_active)
                  <button type="button" class="btn btn-success btn-activate" 
                    data-id="{{ $periode->id }}"
                    title="Aktifkan">
                    <i class="fas fa-check"></i>
                  </button>
                  @endif
                  <button type="button" class="btn btn-danger btn-delete" 
                    data-id="{{ $periode->id }}"
                    data-nama="{{ $periode->nama }}"
                    title="Hapus">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="modal-create">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('periode.store') }}" method="POST" id="form-create">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title">Tambah Periode Ujian</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="nama">Nama Periode <span class="text-danger">*</span></label>
            <input type="text" name="nama" id="nama" class="form-control" placeholder="Contoh: Ujian Tengah Semester" required>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="tahun_ajaran">Tahun Ajaran <span class="text-danger">*</span></label>
                <input type="text" name="tahun_ajaran" id="tahun_ajaran" class="form-control" placeholder="Contoh: 2024/2025" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="semester">Semester <span class="text-danger">*</span></label>
                <select name="semester" id="semester" class="form-control" required>
                  <option value="">Pilih Semester</option>
                  <option value="ganjil">Ganjil</option>
                  <option value="genap">Genap</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="tanggal_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" rows="3" class="form-control" placeholder="Deskripsi singkat tentang periode ini"></textarea>
          </div>

          <div class="form-group">
            <div class="icheck-primary">
              <input type="checkbox" name="is_active" id="is_active" value="1">
              <label for="is_active">Aktifkan periode ini</label>
            </div>
            <small class="form-text text-muted">Hanya satu periode yang dapat aktif dalam satu waktu</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modal-edit">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="" method="POST" id="form-edit">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h4 class="modal-title">Edit Periode Ujian</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="edit_nama">Nama Periode <span class="text-danger">*</span></label>
            <input type="text" name="nama" id="edit_nama" class="form-control" required>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_tahun_ajaran">Tahun Ajaran <span class="text-danger">*</span></label>
                <input type="text" name="tahun_ajaran" id="edit_tahun_ajaran" class="form-control" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_semester">Semester <span class="text-danger">*</span></label>
                <select name="semester" id="edit_semester" class="form-control" required>
                  <option value="">Pilih Semester</option>
                  <option value="ganjil">Ganjil</option>
                  <option value="genap">Genap</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai" class="form-control" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_tanggal_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_selesai" id="edit_tanggal_selesai" class="form-control" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="edit_deskripsi">Deskripsi</label>
            <textarea name="deskripsi" id="edit_deskripsi" rows="3" class="form-control"></textarea>
          </div>

          <div class="form-group">
            <div class="icheck-primary">
              <input type="checkbox" name="is_active" id="edit_is_active" value="1">
              <label for="edit_is_active">Aktifkan periode ini</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Update
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<!-- DataTables -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

<script>
$(function() {
  // Initialize DataTable
  $('#periode-table').DataTable({
    responsive: true,
    autoWidth: false,
    order: [[4, 'desc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
    }
  });

  // Edit button
  $('.btn-edit').on('click', function() {
    let id = $(this).data('id');
    let nama = $(this).data('nama');
    let tahun = $(this).data('tahun');
    let semester = $(this).data('semester');
    let mulai = $(this).data('mulai');
    let selesai = $(this).data('selesai');
    let deskripsi = $(this).data('deskripsi');
    let active = $(this).data('active');

    $('#form-edit').attr('action', `/periode/${id}`);
    $('#edit_nama').val(nama);
    $('#edit_tahun_ajaran').val(tahun);
    $('#edit_semester').val(semester);
    $('#edit_tanggal_mulai').val(mulai);
    $('#edit_tanggal_selesai').val(selesai);
    $('#edit_deskripsi').val(deskripsi);
    $('#edit_is_active').prop('checked', active == 1);

    $('#modal-edit').modal('show');
  });

  // Activate button
  $('.btn-activate').on('click', function() {
    let id = $(this).data('id');
    
    Swal.fire({
      title: 'Aktifkan Periode?',
      text: 'Periode lain yang aktif akan dinonaktifkan',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Aktifkan',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `/periode/${id}/activate`,
          method: 'POST',
          data: {
            _token: '{{ csrf_token() }}'
          },
          success: function(response) {
            Swal.fire('Berhasil!', response.message, 'success')
              .then(() => location.reload());
          },
          error: function(xhr) {
            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
          }
        });
      }
    });
  });

  // Delete button
  $('.btn-delete').on('click', function() {
    let id = $(this).data('id');
    let nama = $(this).data('nama');
    
    Swal.fire({
      title: 'Hapus Periode?',
      text: `Periode "${nama}" akan dihapus beserta semua fase dan ujian terkait`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `/periode/${id}`,
          method: 'DELETE',
          data: {
            _token: '{{ csrf_token() }}'
          },
          success: function(response) {
            Swal.fire('Berhasil!', response.message, 'success')
              .then(() => location.reload());
          },
          error: function(xhr) {
            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
          }
        });
      }
    });
  });

  // Form validation
  $('#tanggal_mulai, #edit_tanggal_mulai').on('change', function() {
    let form = $(this).closest('form');
    let mulai = form.find('[name="tanggal_mulai"]').val();
    let selesai = form.find('[name="tanggal_selesai"]');
    
    if (mulai) {
      selesai.attr('min', mulai);
    }
  });
});
</script>
@endpush
