@extends('layouts.admin')
@section('title', 'FAQ PPDB')
@section('page_title', 'FAQ Pendaftaran PPDB')

@section('content')
<div class="row">
  {{-- List --}}
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-question-circle mr-2"></i>Daftar FAQ</h3></div>
      <div class="card-body p-0">
        @if($faqList->isEmpty())
          <div class="text-center py-5 text-muted">
            <i class="fas fa-question-circle fa-3x mb-3 d-block"></i>Belum ada FAQ.
          </div>
        @else
          <table class="table table-hover mb-0">
            <thead class="thead-light"><tr><th>Pertanyaan</th><th class="text-center">Status</th><th></th></tr></thead>
            <tbody>
              @foreach($faqList as $f)
              <tr>
                <td>
                  <div class="font-weight-bold small">{{ $f->pertanyaan }}</div>
                  <div class="text-muted small mt-1">{{ Str::limit($f->jawaban, 80) }}</div>
                </td>
                <td class="text-center">
                  <span class="badge badge-{{ $f->is_aktif ? 'success' : 'secondary' }}">
                    {{ $f->is_aktif ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="text-right" style="white-space:nowrap">
                  <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#editFaq{{ $f->id }}">
                    <i class="fas fa-edit"></i>
                  </button>
                  <form action="{{ route('admin.ppdb.faq.destroy', $f) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-danger" onclick="return confirm('Hapus FAQ ini?')"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              {{-- Modal Edit --}}
              <div class="modal fade" id="editFaq{{ $f->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <form action="{{ route('admin.ppdb.faq.update', $f) }}" method="POST">
                      @csrf @method('PUT')
                      <div class="modal-header"><h5 class="modal-title">Edit FAQ</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                      <div class="modal-body">
                        <div class="form-group"><label>Pertanyaan *</label><input type="text" name="pertanyaan" class="form-control" value="{{ $f->pertanyaan }}" required></div>
                        <div class="form-group"><label>Jawaban *</label><textarea name="jawaban" rows="4" class="form-control" required>{{ $f->jawaban }}</textarea></div>
                        <div class="row">
                          <div class="col-4"><div class="form-group"><label>Urutan</label><input type="number" name="urutan" class="form-control" value="{{ $f->urutan }}"></div></div>
                          <div class="col-4 d-flex align-items-end"><div class="form-group"><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="faqAktif{{ $f->id }}" name="is_aktif" value="1" {{ $f->is_aktif ? 'checked' : '' }}><label class="custom-control-label" for="faqAktif{{ $f->id }}">Aktif</label></div></div></div>
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
    <div class="card card-info card-outline">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-plus mr-2"></i>Tambah FAQ</h3></div>
      <form action="{{ route('admin.ppdb.faq.store') }}" method="POST">
        @csrf
        <div class="card-body">
          <div class="form-group">
            <label>Pertanyaan <span class="text-danger">*</span></label>
            <input type="text" name="pertanyaan" class="form-control @error('pertanyaan') is-invalid @enderror"
                   value="{{ old('pertanyaan') }}" placeholder="Tulis pertanyaan..." required>
            @error('pertanyaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Jawaban <span class="text-danger">*</span></label>
            <textarea name="jawaban" rows="4" class="form-control @error('jawaban') is-invalid @enderror"
                      placeholder="Tulis jawaban...">{{ old('jawaban') }}</textarea>
            @error('jawaban') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="row">
            <div class="col-6"><div class="form-group"><label>Urutan</label><input type="number" name="urutan" class="form-control" value="{{ old('urutan', $faqList->count() + 1) }}"></div></div>
            <div class="col-6 d-flex align-items-end"><div class="form-group"><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="newFaqAktif" name="is_aktif" value="1" checked><label class="custom-control-label" for="newFaqAktif">Aktif</label></div></div></div>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-info btn-block">
            <i class="fas fa-plus mr-1"></i> Tambah FAQ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
