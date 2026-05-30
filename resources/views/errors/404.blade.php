@extends('layouts.public')
@section('title', '404 Not Found')
@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-800">404</h1>
        <p class="text-xl text-gray-600 mt-4">Halaman Tidak Ditemukan.</p>
        <p class="text-gray-500 mt-2">Halaman yang Anda cari mungkin telah dihapus atau URL salah.</p>
        <a href="{{ url('/') }}" class="mt-6 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Kembali ke Beranda</a>
    </div>
</div>
@endsection
