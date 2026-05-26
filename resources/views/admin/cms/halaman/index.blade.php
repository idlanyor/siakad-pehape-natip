@extends('layouts.admin')
@section('title', 'Halaman Statis')
@section('page_title', 'Manajemen Halaman Statis')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i>Halaman Statis</h3>
    <a href="{{ route('admin.cms.halaman.create') }}" class="btn btn-primary btn-sm">
      <i class="fas fa-plus mr-1"></i> Tambah Halaman
    </a>
  </div>
  <div class="card-body p-0">
    @if($halamanList->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="fas fa-file-alt fa-3x mb-3 d-block"></i>Belum ada halaman statis.
      </div>
    @else
      <table class="table table-hover table-striped mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width:5%">No</th>
            <th>Judul</th>
            <th>Slug / URL</th>
            <th class="text-center">Urutan</th>
            <th class="text-center">Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($halamanList as $i => $h)
          <tr>
            <td class="text-muted">{{ $i + 1 }}</td>
            <td class="font-weight-bold">{{ $h->judul }}</td>
            <td>
              <code>/halaman/{{ $h->slug }}</code>
            </td>
            <td class="text-center">{{ $h->urutan }}</td>
            <td class="text-center">
              @if($h->is_published)
                <span class="badge badge-success">Published</span>
              @else
                <span class="badge badge-secondary">Draft</span>
              @endif
            </td>
            <td class="text-right" style="white-space:nowrap">
              <a href="{{ route('admin.cms.halaman.edit', $h) }}" class="btn btn-xs btn-warning">
                <i class="fas fa-edit"></i>
              </a>
              <form action="{{ route('admin.cms.halaman.destroy', $h) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-xs btn-danger" onclick="return confirm('Hapus halaman ini?')">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>
@endsection
