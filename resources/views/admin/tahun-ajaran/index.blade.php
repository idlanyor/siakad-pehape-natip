@extends('layouts.admin')
@section('title', 'Tahun Ajaran & Semester')
@section('page_title', 'Tahun Ajaran & Semester')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Tahun Ajaran</h3>
        <a href="{{ route('admin.tahun-ajaran.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Tahun Ajaran
        </a>
      </div>
      <div class="card-body p-0">
        <table class="table table-bordered mb-0">
          <thead class="thead-light">
            <tr>
              <th>#</th>
              <th>Tahun Ajaran</th>
              <th>Status</th>
              <th>Semester</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($tahunAjarans as $i => $ta)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td class="font-weight-bold">{{ $ta->nama }}</td>
              <td>
                @if($ta->aktif)
                  <span class="badge badge-success">Aktif</span>
                @else
                  <span class="badge badge-secondary">Tidak Aktif</span>
                @endif
              </td>
              <td>
                <div class="d-flex flex-wrap gap-1">
                  @foreach($ta->semesters as $sem)
                    <div class="d-flex align-items-center mr-2">
                      <span class="badge {{ $sem->aktif ? 'badge-success' : 'badge-light border' }} mr-1">
                        Semester {{ $sem->semester }}
                        @if($sem->aktif) <i class="fas fa-check ml-1"></i> @endif
                      </span>
                      @if(!$sem->aktif)
                      <form action="{{ route('admin.semester.aktif', $sem) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-xs btn-outline-success" title="Set Aktif">Set Aktif</button>
                      </form>
                      @endif
                    </div>
                  @endforeach
                </div>
              </td>
              <td>
                <a href="{{ route('admin.tahun-ajaran.edit', $ta) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i> Edit
                </a>
                @if(!$ta->aktif)
                <form action="{{ route('admin.tahun-ajaran.aktif', $ta) }}" method="POST" class="d-inline">
                  @csrf
                  <button class="btn btn-success btn-xs"><i class="fas fa-check"></i> Set Aktif</button>
                </form>
                <form action="{{ route('admin.tahun-ajaran.destroy', $ta) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus tahun ajaran {{ $ta->nama }}?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                </form>
                @endif
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
