@extends('install.layout')
@section('step', 'Selesai')
@php $activeStep = 3; @endphp

@section('wizard')
<div class="text-center py-3">
  <i class="fas fa-check-circle text-success" style="font-size:4rem"></i>
  <h4 class="mt-3">Instalasi Selesai!</h4>
  <p class="text-muted">SIAKAD berhasil dipasang. Silakan login dengan akun administrator yang baru dibuat.</p>
  <a href="{{ url('/login') }}" class="btn btn-primary mt-2"><i class="fas fa-sign-in-alt me-1"></i> Masuk ke Aplikasi</a>
</div>
<div class="alert alert-secondary small mt-3 mb-0">
  <i class="fas fa-lock me-1"></i> Demi keamanan, wizard instalasi kini terkunci. Untuk install ulang, hapus file <code>storage/installed</code>.
</div>
@endsection
