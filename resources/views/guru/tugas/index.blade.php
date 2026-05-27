@extends('layouts.admin')
@section('title', 'Tugas')
@section('page_title', 'Manajemen Tugas')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Pilih Jadwal untuk Kelola Tugas</h3>
      </div>
      <div class="card-body">
        <table id="tabel-jadwal" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Mata Pelajaran</th>
              <th>Kelas</th>
              <th>Hari / Jam</th>
              <th>Semester</th>
              <th class="text-center">Jumlah Tugas</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($jadwals as $i => $j)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $j->mapel->nama_mapel }}</td>
              <td>{{ $j->kelas?->nama_kelas ?? '-' }}</td>
              <td>
                <span class="badge badge-secondary">{{ $j->hari }}</span>
                {{ $j->jam_mulai }} - {{ $j->jam_selesai }}
              </td>
              <td>Sem {{ $j->semester?->semester }} / {{ $j->semester?->tahunAjaran->nama }}</td>
              <td class="text-center">
                <span class="badge badge-warning">{{ $j->tugas_count }}</span>
              </td>
              <td>
                <a href="{{ route('guru.tugas.show', $j) }}" class="btn btn-warning btn-sm">
                  <i class="fas fa-tasks"></i> Kelola Tugas
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
<script>$(function(){ $('#tabel-jadwal').DataTable({ responsive:true, autoWidth:false }); });</script>
@endpush
