<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Instalasi SIAKAD — @yield('step')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { background:linear-gradient(135deg,#1a3a6b,#2d5a9e); min-height:100vh; padding:2rem 1rem; font-family:system-ui,sans-serif; }
    .wizard { max-width:680px; margin:0 auto; background:#fff; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,.3); overflow:hidden; }
    .wizard-head { background:#1a3a6b; color:#fff; padding:1.5rem 2rem; }
    .wizard-body { padding:2rem; }
    .steps { display:flex; gap:.5rem; margin-top:1rem; }
    .steps .dot { flex:1; height:5px; border-radius:3px; background:rgba(255,255,255,.3); }
    .steps .dot.on { background:#fff; }
  </style>
</head>
<body>
<div class="wizard">
  <div class="wizard-head">
    <h4 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Instalasi SIAKAD SMK</h4>
    <div class="steps">
      @for($i = 1; $i <= 3; $i++)
        <div class="dot {{ $i <= ($activeStep ?? 1) ? 'on' : '' }}"></div>
      @endfor
    </div>
  </div>
  <div class="wizard-body">
    @if(session('error'))
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}</div>
    @endif
    @yield('wizard')
  </div>
</div>
</body>
</html>
