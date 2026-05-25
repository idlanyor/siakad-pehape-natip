@extends('layouts.admin')

@section('title', 'Dashboard Guru')
@section('page_title', 'Dashboard Guru')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_mapel'] }}</h3>
                <p>Mata Pelajaran</p>
            </div>
            <div class="icon">
                <i class="fas fa-book"></i>
            </div>
            <a href="{{ route('dosen.matkul') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['total_siswa'] }}</h3>
                <p>Total Siswa</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['total_jurusan'] }}</h3>
                <p>Jurusan</p>
            </div>
            <div class="icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Selamat Datang</h3>
            </div>
            <div class="card-body">
                <p>Halo, <strong>{{ auth()->user()->name }}</strong>! Selamat datang di Panel Guru Simakad Sekolah SMK. Anda dapat mengelola mata pelajaran dan melihat data siswa melalui menu di samping.</p>
            </div>
        </div>
    </div>
</div>
@endsection
