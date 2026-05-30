@extends('layouts.admin')
@section('title', 'Struktur Organisasi')
@section('page_title', 'Struktur Organisasi Sekolah')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-sitemap mr-2 text-primary"></i>Daftar Jabatan Organisasi</h3>
        <a href="{{ route('admin.organisasi.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Jabatan
        </a>
      </div>
      <div class="card-body">

        <table id="tabel-organisasi" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Jabatan</th>
              <th>Induk Jabatan</th>
              <th>Urutan</th>
              <th class="text-center">Anggota Aktif</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($jabatans as $i => $j)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>
                @if($j->parent_id)
                  <span class="text-muted mr-1">↳</span>
                @endif
                <strong>{{ $j->nama_jabatan }}</strong>
              </td>
              <td>{{ $j->parent?->nama_jabatan ?? '<span class="badge badge-primary">Root</span>' }}</td>
              <td><span class="badge badge-light">{{ $j->urutan }}</span></td>
              <td class="text-center">
                <span class="badge badge-info">{{ $j->anggota->count() }} orang</span>
              </td>
              <td>
                <a href="{{ route('admin.organisasi.anggota', $j) }}" class="btn btn-info btn-xs" title="Kelola Anggota">
                  <i class="fas fa-users"></i> Anggota
                </a>
                <a href="{{ route('admin.organisasi.edit', $j) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.organisasi.destroy', $j) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus jabatan {{ $j->nama_jabatan }}? Semua anggota dan jabatan turunannya akan ikut terhapus.')">
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

<div class="row mt-2">
  <div class="col-12">
    <div class="card card-outline card-info">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-eye mr-1"></i> Preview Hierarki</h3>
      </div>
      <div class="card-body">
        @php $roots = $jabatans->whereNull('parent_id')->sortBy('urutan'); @endphp
        @forelse($roots as $root)
          <div class="mb-3">
            {{-- Level 0 --}}
            <div class="d-flex align-items-center">
              <span class="badge badge-primary px-3 py-2">{{ $root->nama_jabatan }}</span>
              <span class="badge badge-pill badge-light ml-2" style="font-size:.75rem">{{ ucfirst($root->tipe_hubungan) }}</span>
              @if($root->anggota->count())
                <small class="text-muted ml-2">{{ $root->anggota->first()->nama }}</small>
              @endif
            </div>

            {{-- Level 1 --}}
            @php $children = $jabatans->where('parent_id', $root->id)->sortBy('urutan'); @endphp
            @if($children->count())
            <div class="ml-4 mt-1 border-left pl-3" style="border-color:#dee2e6!important">
              @foreach($children as $child)
                <div class="mb-2">
                  <div class="d-flex align-items-center">
                    <span class="text-muted mr-2" style="font-size:.8rem">└─</span>
                    <span class="badge badge-secondary">{{ $child->nama_jabatan }}</span>
                    @if($child->anggota->count())
                      <small class="text-muted ml-2">{{ $child->anggota->first()->nama }}</small>
                    @endif
                  </div>

                  {{-- Level 2 --}}
                  @php $grandchildren = $jabatans->where('parent_id', $child->id)->sortBy('urutan'); @endphp
                  @if($grandchildren->count())
                  <div class="ml-4 mt-1">
                    @foreach($grandchildren as $gc)
                      <div class="d-flex align-items-center mb-1">
                        <span class="text-muted mr-2" style="font-size:.75rem">└─</span>
                        <span class="badge badge-light border">{{ $gc->nama_jabatan }}</span>
                        @if($gc->anggota->count())
                          <small class="text-muted ml-1" style="font-size:.75rem">{{ $gc->anggota->first()->nama }}</small>
                        @endif
                      </div>
                    @endforeach
                  </div>
                  @endif
                </div>
              @endforeach
            </div>
            @endif
          </div>
        @empty
          <p class="text-muted">Belum ada jabatan. Tambah jabatan terlebih dahulu.</p>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
  $(function () { $('#tabel-organisasi').DataTable({ responsive: true, autoWidth: false, order: [[3, 'asc']] }); });
</script>
@endpush
