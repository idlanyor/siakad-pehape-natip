@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard Admin')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total_siswa'] }}</h3>
                <p>Total Siswa</p>
            </div>
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
            <a href="#" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['total_guru'] }}</h3>
                <p>Total Guru</p>
            </div>
            <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <a href="#" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['total_kelas'] }}</h3>
                <p>Total Kelas</p>
            </div>
            <div class="icon"><i class="fas fa-door-open"></i></div>
            <a href="#" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['total_jurusan'] }}</h3>
                <p>Jurusan</p>
            </div>
            <div class="icon"><i class="fas fa-graduation-cap"></i></div>
            <a href="#" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_mapel'] }}</h3>
                <p>Mata Pelajaran</p>
            </div>
            <div class="icon"><i class="fas fa-book"></i></div>
            <a href="#" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $stats['tahun_ajaran'] }}</h3>
                <p>Tahun Ajaran Aktif</p>
            </div>
            <div class="icon"><i class="fas fa-calendar"></i></div>
            <a href="#" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box" style="background:#6f42c1; color:#fff;">
            <div class="inner">
                <h3>Semester {{ $stats['semester_aktif'] }}</h3>
                <p>Semester Aktif</p>
            </div>
            <div class="icon"><i class="fas fa-clipboard-list"></i></div>
            <a href="#" class="small-box-footer" style="color:#ddd;">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> Panel Administrator</h3>
            </div>
            <div class="card-body">
                <p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>. Anda login sebagai <span class="badge badge-danger">Administrator</span>.</p>
                <p class="text-muted mb-0">Gunakan menu di sidebar untuk mengelola data sekolah.</p>
            </div>
        </div>
    </div>
</div>
@endsection
