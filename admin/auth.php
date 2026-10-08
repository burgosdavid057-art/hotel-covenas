<?php
require_once __DIR__ . '/../includes/functions.php';

function is_admin(): bool {
    return !empty($_SESSION['admin']);
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: ' . base_url('admin/login.php'));
        exit;
    }
}

function check_admin_login(string $user, string $pass): bool {
    if (!hash_equals(ADMIN_USER, $user)) return false;
    if (password_verify($pass, ADMIN_PASS_HASH)) return true;
    return hash_equals(ADMIN_PASS_PLAIN, $pass);
}

/** Layout del panel admin */
function admin_header(string $title, string $active = ''): void {
    $nav = [
        'index.php'        => ['Dashboard', 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25Zm9.75-9.75A2.25 2.25 0 0 1 15.75 3.75H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Z'],
        'reservas.php'     => ['Reservas', 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272'],
        'calendario.php'   => ['Calendario', 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'],
        'habitaciones.php' => ['Habitaciones', 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21'],
        'unidades.php'     => ['Unidades', 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.45c0-.24.195-.435.435-.435h4.13c.24 0 .435.195.435.435V21'],
        'extras.php'       => ['Extras', 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z'],
    ];
    ?>
<!DOCTYPE html>
<html lang="es" class="no-js">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · Admin <?= e(SITE_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config={theme:{extend:{colors:{primary:{DEFAULT:'#005e99',deep:'#004a7a',container:'#0077c0','fixed-dim':'#9bcbff'},arena:'#f1f4f8','arena-2':'#ebeef2',mar:{DEFAULT:'#005e99',dark:'#004a7a',light:'#0077c0'},navy:'#181c1f','on-surface':'#181c1f','on-surface-variant':'#404751','inverse-surface':'#1d2a33','outline-variant':'#c0c7d2',texto:'#404751',mute:'#707882',espuma:'#e7f1fb',oro:{DEFAULT:'#005e99',dark:'#004a7a',light:'#9bcbff'}},fontFamily:{display:['Montserrat','sans-serif'],sans:['Montserrat','sans-serif']}}}}
</script>
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
<script>document.documentElement.classList.remove('no-js');</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans bg-arena text-texto min-h-screen">
<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="hidden lg:flex flex-col w-64 shrink-0 bg-inverse-surface text-white p-6 sticky top-0 h-screen">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary via-primary-container to-primary-fixed-dim"></div>
    <a href="<?= base_url('admin/index.php') ?>" class="font-display text-xl font-extrabold text-white leading-tight mt-2">Hotel Playa <span class="text-primary-fixed-dim">Coveñas</span></a>
    <p class="text-[.6rem] tracking-[.28em] text-white/45 uppercase mt-1 mb-8">Panel de administración</p>
    <nav class="space-y-1 flex-1">
      <?php foreach ($nav as $file => $n): ?>
        <a href="<?= base_url('admin/' . $file) ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition-colors <?= $active === $file ? 'bg-primary text-white font-semibold shadow-lg shadow-primary/30' : 'text-white/65 hover:text-white hover:bg-white/5' ?>">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $n[1] ?>"/></svg>
          <?= e($n[0]) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="border-t border-white/10 pt-4 space-y-1">
      <a href="<?= base_url('') ?>" target="_blank" class="block px-4 py-2 text-sm text-white/55 hover:text-white">Ver sitio ↗</a>
      <a href="<?= base_url('admin/logout.php') ?>" class="block px-4 py-2 text-sm text-white/55 hover:text-white">Cerrar sesión</a>
    </div>
  </aside>

  <div class="flex-1 min-w-0">
    <!-- Topbar móvil -->
    <header class="lg:hidden flex items-center justify-between p-4 bg-inverse-surface text-white">
      <span class="font-display text-lg font-extrabold">Hotel Playa <span class="text-primary-fixed-dim">Coveñas</span></span>
      <a href="<?= base_url('admin/logout.php') ?>" class="text-sm text-white/70">Salir</a>
    </header>
    <nav class="lg:hidden flex gap-1 overflow-x-auto p-3 border-b border-black/5 text-xs bg-white">
      <?php foreach ($nav as $file => $n): ?>
        <a href="<?= base_url('admin/' . $file) ?>" class="px-3 py-2 rounded-lg whitespace-nowrap <?= $active === $file ? 'bg-primary text-white font-semibold' : 'text-navy/70' ?>"><?= e($n[0]) ?></a>
      <?php endforeach; ?>
    </nav>

    <main class="p-5 sm:p-8 max-w-7xl">
    <?php
}

function admin_footer(): void {
    ?>
    </main>
  </div>
</div>
</body>
</html>
    <?php
}

/** Píldora de estado para la tabla de reservas */
function estado_badge(string $estado): string {
    return '<span class="state state-' . e($estado) . '">' . e(estado_label($estado)) . '</span>';
}
