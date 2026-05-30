@extends('layouts.admin')
@section('title', 'Data Guru')
@section('page_title', 'Data Guru')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Guru</h3>
        <div>
          <button type="button" id="btn-bulk-hapus" class="btn btn-danger btn-sm" disabled
                  onclick="bulkHapusGuru()">
            <i class="fas fa-trash"></i> Hapus Terpilih (<span id="bulk-count">0</span>)
          </button>
          <a href="{{ route('admin.guru.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Guru
          </a>
        </div>
      </div>
      <div class="card-body">
        <form id="form-bulk-guru" action="{{ route('admin.guru.bulk-destroy') }}" method="POST">
          @csrf @method('DELETE')
        </form>
        <table id="tabel-guru" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th style="width:34px"><input type="checkbox" id="cek-semua" title="Pilih semua"></th>
              <th>#</th>
              <th>Nama</th>
              <th>NIP</th>
              <th>Username</th>
              <th>Bidang Studi</th>
              <th>No. HP</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($gurus as $i => $g)
            <tr>
              <td><input type="checkbox" class="cek-baris" value="{{ $g->id }}"></td>
              <td>{{ $i + 1 }}</td>
              <td>{{ $g->nama }}</td>
              <td>{{ $g->nip ?? '-' }}</td>
              <td><span class="badge badge-secondary">{{ $g->user->username }}</span></td>
              <td>{{ $g->bidang_studi ?? '-' }}</td>
              <td>{{ $g->no_hp ?? '-' }}</td>
              <td>
                <a href="{{ route('admin.guru.permission.edit', $g) }}" class="btn btn-info btn-xs" title="Hak Akses Menu">
                  <i class="fas fa-user-shield"></i> Hak Akses
                </a>
                <a href="{{ route('admin.guru.edit', $g) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.guru.destroy', $g) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus guru {{ $g->nama }}? Akun login-nya juga akan dihapus.')">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i> Hapus</button>
                </form>
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
    $('#tabel-guru').DataTable({ responsive: true, autoWidth: false, columnDefs: [{ orderable: false, targets: 0 }] });
  });

  function refreshBulkGuru() {
    var n = document.querySelectorAll('.cek-baris:checked').length;
    document.getElementById('bulk-count').textContent = n;
    document.getElementById('btn-bulk-hapus').disabled = n === 0;
  }

  document.addEventListener('change', function (e) {
    if (e.target.id === 'cek-semua') {
      document.querySelectorAll('.cek-baris').forEach(function (c) { c.checked = e.target.checked; });
    }
    if (e.target.classList.contains('cek-baris') || e.target.id === 'cek-semua') {
      refreshBulkGuru();
    }
  });

  function bulkHapusGuru() {
    var ids = Array.from(document.querySelectorAll('.cek-baris:checked')).map(function (c) { return c.value; });
    if (!ids.length) return;
    if (!confirm('Hapus ' + ids.length + ' guru terpilih? Akun login-nya juga akan dihapus.')) return;
    var form = document.getElementById('form-bulk-guru');
    ids.forEach(function (id) {
      var inp = document.createElement('input');
      inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
      form.appendChild(inp);
    });
    form.submit();
  }
</script>
@endpush
