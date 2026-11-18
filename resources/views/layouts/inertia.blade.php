{{--
    Root view Inertia untuk modul Ujian Online.
    Memakai shell AdminLTE SIAKAD (navbar + sidebar Blade); Vue hanya merender
    area konten. Header halaman dan flash message dirender oleh Vue.
--}}
@extends('layouts.admin')

@section('inertia', '1')

@push('styles')
  @routes('ujian')
  @vite(['resources/js/ujian.js', "resources/js/Ujian/Pages/{$page['component']}.vue"])
  @inertiaHead
@endpush

@section('content')
  @inertia
@endsection
