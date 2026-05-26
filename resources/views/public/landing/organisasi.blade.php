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
  padding-bottom: 24px;
}
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
  padding: 0 20px;
}

/* Garis vertikal turun ke baris children */
.org-vline {
  width: 2px;
  height: 32px;
  margin: 0 auto;
}
.org-vline.komando    { background: #1a3a6b; }
.org-vline.koordinasi { background: repeating-linear-gradient(to bottom,#e8a020 0 6px,transparent 6px 10px); }
.org-vline.konsultasi { background: repeating-linear-gradient(to bottom,#718096 0 2px,transparent 2px 6px); }

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
  padding: 0 10px;
}
.org-l1-col::before {
  content: '';
  display: block;
  width: 2px;
  height: 28px;
}
.org-l1-col.komando::before    { background: #1a3a6b; }
.org-l1-col.koordinasi::before { background: repeating-linear-gradient(to bottom,#e8a020 0 6px,transparent 6px 10px); }
.org-l1-col.konsultasi::before { background: repeating-linear-gradient(to bottom,#718096 0 2px,transparent 2px 6px); }

/* Node cards */
.org-node {
  border-radius: 10px;
  padding: 12px 16px;
  text-align: center;
  width: 170px;
  box-shadow: 0 2px 8px rgba(26,58,107,.09);
  position: relative; z-index: 1;
  transition: transform .2s, box-shadow .2s;
  border: 2px solid #1a3a6b;
  background: #fff;
}
.org-node:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(26,58,107,.15); }

.org-node.lv0 { background:#1a3a6b; border-color:#1a3a6b; width:190px; }
.org-node.lv0 .nt, .org-node.lv0 .nn, .org-node.lv0 .nm { color:#fff; }
.org-node.lv0 .nm { color:rgba(255,255,255,.7); }

.org-node.koordinasi-border { border-style:dashed; border-color:#e8a020; }
.org-node.lv0.koordinasi-border { background:#2d5f9e; border-color:#e8a020; }

.org-node.lv2 { border-color:#94a3b8; width:155px; background:#f8fafc; }

.nt { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#1a3a6b; margin-bottom:3px; }
.nn { font-size:.82rem; font-weight:600; color:#2d3748; margin-bottom:1px; }
.nm { font-size:.7rem;  color:#718096; }

/* Foto avatar */
.org-avatar {
  width:52px; height:52px; border-radius:50%; object-fit:cover;
  border:3px solid #e8a020; margin:0 auto 8px;
}
.org-avatar-ph {
  width:52px; height:52px; border-radius:50%;
  background:#e2e8f0; display:flex; align-items:center; justify-content:center;
  margin:0 auto 8px; border:2px solid #cbd5e0;
}

/* Level 2: compact list di bawah l1 card */
.org-l2-list {
  margin-top:6px;
  width:100%;
}
.org-l2-item {
  display:flex; align-items:flex-start;
  padding:5px 8px;
  border-radius:6px;
  background:#fff;
  border:1px solid #e2e8f0;
  margin-bottom:4px;
  text-align:left;
  gap:6px;
}
.org-l2-dot {
  width:6px; height:6px; border-radius:50%;
  background:#1a3a6b; flex-shrink:0; margin-top:5px;
}
.org-l2-dot.koordinasi { background:#e8a020; }
.org-l2-dot.konsultasi { background:#718096; }
.org-l2-name { font-size:.72rem; font-weight:600; color:#334155; line-height:1.3; }
.org-l2-person { font-size:.68rem; color:#94a3b8; line-height:1.3; }

/* Legenda */
.org-legend { display:flex; flex-wrap:wrap; gap:16px; justify-content:center; }
.org-legend-item { display:flex; align-items:center; gap:8px; font-size:.8rem; color:#4a5568; }
.leg-line { width:32px; border-top-width:2px; border-top-style:solid; }
.leg-line.komando    { border-color:#1a3a6b; }
.leg-line.koordinasi { border-color:#e8a020; border-top-style:dashed; }
.leg-line.konsultasi { border-color:#718096; border-top-style:dotted; border-top-width:3px; }

/* ═══════════════════════════════════════════
   MOBILE ACCORDION  (<992px)
═══════════════════════════════════════════ */
.org-mobile { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.07); overflow:hidden; }
.org-mobile-root {
  border-bottom: 1px solid #e2e8f0;
}
.org-mobile-root:last-child { border-bottom:none; }
.org-mobile-root-header {
  display:flex; align-items:center; justify-content:space-between;
  padding:14px 16px; cursor:pointer;
  background:#1a3a6b; color:#fff;
  gap:12px;
}
.org-mobile-root-header.koordinasi { background:#2d5f9e; border-left:4px solid #e8a020; }
.org-mobile-root-header h6 { margin:0; font-size:.9rem; font-weight:700; }
.org-mobile-root-header small { font-size:.75rem; opacity:.8; }
.org-mobile-root-header .chevron { transition:transform .25s; flex-shrink:0; }
.org-mobile-root-header.collapsed .chevron { transform:rotate(-90deg); }

.org-mobile-children { padding:0 0 8px; }
.org-mobile-l1 { padding:10px 16px; border-bottom:1px solid #f1f5f9; }
.org-mobile-l1:last-child { border-bottom:none; }
.org-mobile-l1-header { display:flex; align-items:center; gap:8px; margin-bottom:6px; }
.org-mobile-l1-dot { width:8px; height:8px; border-radius:50%; background:#1a3a6b; flex-shrink:0; }
.org-mobile-l1-name { font-size:.85rem; font-weight:600; color:#1a3a6b; }
.org-mobile-l1-person { font-size:.75rem; color:#718096; }
.org-mobile-l2 { margin-left:20px; margin-top:4px; }
.org-mobile-l2-item { display:flex; align-items:flex-start; gap:6px; padding:3px 0; }
.org-mobile-l2-dash { color:#cbd5e0; font-size:.8rem; flex-shrink:0; margin-top:1px; }
.org-mobile-l2-text .name { font-size:.78rem; font-weight:500; color:#374151; }
.org-mobile-l2-text .person { font-size:.72rem; color:#9ca3af; }
</style>
@endpush

@section('content')

{{-- Header --}}
<section style="background:linear-gradient(135deg,#1a3a6b 0%,#2d5f9e 100%);padding:100px 0 60px;margin-top:66px">
  <div class="container text-center text-white">
    <h1 class="fw-bold mb-2">Struktur Organisasi</h1>
    <p class="mb-0" style="opacity:.8">{{ setting('sekolah_nama','SMK') }}</p>
  </div>
</section>

<section class="py-5" style="background:#f7f9fc">
  <div class="container-fluid px-3 px-lg-4">

    @if($jabatans->count())

    {{-- Legenda --}}
    <div class="org-legend mb-4">
      <div class="org-legend-item"><div class="leg-line komando"></div> Garis Komando</div>
      <div class="org-legend-item"><div class="leg-line koordinasi"></div> Garis Koordinasi</div>
      <div class="org-legend-item"><div class="leg-line konsultasi"></div> Garis Konsultasi</div>
    </div>

    {{-- ─────────────────────────────────────────── --}}
    {{-- DESKTOP TREE  (d-none d-lg-block)          --}}
    {{-- ─────────────────────────────────────────── --}}
    <div class="d-none d-lg-block">
      <div class="org-scroll-wrapper">
        <div class="org-tree-desktop">

          {{-- BARIS ROOT --}}
          <div class="org-root-row" id="rootRow">
            @foreach($jabatans as $root)
            <div class="org-root-col" data-tipe="{{ $root->tipe_hubungan }}">
              @php $ar = $root->anggota->first(); @endphp
              <div class="org-node lv0 {{ $root->tipe_hubungan==='koordinasi'?'koordinasi-border':'' }}">
                @if($ar?->foto)
                  <img src="{{ asset('storage/'.$ar->foto) }}" class="org-avatar" alt="{{ $ar->nama }}">
                @else
                  <div class="org-avatar-ph"><i class="fas fa-user" style="color:#a0aec0"></i></div>
                @endif
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
    <div class="d-lg-none">
      <div class="org-mobile">
        @foreach($jabatans as $idx => $root)
        @php $ar = $root->anggota->first(); $accId = 'acc-'.$root->id; @endphp
        <div class="org-mobile-root">
          {{-- Header root --}}
          <div class="org-mobile-root-header {{ $root->tipe_hubungan==='koordinasi'?'koordinasi':'' }} {{ $root->children->count()?'':'pe-none' }}"
               data-bs-toggle="{{ $root->children->count()?'collapse':'' }}"
               data-bs-target="#{{ $accId }}">
            <div>
              <h6>{{ $root->nama_jabatan }}</h6>
              @if($ar) <small>{{ $ar->nama }}</small> @endif
            </div>
            @if($root->children->count())
            <i class="fas fa-chevron-down chevron"></i>
            @endif
          </div>

          {{-- Children accordion body --}}
          @if($root->children->count())
          <div id="{{ $accId }}" class="collapse {{ $idx===1?'show':'' }}">
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
                    <div class="org-mobile-l2-dash">└</div>
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
    <div class="text-center py-5">
      <i class="fas fa-sitemap fa-3x mb-3" style="color:#cbd5e0"></i>
      <p class="text-muted">Struktur organisasi belum tersedia.</p>
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
    bar.style.cssText = 'position:absolute;top:0;left:' + left + 'px;right:' + right + 'px;height:2px;background:#1a3a6b;pointer-events:none;';
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

  for (let i = 1; i < cols.length; i++) {
    const tipe = cols[i].dataset.tipe;
    if (tipe === 'komando') continue;

    const a = centerOf(cols[i - 1]);
    const b = centerOf(cols[i]);

    const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    line.setAttribute('x1', a.x); line.setAttribute('y1', a.y);
    line.setAttribute('x2', b.x); line.setAttribute('y2', b.y);
    line.setAttribute('stroke-width', '2');
    line.setAttribute('stroke', tipe === 'koordinasi' ? '#e8a020' : '#718096');
    line.setAttribute('stroke-dasharray', tipe === 'koordinasi' ? '8 5' : '2 5');
    svg.appendChild(line);
  }

  // ── Accordion chevron toggle ──
  document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (el) {
    el.addEventListener('click', function () {
      this.classList.toggle('collapsed');
    });
  });
})();
</script>
@endpush
