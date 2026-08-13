@php
    $logoSrc = null;
    $logoPaths = [
        public_path('logo.png'),
        base_path('../public_html/logo.png'),
        base_path('public/logo.png'),
        public_path('images/logo.png'),
        base_path('../public_html/images/logo.png'),
    ];
    foreach ($logoPaths as $path) {
        if ($path && file_exists($path)) {
            $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
            break;
        }
    }
@endphp
@if($logoSrc)
    <img src="{{ $logoSrc }}" class="{{ $class ?? 'logo-img' }}" style="{{ $style ?? '' }}" alt="Logo Toko">
@endif
