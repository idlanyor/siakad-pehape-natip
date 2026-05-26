@extends('layouts.admin')
@section('title', 'Data Nilai')
@section('page_title', 'Data Nilai Siswa')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Daftar Jadwal — Klik Detail untuk Melihat Nilai</h3>
      </div>
      <div class="card-body">
        <table id="tabel-nilai" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Mata Pelajaran</th>
              <th>Kelas</th>
              <th>Guru</th>
              <th>Progress</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($jadwals as $i => $j)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $j->mapel->nama_mapel }}</td>
              <td>{{ $j->kelas?->nama_kelas ?? '-' }}</td>
              <td>{{ $j->guru?->nama ?? '-' }}</td>
              <td>
                @php
                  $persen = $j->jumlah_siswa > 0 ? round($j->jumlah_dinilai / $j->jumlah_siswa * 100) : 0;
                  $color = $persen >= 100 ? 'success' : ($persen >= 50 ? 'warning' : 'danger');
                @endphp
                <div class="progress progress-sm">
                  <div class="progress-bar bg-{{ $color }}" style="width: {{ $persen }}%"></div>
                </div>
                <small>{{ $j->jumlah_dinilai }}/{{ $j->jumlah_siswa }} siswa</small>
              </td>
              <td>
                <a href="{{ route('admin.nilai.detail', $j) }}" class="btn btn-info btn-sm">
                  <i class="fas fa-eye"></i> Detail
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>$(function(){ $('#tabel-nilai').DataTable({ responsive:true, autoWidth:false }); });</script>
@endpush
