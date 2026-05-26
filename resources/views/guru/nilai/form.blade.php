@extends('layouts.admin')
@section('title', 'Input Nilai')
@section('page_title', 'Input Nilai — ' . $jadwal->mapel->nama_mapel)

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card card-primary card-outline">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          <span class="badge badge-primary">{{ $jadwal->mapel->nama_mapel }}</span>
          &mdash; {{ $jadwal->kelas?->nama_kelas }}
          | {{ $jadwal->hari }}, {{ $jadwal->jam_mulai }}-{{ $jadwal->jam_selesai }}
          | Sem {{ $jadwal->semester?->semester }} / {{ $jadwal->semester?->tahunAjaran->nama }}
        </h3>
        <a href="{{ route('guru.nilai.index') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>

      @if($existing->isNotEmpty())
      <div class="card-body pb-0">
        <div class="alert alert-info mb-0">
          <i class="fas fa-info-circle"></i> Nilai sudah pernah diisi — data lama akan ditampilkan dan bisa diperbarui.
        </div>
      </div>
      @endif

      <form action="{{ route('guru.nilai.simpan', $jadwal) }}" method="POST">
        @csrf
        <div class="card-body table-responsive p-0">
          <table class="table table-bordered table-hover table-sm">
            <thead class="thead-light">
              <tr>
                <th class="text-center" style="width:40px">#</th>
                <th>Nama Siswa</th>
                <th>NISN</th>
                <th class="text-center" colspan="3">Pengetahuan</th>
                <th class="text-center" colspan="3">Keterampilan</th>
                <th>Catatan</th>
              </tr>
              <tr>
                <th></th>
                <th></th>
                <th></th>
                <th class="text-center" style="width:80px">Tugas</th>
                <th class="text-center" style="width:80px">PTS</th>
                <th class="text-center" style="width:80px">PAS</th>
                <th class="text-center" style="width:80px">Praktik</th>
                <th class="text-center" style="width:80px">Proyek</th>
                <th class="text-center" style="width:90px">Portofolio</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach($siswas as $i => $s)
              @php $n = $existing[$s->id] ?? null; @endphp
              <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $s->nama }}</td>
                <td><small class="text-muted">{{ $s->nisn }}</small></td>
                @foreach(\App\Models\Nilai::KOMPONEN_ALL as $k)
                <td>
                  <input type="number" step="0.01" min="0" max="100"
                         name="nilai[{{ $s->id }}][{{ $k }}]"
                         value="{{ old("nilai.{$s->id}.{$k}", $n?->$k) }}"
                         class="form-control form-control-sm text-center nilai-input"
                         placeholder="-">
                </td>
                @endforeach
                <td>
                  <input type="text" name="nilai[{{ $s->id }}][catatan]"
                         value="{{ old("nilai.{$s->id}.catatan", $n?->catatan) }}"
                         class="form-control form-control-sm" placeholder="Opsional">
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('guru.nilai.rekap', $jadwal) }}" class="btn btn-info">
            <i class="fas fa-table"></i> Lihat Rekap
          </a>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Simpan Nilai
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .nilai-input { width: 70px !important; }
  .nilai-input:focus { background-color: #fffde7; }
</style>
@endpush
