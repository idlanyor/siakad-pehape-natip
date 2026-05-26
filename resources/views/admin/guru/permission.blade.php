@extends('layouts.admin')
@section('title', 'Hak Akses Menu — ' . $guru->nama)
@section('page_title', 'Hak Akses Menu Guru')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-7">

    <div class="card card-primary card-outline mb-3">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-shield mr-1"></i> {{ $guru->nama }}</h3>
        <div class="card-tools">
          <span class="badge badge-secondary">{{ $guru->nip ?? 'NIP tidak diisi' }}</span>
          <span class="badge badge-info">{{ $guru->bidang_studi ?? '-' }}</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Kelola Akses Menu</h3>
      </div>
      <form action="{{ route('admin.guru.permission.update', $guru) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
          <p class="text-muted mb-3">
            <i class="fas fa-info-circle mr-1"></i>
            Centang menu yang <strong>boleh diakses</strong> oleh guru ini. Menu yang tidak dicentang akan disembunyikan dan aksesnya diblokir.
          </p>

          @php
            $menus = [
              'menu_jadwal'  => ['label' => 'Jadwal Mengajar',  'icon' => 'fas fa-calendar-week',   'desc' => 'Lihat jadwal mengajar per hari/jam'],
              'menu_mapel'   => ['label' => 'Mata Pelajaran',   'icon' => 'fas fa-book',             'desc' => 'Lihat daftar mata pelajaran yang diampu'],
              'menu_absensi' => ['label' => 'Absensi Kelas',    'icon' => 'fas fa-clipboard-list',   'desc' => 'Input dan lihat rekap absensi siswa'],
              'menu_nilai'   => ['label' => 'Penilaian / Nilai','icon' => 'fas fa-star-half-alt',    'desc' => 'Input nilai tugas, PTS, PAS, praktik, dst'],
              'menu_pkl'     => ['label' => 'PKL Bimbingan',    'icon' => 'fas fa-briefcase',        'desc' => 'Monitor dan nilai siswa PKL bimbingan'],
              'menu_ekskul'  => ['label' => 'Ekskul Binaan',    'icon' => 'fas fa-users',            'desc' => 'Input absensi dan kelola ekskul yang dibina'],
            ];
          @endphp

          @foreach($menus as $key => $meta)
          <div class="d-flex align-items-center justify-content-between border rounded p-3 mb-2 {{ ($permission->$key ?? true) ? '' : 'bg-light' }}">
            <div class="d-flex align-items-center">
              <i class="{{ $meta['icon'] }} fa-lg text-primary mr-3" style="width:24px; text-align:center;"></i>
              <div>
                <div class="font-weight-bold">{{ $meta['label'] }}</div>
                <small class="text-muted">{{ $meta['desc'] }}</small>
              </div>
            </div>
            <div class="custom-control custom-switch ml-3">
              <input type="checkbox"
                     class="custom-control-input"
                     id="{{ $key }}"
                     name="{{ $key }}"
                     value="1"
                     {{ ($permission->$key ?? true) ? 'checked' : '' }}>
              <label class="custom-control-label" for="{{ $key }}"></label>
            </div>
          </div>
          @endforeach

        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.guru.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Simpan Hak Akses
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
@endsection
