{{-- Pemilih anak aktif — tampil hanya jika orang tua punya >1 anak --}}
@if($ortu->siswas->count() > 1)
<div class="card card-outline card-primary">
  <div class="card-body py-2">
    <form action="{{ route('orangtua.anak.switch') }}" method="POST" class="form-inline">
      @csrf
      <label class="mr-2 font-weight-bold"><i class="fas fa-child mr-1"></i> Lihat data anak:</label>
      <select name="siswa_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
        @foreach($ortu->siswas as $s)
          <option value="{{ $s->id }}" {{ ($anak && $anak->id == $s->id) ? 'selected' : '' }}>
            {{ $s->nama }} ({{ $s->kelas->nama_kelas ?? '—' }}) — {{ ucfirst($s->pivot->hubungan) }}
          </option>
        @endforeach
      </select>
      <noscript><button type="submit" class="btn btn-sm btn-primary">Ganti</button></noscript>
    </form>
  </div>
</div>
@elseif($anak)
<p class="text-muted"><i class="fas fa-child mr-1"></i> Anak: <strong>{{ $anak->nama }}</strong> — {{ $anak->kelas->nama_kelas ?? '—' }}</p>
@endif
