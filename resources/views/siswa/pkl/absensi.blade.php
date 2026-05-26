@extends('layouts.admin')
@section('title', 'Absensi PKL')
@section('page_title', 'Absensi PKL — Tempat: ' . $penempatan->tempatPkl->nama)

@section('content')
<div class="row">
  {{-- Absensi Hari Ini --}}
  <div class="col-md-6">
    <div class="card card-primary card-outline">
      <div class="card-header"><h3 class="card-title">Absensi Hari Ini ({{ now()->isoFormat('dddd, D MMMM Y') }})</h3></div>
      <div class="card-body">

        {{-- Status absensi hari ini --}}
        @if($absensiHariIni)
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <strong>Masuk:</strong> {{ $absensiHariIni->jam_masuk ?? '-' }}
            &nbsp;|&nbsp;
            <strong>Pulang:</strong> {{ $absensiHariIni->jam_pulang ?? '(Belum absen pulang)' }}
          </div>
        @endif

        {{-- Preview Kamera --}}
        <div id="kamera-section">
          <div class="mb-2">
            <video id="video-preview" autoplay playsinline
                   style="width:100%;border-radius:8px;background:#000;display:none"></video>
            <canvas id="canvas" style="display:none"></canvas>
            <div id="foto-preview-wrap" style="display:none;margin-top:8px">
              <img id="foto-preview" style="width:100%;border-radius:8px">
            </div>
          </div>
          <div class="mb-2" id="lokasi-info" style="display:none">
            <small class="text-muted"><i class="fas fa-map-marker-alt"></i> Lokasi terdeteksi</small>
          </div>
          <div id="btn-buka-kamera" class="text-center">
            <button type="button" class="btn btn-info btn-block" onclick="bukaKamera()">
              <i class="fas fa-camera"></i> Buka Kamera & Ambil Lokasi
            </button>
          </div>
          <div id="btn-capture" style="display:none" class="text-center">
            <button type="button" class="btn btn-warning btn-block" onclick="ambilFoto()">
              <i class="fas fa-circle"></i> Ambil Foto
            </button>
          </div>
          <div id="btn-absensi" style="display:none" class="mt-2">
            @if(!$absensiHariIni)
              <button type="button" class="btn btn-success btn-block" onclick="kirimAbsensi('masuk')">
                <i class="fas fa-sign-in-alt"></i> Absen Masuk
              </button>
            @elseif(!$absensiHariIni->jam_pulang)
              <button type="button" class="btn btn-danger btn-block" onclick="kirimAbsensi('pulang')">
                <i class="fas fa-sign-out-alt"></i> Absen Pulang
              </button>
            @else
              <div class="alert alert-info mb-0">Absensi hari ini sudah lengkap.</div>
            @endif
          </div>
          <div id="status-msg" class="mt-2"></div>
        </div>

        {{-- Already fully done --}}
        @if($absensiHariIni && $absensiHariIni->jam_pulang)
          <div id="kamera-section" style="display:none"></div>
          <div class="alert alert-info">
            Absensi hari ini sudah selesai: Masuk {{ $absensiHariIni->jam_masuk }} — Pulang {{ $absensiHariIni->jam_pulang }}.
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Riwayat Absensi --}}
  <div class="col-md-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Riwayat Absensi ({{ $riwayat->count() }} hari)</h3>
        <a href="{{ route('siswa.pkl') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>
      <div class="card-body p-0 table-responsive" style="max-height:500px;overflow-y:auto">
        <table class="table table-sm table-bordered mb-0">
          <thead class="thead-light">
            <tr><th>Tanggal</th><th>Masuk</th><th>Pulang</th><th>Lokasi</th><th>Foto</th></tr>
          </thead>
          <tbody>
            @forelse($riwayat as $ab)
            <tr>
              <td>{{ $ab->tanggal->format('d/m/Y') }}</td>
              <td>{{ $ab->jam_masuk ?? '-' }}</td>
              <td class="{{ !$ab->jam_pulang ? 'text-danger' : '' }}">{{ $ab->jam_pulang ?? 'Belum' }}</td>
              <td>
                @if($ab->latitude)
                  <a href="https://maps.google.com/?q={{ $ab->latitude }},{{ $ab->longitude }}" target="_blank">
                    <i class="fas fa-map-marker-alt text-danger"></i>
                  </a>
                @else - @endif
              </td>
              <td>
                @if($ab->foto_masuk)
                  <a href="{{ asset('storage/' . $ab->foto_masuk) }}" target="_blank">
                    <img src="{{ asset('storage/' . $ab->foto_masuk) }}" width="36" class="rounded">
                  </a>
                @else - @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Belum ada riwayat.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let stream = null;
let fotoBase64 = null;
let lat = null, lng = null;

function bukaKamera() {
  document.getElementById('btn-buka-kamera').style.display = 'none';
  document.getElementById('status-msg').innerHTML = '<small class="text-info">Meminta akses kamera & lokasi...</small>';

  // Ambil lokasi
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(pos) {
      lat = pos.coords.latitude;
      lng = pos.coords.longitude;
      document.getElementById('lokasi-info').style.display = 'block';
      document.getElementById('lokasi-info').innerHTML =
        '<small class="text-success"><i class="fas fa-map-marker-alt"></i> ' +
        'Lokasi: ' + lat.toFixed(5) + ', ' + lng.toFixed(5) + '</small>';
    }, function() {
      document.getElementById('lokasi-info').style.display = 'block';
      document.getElementById('lokasi-info').innerHTML =
        '<small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Lokasi tidak terdeteksi (izin ditolak)</small>';
    });
  }

  // Buka kamera
  navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false })
    .then(function(s) {
      stream = s;
      var video = document.getElementById('video-preview');
      video.srcObject = s;
      video.style.display = 'block';
      document.getElementById('btn-capture').style.display = 'block';
      document.getElementById('status-msg').innerHTML = '';
    })
    .catch(function(err) {
      document.getElementById('btn-buka-kamera').style.display = 'block';
      document.getElementById('status-msg').innerHTML =
        '<div class="alert alert-danger py-1">Tidak bisa akses kamera: ' + err.message + '</div>';
    });
}

function ambilFoto() {
  var video = document.getElementById('video-preview');
  var canvas = document.getElementById('canvas');
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  canvas.getContext('2d').drawImage(video, 0, 0);
  fotoBase64 = canvas.toDataURL('image/jpeg', 0.7);

  // Preview foto
  document.getElementById('foto-preview').src = fotoBase64;
  document.getElementById('foto-preview-wrap').style.display = 'block';
  video.style.display = 'none';

  // Stop kamera
  if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }

  document.getElementById('btn-capture').style.display = 'none';
  document.getElementById('btn-absensi').style.display = 'block';
}

function kirimAbsensi(tipe) {
  if (!fotoBase64) {
    alert('Ambil foto terlebih dahulu!');
    return;
  }

  var btn = document.querySelector('#btn-absensi button');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

  var url = tipe === 'masuk'
    ? '{{ route("siswa.pkl.absensi.masuk") }}'
    : '{{ route("siswa.pkl.absensi.pulang") }}';

  fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({
      tanggal:   '{{ $hari_ini }}',
      latitude:  lat || 0,
      longitude: lng || 0,
      foto:      fotoBase64
    })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.getElementById('btn-absensi').innerHTML =
        '<div class="alert alert-success mb-0"><i class="fas fa-check-circle"></i> ' +
        'Absen ' + tipe + ' berhasil! Jam: ' + data.jam + '</div>';
      setTimeout(() => location.reload(), 2000);
    } else {
      document.getElementById('status-msg').innerHTML =
        '<div class="alert alert-danger py-1">' + (data.error || 'Gagal.') + '</div>';
      btn.disabled = false;
      btn.innerHTML = 'Coba Lagi';
    }
  })
  .catch(() => {
    document.getElementById('status-msg').innerHTML =
      '<div class="alert alert-danger py-1">Koneksi error. Coba lagi.</div>';
    btn.disabled = false;
  });
}
</script>
@endpush
