@extends('layouts.admin')

@section('title', 'Detail Periode - ' . $periode->nama)

@section('page_title', 'Detail Periode Ujian')

@push('styles')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Periode Info -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Informasi Periode</h3>
        <div class="card-tools">
          <a href="{{ route('periode.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
          <button type="button" class="btn btn-warning btn-sm" id="btn-edit-periode">
            <i class="fas fa-edit"></i> Edit
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <table class="table table-sm">
              <tr>
                <th width="40%">Nama Periode</th>
                <td>{{ $periode->nama }}</td>
              </tr>
              <tr>
                <th>Tahun Ajaran</th>
                <td>{{ $periode->tahun_ajaran }}</td>
              </tr>
              <tr>
                <th>Semester</th>
                <td>
                  <span class="badge badge-{{ $periode->semester == 'ganjil' ? 'info' : 'warning' }}">
                    {{ ucfirst($periode->semester) }}
                  </span>
                </td>
              </tr>
              <tr>
                <th>Status</th>
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
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-sm">
              <tr>
                <th width="40%">Tanggal Mulai</th>
                <td>{{ \Carbon\Carbon::parse($periode->tanggal_mulai)->format('d F Y') }}</td>
              </tr>
              <tr>
                <th>Tanggal Selesai</th>
                <td>{{ \Carbon\Carbon::parse($periode->tanggal_selesai)->format('d F Y') }}</td>
              </tr>
              <tr>
                <th>Durasi</th>
                <td>
                  {{ \Carbon\Carbon::parse($periode->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($periode->tanggal_selesai)) }} hari
                </td>
              </tr>
              <tr>
                <th>Dibuat</th>
                <td>{{ $periode->created_at->format('d M Y H:i') }}</td>
              </tr>
            </table>
          </div>
        </div>
        @if($periode->deskripsi)
        <div class="row mt-3">
          <div class="col-12">
            <strong>Deskripsi:</strong>
            <p class="text-muted">{{ $periode->deskripsi }}</p>
          </div>
        </div>
        @endif
      </div>
    </div>

    <!-- Statistik -->
    <div class="row">
      <div class="col-md-3">
        <div class="info-box bg-info">
          <span class="info-box-icon"><i class="fas fa-layer-group"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Total Fase</span>
            <span class="info-box-number">{{ $periode->fases_count }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-success">
          <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Total Ujian</span>
            <span class="info-box-number">{{ $periode->ujians_count }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-warning">
          <span class="info-box-icon"><i class="fas fa-users"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Total Peserta</span>
            <span class="info-box-number">{{ $totalPeserta }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-danger">
          <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Ujian Selesai</span>
            <span class="info-box-number">{{ $ujianSelesai }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Fase List -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Daftar Fase</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-create-fase">
            <i class="fas fa-plus"></i> Tambah Fase
          </button>
        </div>
      </div>
      <div class="card-body">
        <table id="fase-table" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th width="5%">No</th>
              <th>Nama Fase</th>
              <th>Tanggal Mulai</th>
              <th>Tanggal Selesai</th>
              <th>Status</th>
              <th>Ujian</th>
              <th width="15%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($periode->fases as $index => $fase)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>
                <strong>{{ $fase->nama }}</strong>
                @if($fase->deskripsi)
                <br><small class="text-muted">{{ Str::limit($fase->deskripsi, 50) }}</small>
                @endif
              </td>
              <td>{{ \Carbon\Carbon::parse($fase->tanggal_mulai)->format('d M Y') }}</td>
              <td>{{ \Carbon\Carbon::parse($fase->tanggal_selesai)->format('d M Y') }}</td>
              <td>
                @if($fase->is_active)
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
                  {{ $fase->ujians_count }} Ujian
                </span>
              </td>
              <td>
                <div class="btn-group btn-group-sm">
                  <button type="button" class="btn btn-warning btn-edit-fase" 
                    data-id="{{ $fase->id }}"
                    data-nama="{{ $fase->nama }}"
                    data-mulai="{{ $fase->tanggal_mulai }}"
                    data-selesai="{{ $fase->tanggal_selesai }}"
                    data-deskripsi="{{ $fase->deskripsi }}"
                    data-active="{{ $fase->is_active }}"
                    title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  @if(!$fase->is_active)
                  <button type="button" class="btn btn-success btn-activate-fase" 
                    data-id="{{ $fase->id }}"
                    title="Aktifkan">
                    <i class="fas fa-check"></i>
                  </button>
                  @endif
                  <button type="button" class="btn btn-danger btn-delete-fase" 
                    data-id="{{ $fase->id }}"
                    data-nama="{{ $fase->nama }}"
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

    <!-- Ujian List -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Daftar Ujian</h3>
      </div>
      <div class="card-body">
        <table id="ujian-table" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th width="5%">No</th>
              <th>Judul Ujian</th>
              <th>Fase</th>
              <th>Kelas</th>
              <th>Mapel</th>
              <th>Waktu</th>
              <th>Status</th>
              <th width="10%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($periode->ujians as $index => $ujian)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>
                <strong>{{ $ujian->judul }}</strong>
                <br><small class="text-muted">{{ $ujian->soals_count }} soal</small>
              </td>
              <td>{{ $ujian->fase->nama ?? '-' }}</td>
              <td>{{ $ujian->kelas_nama }}</td>
              <td>{{ $ujian->mapel_nama }}</td>
              <td>
                <small>
                  {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('d M Y H:i') }}
                  <br>s/d<br>
                  {{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('d M Y H:i') }}
                </small>
              </td>
              <td>
                @if($ujian->is_published)
                <span class="badge badge-success">Published</span>
                @else
                <span class="badge badge-warning">Draft</span>
                @endif
              </td>
              <td>
                <a href="{{ route('ujian.show', $ujian->id) }}" class="btn btn-sm btn-info">
                  <i class="fas fa-eye"></i>
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Create Fase -->
<div class="modal fade" id="modal-create-fase">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('fase.store', $periode->id) }}" method="POST" id="form-create-fase">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title">Tambah Fase</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="nama_fase">Nama Fase <span class="text-danger">*</span></label>
            <input type="text" name="nama" id="nama_fase" class="form-control" placeholder="Contoh: Fase 1 - Ujian Harian" required>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="tanggal_mulai_fase">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai_fase" class="form-control" 
                  min="{{ $periode->tanggal_mulai }}" 
                  max="{{ $periode->tanggal_selesai }}" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="tanggal_selesai_fase">Tanggal Selesai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_selesai" id="tanggal_selesai_fase" class="form-control" 
                  min="{{ $periode->tanggal_mulai }}" 
                  max="{{ $periode->tanggal_selesai }}" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="deskripsi_fase">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi_fase" rows="3" class="form-control" placeholder="Deskripsi singkat tentang fase ini"></textarea>
          </div>

          <div class="form-group">
            <div class="icheck-primary">
              <input type="checkbox" name="is_active" id="is_active_fase" value="1">
              <label for="is_active_fase">Aktifkan fase ini</label>
            </div>
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

<!-- Modal Edit Fase -->
<div class="modal fade" id="modal-edit-fase">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="" method="POST" id="form-edit-fase">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h4 class="modal-title">Edit Fase</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="edit_nama_fase">Nama Fase <span class="text-danger">*</span></label>
            <input type="text" name="nama" id="edit_nama_fase" class="form-control" required>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_tanggal_mulai_fase">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai_fase" class="form-control" 
                  min="{{ $periode->tanggal_mulai }}" 
                  max="{{ $periode->tanggal_selesai }}" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_tanggal_selesai_fase">Tanggal Selesai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_selesai" id="edit_tanggal_selesai_fase" class="form-control" 
                  min="{{ $periode->tanggal_mulai }}" 
                  max="{{ $periode->tanggal_selesai }}" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="edit_deskripsi_fase">Deskripsi</label>
            <textarea name="deskripsi" id="edit_deskripsi_fase" rows="3" class="form-control"></textarea>
          </div>

          <div class="form-group">
            <div class="icheck-primary">
              <input type="checkbox" name="is_active" id="edit_is_active_fase" value="1">
              <label for="edit_is_active_fase">Aktifkan fase ini</label>
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
  // Initialize DataTables
  $('#fase-table').DataTable({
    responsive: true,
    autoWidth: false,
    order: [[2, 'asc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
    }
  });

  $('#ujian-table').DataTable({
    responsive: true,
    autoWidth: false,
    order: [[5, 'desc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
    }
  });

  // Edit Fase
  $('.btn-edit-fase').on('click', function() {
    let id = $(this).data('id');
    let nama = $(this).data('nama');
    let mulai = $(this).data('mulai');
    let selesai = $(this).data('selesai');
    let deskripsi = $(this).data('deskripsi');
    let active = $(this).data('active');

    $('#form-edit-fase').attr('action', `/fase/${id}`);
    $('#edit_nama_fase').val(nama);
    $('#edit_tanggal_mulai_fase').val(mulai);
    $('#edit_tanggal_selesai_fase').val(selesai);
    $('#edit_deskripsi_fase').val(deskripsi);
    $('#edit_is_active_fase').prop('checked', active == 1);

    $('#modal-edit-fase').modal('show');
  });

  // Activate Fase
  $('.btn-activate-fase').on('click', function() {
    let id = $(this).data('id');
    
    Swal.fire({
      title: 'Aktifkan Fase?',
      text: 'Fase lain yang aktif akan dinonaktifkan',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Aktifkan',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `/fase/${id}/activate`,
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

  // Delete Fase
  $('.btn-delete-fase').on('click', function() {
    let id = $(this).data('id');
    let nama = $(this).data('nama');
    
    Swal.fire({
      title: 'Hapus Fase?',
      text: `Fase "${nama}" akan dihapus beserta semua ujian terkait`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `/fase/${id}`,
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

  // Date validation
  $('#tanggal_mulai_fase, #edit_tanggal_mulai_fase').on('change', function() {
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
