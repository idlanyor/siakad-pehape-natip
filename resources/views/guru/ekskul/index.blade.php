@extends('layouts.admin')
@section('title', 'Ekskul Binaan')
@section('page_title', 'Ekskul Binaan Saya')

@section('content')
@if(session('success'))
  <div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('success') }}
  </div>
@endif

@if($ekskuls->isEmpty())
  <div class="callout callout-warning">
    <h5>Belum ada ekskul binaan</h5>
    <p>Anda belum ditunjuk sebagai pembina ekskul manapun. Hubungi admin untuk penugasan.</p>
  </div>
@else
  <div class="row">
    @foreach($ekskuls as $e)
    <div class="col-md-4">
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-users mr-2 text-primary"></i>{{ $e->nama }}
          </h3>
        </div>
        <div class="card-body">
          <p class="text-muted" style="min-height:40px;">{{ $e->deskripsi ?: 'Tidak ada deskripsi.' }}</p>
          <p class="mb-1">
            <i class="fas fa-user-friends mr-1 text-info"></i>
            <strong>{{ $e->anggotas->count() }}</strong> anggota aktif
          </p>
        </div>
        <div class="card-footer p-2">
          <div class="d-flex justify-content-between">
            <a href="{{ route('guru.ekskul.detail', $e) }}" class="btn btn-info btn-sm">
              <i class="fas fa-eye"></i> Detail
            </a>
            <a href="{{ route('guru.ekskul.absensi', $e) }}" class="btn btn-success btn-sm">
              <i class="fas fa-clipboard-check"></i> Input Absensi
            </a>
            <a href="{{ route('guru.ekskul.rekap', $e) }}" class="btn btn-secondary btn-sm">
              <i class="fas fa-chart-bar"></i> Rekap
            </a>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
@endif
@endsection
