@extends('layouts.public')
@section('title', 'Struktur Organisasi')
@section('page_title', 'Struktur Organisasi')
@section('meta_desc', 'Struktur organisasi ' . setting('sekolah_nama', 'SMK'))

@push('styles')
<style>
/* ═══════════════════════════════════════════
   DESKTOP TREE  (≥992px)
═══════════════════════════════════════════ */
.org-scroll-wrapper {
  overflow-x: auto;
  overflow-y: visible;
  padding: 40px 0;
  display: block; /* Removed flex to allow normal horizontal scrolling */
  text-align: center; /* Keep content centered when smaller than wrapper */
  /* Tambahkan custom scrollbar */
  scrollbar-width: thin;
  scrollbar-color: var(--primary) transparent;
}
.org-scroll-wrapper::-webkit-scrollbar { height: 8px; }
.org-scroll-wrapper::-webkit-scrollbar-thumb { background-color: var(--primary); border-radius: 4px; }

.org-tree-desktop {
  display: inline-flex;
  flex-direction: column;
  align-items: center;
  min-width: max-content;
  padding: 20px 40px;
}

/* Baris root */
.org-root-row {
  display: flex;
  align-items: flex-start;
  gap: 0;
  position: relative;
}
.org-root-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 30px;
}

/* Garis vertikal turun ke baris children */
.org-vline {
  width: 2px;
  height: 40px;
  margin: 0 auto;
}
.org-vline.komando    { background: var(--primary); }
.org-vline.koordinasi { background: repeating-linear-gradient(to bottom,var(--accent) 0 6px,transparent 6px 10px); }
.org-vline.konsultasi { background: repeating-linear-gradient(to bottom,var(--text-muted) 0 2px,transparent 2px 6px); }

/* Baris children (level 1) */
.org-l1-row {
  display: flex;
  align-items: flex-start;
  gap: 0;
  position: relative;
}
.org-l1-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 15px;
}
.org-l1-col::before {
  content: '';
  display: block;
  width: 2px;
  height: 30px;
}
.org-l1-col.komando::before    { background: var(--primary); }
.org-l1-col.koordinasi::before { background: repeating-linear-gradient(to bottom,var(--accent) 0 6px,transparent 6px 10px); }
.org-l1-col.konsultasi::before { background: repeating-linear-gradient(to bottom,var(--text-muted) 0 2px,transparent 2px 6px); }

/* Node cards (Modernized) */
.org-node {
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  width: 220px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  position: relative; z-index: 1;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s;
  border: 2px solid transparent;
  background: #fff;
}
.org-node:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  border-color: var(--primary);
}

.org-node.lv0 {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
  width: 250px;
  border: none;
}
.org-node.lv0 .nt { color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.2); font-size: 0.85rem; }
.org-node.lv0 .nn { color: #fff; font-size: 1rem; }
.org-node.lv0 .nm { color: rgba(255,255,255,0.8); }

.org-node.koordinasi-border { border-style: dashed; border-color: var(--accent); }
.org-node.lv0.koordinasi-border { background: linear-gradient(135deg, #185a8a 0%, #104163 100%); border: 2px dashed var(--accent); }

.org-node.lv1 { border: 1px solid rgba(0,0,0,0.08); border-top: 4px solid var(--primary); }

.nt { font-family: 'Poppins', sans-serif; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--primary); margin-bottom: 8px; }
.nn { font-family: 'Poppins', sans-serif; font-size: 0.9rem; font-weight: 600; color: var(--text-dark); margin-bottom: 2px; }
.nm { font-size: 0.75rem; color: var(--text-muted); }

/* Foto avatar (Modernized) */
.org-avatar-wrapper {
  width: 75px; height: 75px;
  margin: 0 auto 15px;
  position: relative;
  border-radius: 50%;
  padding: 3px;
  background: #fff;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.org-node.lv0 .org-avatar-wrapper {
  background: rgba(255,255,255,0.2);
}
.org-avatar {
  width: 100%; height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--accent);
}
.org-avatar-ph {
  width: 100%; height: 100%;
  border-radius: 50%;
  background: var(--bg-gray);
  display: flex; align-items: center; justify-content: center;
  border: 2px solid #cbd5e0;
}
.org-avatar-ph i { font-size: 1.5rem; color: #a0aec0; }

/* Level 2: compact list (Modernized) */
.org-l2-list {
  margin-top: 15px;
  width: 100%;
  background: var(--bg-light);
  border-radius: 8px;
  padding: 5px;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
}
.org-l2-item {
  display: flex; align-items: flex-start;
  padding: 8px 10px;
  border-radius: 6px;
  background: #fff;
  border: 1px solid rgba(0,0,0,0.05);
  margin-bottom: 5px;
  text-align: left;
  gap: 10px;
  transition: background 0.2s;
}
.org-l2-item:last-child { margin-bottom: 0; }
.org-l2-item:hover { background: #f8f9fa; }

.org-l2-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--primary); flex-shrink: 0; margin-top: 6px;
}
.org-l2-dot.koordinasi { background: var(--accent); }
.org-l2-dot.konsultasi { background: var(--text-muted); }
.org-l2-name { font-size: 0.75rem; font-weight: 600; color: var(--text-dark); line-height: 1.3; font-family: 'Poppins', sans-serif;}
.org-l2-person { font-size: 0.7rem; color: var(--text-muted); line-height: 1.3; }

/* Legenda (Modernized) */
.org-legend {
  display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;
  background: #fff; padding: 15px 25px; border-radius: 30px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  display: inline-flex; margin: 0 auto 2rem;
}
.org-legend-item { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; color: var(--text-dark); font-weight: 500; }
.leg-line { width: 35px; border-top-width: 3px; border-top-style: solid; }
.leg-line.komando    { border-color: var(--primary); }
.leg-line.koordinasi { border-color: var(--accent); border-top-style: dashed; }
.leg-line.konsultasi { border-color: var(--text-muted); border-top-style: dotted; border-top-width: 3px; }

/* ═══════════════════════════════════════════
   MOBILE ACCORDION  (<992px)
═══════════════════════════════════════════ */
.org-mobile { background:#fff; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,.08); overflow:hidden; }
.org-mobile-root {
  border-bottom: 1px solid #f1f5f9;
}
.org-mobile-root:last-child { border-bottom:none; }
.org-mobile-root-header {
  display:flex; align-items:center; justify-content:space-between;
  padding: 18px 20px; cursor:pointer;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
  color: #fff;
  gap: 15px;
  transition: background 0.3s;
}
.org-mobile-root-header:hover { background: var(--primary); }
.org-mobile-root-header.koordinasi { background: #185a8a; border-left: 5px solid var(--accent); }
.org-mobile-root-header h6 { margin:0; font-size: 1rem; font-weight:700; font-family: 'Poppins', sans-serif;}
.org-mobile-root-header small { font-size: 0.8rem; opacity: 0.85; }
.org-mobile-root-header .chevron { transition:transform 0.3s ease; flex-shrink:0; }
.org-mobile-root-header:not(.collapsed) .chevron { transform:rotate(180deg); }

.org-mobile-children { padding:0 0 10px; background: var(--bg-light); }
.org-mobile-l1 { padding: 15px 20px; border-bottom: 1px solid #e2e8f0; background: #fff; }
.org-mobile-l1:last-child { border-bottom:none; }
.org-mobile-l1-header { display:flex; align-items:center; gap: 12px; margin-bottom: 8px; }
.org-mobile-l1-dot { width: 10px; height: 10px; border-radius:50%; background: var(--primary); flex-shrink:0; }
.org-mobile-l1-name { font-size: 0.9rem; font-weight:600; color: var(--primary); font-family: 'Poppins', sans-serif;}
.org-mobile-l1-person { font-size: 0.8rem; color: var(--text-muted); }
.org-mobile-l2 { margin-left: 22px; margin-top: 10px; border-left: 2px solid var(--bg-gray); padding-left: 15px; }
.org-mobile-l2-item { display:flex; align-items:flex-start; gap: 10px; padding: 5px 0; position: relative; }
.org-mobile-l2-item::before {
  content: ''; position: absolute; left: -15px; top: 15px; width: 10px; height: 2px; background: var(--bg-gray);
}
.org-mobile-l2-text .name { font-size: 0.85rem; font-weight:500; color: var(--text-dark); font-family: 'Poppins', sans-serif;}
.org-mobile-l2-text .person { font-size: 0.75rem; color: var(--text-muted); }
</style>
@endpush

@section('content')

{{-- Header --}}
<section style="background: linear-gradient(135deg, rgba(15,60,92,0.95) 0%, rgba(24,90,138,0.9) 100%); padding:120px 0 80px; position:relative; overflow:hidden;">
  <div class="container text-center text-white position-relative" style="z-index: 2;">
    <h6 class="text-warning fw-bold text-uppercase tracking-wider mb-2" data-aos="fade-down">Profil Sekolah</h6>
    <h1 class="fw-bold mb-3" style="font-size: clamp(2rem, 4vw, 3rem); font-family: 'Poppins', sans-serif;" data-aos="fade-up">Struktur Organisasi</h1>
    <p class="mb-0 mx-auto" style="opacity: 0.9; max-width: 600px; font-size: 1.1rem;" data-aos="fade-up" data-aos-delay="100">
      Susunan kepengurusan dan tata kelola {{ setting('sekolah_nama','SMK') }}
    </p>
  </div>
  <!-- Decorative background elements -->
  <i class="fas fa-sitemap position-absolute" style="font-size: 20rem; color: rgba(255,255,255,0.05); right: -50px; bottom: -50px; z-index: 1;"></i>
</section>

<section class="py-5" style="background:var(--bg-light); min-height: 50vh;">
  <div class="container-fluid px-3 px-lg-5">

    @if($jabatans->count())

    {{-- Legenda --}}
    <div class="text-center w-100" data-aos="fade-in" data-aos-delay="200">
      <div class="org-legend">
        <div class="org-legend-item"><div class="leg-line komando"></div> Garis Komando</div>
        <div class="org-legend-item"><div class="leg-line koordinasi"></div> Garis Koordinasi</div>
        <div class="org-legend-item"><div class="leg-line konsultasi"></div> Garis Konsultasi</div>
      </div>
    </div>

    {{-- ─────────────────────────────────────────── --}}
    {{-- DESKTOP TREE  (d-none d-lg-block)          --}}
    {{-- ─────────────────────────────────────────── --}}
    <div class="d-none d-lg-block" data-aos="fade-up" data-aos-delay="300">
      <div class="org-scroll-wrapper">
        <div class="org-tree-desktop">

          {{-- BARIS ROOT --}}
          <div class="org-root-row" id="rootRow">
            @foreach($jabatans as $root)
            <div class="org-root-col" data-tipe="{{ $root->tipe_hubungan }}">
              @php $ar = $root->anggota->first(); @endphp
              <div class="org-node lv0 {{ $root->tipe_hubungan==='koordinasi'?'koordinasi-border':'' }}">
                <div class="org-avatar-wrapper">
                  @if($ar?->foto)
                    <img src="{{ asset('storage/'.$ar->foto) }}" class="org-avatar" alt="{{ $ar->nama }}">
                  @else
                    <div class="org-avatar-ph"><i class="fas fa-user"></i></div>
                  @endif
                </div>
                <div class="nt">{{ $root->nama_jabatan }}</div>
                @if($ar) <div class="nn">{{ $ar->nama }}</div>
                  @if($ar->nip) <div class="nm">NIP. {{ $ar->nip }}</div> @endif
                @endif
              </div>

              {{-- Level 1: hanya tampil kalau ada children --}}
              @if($root->children->count())
              <div class="org-vline komando"></div>
              <div class="org-l1-row" id="l1-{{ $root->id }}">
                @foreach($root->children as $l1)
                @php $al1 = $l1->anggota->first(); @endphp
                <div class="org-l1-col {{ $l1->tipe_hubungan }}">
                  <div class="org-node lv1 {{ $l1->tipe_hubungan==='koordinasi'?'koordinasi-border':'' }}">
                    <div class="nt">{{ $l1->nama_jabatan }}</div>
                    @if($al1)
                      <div class="nn">{{ $al1->nama }}</div>
                      @if($al1->nip) <div class="nm">NIP. {{ $al1->nip }}</div> @endif
                    @endif
                  </div>

                  {{-- Level 2: compact list di bawah card l1 --}}
                  @if($l1->children->count())
                  <div class="org-l2-list">
                    @foreach($l1->children as $l2)
                    @php $al2 = $l2->anggota->first(); @endphp
                    <div class="org-l2-item">
                      <div class="org-l2-dot {{ $l2->tipe_hubungan }}"></div>
                      <div>
                        <div class="org-l2-name">{{ $l2->nama_jabatan }}</div>
                        @if($al2) <div class="org-l2-person">{{ $al2->nama }}</div> @endif
                      </div>
                    </div>
                    @endforeach
                  </div>
                  @endif
                </div>
                @endforeach
              </div>
              @endif

            </div>
            @endforeach
          </div>

        </div>
      </div>
    </div>

    {{-- ─────────────────────────────────────────── --}}
    {{-- MOBILE ACCORDION  (d-lg-none)              --}}
    {{-- ─────────────────────────────────────────── --}}
    <div class="d-lg-none" data-aos="fade-up" data-aos-delay="300">
      <div class="org-mobile mx-auto" style="max-width: 600px;">
        @foreach($jabatans as $idx => $root)
        @php $ar = $root->anggota->first(); $accId = 'acc-'.$root->id; @endphp
        <div class="org-mobile-root">
          {{-- Header root --}}
          <div class="org-mobile-root-header {{ $root->tipe_hubungan==='koordinasi'?'koordinasi':'' }} {{ $root->children->count()?'':'pe-none collapsed' }}"
               data-bs-toggle="{{ $root->children->count()?'collapse':'' }}"
               data-bs-target="#{{ $accId }}"
               aria-expanded="{{ $idx === 0 && $root->children->count() ? 'true' : 'false' }}">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-white rounded-circle p-1" style="width:45px; height:45px;">
                @if($ar?->foto)
                  <img src="{{ asset('storage/'.$ar->foto) }}" class="rounded-circle w-100 h-100" style="object-fit:cover;" alt="">
                @else
                  <div class="w-100 h-100 rounded-circle bg-light d-flex align-items-center justify-content-center text-primary"><i class="fas fa-user"></i></div>
                @endif
              </div>
              <div>
                <h6>{{ $root->nama_jabatan }}</h6>
                @if($ar) <small>{{ $ar->nama }}</small> @endif
              </div>
            </div>
            @if($root->children->count())
            <i class="fas fa-chevron-down chevron fa-lg"></i>
            @endif
          </div>

          {{-- Children accordion body --}}
          @if($root->children->count())
          <div id="{{ $accId }}" class="collapse {{ $idx===0?'show':'' }}">
            <div class="org-mobile-children">
              @foreach($root->children as $l1)
              @php $al1 = $l1->anggota->first(); @endphp
              <div class="org-mobile-l1">
                <div class="org-mobile-l1-header">
                  <div class="org-mobile-l1-dot"></div>
                  <div>
                    <div class="org-mobile-l1-name">{{ $l1->nama_jabatan }}</div>
                    @if($al1) <div class="org-mobile-l1-person">{{ $al1->nama }}</div> @endif
                  </div>
                </div>
                @if($l1->children->count())
                <div class="org-mobile-l2">
                  @foreach($l1->children as $l2)
                  @php $al2 = $l2->anggota->first(); @endphp
                  <div class="org-mobile-l2-item">
                    <div class="org-mobile-l2-text">
                      <div class="name">{{ $l2->nama_jabatan }}</div>
                      @if($al2) <div class="person">{{ $al2->nama }}</div> @endif
                    </div>
                  </div>
                  @endforeach
                </div>
                @endif
              </div>
              @endforeach
            </div>
          </div>
          @endif
        </div>
        @endforeach
      </div>
    </div>

    @else
    <div class="text-center py-5" data-aos="fade-in">
      <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-4" style="width: 100px; height: 100px;">
        <i class="fas fa-sitemap fa-3x" style="color:var(--text-muted)"></i>
      </div>
      <h4 class="fw-bold text-dark mb-2">Data Belum Tersedia</h4>
      <p class="text-muted">Struktur organisasi sekolah belum diisi.</p>
    </div>
    @endif

  </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
  // ── Horizontal bar di atas baris level 1 (garis komando) ──
  document.querySelectorAll('[id^="l1-"]').forEach(function (row) {
    const cols = row.querySelectorAll('.org-l1-col');
    if (cols.length < 2) return;
    const rr    = row.getBoundingClientRect();
    const first = cols[0].getBoundingClientRect();
    const last  = cols[cols.length - 1].getBoundingClientRect();
    const left  = first.left + first.width / 2 - rr.left;
    const right = rr.right - (last.left + last.width / 2);
    row.style.position = 'relative';
    const bar = document.createElement('div');
    bar.style.cssText = 'position:absolute;top:0;left:' + left + 'px;right:' + right + 'px;height:2px;background:var(--primary);pointer-events:none;';
    row.insertBefore(bar, row.firstChild);
  });

  // ── Garis koordinasi/konsultasi antar root ──
  const svg     = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;overflow:visible;';
  const rootRow = document.getElementById('rootRow');
  if (!rootRow) return;
  rootRow.style.position = 'relative';
  rootRow.appendChild(svg);

  const cols = Array.from(rootRow.querySelectorAll('.org-root-col'));
  if (cols.length < 2) return;

  const rrRect = rootRow.getBoundingClientRect();

  function centerOf(col) {
    const card = col.querySelector('.org-node');
    const r = card.getBoundingClientRect();
    return {
      x: r.left + r.width / 2 - rrRect.left,
      y: r.top  + r.height / 2 - rrRect.top,
    };
  }

  // Get CSS variables for colors
  const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#0f3c5c';
  const accentColor = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#f39c12';
  const mutedColor = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim() || '#7f8c8d';

  for (let i = 1; i < cols.length; i++) {
    const tipe = cols[i].dataset.tipe;
    if (tipe === 'komando') continue;

    const a = centerOf(cols[i - 1]);
    const b = centerOf(cols[i]);

    const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    line.setAttribute('x1', a.x); line.setAttribute('y1', a.y);
    line.setAttribute('x2', b.x); line.setAttribute('y2', b.y);
    line.setAttribute('stroke-width', '3');
    line.setAttribute('stroke', tipe === 'koordinasi' ? accentColor : mutedColor);
    line.setAttribute('stroke-dasharray', tipe === 'koordinasi' ? '8 6' : '3 6');
    svg.appendChild(line);
  }
})();
</script>
@endpush
