<?php
require_once __DIR__ . '/includes/functions.php';

$enviado = false; $errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $msg    = trim($_POST['mensaje'] ?? '');
    if ($nombre === '') $errores[] = 'Ingresa tu nombre.';
    if ($msg === '')    $errores[] = 'Escribe tu mensaje.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Correo no válido.';
    if (!$errores) $enviado = true;
}

$titulo = 'Contacto · ' . SITE_NAME;
$activo = 'contacto';
require __DIR__ . '/includes/header.php';
?>

<section class="container-x pt-10 pb-6">
  <p class="eyebrow">Contacto</p>
  <h1 class="font-display text-4xl sm:text-5xl text-navy mt-2">Estamos para ayudarte</h1>
  <p class="text-mute mt-3 max-w-2xl">¿Dudas sobre tu reserva o un evento especial? Escríbenos y te responderemos pronto.</p>
</section>

<section class="container-x pb-20 grid lg:grid-cols-2 gap-10 items-start">
  <!-- Formulario -->
  <div class="card-soft p-6 sm:p-8">
    <?php if ($enviado): ?>
      <div class="text-center py-8">
        <div class="w-16 h-16 mx-auto rounded-full grid place-items-center" style="background:var(--mar-grad)">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        </div>
        <h2 class="font-display text-2xl text-navy mt-4">¡Mensaje enviado!</h2>
        <p class="text-mute mt-2">Gracias por escribirnos. Te contactaremos muy pronto.</p>
        <a href="<?= base_url('') ?>" class="btn btn-ghost mt-5">Volver al inicio</a>
      </div>
    <?php else: ?>
      <?php if ($errores): ?>
        <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 text-sm"><?= e(implode(' ', $errores)) ?></div>
      <?php endif; ?>
      <form method="post" class="space-y-4">
        <div>
          <label class="lbl" for="c-nombre">Nombre *</label>
          <input id="c-nombre" name="nombre" type="text" class="field" value="<?= e($_POST['nombre'] ?? '') ?>" autocomplete="name">
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="lbl" for="c-email">Correo</label>
            <input id="c-email" name="email" type="email" class="field" value="<?= e($_POST['email'] ?? '') ?>" autocomplete="email">
          </div>
          <div>
            <label class="lbl" for="c-tel">Teléfono</label>
            <input id="c-tel" name="telefono" type="tel" class="field" value="<?= e($_POST['telefono'] ?? '') ?>" autocomplete="tel">
          </div>
        </div>
        <div>
          <label class="lbl" for="c-msg">Mensaje *</label>
          <textarea id="c-msg" name="mensaje" rows="5" class="field"><?= e($_POST['mensaje'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-mar w-full">Enviar mensaje</button>
        <p class="text-center text-xs text-mute">o escríbenos directo por <a href="<?= whatsapp_link() ?>" target="_blank" rel="noopener" class="text-mar link-under">WhatsApp</a></p>
      </form>
    <?php endif; ?>
  </div>

  <!-- Info + mapa -->
  <div>
    <div class="rounded-3xl overflow-hidden card-soft aspect-[4/3]">
      <iframe title="Mapa Coveñas" class="w-full h-full" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
        src="https://www.google.com/maps?q=Cove%C3%B1as%2C+Sucre%2C+Colombia&output=embed"></iframe>
    </div>
    <div class="grid sm:grid-cols-2 gap-4 mt-6">
      <div class="card-soft p-5">
        <p class="eyebrow mb-1">Dirección</p>
        <p class="text-sm text-navy"><?= e(CONTACT_ADDRESS) ?></p>
      </div>
      <div class="card-soft p-5">
        <p class="eyebrow mb-1">Teléfono</p>
        <a href="tel:<?= e(CONTACT_PHONE) ?>" class="text-sm text-navy hover:text-mar"><?= e(CONTACT_PHONE) ?></a>
      </div>
      <div class="card-soft p-5">
        <p class="eyebrow mb-1">Correo</p>
        <a href="mailto:<?= e(CONTACT_EMAIL) ?>" class="text-sm text-navy hover:text-mar break-all"><?= e(CONTACT_EMAIL) ?></a>
      </div>
      <div class="card-soft p-5">
        <p class="eyebrow mb-1">Recepción</p>
        <p class="text-sm text-navy"><?= e(CONTACT_HOURS) ?></p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
