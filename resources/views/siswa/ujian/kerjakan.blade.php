@extends('layouts.app')

@section('title', 'Kerjakan Ujian - ' . $sesi->ujian->judul)

@section('content')
<div class="container-fluid">
    <!-- Timer & Header -->
    <div class="card mb-3 sticky-top" style="top: 70px; z-index: 100;">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">{{ $sesi->ujian->judul }}</h6>
                    <small class="text-muted">{{ $sesi->ujian->jadwal->mapel->nama }}</small>
                </div>
                <div class="text-end">
                    <div id="timer" class="fs-4 fw-bold text-success mb-0"></div>
                    <small class="text-muted">Sisa Waktu</small>
                </div>
            </div>
            <div class="progress mt-2" style="height: 5px;">
                <div class="progress-bar" id="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Soal -->
        <div class="col-lg-9">
            <form id="form-ujian">
                @csrf
                @foreach($soals as $index => $soal)
                    <div class="card mb-3 soal-item" data-soal-id="{{ $soal->id }}" id="soal-{{ $index + 1 }}">
                        <div class="card-body">
                            <!-- Nomor & Tipe -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-primary me-2">Nomor {{ $index + 1 }}</span>
                                    <span class="badge bg-secondary">
                                        <i class="{{ $soal->tipe_icon }}"></i> {{ $soal->tipe_label }}
                                    </span>
                                    <span class="badge bg-info">Bobot: {{ $soal->bobot_nilai }}</span>
                                </div>
                                <span class="badge bg-success jawaban-status" data-soal="{{ $index + 1 }}" style="display: none;">
                                    <i class="fas fa-check"></i> Dijawab
                                </span>
                            </div>

                            <!-- Pertanyaan -->
                            <div class="mb-3">
                                <p class="mb-2">{!! nl2br(e($soal->pertanyaan)) !!}</p>
                                @if($soal->gambar)
                                    <img src="{{ $soal->gambar_url }}" alt="Gambar Soal" class="img-fluid rounded mb-2" style="max-width: 500px;">
                                @endif
                            </div>

                            @php
                                $jawaban = $sesi->jawabanSoal($soal->id);
                            @endphp

                            <!-- Pilihan Ganda -->
                            @if($soal->tipe_soal === 'pilihan_ganda')
                                <div class="opsi-container">
                                    @foreach($soal->opsi_acak ?? $soal->opsiJawabans as $opsi)
                                        <div class="form-check mb-2">
                                            <input
                                                class="form-check-input jawaban-input"
                                                type="radio"
                                                name="jawaban_{{ $soal->id }}"
                                                id="opsi_{{ $opsi->id }}"
                                                value="{{ $opsi->id }}"
                                                data-soal-id="{{ $soal->id }}"
                                                data-soal-nomor="{{ $index + 1 }}"
                                                {{ $jawaban && $jawaban->opsi_jawaban_id == $opsi->id ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="opsi_{{ $opsi->id }}">
                                                <strong>{{ $opsi->label }}.</strong> {{ $opsi->teks_opsi }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Benar/Salah -->
                            @if($soal->tipe_soal === 'benar_salah')
                                <div class="opsi-container">
                                    @foreach($soal->opsi_acak ?? $soal->opsiJawabans as $opsi)
                                        <div class="form-check mb-2">
                                            <input
                                                class="form-check-input jawaban-input"
                                                type="radio"
                                                name="jawaban_{{ $soal->id }}"
                                                id="opsi_{{ $opsi->id }}"
                                                value="{{ $opsi->id }}"
                                                data-soal-id="{{ $soal->id }}"
                                                data-soal-nomor="{{ $index + 1 }}"
                                                {{ $jawaban && $jawaban->opsi_jawaban_id == $opsi->id ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="opsi_{{ $opsi->id }}">
                                                <strong>{{ $opsi->teks_opsi }}</strong>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Essay -->
                            @if($soal->tipe_soal === 'essay')
                                <div>
                                    <textarea
                                        class="form-control jawaban-essay"
                                        name="essay_{{ $soal->id }}"
                                        rows="6"
                                        placeholder="Tulis jawaban Anda di sini..."
                                        data-soal-id="{{ $soal->id }}"
                                        data-soal-nomor="{{ $index + 1 }}"
                                    >{{ $jawaban ? $jawaban->jawaban_essay : '' }}</textarea>
                                    <small class="text-muted">Minimal 10 karakter</small>
                                </div>
                            @endif

                            <!-- Isian Singkat -->
                            @if($soal->tipe_soal === 'isian_singkat')
                                <div>
                                    <input
                                        type="text"
                                        class="form-control jawaban-isian"
                                        name="isian_{{ $soal->id }}"
                                        placeholder="Tulis jawaban singkat..."
                                        data-soal-id="{{ $soal->id }}"
                                        data-soal-nomor="{{ $index + 1 }}"
                                        value="{{ $jawaban ? $jawaban->jawaban_essay : '' }}"
                                    >
                                    <small class="text-muted">Jawaban singkat (1 baris)</small>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </form>

            <!-- Submit Button -->
            <div class="card">
                <div class="card-body text-center">
                    <button type="button" class="btn btn-success btn-lg" id="btn-submit">
                        <i class="fas fa-paper-plane"></i> Selesai & Submit Ujian
                    </button>
                </div>
            </div>
        </div>

        <!-- Navigasi Soal -->
        <div class="col-lg-3">
            <div class="card sticky-top" style="top: 150px;">
                <div class="card-header">
                    <h6 class="mb-0">Navigasi Soal</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2" id="nav-soal">
                        @foreach($soals as $index => $soal)
                            @php
                                $jawaban = $sesi->jawabanSoal($soal->id);
                                $isDijawab = false;
                                if ($soal->is_objektif) {
                                    $isDijawab = $jawaban && $jawaban->opsi_jawaban_id;
                                } else {
                                    $isDijawab = $jawaban && !empty($jawaban->jawaban_essay);
                                }
                            @endphp
                            <a
                                href="#soal-{{ $index + 1 }}"
                                class="btn btn-sm nav-soal-btn {{ $isDijawab ? 'btn-primary' : 'btn-outline-secondary' }}"
                                data-nomor="{{ $index + 1 }}"
                            >
                                {{ $index + 1 }}
                            </a>
                        @endforeach
                    </div>

                    <hr>

                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Dijawab:</span>
                            <strong id="count-dijawab">{{ $sesi->jawabanSiswas->where('dijawab_pada', '!=', null)->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Belum:</span>
                            <strong id="count-belum">{{ $soals->count() - $sesi->jawabanSiswas->where('dijawab_pada', '!=', null)->count() }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Submit Konfirmasi -->
<div class="modal fade" id="modal-submit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Submit Ujian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Anda akan submit ujian dengan status:</p>
                <ul>
                    <li>Soal dijawab: <strong id="modal-dijawab">0</strong></li>
                    <li>Soal belum dijawab: <strong id="modal-belum">0</strong></li>
                </ul>
                <p class="text-danger"><strong>Setelah submit, Anda tidak bisa mengubah jawaban!</strong></p>
                <p>Ketik <strong>SUBMIT</strong> untuk konfirmasi:</p>
                <input type="text" class="form-control" id="konfirmasi-input" placeholder="Ketik SUBMIT">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-submit-final" disabled>
                    <i class="fas fa-paper-plane"></i> Submit Ujian
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const sesiId = {{ $sesi->id }};
const totalSoal = {{ $soals->count() }};
const waktuMulai = new Date('{{ $sesi->waktu_mulai }}');
const durasiMenit = {{ $sesi->ujian->durasi_menit }};
const waktuSelesai = new Date(waktuMulai.getTime() + durasiMenit * 60000);

let autoSaveTimeout;
let tabSwitchCount = {{ $sesi->tab_switch_count }};

// Timer
function updateTimer() {
    const now = new Date();
    const sisaMs = waktuSelesai - now;

    if (sisaMs <= 0) {
        clearInterval(timerInterval);
        autoSubmit();
        return;
    }

    const sisaMenit = Math.floor(sisaMs / 60000);
    const sisaDetik = Math.floor((sisaMs % 60000) / 1000);
    const display = `${sisaMenit}:${sisaDetik.toString().padStart(2, '0')}`;

    document.getElementById('timer').textContent = display;

    // Warna timer
    const persen = (sisaMs / (durasiMenit * 60000)) * 100;
    const timer = document.getElementById('timer');
    if (persen > 50) {
        timer.className = 'fs-4 fw-bold text-success mb-0';
    } else if (persen > 20) {
        timer.className = 'fs-4 fw-bold text-warning mb-0';
    } else {
        timer.className = 'fs-4 fw-bold text-danger mb-0';
    }

    // Warning 5 menit terakhir
    if (sisaMenit === 5 && sisaDetik === 0) {
        alert('⚠️ Sisa waktu 5 menit!');
    }
}

const timerInterval = setInterval(updateTimer, 1000);
updateTimer();

// Auto-save jawaban
function saveJawaban(soalId, opsiId = null, essay = null) {
    const data = {
        soal_ujian_id: soalId,
        opsi_jawaban_id: opsiId,
        jawaban_essay: essay,
    };

    fetch(`/siswa/ujian/sesi/${sesiId}/jawab`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(data),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log('Jawaban tersimpan');
        }
    })
    .catch(err => console.error('Error save:', err));
}

// Radio button change
document.querySelectorAll('.jawaban-input').forEach(input => {
    input.addEventListener('change', function() {
        const soalId = this.dataset.soalId;
        const soalNomor = this.dataset.soalNomor;
        const opsiId = this.value;

        saveJawaban(soalId, opsiId, null);
        updateNavButton(soalNomor, true);
        updateCounter();
    });
});

// Essay auto-save (debounce)
document.querySelectorAll('.jawaban-essay, .jawaban-isian').forEach(input => {
    input.addEventListener('input', function() {
        const soalId = this.dataset.soalId;
        const soalNomor = this.dataset.soalNomor;
        const essay = this.value;

        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            saveJawaban(soalId, null, essay);
            updateNavButton(soalNomor, essay.trim().length > 0);
            updateCounter();
        }, 2000);
    });
});

// Update nav button
function updateNavButton(nomor, isDijawab) {
    const btn = document.querySelector(`.nav-soal-btn[data-nomor="${nomor}"]`);
    if (isDijawab) {
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-primary');
        document.querySelector(`.jawaban-status[data-soal="${nomor}"]`).style.display = 'inline-block';
    } else {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-secondary');
        document.querySelector(`.jawaban-status[data-soal="${nomor}"]`).style.display = 'none';
    }
}

// Update counter
function updateCounter() {
    const dijawab = document.querySelectorAll('.nav-soal-btn.btn-primary').length;
    const belum = totalSoal - dijawab;
    document.getElementById('count-dijawab').textContent = dijawab;
    document.getElementById('count-belum').textContent = belum;

    // Progress bar
    const persen = (dijawab / totalSoal) * 100;
    document.getElementById('progress-bar').style.width = persen + '%';
}

// Tab switch detection
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        tabSwitchCount++;
        fetch(`/siswa/ujian/sesi/${sesiId}/tab-switch`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
        .then(res => res.json())
        .then(data => {
            if (data.diskualifikasi) {
                alert(data.message);
                window.location.href = `/siswa/ujian/sesi/${sesiId}/hasil`;
            } else if (tabSwitchCount === 1 || tabSwitchCount === 3 || tabSwitchCount === 5) {
                alert(`⚠️ Peringatan! Anda telah pindah tab ${tabSwitchCount} kali. Jangan pindah tab lagi!`);
            } else if (tabSwitchCount > 8) {
                alert('⚠️ Peringatan terakhir! Anda akan didiskualifikasi jika pindah tab lagi!');
            }
        });
    }
});

// Prevent close tab
window.addEventListener('beforeunload', function(e) {
    e.preventDefault();
    e.returnValue = '';
});

// Submit button
document.getElementById('btn-submit').addEventListener('click', function() {
    const dijawab = document.querySelectorAll('.nav-soal-btn.btn-primary').length;
    const belum = totalSoal - dijawab;

    document.getElementById('modal-dijawab').textContent = dijawab;
    document.getElementById('modal-belum').textContent = belum;

    const modal = new bootstrap.Modal(document.getElementById('modal-submit'));
    modal.show();
});

// Konfirmasi input
document.getElementById('konfirmasi-input').addEventListener('input', function() {
    const btn = document.getElementById('btn-submit-final');
    if (this.value === 'SUBMIT') {
        btn.disabled = false;
    } else {
        btn.disabled = true;
    }
});

// Submit final
document.getElementById('btn-submit-final').addEventListener('click', function() {
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

    fetch(`/siswa/ujian/sesi/${sesiId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(res => res.json())
    .then(data => {
        window.location.href = `/siswa/ujian/sesi/${sesiId}/hasil`;
    })
    .catch(err => {
        alert('Error submit ujian. Coba lagi.');
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Ujian';
    });
});

// Auto-submit saat timeout
function autoSubmit() {
    alert('⏰ Waktu habis! Ujian otomatis di-submit.');
    fetch(`/siswa/ujian/sesi/${sesiId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(() => {
        window.location.href = `/siswa/ujian/sesi/${sesiId}/hasil`;
    });
}

// Smooth scroll nav
document.querySelectorAll('.nav-soal-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});
</script>
@endpush
