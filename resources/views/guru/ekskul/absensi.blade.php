@extends('layouts.admin')
@section('title', 'Absensi Ekskul — ' . $ekskul->nama)
@section('page_title', 'Input Absensi: ' . $ekskul->nama)

@section('content')
<div class="row">
  <div class="col-md-3">
    <div class="card card-success card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Tanggal</h3>
      </div>
      <div class="card-body">
        <form method="GET">
          <div class="form-group">
            <label>Pilih Tanggal Pertemuan</label>
            <input type="date" name="tanggal" id="tanggal" class="form-control"
                   value="{{ $tanggal }}"
                   max="{{ today()->toDateString() }}"
                   onchange="this.form.submit()">
          </div>
        </form>
        <hr>
        <a href="{{ route('guru.ekskul.detail', $ekskul) }}" class="btn btn-link btn-sm btn-block">← Kembali</a>
      </div>
    </div>
  </div>

  <div class="col-md-9">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          <i class="fas fa-clipboard-check mr-2"></i>
          Absensi — {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
        </h3>
        @if($anggotas->isEmpty())
          <span class="badge badge-warning">Belum ada anggota</span>
        @else
          <span class="badge badge-info">{{ $anggotas->count() }} siswa</span>
        @endif
      </div>
      @if($anggotas->isEmpty())
        <div class="card-body text-center text-muted py-5">
          <i class="fas fa-user-slash fa-2x mb-2"></i>
          <p>Belum ada anggota untuk ekskul ini. Silakan daftarkan siswa melalui admin.</p>
        </div>
      @else
        <form action="{{ route('guru.ekskul.absensi.store', $ekskul) }}" method="POST">
          @csrf
          <input type="hidden" name="tanggal" value="{{ $tanggal }}">
          <div class="card-body p-0">
            <table class="table table-hover mb-0">
              <thead class="thead-light">
                <tr>
                  <th width="40">#</th>
                  <th>Nama Siswa</th>
                  <th>Kelas</th>
                  <th>Status</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                @foreach($anggotas as $i => $a)
                @php $prevAbsensi = $existingAbsensi->get($a->siswa_id); @endphp
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td><strong>{{ $a->siswa->nama }}</strong></td>
                  <td>{{ $a->siswa->kelas?->nama_kelas ?? '-' }}</td>
                  <td>
                    <div class="d-flex" style="gap:6px;">
                      @foreach(['Hadir', 'Izin', 'Sakit', 'Alpa'] as $status)
                        @php
                          $checked = old("absensi.{$a->siswa_id}.status", $prevAbsensi?->status ?? 'Hadir') === $status;
                          $colorMap = ['Hadir' => 'success', 'Izin' => 'info', 'Sakit' => 'warning', 'Alpa' => 'danger'];
                        @endphp
                        <div class="custom-control custom-radio">
                          <input type="radio"
                                 id="status_{{ $a->siswa_id }}_{{ $status }}"
                                 name="absensi[{{ $a->siswa_id }}][status]"
                                 value="{{ $status }}"
                                 class="custom-control-input"
                                 {{ $checked ? 'checked' : '' }}>
                          <label class="custom-control-label badge badge-{{ $colorMap[$status] }}"
                                 for="status_{{ $a->siswa_id }}_{{ $status }}"
                                 style="cursor:pointer;padding:4px 8px;">
                            {{ $status }}
                          </label>
                        </div>
                      @endforeach
                    </div>
                  </td>
                  <td>
                    <input type="text" name="absensi[{{ $a->siswa_id }}][keterangan]"
                           class="form-control form-control-sm"
                           value="{{ old("absensi.{$a->siswa_id}.keterangan", $prevAbsensi?->keterangan) }}"
                           placeholder="Opsional">
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save mr-1"></i> Simpan Absensi
            </button>
            @if($existingAbsensi->count() > 0)
              <span class="text-muted ml-3">
                <i class="fas fa-info-circle"></i>
                Data absensi tanggal ini sudah ada dan akan diperbarui.
              </span>
            @endif
          </div>
        </form>
      @endif
    </div>
  </div>
</div>
@endsection
