@extends('layouts.public')
@section('title', '503 Service Unavailable')
@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-800">503</h1>
        <p class="text-xl text-gray-600 mt-4">Layanan Sedang Pemeliharaan.</p>
        <p class="text-gray-500 mt-2">Kami sedang melakukan peningkatan sistem. Silakan kembali lagi nanti.</p>
    </div>
</div>
@endsection
