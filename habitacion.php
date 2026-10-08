<?php
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$h = $slug ? get_habitacion_slug($slug) : null;

if (!$h) {
    http_response_code(404);
    $titulo = 'Habitación no encontrada · ' . SITE_NAME;
    require __DIR__ . '/includes/header.php';
    echo '<section class="container-x py-24 text-center"><h1 class="font-display text-4xl text-navy">Habitación no encontrada</h1><a href="' . base_url('habitaciones.php') . '" class="btn btn-mar mt-6">Ver habitaciones</a></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$img    = room_image($h);
$final  = precio_final($h);
$oferta = tiene_oferta($h);
$amen   = json_col($h['amenidades']);
$otras  = array_filter(get_habitaciones(['limit' => 4]), fn($x) => $x['id'] != $h['id']);

$titulo = $h['nombre'] . ' · ' . SITE_NAME;
$activo = 'habitaciones';
require __DIR__ . '/includes/header.php';
?>

<section class="container-x pt-6 pb-4 text-sm text-mute">
  <a href="<?= base_url('habitaciones.php') ?>" class="hover:text-mar">Habitaciones</a> <span class="mx-1">/</span> <span class="text-navy"><?= e($h['nombre']) ?></span>
</section>

<section class="container-x pb-16 grid lg:grid-cols-[1.4fr_1fr] gap-10 items-start">
  <div data-reveal-group>
    <div class="reveal rounded-3xl overflow-hidden card-soft">
      <img src="<?= $img ?>" alt="<?= e($h['nombre']) ?>" class="w-full aspect-[16/10] object-cover">
    </div>
    <div class="reveal mt-8">
      <p class="eyebrow"><?= e(vista_label($h['vista'])) ?></p>
      <h1 class="font-display text-4xl text-navy mt-1"><?= e($h['nombre']) ?></h1>
      <div class="flex flex-wrap gap-2 mt-4">
        <span class="chip"><svg class="w-4 h-4 text-mar" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 7.5h18M3 12h18M3 16.5h18"/></svg><?= e($h['camas']) ?></span>
        <span class="chip"><?= (int)$h['m2'] ?> m²</span>
        <span class="chip">Hasta <?= (int)$h['capacidad_adultos'] ?> adultos · <?= (int)$h['capacidad_ninos'] ?> niños</span>
      </div>
      <p class="text-texto mt-5 leading-relaxed"><?= e($h['descripcion']) ?></p>

      <h2 class="font-display text-2xl text-navy mt-8 mb-4">Amenidades</h2>
      <div class="grid sm:grid-cols-2 gap-2.5">
        <?php foreach ($amen as $a): ?>
          <div class="flex items-center gap-2.5 text-sm text-texto">
            <svg class="w-5 h-5 text-mar shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            <?= e($a) ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Caja de reserva -->
  <aside class="lg:sticky lg:top-24">
    <div class="card-soft p-6">
      <div class="flex items-end justify-between">
        <div>
          <?php if ($oferta): ?><span class="block text-sm text-mute line-through tabular"><?= precio_cop($h['precio_noche']) ?></span><?php endif; ?>
          <span class="font-display text-4xl text-mar tabular"><?= precio_cop($final) ?></span>
          <span class="text-sm text-mute">/ noche</span>
        </div>
        <?php if ($oferta): ?><span class="badge badge-oferta">-<?= descuento_pct($h) ?>%</span><?php endif; ?>
      </div>
      <a href="<?= base_url('reservar.php?hab=' . urlencode($h['slug'])) ?>" class="btn btn-mar w-full mt-5">Reservar esta habitación</a>
      <a href="<?= whatsapp_link('Hola, quiero información sobre la ' . $h['nombre']) ?>" target="_blank" rel="noopener" class="btn btn-ghost w-full mt-3">Consultar por WhatsApp</a>
      <ul class="mt-6 space-y-2 text-sm text-mute">
        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-mar" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>Cancelación flexible</li>
        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-mar" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>Mejor precio garantizado</li>
        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-mar" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>Check-in <?= e(CHECKIN_HORA) ?> · Check-out <?= e(CHECKOUT_HORA) ?></li>
      </ul>
    </div>
  </aside>
</section>

<?php if ($otras): ?>
<section class="container-x pb-20">
  <h2 class="font-display text-3xl text-navy mb-6">Otras opciones</h2>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6" data-reveal-group>
    <?php foreach (array_slice($otras, 0, 3) as $o) room_card($o); ?>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
