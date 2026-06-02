@extends('layouts.admin')

@section('title', 'Kelola Ujian Online')
@section('page_title', 'Kelola Ujian Online')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>Daftar Ujian</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-sm btn-primary" data-toggle="collapse" data-target="#filterCollapse">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
    </div>

    {{-- Filter --}}
    <div class="collapse {{ request()->hasAny(['kelas_id', 'mapel_id', 'guru_id', 'tanggal_mulai', 'tanggal_selesai', 'status']) ? 'show' : '' }}" id="filterCollapse">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.ujian.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Kelas</label>
                            <select name="kelas_id" class="form-control form-control-sm">
                                <option value="">Semua Kelas</option>
                                @foreach($kelasList as $k)
                                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Mata Pelajaran</label>
                            <select name="mapel_id" class="form-control form-control-sm">
                                <option value="">Semua Mapel</option>
                                @foreach($mapelList as $m)
                                    <option value="{{ $m->id }}" {{ request('mapel_id') == $m->id ? 'selected' : '' }}>
                                        {{ $m->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Guru</label>
                            <select name="guru_id" class="form-control form-control-sm">
                                <option value="">Semua Guru</option>
                                @foreach($guruList as $g)
                                    <option value="{{ $g->id }}" {{ request('guru_id') == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="belum_mulai" {{ request('status') == 'belum_mulai' ? 'selected' : '' }}>Belum Mulai</option>
                                <option value="berlangsung" {{ request('status') == 'berlangsung' ? 'selected' : '' }}>Sedang Berlangsung</option>
                                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="{{ request('tanggal_mulai') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control form-control-sm" value="{{ request('tanggal_selesai') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="{{ route('admin.ujian.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        @if($ujians->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <p>Belum ada ujian</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Judul Ujian</th>
                            <th>Guru</th>
                            <th>Kelas</th>
                            <th>Mapel</th>
                            <th>Tipe</th>
                            <th>Waktu</th>
                            <th>Peserta</th>
                            <th>Status</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ujians as $ujian)
                        <tr>
                            <td>{{ $ujians->firstItem() + $loop->index }}</td>
                            <td>
                                <strong>{{ $ujian->judul }}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> {{ $ujian->durasi_menit }} menit
                                    | <i class="fas fa-list-ol"></i> {{ $ujian->jumlah_soal }} soal
                                </small>
                            </td>
                            <td>{{ $ujian->guru->nama ?? '-' }}</td>
                            <td>{{ $ujian->jadwal->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ $ujian->jadwal->mapel->nama_mapel ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $ujian->tipe_color }}">{{ $ujian->tipe_label }}</span>
                            </td>
                            <td>
                                <small>
                                    {{ $ujian->waktu_mulai->format('d/m/Y H:i') }}
                                    <br>
                                    s/d {{ $ujian->waktu_selesai->format('d/m/Y H:i') }}
                                </small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $ujian->jumlah_peserta }}</span>
                                <br>
                                <small class="text-muted">{{ $ujian->jumlah_selesai }} selesai</small>
                            </td>
                            <td>
                                <span class="badge badge-{{ $ujian->status_color }}">{{ $ujian->status_label }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.ujian.statistik', $ujian) }}" class="btn btn-sm btn-info" title="Statistik">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('admin.ujian.export.excel', $ujian) }}">
                                            <i class="fas fa-file-excel text-success"></i> Excel
                                        </a>
                                        <a class="dropdown-item" href="{{ route('admin.ujian.export.pdf', $ujian) }}">
                                            <i class="fas fa-file-pdf text-danger"></i> PDF
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($ujians->hasPages())
    <div class="card-footer">
        {{ $ujians->links() }}
    </div>
    @endif
</div>
@endsection
