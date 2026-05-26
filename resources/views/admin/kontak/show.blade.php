@extends('layouts.admin')
@section('title', 'Detail Pesan')
@section('page_title', 'Detail Pesan Kontak')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          <i class="fas fa-envelope-open text-primary mr-2"></i>{{ $kontak->subjek }}
        </h3>
        <span class="badge badge-success">Dibaca</span>
      </div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-4">
          <tr><th style="width:120px">Dari</th><td><strong>{{ $kontak->nama }}</strong></td></tr>
          <tr><th>Email</th><td><a href="mailto:{{ $kontak->email }}">{{ $kontak->email }}</a></td></tr>
          @if($kontak->no_hp)
          <tr><th>No. HP</th><td><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $kontak->no_hp) }}" target="_blank">{{ $kontak->no_hp }}</a></td></tr>
          @endif
          <tr><th>Dikirim</th><td class="text-muted">{{ $kontak->created_at->translatedFormat('d F Y, H:i') }}</td></tr>
        </table>

        <div class="card bg-light border-0 p-4 rounded-3">
          <h6 class="font-weight-bold text-muted mb-3">Isi Pesan:</h6>
          <p class="mb-0 lh-lg" style="white-space: pre-line">{{ $kontak->pesan }}</p>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('admin.kontak.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left mr-1"></i>Kembali
        </a>
        <div>
          <a href="mailto:{{ $kontak->email }}?subject=Re: {{ $kontak->subjek }}"
             class="btn btn-primary mr-2">
            <i class="fas fa-reply mr-1"></i>Balas via Email
          </a>
          <form action="{{ route('admin.kontak.destroy', $kontak) }}" method="POST" class="d-inline">
            @csrf @method('DELETE')
            <button class="btn btn-danger" onclick="return confirm('Hapus pesan ini?')">
              <i class="fas fa-trash mr-1"></i>Hapus
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
