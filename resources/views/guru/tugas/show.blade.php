@extends('layouts.admin')
@section('title', 'Tugas — ' . $jadwal->mapel->nama_mapel)
@section('page_title', 'Daftar Tugas')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card card-warning card-outline">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          <span class="badge badge-warning">{{ $jadwal->mapel->nama_mapel }}</span>
          &mdash; {{ $jadwal->kelas?->nama_kelas }}
          | Sem {{ $jadwal->semester?->semester }} / {{ $jadwal->semester?->tahunAjaran->nama }}
        </h3>
        <div>
          <a href="{{ route('guru.tugas.create', $jadwal) }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus"></i> Buat Tugas
          </a>
          <a href="{{ route('guru.tugas.index') }}" class="btn btn-secondary btn-sm ml-1">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        @if($tugas->isEmpty())
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Belum ada tugas.
            <a href="{{ route('guru.tugas.create', $jadwal) }}" class="alert-link">Buat tugas pertama.</a>
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Judul Tugas</th>
                  <th>Tipe</th>
                  <th>Deadline</th>
                  <th class="text-center">Pengumpulan</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @foreach($tugas as $i => $t)
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td>
                    <strong>{{ $t->judul }}</strong>
                    <br><small class="text-muted">{{ Str::limit($t->instruksi, 60) }}</small>
                  </td>
                  <td><span class="badge badge-info">{{ $t->tipe_label }}</span></td>
                  <td>
                    <span class="text-{{ $t->deadline_color }}">
                      {{ $t->deadline->format('d M Y H:i') }}
                    </span>
                    <br><small class="{{ $t->is_expired ? 'text-danger' : 'text-muted' }}">{{ $t->sisa_waktu }}</small>
                  </td>
                  <td class="text-center">
                    <span class="badge badge-primary">{{ $t->pengumpulans_count }}</span> kumpul
                  </td>
                  <td>
                    <a href="{{ route('guru.tugas.pengumpulan', $t) }}" class="btn btn-xs btn-primary">
                      <i class="fas fa-list-check"></i> Pengumpulan
                    </a>
                    <a href="{{ route('guru.tugas.edit', $t) }}" class="btn btn-xs btn-warning">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('guru.tugas.destroy', $t) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Hapus tugas ini beserta semua pengumpulannya?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
