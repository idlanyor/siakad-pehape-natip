{{-- Flash message terpusat. Di-include sekali di layouts.admin.
     Jangan menambah blok session('success'/'error') inline di view lain — akan dobel. --}}
@php
    $flashMap = [
        'success' => ['alert-success', 'fa-check-circle'],
        'error'   => ['alert-danger',  'fa-exclamation-circle'],
        'warning' => ['alert-warning', 'fa-exclamation-triangle'],
        'info'    => ['alert-info',    'fa-info-circle'],
    ];
@endphp
@foreach($flashMap as $key => [$class, $icon])
    @if(session($key))
        <div class="alert {{ $class }} alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas {{ $icon }} mr-1"></i> {{ session($key) }}
        </div>
    @endif
@endforeach
