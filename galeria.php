<?php
require_once __DIR__ . '/includes/functions.php';
$titulo = 'Galería · ' . SITE_NAME;
$activo = 'galeria';
require __DIR__ . '/includes/header.php';

// Fotos reales del hotel
$fotos = [
  'hero-1.jpg'=>'Vista al mar','beach.jpg'=>'Playa de Coveñas','pool-1.jpg'=>'Piscina','pool-2.jpg'=>'Zona de piscina',
  'room-1.jpg'=>'Habitación Vista Mar','room-2.jpg'=>'Suite Familiar','room-3.jpg'=>'Habitación Estándar','room-4.jpg'=>'Suite Presidencial',
  'view-1.jpg'=>'Atardecer en el Golfo','hallway.jpg'=>'Pasillo central','gal-1.jpg'=>'Instalaciones','gal-2.jpg'=>'El hotel',
  'gal-3.jpg'=>'Áreas comunes','gal-4.jpg'=>'Descanso','gal-5.jpg'=>'Rincones del hotel','excursions.jpg'=>'Excursiones a las islas',
  'hero-2.jpg'=>'Frente al mar',
];
$imgs = [];
foreach ($fotos as $f => $cap) {
    $imgs[] = ['src' => base_url('assets/img/fotos/' . $f), 'cap' => $cap];
}
?>

<section class="container-x pt-10 pb-6">
  <p class="eyebrow">Galería</p>
  <h1 class="font-display text-4xl sm:text-5xl text-navy mt-2">Un vistazo al paraíso</h1>
  <p class="text-mute mt-3 max-w-2xl">Espacios, rincones y vistas que te esperan en Coveñas.</p>
</section>

<section class="container-x pb-20" x-data="{ open:false, src:'', cap:'' }">
  <div class="columns-1 sm:columns-2 lg:columns-3 gap-5 [&>*]:mb-5" data-reveal-group>
    <?php foreach ($imgs as $i => $im): ?>
      <button type="button" class="reveal block w-full rounded-2xl overflow-hidden card-soft group relative"
              @click="open=true; src='<?= e($im['src']) ?>'; cap='<?= e($im['cap']) ?>'">
        <img src="<?= e($im['src']) ?>" alt="<?= e($im['cap']) ?>" loading="lazy" class="w-full object-cover transition-transform duration-700 group-hover:scale-105" style="aspect-ratio:<?= $i % 3 === 0 ? '4/5' : '4/3' ?>">
        <span class="absolute inset-x-0 bottom-0 p-3 text-left text-white text-sm font-medium bg-gradient-to-t from-black/55 to-transparent"><?= e($im['cap']) ?></span>
      </button>
    <?php endforeach; ?>
  </div>

  <!-- Lightbox -->
  <div x-show="open" x-cloak x-transition.opacity @keydown.escape.window="open=false"
       class="fixed inset-0 z-[60] bg-navy/80 backdrop-blur-sm flex items-center justify-center p-5" @click="open=false">
    <figure class="max-w-4xl w-full" @click.stop>
      <img :src="src" :alt="cap" class="w-full rounded-2xl shadow-2xl">
      <figcaption class="text-center text-espuma/90 mt-3 text-sm" x-text="cap"></figcaption>
    </figure>
    <button @click="open=false" class="absolute top-5 right-5 w-11 h-11 rounded-full bg-white/15 text-white grid place-items-center hover:bg-white/25" aria-label="Cerrar">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
    </button>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
