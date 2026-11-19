{{-- Gaya bersama halaman PPDB. Dipanggil sekali per halaman lewat @include('ppdb.partials.gaya'). --}}
@once
@push('styles')
<style>
  /* ── Header halaman ── */
  .ppdb-head {
    position: relative; overflow: hidden; padding: 56px 0 48px;
    background:
      radial-gradient(700px 300px at 90% -20%, rgba(245, 158, 11, .28), transparent 60%),
      linear-gradient(135deg, #071f33 0%, var(--primary) 55%, var(--primary-light) 100%);
    color: #fff;
  }
  .ppdb-head::before {
    content: ''; position: absolute; inset: 0; pointer-events: none;
    background-image: radial-gradient(rgba(255, 255, 255, .08) 1px, transparent 1px); background-size: 22px 22px;
    mask-image: linear-gradient(to right, transparent, #000 60%); -webkit-mask-image: linear-gradient(to right, transparent, #000 60%);
  }
  .ppdb-head .container { position: relative; }
  .ppdb-head h1 { color: #fff; font-weight: 800; font-size: clamp(1.8rem, 3.6vw, 2.6rem); letter-spacing: -.03em; margin: .6rem 0 .5rem; }
  .ppdb-head p { color: rgba(255, 255, 255, .75); font-size: 1.05rem; margin: 0; max-width: 640px; }
  .crumbs { display: flex; flex-wrap: wrap; gap: .4rem; font-size: .85rem; color: rgba(255, 255, 255, .6); list-style: none; padding: 0; margin: 0; }
  .crumbs a { color: rgba(255, 255, 255, .75); text-decoration: none; }
  .crumbs a:hover { color: #fff; }
  .crumbs li + li::before { content: '/'; margin-right: .4rem; opacity: .5; }

  /* ── Kartu & isi ── */
  .ppdb-body { padding: 48px 0 88px; background: var(--bg-light); }
  .ppdb-card { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); }
  .ppdb-card-pad { padding: clamp(1.25rem, 3vw, 2rem); }
  .ppdb-card h2 { font-size: 1.15rem; font-weight: 800; color: var(--ink); }
  .muted { color: var(--text-muted); }

  /* ── Form ── */
  .form-step { display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 1.5rem; }
  .form-step .n { width: 36px; height: 36px; border-radius: 12px; flex-shrink: 0; display: grid; place-items: center; background: var(--primary-soft); color: var(--primary); font-weight: 800; }
  .form-step h2 { margin: 0; }
  .form-step p { margin: .15rem 0 0; color: var(--text-muted); font-size: .9rem; }
  .ppdb-form .form-label { font-weight: 600; font-size: .9rem; color: var(--text-dark); margin-bottom: .4rem; }
  .ppdb-form .form-label .req { color: #dc2626; margin-left: 2px; }
  .ppdb-form .form-label .opt { color: var(--text-muted); font-weight: 500; font-size: .8rem; margin-left: .25rem; }
  .ppdb-form .form-control, .ppdb-form .form-select {
    border-radius: 12px; border-color: var(--line); padding: .7rem .9rem; font-size: .97rem; background-color: #fff;
  }
  .ppdb-form .form-control:focus, .ppdb-form .form-select:focus { border-color: var(--primary-light); box-shadow: 0 0 0 4px rgba(27, 93, 143, .12); }
  .ppdb-form .form-control::placeholder { color: #a0aec0; }
  .ppdb-form .form-text { color: var(--text-muted); font-size: .8rem; }
  .divider-x { height: 1px; background: var(--line); margin: 2rem 0; }

  .note { display: flex; gap: .9rem; padding: 1rem 1.1rem; border-radius: 14px; font-size: .93rem; line-height: 1.6; }
  .note i { margin-top: .2rem; }
  .note-info { background: var(--primary-soft); color: #0c3352; }
  .note-warn { background: #fffbeb; color: #78350f; border: 1px solid #fde68a; }
  .note-danger { background: #fef2f2; color: #7f1d1d; border: 1px solid #fecaca; }
  .note-ok { background: #f0fdf4; color: #14532d; border: 1px solid #bbf7d0; }

  /* ── Badge status (nilai warna dari model memakai nama Bootstrap) ── */
  .pill { display: inline-flex; align-items: center; gap: .4rem; padding: .3rem .75rem; border-radius: 999px; font-size: .8rem; font-weight: 700; }
  .pill::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
  .pill-success { background: #dcfce7; color: #166534; }
  .pill-warning { background: #fef3c7; color: #92400e; }
  .pill-info { background: #dbeafe; color: #1e40af; }
  .pill-danger { background: #fee2e2; color: #991b1b; }
  .pill-secondary { background: #f1f5f9; color: #475569; }

  /* ── Daftar data (label : nilai) ── */
  .kv { display: grid; grid-template-columns: minmax(120px, 38%) 1fr; margin: 0; }
  .kv dt, .kv dd { padding: .7rem 0; border-top: 1px solid var(--line); margin: 0; font-size: .95rem; }
  .kv dt { color: var(--text-muted); font-weight: 500; }
  .kv dd { color: var(--ink); font-weight: 600; }
  .kv dt:first-of-type, .kv dt:first-of-type + dd { border-top: 0; }
</style>
@endpush
@endonce
