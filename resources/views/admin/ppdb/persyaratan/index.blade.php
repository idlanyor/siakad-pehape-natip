@extends('layouts.admin')
@section('title', 'Persyaratan PPDB')
@section('page_title', 'Persyaratan Pendaftaran PPDB')

@section('content')
<div class="row">
  {{-- List --}}
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list-check mr-2"></i>Daftar Persyaratan</h3>
      </div>
      <div class="card-body p-0">
        @if($persyaratanList->isEmpty())
          <div class="text-center py-5 text-muted">
            <i class="fas fa-list fa-3x mb-3 d-block"></i>Belum ada persyaratan.
          </div>
        @else
          <table class="table table-hover mb-0">
            <thead class="thead-light"><tr><th style="width:4%">No</th><th>Persyaratan</th><th class="text-center">Status</th><th></th></tr></thead>
            <tbody>
              @foreach($persyaratanList as $p)
              <tr>
                <td class="text-muted">{{ $p->urutan }}</td>
                <td>
                  <div class="font-weight-bold">{{ $p->judul }}</div>
                  @if($p->deskripsi)<small class="text-muted">{{ $p->deskripsi }}</small>@endif
                </td>
                <td class="text-center">
                  <span class="badge badge-{{ $p->is_aktif ? 'success' : 'secondary' }}">
                    {{ $p->is_aktif ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="text-right" style="white-space:nowrap">
                  <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#editModal{{ $p->id }}">
                    <i class="fas fa-edit"></i>
                  </button>
                  <form action="{{ route('admin.ppdb.persyaratan.destroy', $p) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-danger" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              {{-- Edit Modal --}}
              <div class="modal fade" id="editModal{{ $p->id }}" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form action="{{ route('admin.ppdb.persyaratan.update', $p) }}" method="POST">
                      @csrf @method('PUT')
                      <div class="modal-header"><h5 class="modal-title">Edit Persyaratan</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                      <div class="modal-body">
                        <div class="form-group"><label>Judul *</label><input type="text" name="judul" class="form-control" value="{{ $p->judul }}" required></div>
                        <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" rows="2" class="form-control">{{ $p->deskripsi }}</textarea></div>
                        <div class="row">
                          <div class="col-6"><div class="form-group"><label>Urutan</label><input type="number" name="urutan" class="form-control" value="{{ $p->urutan }}"></div></div>
                          <div class="col-6"><div class="form-group"><label>&nbsp;</label><div class="custom-control custom-switch mt-2"><input type="checkbox" class="custom-control-input" id="aktif{{ $p->id }}" name="is_aktif" value="1" {{ $p->is_aktif ? 'checked' : '' }}><label class="custom-control-label" for="aktif{{ $p->id }}">Aktif</label></div></div></div>
                        </div>
                      </div>
                      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
                    </form>
                  </div>
                </div>
              </div>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>
  </div>

  {{-- Form Tambah --}}
  <div class="col-lg-5">
    <div class="card card-success card-outline">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-plus mr-2"></i>Tambah Persyaratan</h3></div>
      <form action="{{ route('admin.ppdb.persyaratan.store') }}" method="POST">
        @csrf
        <div class="card-body">
          <div class="form-group">
            <label>Judul Persyaratan <span class="text-danger">*</span></label>
            <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
                   value="{{ old('judul') }}" placeholder="e.g. Foto copy Ijazah / SKL" required>
            @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Keterangan Tambahan</label>
            <textarea name="deskripsi" rows="2" class="form-control" placeholder="Opsional...">{{ old('deskripsi') }}</textarea>
          </div>
          <div class="row">
            <div class="col-6">
              <div class="form-group">
                <label>Urutan</label>
                <input type="number" name="urutan" class="form-control" value="{{ old('urutan', $persyaratanList->count() + 1) }}" min="1">
              </div>
            </div>
            <div class="col-6 d-flex align-items-end">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input" id="newAktif" name="is_aktif" value="1" checked>
                  <label class="custom-control-label" for="newAktif">Aktif</label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-success btn-block">
            <i class="fas fa-plus mr-1"></i> Tambah Persyaratan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
