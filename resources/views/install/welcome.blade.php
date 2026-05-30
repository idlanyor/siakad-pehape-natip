@extends('install.layout')
@section('step', 'Persyaratan Sistem')
@php $activeStep = 1; @endphp

@section('wizard')
<h5 class="mb-3">Selamat datang 👋</h5>
<p class="text-muted">Wizard ini akan memandu pemasangan SIAKAD. Pastikan semua persyaratan terpenuhi.</p>

<table class="table">
  <tbody>
    @foreach($requirements as $name => $ok)
    <tr>
      <td>{{ $name }}</td>
      <td class="text-end">
        @if($ok)
          <span class="badge bg-success"><i class="fas fa-check"></i> OK</span>
        @else
          <span class="badge bg-danger"><i class="fas fa-times"></i> Tidak</span>
        @endif
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

@php $allOk = collect($requirements)->every(fn($v) => $v === true); @endphp

@if($allOk)
  <a href="{{ route('install.database') }}" class="btn btn-primary w-100">
    Lanjut: Konfigurasi Database <i class="fas fa-arrow-right ms-1"></i>
  </a>
@else
  <div class="alert alert-warning mb-0">Perbaiki persyaratan yang gagal lalu muat ulang halaman ini.</div>
@endif
@endsection
