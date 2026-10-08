<?php
require_once __DIR__ . '/includes/functions.php';
$titulo = 'Preguntas frecuentes · ' . SITE_NAME;
$activo = 'faq';
require __DIR__ . '/includes/header.php';

$faqs = [
  ['¿Cuáles son los horarios de check-in y check-out?', 'El check-in es a partir de las ' . CHECKIN_HORA . ' y el check-out hasta las ' . CHECKOUT_HORA . '. Si necesitas salir más tarde, puedes añadir el servicio de late check-out al reservar.'],
  ['¿Cómo reservo y pago?', 'Reserva en línea eligiendo fechas y habitación en nuestro mapa. Confirmas con un anticipo del ' . DEPOSIT_PCT . '% y el saldo se paga al llegar. Próximamente habilitaremos el pago en línea con pasarela segura.'],
  ['¿Puedo cancelar o modificar mi reserva?', 'Sí. Ofrecemos cancelación flexible. Escríbenos por WhatsApp con tu código de reserva y gestionamos el cambio o la cancelación según la política vigente.'],
  ['¿Aceptan niños y mascotas?', 'Los niños son siempre bienvenidos; varias habitaciones admiten cuna o cama adicional. Para mascotas, contáctanos antes de reservar para confirmar disponibilidad de habitaciones pet-friendly.'],
  ['¿El desayuno está incluido?', 'Algunas tarifas lo incluyen y en otras puedes añadirlo como extra al reservar. Nuestro desayuno buffet caribeño se sirve cada mañana en el restaurante.'],
  ['¿Tienen estacionamiento y WiFi?', 'Sí, contamos con estacionamiento para huéspedes y WiFi gratuito de alta velocidad en todas las áreas del hotel.'],
  ['¿Cómo llego al hotel?', 'Estamos en ' . CONTACT_ADDRESS . '. Ofrecemos traslados desde los aeropuertos de Montería y Tolú; puedes añadirlo como servicio adicional en tu reserva.'],
];
?>

<section class="container-x pt-10 pb-6">
  <p class="eyebrow">Ayuda</p>
  <h1 class="font-display text-4xl sm:text-5xl text-navy mt-2">Preguntas frecuentes</h1>
  <p class="text-mute mt-3 max-w-2xl">Resolvemos las dudas más comunes antes de tu viaje a Coveñas.</p>
</section>

<section class="container-x pb-20 max-w-3xl">
  <div class="card-soft divide-y divide-black/5" x-data="{ open: 0 }">
    <?php foreach ($faqs as $i => $f): ?>
      <div>
        <button type="button" class="w-full flex items-center justify-between gap-4 text-left p-5 sm:p-6"
                @click="open === <?= $i ?> ? open = null : open = <?= $i ?>" :aria-expanded="open === <?= $i ?>">
          <span class="font-display text-lg sm:text-xl text-navy"><?= e($f[0]) ?></span>
          <svg class="w-5 h-5 text-mar shrink-0 transition-transform duration-300" :class="open === <?= $i ?> ? 'rotate-45' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        </button>
        <div x-show="open === <?= $i ?>" x-cloak
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
          <p class="px-5 sm:px-6 pb-6 -mt-1 text-mute leading-relaxed"><?= e($f[1]) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="text-center mt-10">
    <p class="text-mute">¿No encuentras tu respuesta?</p>
    <a href="<?= base_url('contacto.php') ?>" class="btn btn-mar mt-4">Contáctanos</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
