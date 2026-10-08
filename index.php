<?php
require_once __DIR__ . '/includes/functions.php';
function img($f) { return base_url('assets/img/fotos/' . $f); }
$destacadas = get_habitaciones(['destacado' => 1, 'limit' => 3]);
if (!$destacadas) $destacadas = get_habitaciones(['limit' => 3]);
$hoy = date('Y-m-d');
$manana = date('Y-m-d', strtotime('+1 day'));

$titulo = SITE_NAME . ' · ' . SITE_TAGLINE;
$activo = '';
$hero = true;
require __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO ===== -->
<section class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden bg-black">
  <img alt="<?= e(SITE_NAME) ?>" class="absolute inset-0 w-full h-full object-cover opacity-75 scale-105" src="<?= img('pool-2.jpg') ?>"/>
  <div class="absolute inset-0 hero-overlay"></div>

  <div class="relative z-10 text-center px-gutter max-w-6xl mx-auto pt-24">
    <div id="hero-parallax">
      <p class="text-white/60 text-[.72rem] font-bold tracking-[0.55em] uppercase mb-6 flex items-center justify-center gap-4">
        <span class="w-8 h-px bg-white/30"></span> Paradise Found <span class="w-8 h-px bg-white/30"></span>
      </p>
      <h1 class="text-white font-extrabold mb-8 leading-[1.02] text-5xl md:text-8xl">
        Donde el Azul <br/>
        <span class="text-primary-fixed-dim">Abraza</span> el Mañana
      </h1>
      <p class="text-white/85 max-w-2xl mx-auto mb-2 text-lg md:text-xl leading-relaxed">
        Descubre el descanso frente al mar en el corazón de Coveñas.<br class="hidden sm:block"/>
        Una experiencia de serenidad en la Segunda Ensenada.
      </p>
    </div>
  </div>

  <!-- Barra de reserva — centrada + liquid glass -->
  <div class="absolute bottom-10 left-1/2 -translate-x-1/2 w-full max-w-4xl px-gutter z-20">
    <form action="<?= base_url('reservar.php') ?>" method="get"
          class="booking-glass rounded-3xl md:rounded-full p-2 flex flex-col md:flex-row items-stretch md:items-center search-bar-shadow gap-2 md:gap-0">
      <label class="flex-1 flex items-center px-6 py-3 md:border-r border-white/25 cursor-pointer">
        <span class="material-symbols-outlined text-white/80 mr-3">calendar_month</span>
        <span class="flex flex-col items-start w-full">
          <span class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Entrada</span>
          <input name="check_in" type="date" value="<?= $hoy ?>" min="<?= $hoy ?>" class="bk-input">
        </span>
      </label>
      <label class="flex-1 flex items-center px-6 py-3 md:border-r border-white/25 cursor-pointer">
        <span class="material-symbols-outlined text-white/80 mr-3">event_available</span>
        <span class="flex flex-col items-start w-full">
          <span class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Salida</span>
          <input name="check_out" type="date" value="<?= $manana ?>" min="<?= $manana ?>" class="bk-input">
        </span>
      </label>
      <label class="flex-1 flex items-center px-6 py-3 cursor-pointer">
        <span class="material-symbols-outlined text-white/80 mr-3">group</span>
        <span class="flex flex-col items-start w-full">
          <span class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Huéspedes</span>
          <select name="adultos" class="bk-input"><?php for($i=1;$i<=8;$i++): ?><option value="<?= $i ?>" <?= $i===2?'selected':'' ?>><?= $i ?> <?= $i==1?'adulto':'adultos' ?></option><?php endfor; ?></select>
        </span>
      </label>
      <input type="hidden" name="ninos" value="0">
      <button type="submit" class="btn btn-dark md:!rounded-full !px-10 !py-4 shrink-0">Buscar</button>
    </form>
  </div>
</section>

<!-- ===== BIENVENIDA (collage) ===== -->
<section class="py-xl bg-white overflow-hidden">
  <div class="max-w-container-max mx-auto px-gutter grid grid-cols-1 lg:grid-cols-12 gap-gutter items-center">
    <div class="lg:col-span-5 reveal">
      <span class="eyebrow">La esencia del descanso</span>
      <h2 class="text-4xl md:text-5xl text-primary mt-3 mb-5 leading-tight">Hospitalidad costera<br/>frente al Caribe</h2>
      <div class="w-16 h-1 bg-primary mb-6 rounded-full"></div>
      <p class="text-on-surface-variant leading-relaxed mb-7 text-lg">
        En <span class="font-bold text-on-surface"><?= e(SITE_NAME) ?></span> redefinimos el descanso costero: un refugio donde la sencillez se encuentra con el confort, con servicio impecable frente a las aguas tranquilas de la Segunda Ensenada de Coveñas.
      </p>
      <div class="grid grid-cols-2 gap-6 mb-8">
        <div>
          <span class="material-symbols-outlined text-primary text-3xl">support_agent</span>
          <h4 class="font-bold text-base mt-2">Recepción 24 h</h4>
          <p class="text-sm text-mute">Atención durante toda tu estadía.</p>
        </div>
        <div>
          <span class="material-symbols-outlined text-primary text-3xl">waves</span>
          <h4 class="font-bold text-base mt-2">Acceso a la playa</h4>
          <p class="text-sm text-mute">Directo a la mejor playa de Coveñas.</p>
        </div>
      </div>
      <a href="<?= base_url('reservar.php') ?>" class="btn btn-primary">Reservar ahora</a>
    </div>
    <div class="lg:col-span-7 relative h-[560px] reveal">
      <div class="absolute top-0 right-0 w-3/4 h-4/5 z-0 group rounded-[2.5rem] overflow-hidden shadow-2xl">
        <img alt="Vista del hotel" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.03]" src="<?= img('room-4.jpg') ?>"/>
      </div>
      <div class="absolute bottom-0 left-0 w-2/3 h-3/5 z-10 glass-card p-3 rounded-[2.8rem]">
        <img alt="Piscina" class="rounded-[2.2rem] w-full h-full object-cover" src="<?= img('pool-1.jpg') ?>"/>
      </div>
      <div class="absolute top-8 left-6 z-20 glass-panel px-7 py-5 rounded-3xl hidden xl:block floating">
        <p class="text-primary font-extrabold text-3xl">"Inolvidable"</p>
        <p class="text-on-surface-variant text-sm">— Nuestros huéspedes</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== HABITACIONES ===== -->
<section class="py-xl bg-surface-container-low">
  <div class="max-w-container-max mx-auto px-gutter">
    <div class="flex items-end justify-between flex-wrap gap-4 mb-10 reveal">
      <div>
        <span class="eyebrow">Alojamiento</span>
        <h2 class="text-4xl md:text-5xl text-primary mt-2 leading-none">Habitaciones frente<br/>al mar</h2>
      </div>
      <a href="<?= base_url('habitaciones.php') ?>" class="btn btn-ghost">Ver todas</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($destacadas as $h) room_card($h); ?>
    </div>
  </div>
</section>

<!-- ===== SERVICIOS (strip) ===== -->
<section class="py-xl bg-white">
  <div class="max-w-container-max mx-auto px-gutter">
    <div class="text-center max-w-2xl mx-auto mb-12 reveal">
      <span class="eyebrow">La experiencia</span>
      <h2 class="text-4xl md:text-5xl text-primary mt-2">Todo para desconectar</h2>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
      <?php
      $servicios = [
        ['pool','Piscina frente al mar','Piscina al aire libre con zona de descanso.'],
        ['restaurant','Restaurante & bar','Cocina caribeña y mariscos frescos.'],
        ['directions_boat','Excursiones','Paseos en lancha a las Islas de San Bernardo.'],
        ['wifi','WiFi & confort','Internet gratis y aire acondicionado.'],
      ];
      foreach ($servicios as $s): ?>
        <div class="reveal glass-card rounded-3xl p-7">
          <span class="material-symbols-outlined text-primary text-4xl"><?= $s[0] ?></span>
          <h3 class="text-xl mt-4 mb-2"><?= e($s[1]) ?></h3>
          <p class="text-sm text-mute leading-relaxed"><?= e($s[2]) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-10 reveal"><a href="<?= base_url('servicios.php') ?>" class="btn btn-ghost">Todos los servicios</a></div>
  </div>
</section>

<!-- ===== UBICACIÓN ===== -->
<section class="bg-surface-container-low py-xl overflow-hidden">
  <div class="max-w-container-max mx-auto px-gutter grid grid-cols-1 md:grid-cols-12 gap-md items-stretch">
    <div class="md:col-span-7 glass-card rounded-[2.5rem] p-lg reveal">
      <span class="eyebrow">Segunda Ensenada</span>
      <h2 class="text-3xl md:text-4xl text-primary mt-2 mb-5">Ubicación de ensueño</h2>
      <p class="text-on-surface-variant text-lg leading-relaxed mb-7 max-w-2xl">
        En el epicentro de la belleza costeña, sobre el Golfo de Morrosquillo. Acceso directo a la arena y conexión rápida con Tolú y San Antero. La puerta de entrada a las Islas de San Bernardo.
      </p>
      <div class="flex gap-10 border-t border-primary/10 pt-6">
        <div><p class="text-mute text-xs uppercase tracking-widest mb-1">Check-In</p><p class="text-3xl font-extrabold text-on-surface tabular"><?= e(CHECKIN_HORA) ?></p></div>
        <div class="w-px h-12 bg-primary/10"></div>
        <div><p class="text-mute text-xs uppercase tracking-widest mb-1">Check-Out</p><p class="text-3xl font-extrabold text-on-surface tabular"><?= e(CHECKOUT_HORA) ?></p></div>
        <div class="w-px h-12 bg-primary/10 hidden sm:block"></div>
        <div class="hidden sm:block"><p class="text-mute text-xs uppercase tracking-widest mb-1">Recepción</p><p class="text-3xl font-extrabold text-on-surface">24 h</p></div>
      </div>
    </div>
    <div class="md:col-span-5 reveal min-h-[340px]">
      <div class="w-full h-full rounded-[2.5rem] overflow-hidden shadow-2xl">
        <iframe title="Mapa Coveñas" class="w-full h-full min-h-[340px]" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
          src="https://www.google.com/maps?q=Cove%C3%B1as%20Segunda%20Ensenada%2C%20Sucre%2C%20Colombia&output=embed"></iframe>
      </div>
    </div>
  </div>
</section>

<!-- ===== IMAGEN INMERSIVA ===== -->
<section class="py-xl relative overflow-hidden group">
  <div class="max-w-[1440px] mx-auto px-gutter reveal">
    <div class="rounded-[3.5rem] overflow-hidden h-[560px] relative shadow-[0_40px_100px_-15px_rgba(0,119,192,0.3)]">
      <img alt="Instalaciones del hotel" class="w-full h-full object-cover transition-transform duration-[3s] group-hover:scale-110" src="<?= img('gal-1.jpg') ?>"/>
      <div class="absolute inset-0 bg-black/10 flex items-center justify-start p-lg md:p-xl">
        <div class="glass-panel p-8 md:p-12 rounded-[2.5rem] max-w-xl">
          <span class="eyebrow">Piscina & playa</span>
          <h3 class="text-3xl md:text-5xl text-on-surface mt-2 mb-5">El reflejo de la calma</h3>
          <p class="text-on-surface-variant text-lg leading-relaxed mb-7">Disfruta nuestras piscinas y la playa mientras el sol caribeño tiñe el horizonte. El plan perfecto para descansar.</p>
          <a href="<?= base_url('servicios.php') ?>" class="btn btn-primary">Conoce las instalaciones</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== TESTIMONIOS ===== -->
<section class="py-xl bg-white">
  <div class="max-w-container-max mx-auto px-gutter">
    <div class="text-center mb-14 reveal">
      <span class="eyebrow">Reseñas</span>
      <h2 class="text-4xl md:text-5xl text-primary mt-2">Lo que dicen nuestros huéspedes</h2>
      <div class="w-24 h-1 bg-primary mx-auto rounded-full mt-5"></div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php
      $reviews = [
        ['"Excelente sitio para descansar, buena atención y la comida espectacular. Un oasis frente al mar."', 'Edel Enrique Buelvas', 'EB'],
        ['"Excelente servicio! La comida uuff, es sencillamente ESPECTACULAR, también la coctelería."', 'DJ Juan de León', 'JD'],
        ['"Perfecto para disfrutar, buen servicio y muy buena ubicación. Volveremos sin duda."', 'Ester Cortés', 'EC'],
      ];
      foreach ($reviews as $i => $r): ?>
        <div class="glass-card rounded-[2rem] p-8 reveal <?= $i==1?'md:mt-10':'' ?>">
          <div class="flex text-primary mb-4">
            <?php for($s=0;$s<5;$s++): ?><span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">star</span><?php endfor; ?>
          </div>
          <p class="text-on-surface-variant mb-6 text-lg leading-relaxed"><?= e($r[0]) ?></p>
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-primary/10 text-primary grid place-items-center font-extrabold"><?= e($r[2]) ?></div>
            <p class="text-[.72rem] text-primary uppercase tracking-widest font-bold"><?= e($r[1]) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== CTA FINAL ===== -->
<section class="relative overflow-hidden">
  <img src="<?= img('beach.jpg') ?>" alt="Playa de Coveñas" class="absolute inset-0 w-full h-full object-cover">
  <div class="absolute inset-0" style="background:linear-gradient(120deg, rgba(0,74,122,.92), rgba(0,119,192,.7))"></div>
  <div class="container-x relative z-10 py-24 text-center">
    <h2 class="text-4xl md:text-6xl text-white leading-none">¿Listo para tus vacaciones?</h2>
    <p class="text-white/85 mt-4 max-w-xl mx-auto text-lg">Reserva en línea en menos de 2 minutos y asegura tu habitación frente al mar.</p>
    <a href="<?= base_url('reservar.php') ?>" class="btn btn-white mt-8 !px-9 !py-4">Reservar ahora</a>
  </div>
</section>

<!-- sincroniza min de salida con la fecha de entrada -->
<script>
  (function(){
    var ci=document.querySelector('input[name=check_in]'), co=document.querySelector('input[name=check_out]');
    if(ci&&co) ci.addEventListener('change',function(){var d=new Date(ci.value+'T00:00:00');d.setDate(d.getDate()+1);var m=d.toISOString().slice(0,10);co.min=m;if(co.value<=ci.value)co.value=m;});
  })();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
