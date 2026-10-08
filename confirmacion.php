<?php
require_once __DIR__ . '/includes/functions.php';

$codigo  = $_GET['codigo'] ?? '';
$reserva = $codigo ? get_reserva_codigo($codigo) : null;

$titulo = 'Confirmación de reserva · ' . SITE_NAME;
$activo = '';
require __DIR__ . '/includes/header.php';

if (!$reserva):
?>
<section class="container-x py-24 text-center">
  <h1 class="font-display text-4xl text-navy">Reserva no encontrada</h1>
  <p class="text-mute mt-3">No pudimos encontrar una reserva con ese código.</p>
  <a href="<?= base_url('reservar.php') ?>" class="btn btn-mar mt-6">Hacer una reserva</a>
</section>
<?php
require __DIR__ . '/includes/footer.php';
exit;
endif;

$extras  = json_col($reserva['extras']);
$waMsg   = "Hola " . SITE_NAME . ", confirmo mi reserva " . $reserva['codigo'] . ".%0A"
         . "Habitación: " . $reserva['habitacion_nombre'] . " (Hab. " . $reserva['unidad_numero'] . ")%0A"
         . "Entrada: " . $reserva['check_in'] . " · Salida: " . $reserva['check_out'] . "%0A"
         . "Huésped: " . $reserva['huesped_nombre'] . "%0A"
         . "Total: " . precio_cop($reserva['total']);
$waUrl   = 'https://wa.me/' . WHATSAPP_NUMBER . '?text=' . $waMsg;
?>

<section class="container-x py-12 sm:py-16">
  <div class="max-w-3xl mx-auto">

    <div class="text-center" data-reveal-group>
      <div class="reveal w-20 h-20 mx-auto rounded-full grid place-items-center" style="background:var(--mar-grad)">
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
      </div>
      <h1 class="reveal font-display text-4xl sm:text-5xl text-navy mt-5">¡Reserva confirmada!</h1>
      <p class="reveal text-mute mt-3">Gracias, <?= e($reserva['huesped_nombre']) ?>. Te esperamos en Coveñas.</p>
      <div class="reveal inline-flex items-center gap-3 mt-5 px-5 py-3 rounded-full bg-espuma">
        <span class="text-xs uppercase tracking-wider text-mar font-semibold">Código</span>
        <span class="font-display text-2xl text-navy tabular tracking-wide"><?= e($reserva['codigo']) ?></span>
      </div>
    </div>

    <div class="card-soft p-6 sm:p-8 mt-8">
      <div class="flex items-center justify-between flex-wrap gap-3 border-b border-black/5 pb-4 mb-4">
        <div>
          <p class="eyebrow">Tu estadía</p>
          <h2 class="font-display text-2xl text-navy"><?= e($reserva['habitacion_nombre']) ?></h2>
          <p class="text-sm text-mute">Habitación <?= e($reserva['unidad_numero']) ?> · Piso <?= e((string)$reserva['unidad_piso']) ?> · <?= e(vista_label($reserva['vista'] ?? '')) ?></p>
        </div>
        <span class="state state-<?= e($reserva['estado']) ?>"><?= e(estado_label($reserva['estado'])) ?></span>
      </div>

      <div class="grid sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
        <div class="flex justify-between"><span class="text-mute">Entrada</span><span class="text-navy font-medium"><?= e($reserva['check_in']) ?> · <?= e(CHECKIN_HORA) ?></span></div>
        <div class="flex justify-between"><span class="text-mute">Salida</span><span class="text-navy font-medium"><?= e($reserva['check_out']) ?> · <?= e(CHECKOUT_HORA) ?></span></div>
        <div class="flex justify-between"><span class="text-mute">Noches</span><span class="text-navy"><?= (int)$reserva['noches'] ?></span></div>
        <div class="flex justify-between"><span class="text-mute">Huéspedes</span><span class="text-navy"><?= (int)$reserva['adultos'] ?> adultos · <?= (int)$reserva['ninos'] ?> niños</span></div>
        <?php if ($reserva['telefono']): ?><div class="flex justify-between"><span class="text-mute">Teléfono</span><span class="text-navy"><?= e($reserva['telefono']) ?></span></div><?php endif; ?>
        <?php if ($reserva['email']): ?><div class="flex justify-between"><span class="text-mute">Correo</span><span class="text-navy truncate ml-2"><?= e($reserva['email']) ?></span></div><?php endif; ?>
      </div>

      <div class="mt-5 pt-4 border-t border-black/5 space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-mute"><?= precio_cop($reserva['precio_noche']) ?> × <?= (int)$reserva['noches'] ?> noches</span><span class="text-navy tabular"><?= precio_cop($reserva['subtotal']) ?></span></div>
        <?php foreach ($extras as $x): ?>
          <div class="flex justify-between text-xs"><span class="text-mute"><?= e($x['nombre']) ?></span><span class="text-navy tabular"><?= precio_cop($x['costo']) ?></span></div>
        <?php endforeach; ?>
        <div class="flex justify-between items-end pt-2 border-t border-black/5 mt-2">
          <span class="text-mute">Total</span>
          <span class="font-display text-2xl text-mar tabular"><?= precio_cop($reserva['total']) ?></span>
        </div>
        <p class="text-right text-xs text-mute">Anticipo sugerido (<?= DEPOSIT_PCT ?>%): <span class="tabular"><?= precio_cop($reserva['anticipo']) ?></span></p>
      </div>

      <?php if ($reserva['solicitudes']): ?>
      <div class="mt-5 pt-4 border-t border-black/5">
        <p class="text-xs uppercase tracking-wider text-mute mb-1">Solicitudes especiales</p>
        <p class="text-sm text-navy"><?= e($reserva['solicitudes']) ?></p>
      </div>
      <?php endif; ?>

      <div class="mt-6 flex flex-col sm:flex-row gap-3">
        <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="btn btn-mar flex-1">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24z"/></svg>
          Confirmar pago por WhatsApp
        </a>
        <a href="<?= base_url('') ?>" class="btn btn-ghost flex-1">Volver al inicio</a>
      </div>
    </div>

    <p class="text-center text-xs text-mute mt-6">Guarda tu código <strong><?= e($reserva['codigo']) ?></strong>. Te contactaremos para finalizar el pago y darte la bienvenida.</p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
