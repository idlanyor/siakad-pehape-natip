@extends('layouts.admin')
@section('title', 'Manajemen Jadwal')
@section('page_title', 'Manajemen Jadwal Pelajaran')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<style>
  .grid-cell { min-width: 130px; vertical-align: top; }
  .jadwal-card {
    background: #fff; border-left: 3px solid #3c8dbc;
    border-radius: 4px; padding: 6px 8px; margin-bottom: 4px;
    font-size: 0.78rem; box-shadow: 0 1px 3px rgba(0,0,0,.1);
  }
  .jadwal-card.guru-0 { border-color: #3c8dbc; }
  .jadwal-card.guru-1 { border-color: #28a745; }
  .jadwal-card.guru-2 { border-color: #fd7e14; }
  .jadwal-card.guru-3 { border-color: #dc3545; }
  .jadwal-card.guru-4 { border-color: #6f42c1; }
  .jadwal-card strong { display: block; font-size: 0.8rem; }
  .table-grid th { background: #343a40; color:#fff; text-align:center; }
  .table-grid td { padding: 6px 8px; }
</style>
@endpush

@section('content')

{{-- Header Info --}}
@if($semester)
<div class="alert alert-info py-2 mb-3">
  <i class="fas fa-calendar-check mr-1"></i>
  Menampilkan jadwal semester aktif: <strong>Semester {{ $semester->semester }} — {{ $semester->tahunAjaran->nama }}</strong>
</div>
@else
<div class="alert alert-warning py-2 mb-3">
  <i class="fas fa-exclamation-triangle mr-1"></i> Belum ada semester aktif. <a href="{{ route('admin.tahun-ajaran.index') }}">Set semester aktif</a> terlebih dahulu.
</div>
@endif

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3" id="jadwalTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="grid-tab" data-toggle="tab" href="#grid-view">
      <i class="fas fa-th mr-1"></i> Grid Mingguan
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="list-tab" data-toggle="tab" href="#list-view">
      <i class="fas fa-list mr-1"></i> Tabel
    </a>
  </li>
  <li class="nav-item ml-auto">
    <a href="{{ route('admin.jadwal.create') }}" class="btn btn-primary btn-sm mt-1">
      <i class="fas fa-plus"></i> Tambah Jadwal
    </a>
  </li>
</ul>

<div class="tab-content">
  {{-- GRID VIEW --}}
  <div class="tab-pane fade show active" id="grid-view">
    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-bordered table-grid mb-0">
            <thead>
              <tr>
                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
                <th class="grid-cell">{{ $hari }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              <tr>
                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
                <td class="grid-cell">
                  @forelse($grouped->get($hari, collect()) as $j)
                  @php $colorIdx = $j->guru_id % 5; @endphp
                  <div class="jadwal-card guru-{{ $colorIdx }}">
                    <strong>{{ $j->jam_mulai }} - {{ $j->jam_selesai }}</strong>
                    {{ $j->mapel->nama_mapel }}<br>
                    <span class="text-muted">{{ $j->kelas?->nama_kelas ?? '-' }}</span><br>
                    <small class="text-muted">{{ $j->guru?->nama ?? '-' }}</small><br>
                    @if($j->ruangan)<small><i class="fas fa-door-open fa-xs"></i> {{ $j->ruangan }}</small>@endif
                  </div>
                  @empty
                  <span class="text-muted small">—</span>
                  @endforelse
                </td>
                @endforeach
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- LIST VIEW --}}
  <div class="tab-pane fade" id="list-view">
    <div class="card">
      <div class="card-body">
        <table id="tabel-jadwal" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Hari</th>
              <th>Jam</th>
              <th>Mata Pelajaran</th>
              <th>Kelas</th>
              <th>Guru</th>
              <th>Ruangan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($jadwals as $i => $j)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td><span class="badge badge-secondary">{{ $j->hari }}</span></td>
              <td>{{ $j->jam_mulai }} - {{ $j->jam_selesai }}</td>
              <td>{{ $j->mapel->nama_mapel }}</td>
              <td>{{ $j->kelas?->nama_kelas ?? '-' }}</td>
              <td>{{ $j->guru?->nama ?? '-' }}</td>
              <td>{{ $j->ruangan ?? '-' }}</td>
              <td>
                <a href="{{ route('admin.jadwal.edit', $j) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.jadwal.destroy', $j) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus jadwal ini?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
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
    $('#tabel-jadwal').DataTable({ responsive: true, autoWidth: false });
  });
</script>
@endpush
