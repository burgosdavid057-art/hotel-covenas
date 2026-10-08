<?php
require_once __DIR__ . '/auth.php';

if (is_admin()) { header('Location: ' . base_url('admin/index.php')); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['user'] ?? '');
    $pass = $_POST['pass'] ?? '';
    if (check_admin_login($user, $pass)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = $user;
        header('Location: ' . base_url('admin/index.php'));
        exit;
    }
    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin · <?= e(SITE_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="min-h-screen grid place-items-center p-5" style="background:linear-gradient(160deg,#0077c0,#004a7a);font-family:'Montserrat',system-ui,sans-serif">
  <div class="w-full max-w-sm">
    <div class="text-center mb-6">
      <a href="<?= base_url('') ?>" class="font-display text-3xl font-extrabold text-white">Hotel Playa Coveñas</a>
      <p class="text-white/80 text-sm mt-1 uppercase tracking-[.25em]">Panel de administración</p>
    </div>
    <form method="post" class="glass rounded-3xl p-7">
      <?php if ($error): ?>
        <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 text-sm text-center"><?= e($error) ?></div>
      <?php endif; ?>
      <div class="mb-4">
        <label class="lbl" for="user">Usuario</label>
        <input id="user" name="user" type="text" class="field" autofocus autocomplete="username" required>
      </div>
      <div class="mb-5">
        <label class="lbl" for="pass">Contraseña</label>
        <input id="pass" name="pass" type="password" class="field" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn btn-mar w-full">Ingresar</button>
    </form>
    <p class="text-center text-white/70 text-xs mt-4"><a href="<?= base_url('') ?>" class="hover:text-white">← Volver al sitio</a></p>
  </div>
</body>
</html>
