<?php
require_once __DIR__ . '/functions.php';
$titulo = $titulo ?? SITE_NAME . ' · ' . SITE_TAGLINE;
$activo = $activo ?? '';
$hero   = $hero ?? false;   // true = la página tiene hero full-bleed detrás del navbar
$nav = [
    ''             => 'Inicio',
    'habitaciones' => 'Habitaciones',
    'servicios'    => 'Servicios',
    'galeria'      => 'Galería',
    'faq'          => 'FAQ',
    'contacto'     => 'Contacto',
];
?>
<!DOCTYPE html>
<html lang="es" class="no-js scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?= e(SITE_DESC) ?>">
<title><?= e($titulo) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
tailwind.config = {
  theme: { extend: {
    colors: {
      // paleta Azure
      primary: { DEFAULT:'#005e99', strong:'#0077c0', deep:'#004a7a', container:'#0077c0', fixed:'#d0e4ff', 'fixed-dim':'#9bcbff' },
      secondary: { DEFAULT:'#00658d', 'fixed-dim':'#81cfff', fixed:'#c6e7ff' },
      tertiary: { DEFAULT:'#7a5300', 'fixed-dim':'#ffba3b' },
      background:'#f7fafe', surface:'#f7fafe', 'surface-container-low':'#f1f4f8', 'surface-container':'#ebeef2',
      'on-surface':'#181c1f', 'on-surface-variant':'#404751', 'inverse-surface':'#1d2a33',
      outline:'#707882', 'outline-variant':'#c0c7d2',
      // alias legados (páginas internas) -> Azure
      arena:'#f7fafe', 'arena-2':'#ebeef2', cloud:'#f1f4f8', sky:'#e7f1fb', espuma:'#e7f1fb',
      ocean:{ DEFAULT:'#005e99', deep:'#004a7a', light:'#0077c0' },
      mar:{ DEFAULT:'#005e99', dark:'#004a7a', light:'#0077c0' },
      coral:{ DEFAULT:'#005e99', deep:'#004a7a', light:'#0077c0' },
      oro:{ DEFAULT:'#005e99', dark:'#004a7a', light:'#9bcbff' },
      ink:'#181c1f', navy:'#181c1f', texto:'#404751', mute:'#707882',
    },
    fontFamily: {
      display: ['Montserrat','system-ui','sans-serif'], sans: ['Montserrat','system-ui','sans-serif'],
      'display-lg': ['Montserrat'], 'body-md': ['Montserrat'], 'body-lg': ['Montserrat'],
      'label-sm': ['Montserrat'], 'headline-md': ['Montserrat'], accent: ['Montserrat'],
    },
    fontSize: {
      'label-sm': ['12px',{lineHeight:'16px',letterSpacing:'0.05em',fontWeight:'600'}],
      'display-lg': ['64px',{lineHeight:'72px',letterSpacing:'-0.03em',fontWeight:'800'}],
      'body-lg': ['18px',{lineHeight:'28px',fontWeight:'400'}],
      'headline-md': ['24px',{lineHeight:'32px',fontWeight:'600'}],
    },
    spacing: { xs:'4px', base:'8px', sm:'12px', gutter:'24px', md:'24px', lg:'48px', xl:'80px' },
    maxWidth: { 'container-max':'1280px', '8xl':'1320px' },
    borderRadius: { xl:'0.75rem' },
  }}
}
</script>
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
<script>document.documentElement.classList.remove('no-js');</script>
<script defer src="<?= base_url('assets/js/app.js') ?>"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans bg-background text-on-surface-variant antialiased">

<!-- NAVBAR liquid glass -->
<header x-data="{ open:false, scrolled:false }"
        x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 30)"
        :class="(scrolled || !<?= $hero ? 'true':'false' ?>) ? 'nav-glass shadow-lg shadow-black/10' : 'bg-transparent'"
        class="fixed top-0 w-full z-50 transition-all duration-500">
  <nav class="container-x flex items-center justify-between h-[72px]">
    <a href="<?= base_url('') ?>" class="flex items-center gap-3 shrink-0">
      <img src="<?= base_url('assets/img/fotos/logo-light.svg') ?>" alt="<?= e(SITE_NAME) ?>" class="h-9 md:h-11 w-auto"
           onerror="this.outerHTML='<span class=\'font-display text-xl font-extrabold text-white\'>Hotel Playa Cove&ntilde;as</span>'">
    </a>

    <ul class="hidden lg:flex items-center gap-8 text-[.72rem] font-bold tracking-[.12em] uppercase">
      <?php foreach ($nav as $slug => $label): ?>
        <li><a href="<?= base_url($slug ? $slug . '.php' : '') ?>"
               class="link-under transition-colors <?= $activo === $slug ? 'text-primary-fixed-dim' : 'text-white/85 hover:text-white' ?>"><?= e($label) ?></a></li>
      <?php endforeach; ?>
    </ul>

    <div class="flex items-center gap-3">
      <a href="<?= base_url('reservar.php') ?>" class="hidden sm:inline-flex bg-white text-primary px-7 py-3 rounded-full text-[.72rem] font-bold tracking-[.12em] uppercase hover:bg-primary-fixed transition-all">Reservar</a>
      <button @click="open=!open" class="lg:hidden p-2 text-white" aria-label="Menú" :aria-expanded="open">
        <span class="material-symbols-outlined text-3xl">menu</span>
      </button>
    </div>
  </nav>

  <div x-show="open" x-cloak x-transition.opacity class="lg:hidden nav-glass border-t border-white/10">
    <ul class="container-x py-4 flex flex-col gap-1 text-sm font-bold uppercase tracking-[.1em]">
      <?php foreach ($nav as $slug => $label): ?>
        <li><a href="<?= base_url($slug ? $slug . '.php' : '') ?>" class="block py-3 border-b border-white/10 <?= $activo === $slug ? 'text-primary-fixed-dim' : 'text-white/85' ?>"><?= e($label) ?></a></li>
      <?php endforeach; ?>
      <li><a href="<?= base_url('reservar.php') ?>" class="block py-3 text-white">Reservar ahora →</a></li>
    </ul>
  </div>
</header>

<main class="relative z-10 <?= $hero ? '' : 'pt-[72px]' ?>">
