@extends('layouts.admin')
@section('title', 'Bimbingan PKL')
@section('page_title', 'PKL — Bimbingan Saya')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Siswa Bimbingan PKL</h3>
      </div>
      <div class="card-body">
        @forelse($bimbingans as $p)
        <div class="card card-{{ $p->status === 'aktif' ? 'success' : 'secondary' }} card-outline mb-3">
          <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong>{{ $p->siswa->nama }}</strong>
                <span class="badge badge-secondary ml-1">{{ $p->siswa->kelas?->nama_kelas }}</span>
                <br>
                <small class="text-muted">
                  {{ $p->tempatPkl->nama }}
                  @if($p->periode_mulai)
                    | {{ $p->periode_mulai->format('d/m/Y') }} – {{ $p->periode_selesai?->format('d/m/Y') ?? '...' }}
                  @endif
                </small>
              </div>
              <div class="d-flex align-items-center">
                <span class="badge badge-{{ $p->status === 'aktif' ? 'success' : 'secondary' }} mr-3">{{ ucfirst($p->status) }}</span>
                @if($p->nilai)
                  <span class="badge badge-{{ $p->nilai->predikat_color }} mr-3">Nilai: {{ $p->nilai->nilai_akhir }}</span>
                @endif
                <a href="{{ route('guru.pkl.detail', $p) }}" class="btn btn-info btn-sm">
                  <i class="fas fa-eye"></i> Detail
                </a>
                <a href="{{ route('guru.pkl.nilai', $p) }}" class="btn btn-primary btn-sm ml-1">
                  <i class="fas fa-star"></i> Nilai
                </a>
              </div>
            </div>
          </div>
        </div>
        @empty
        <div class="alert alert-info">Anda belum ditugaskan sebagai guru pembimbing PKL.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
