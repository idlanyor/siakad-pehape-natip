@extends('layouts.admin')
@section('title', 'Pengumpulan — ' . $tugas->judul)
@section('page_title', 'Rekap Pengumpulan Tugas')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card card-primary card-outline">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h3 class="card-title">{{ $tugas->judul }}</h3>
          <small class="text-muted">
            <span class="badge badge-{{ $tugas->deadline_color }}">
              Deadline: {{ $tugas->deadline->format('d M Y H:i') }}
            </span>
            | Nilai Maks: <strong>{{ $tugas->nilai_maksimal }}</strong>
            | Kumpul: <strong>{{ $pengumpulans->count() }}</strong>/{{ $siswas->count() }}
          </small>
        </div>
        <a href="{{ route('guru.tugas.show', $tugas->jadwal) }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>
      <div class="card-body p-0">
        <table class="table table-bordered table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:40px">#</th>
              <th>Nama Siswa</th>
              <th>Status</th>
              <th>Dikumpulkan</th>
              <th>Pengumpulan</th>
              <th style="width:280px">Nilai & Feedback</th>
            </tr>
          </thead>
          <tbody>
            @foreach($siswas as $i => $s)
            @php $p = $pengumpulans[$s->id] ?? null; @endphp
            <tr class="{{ $p ? '' : 'table-light' }}">
              <td>{{ $i + 1 }}</td>
              <td><strong>{{ $s->nama }}</strong><br><small class="text-muted">{{ $s->nisn }}</small></td>
              <td>
                @if($p)
                  <span class="badge badge-{{ $p->status_color }}">{{ $p->status_label }}</span>
                @else
                  <span class="badge badge-secondary">Belum Kumpul</span>
                @endif
              </td>
              <td>
                @if($p)
                  <small>{{ $p->created_at->format('d M Y H:i') }}</small>
                  @if($p->created_at > $tugas->deadline)
                    <span class="badge badge-danger badge-sm ml-1">Terlambat</span>
                  @endif
                @else
                  <small class="text-muted">-</small>
                @endif
              </td>
              <td>
                @if($p)
                  @if($tugas->tipe_pengumpulan === 'teks')
                    <small class="text-muted">{{ Str::limit($p->konten, 80) }}</small>
                  @elseif($tugas->tipe_pengumpulan === 'link')
                    <a href="{{ $p->konten }}" target="_blank" class="btn btn-xs btn-outline-info">
                      <i class="fas fa-external-link-alt"></i> Buka Link
                    </a>
                  @else
                    <a href="{{ $p->download_url }}" target="_blank" class="btn btn-xs btn-outline-primary">
                      <i class="fas fa-download"></i> {{ $p->file_name }}
                    </a>
                  @endif
                  @if($p->catatan)
                    <br><small class="text-muted"><i class="fas fa-comment"></i> {{ $p->catatan }}</small>
                  @endif
                @else
                  <small class="text-muted">-</small>
                @endif
              </td>
              <td>
                @if($p)
                  <form action="{{ route('guru.tugas.nilai', $p) }}" method="POST">
                    @csrf
                    <div class="input-group input-group-sm mb-1">
                      <input type="number" name="nilai" step="0.5" min="0" max="{{ $tugas->nilai_maksimal }}"
                             class="form-control" value="{{ $p->nilai }}"
                             placeholder="0–{{ $tugas->nilai_maksimal }}">
                      <div class="input-group-append">
                        <button class="btn btn-success" type="submit"><i class="fas fa-check"></i></button>
                      </div>
                    </div>
                    <input type="text" name="feedback" class="form-control form-control-sm"
                           value="{{ $p->feedback }}" placeholder="Feedback (opsional)">
                  </form>
                  @if($p->is_dinilai)
                    <small class="text-success d-block mt-1">
                      <i class="fas fa-check-circle"></i> Dinilai {{ $p->dinilai_at->format('d M Y') }}
                    </small>
                  @endif
                @else
                  <small class="text-muted">Belum ada pengumpulan</small>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-footer text-muted small">
        Total kumpul: {{ $pengumpulans->count() }} dari {{ $siswas->count() }} siswa |
        Sudah dinilai: {{ $pengumpulans->filter->is_dinilai->count() }}
      </div>
    </div>
  </div>
</div>
@endsection
