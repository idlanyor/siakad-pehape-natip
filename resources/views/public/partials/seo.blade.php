{{--
  Usage: @include('public.partials.seo', ['title' => '...', 'description' => '...', 'image' => '...'])
  Falls back to settings if not provided.
--}}
@php
  $seoTitle   = ($title ?? '') ? ($title . ' — ' . setting('sekolah_nama')) : setting('sekolah_nama');
  $seoDesc    = $description ?? setting('sekolah_tagline', '');
  $seoImage   = $image ?? setting_url('logo') ?? '';
  $seoUrl     = url()->current();
@endphp

<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ $seoUrl }}">
<meta property="og:title"       content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDesc }}">
@if($seoImage)
  <meta property="og:image"     content="{{ $seoImage }}">
@endif
<meta property="og:site_name"   content="{{ setting('sekolah_nama') }}">

<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDesc }}">
@if($seoImage)
  <meta name="twitter:image"    content="{{ $seoImage }}">
@endif

<link rel="canonical" href="{{ $seoUrl }}">
