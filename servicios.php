<?php
require_once __DIR__ . '/includes/functions.php';
$titulo = 'Servicios · ' . SITE_NAME;
$activo = 'servicios';
$hero = true;
require __DIR__ . '/includes/header.php';

$servicios = [
  ['Playa privada', 'Acceso directo a la arena con sombrillas, camastros y servicio de toallas sin costo.', 'M3 13.5 6 6h12l3 7.5M3 13.5h18M3 13.5l1.5 5.25a1.5 1.5 0 0 0 1.44 1.08h12.12a1.5 1.5 0 0 0 1.44-1.08L21 13.5M12 6V3'],
  ['Piscina frente al mar', 'Piscina infinita con bar acuático y zona de niños.', 'M2 18c1.5 0 1.5-1 3-1s1.5 1 3 1 1.5-1 3-1 1.5 1 3 1 1.5-1 3-1 1.5 1 3 1M2 13c1.5 0 1.5-1 3-1s1.5 1 3 1 1.5-1 3-1 1.5 1 3 1 1.5-1 3-1 1.5 1 3 1'],
  ['Restaurante & bar', 'Cocina caribeña, mariscos frescos y coctelería de autor con vista al atardecer.', 'M3 3v18M7.5 3v6a3 3 0 0 0 3 3V3M16.5 3c-1.5 1-2.5 3-2.5 6 0 2 1 3 2.5 3V21'],
  ['Desayuno buffet', 'Frutas tropicales, jugos naturales y estación de huevos cada mañana.', 'M12 3v1.5m0 15V21m9-9h-1.5m-15 0H3m4.5 0a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0Z'],
  ['Tours y experiencias', 'Paseos en lancha a las Islas de San Bernardo y avistamiento de delfines.', 'M3 13.5 6 6h12l3 7.5M3 13.5h18M12 6V3'],
  ['Spa & bienestar', 'Masajes frente al mar y circuito de relajación (con reserva previa).', 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
  ['WiFi de alta velocidad', 'Conexión gratuita en todas las áreas, ideal para trabajar o transmitir.', 'M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z'],
  ['Recepción 24 horas', 'Atención y room service durante todo el día y la noche.', 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
  ['Traslados', 'Servicio de transporte desde aeropuertos de Montería y Tolú.', 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25'],
];
?>

<section class="relative overflow-hidden">
  <img src="<?= base_url('assets/img/fotos/pool-2.jpg') ?>" alt="Servicios del hotel" class="absolute inset-0 w-full h-full object-cover">
  <div class="absolute inset-0" style="background:linear-gradient(120deg, rgba(6,59,92,.9), rgba(10,108,171,.6))"></div>
  <div class="container-x py-24 text-center relative z-10">
    <span class="eyebrow text-coral">Experiencias</span>
    <h1 class="font-display text-5xl sm:text-6xl font-extrabold text-white mt-2 leading-none">Nuestros servicios</h1>
    <p class="text-white/85 mt-4 max-w-2xl mx-auto text-lg">Todo pensado para que vivas el Caribe sin preocupaciones.</p>
  </div>
</section>

<section class="container-x py-16">
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6" data-reveal-group>
    <?php foreach ($servicios as $s): ?>
      <div class="reveal card-soft p-7">
        <div class="w-12 h-12 rounded-2xl grid place-items-center mb-4" style="background:var(--espuma)">
          <svg class="w-6 h-6 text-mar" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $s[2] ?>"/></svg>
        </div>
        <h3 class="font-display text-xl text-navy"><?= e($s[0]) ?></h3>
        <p class="text-sm text-mute mt-2"><?= e($s[1]) ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="text-center mt-12">
    <a href="<?= base_url('reservar.php') ?>" class="btn btn-mar">Reservar mi estadía</a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
