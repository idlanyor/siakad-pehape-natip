@extends('layouts.public')
@section('title', '419 Halaman Kedaluwarsa')
@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-800">419</h1>
        <p class="text-xl text-gray-600 mt-4">Sesi Halaman Kedaluwarsa.</p>
        <p class="text-gray-500 mt-2">Halaman terlalu lama dibuka. Silakan muat ulang dan coba lagi.</p>
        <a href="{{ url()->previous() }}" class="mt-6 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Coba Lagi</a>
    </div>
</div>
@endsection
