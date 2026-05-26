@extends('layouts.admin')
@section('title', 'Kelola Foto — ' . $galeri->nama)
@section('page_title', 'Kelola Foto Galeri')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0">{{ $galeri->nama }}</h4>
    <small class="text-muted">{{ $galeri->tanggal->format('d F Y') }} · {{ $galeri->items->count() }} foto</small>
  </div>
  <div>
    <a href="{{ route('admin.cms.galeri.edit', $galeri) }}" class="btn btn-warning btn-sm mr-2">
      <i class="fas fa-edit mr-1"></i>Edit Info
    </a>
    <a href="{{ route('admin.cms.galeri.index') }}" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
  </div>
</div>

{{-- Upload lebih banyak foto --}}
<div class="card card-outline card-primary mb-4">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-plus mr-2"></i>Tambah Foto</h3>
    <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.cms.galeri.update', $galeri) }}" method="POST" enctype="multipart/form-data">
      @csrf @method('PUT')
      {{-- Pass existing data agar tidak terhapus --}}
      <input type="hidden" name="nama" value="{{ $galeri->nama }}">
      <input type="hidden" name="tanggal" value="{{ $galeri->tanggal->format('Y-m-d') }}">
      <input type="hidden" name="is_published" value="{{ $galeri->is_published ? '1' : '0' }}">

      <div class="d-flex align-items-end gap-3" style="gap:1rem">
        <div class="flex-grow-1">
          <label>Pilih Foto (bisa multiple)</label>
          <input type="file" name="fotos[]" class="form-control-file" accept="image/*" multiple id="addFotosInput">
          <div id="addFotoPreview" class="d-flex flex-wrap mt-2" style="gap:.5rem"></div>
        </div>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-upload mr-1"></i>Upload
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Grid foto --}}
<div class="row g-3">
  @forelse($galeri->items as $item)
  <div class="col-6 col-md-3 col-lg-2">
    <div class="card border h-100">
      <img src="{{ $item->foto_url }}" class="card-img-top" style="height:120px;object-fit:cover" alt="">
      <div class="card-footer p-2 text-center">
        <form action="{{ route('admin.cms.galeri-item.destroy', $item) }}" method="POST">
          @csrf @method('DELETE')
          <button class="btn btn-xs btn-danger w-100" onclick="return confirm('Hapus foto ini?')">
            <i class="fas fa-trash mr-1"></i>Hapus
          </button>
        </form>
      </div>
    </div>
  </div>
  @empty
    <div class="col-12 text-center py-5 text-muted">
      <i class="fas fa-images fa-3x mb-3 d-block"></i>Belum ada foto. Upload foto di atas.
    </div>
  @endforelse
</div>
@endsection

@push('scripts')
<script>
document.getElementById('addFotosInput').addEventListener('change', function() {
  const p = document.getElementById('addFotoPreview');
  p.innerHTML = '';
  Array.from(this.files).forEach(f => {
    const r = new FileReader();
    r.onload = e => {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style = 'height:60px;width:60px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6';
      p.appendChild(img);
    };
    r.readAsDataURL(f);
  });
});
</script>
@endpush
