<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Passiflor – Regresa a Tu Ser Natural' }}</title>
    <meta name="description" content="Passiflor – Regresa a Tu Ser Natural. Terapias, recursos y acompañamiento para tu bienestar natural.">
    <meta name="keywords" content="Passiflor, terapia, bienestar, natural, salud, psicología, recursos, acompañamiento">
    <meta name="author" content="Passiflor">
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://passiflor.org/">
    <meta property="og:title" content="Passiflor – Regresa a Tu Ser Natural">
    <meta property="og:description" content="Terapias, recursos y acompañamiento para tu bienestar natural.">
    <meta property="og:image" content="https://passiflor.org/imgs/icon.png">
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://passiflor.org/">
    <meta name="twitter:title" content="Passiflor – Regresa a Tu Ser Natural">
    <meta name="twitter:description" content="Terapias, recursos y acompañamiento para tu bienestar natural.">
    <meta name="twitter:image" content="https://passiflor.org/imgs/icon.png">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&family=Hedvig+Letters+Serif:wght@400;500;700&family=ZCOOL+XiaoWei&display=swap" rel="stylesheet">
    <!-- Splitting.js and Anime.js CDN -->
    <script src="https://unpkg.com/splitting/dist/splitting.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
    @stack('head')
    <link rel="icon" type="image/png" href="https://passiflor.org/imgs/icon.png">
</head>