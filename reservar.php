<?php
require_once __DIR__ . '/includes/functions.php';

$post_error = '';

// ── Procesar reserva (POST) ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in  = trim($_POST['check_in']  ?? '');
    $out = trim($_POST['check_out'] ?? '');
    $adultos  = max(1, (int)($_POST['adultos'] ?? 1));
    $ninos    = max(0, (int)($_POST['ninos'] ?? 0));
    $unidadId = (int)($_POST['unidad_id'] ?? 0);
    $nombre   = trim($_POST['nombre'] ?? '');
    $documento= trim($_POST['documento'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $solic    = trim($_POST['solicitudes'] ?? '');
    $extrasIn = json_decode($_POST['extras'] ?? '[]', true);
    if (!is_array($extrasIn)) $extrasIn = [];

    $errores = [];
    if (!fecha_valida($in) || !fecha_valida($out)) $errores[] = 'Fechas no válidas.';
    elseif ($in < date('Y-m-d'))                   $errores[] = 'La fecha de entrada no puede ser en el pasado.';
    $noches = (fecha_valida($in) && fecha_valida($out)) ? noches_entre($in, $out) : 0;
    if ($noches <= 0) $errores[] = 'La salida debe ser posterior a la entrada.';
    if ($nombre === '')   $errores[] = 'Ingresa el nombre del huésped.';
    if ($telefono === '') $errores[] = 'Ingresa un teléfono de contacto.';

    // Validar unidad + habitación contra la BD
    $habitacion = null; $unidad = null;
    if ($unidadId > 0) {
        $st = db()->prepare('SELECT * FROM unidades WHERE id = ? AND activo = 1');
        $st->execute([$unidadId]);
        $unidad = $st->fetch() ?: null;
        if ($unidad) $habitacion = get_habitacion((int)$unidad['habitacion_id']);
    }
    if (!$unidad || !$habitacion || !$habitacion['activo']) {
        $errores[] = 'La habitación seleccionada ya no está disponible.';
    } else {
        if ((int)$habitacion['capacidad_adultos'] < $adultos || (int)$habitacion['capacidad_ninos'] < $ninos)
            $errores[] = 'La habitación no admite ese número de huéspedes.';
        if (!$errores && !unidad_disponible($unidadId, $in, $out))
            $errores[] = 'Esa habitación acaba de ser reservada para esas fechas. Elige otra.';
    }

    if (!$errores) {
        // Recalcular precios desde la BD (nunca confiar en el cliente)
        $precioNoche = precio_final($habitacion);
        $subtotal    = $precioNoche * $noches;
        $huespedes   = $adultos + $ninos;

        // Extras válidos desde la BD
        $snapshot = []; $extrasTotal = 0;
        if ($extrasIn) {
            $ph = implode(',', array_fill(0, count($extrasIn), '?'));
            $st = db()->prepare("SELECT * FROM extras WHERE activo = 1 AND id IN ($ph)");
            $st->execute(array_map('intval', $extrasIn));
            foreach ($st->fetchAll() as $x) {
                $costo = costo_extra($x, $noches, $huespedes);
                $extrasTotal += $costo;
                $snapshot[] = ['id'=>(int)$x['id'],'nombre'=>$x['nombre'],'tipo'=>$x['tipo'],'precio'=>(int)$x['precio'],'costo'=>$costo];
            }
        }
        $total    = $subtotal + $extrasTotal;
        $anticipo = (int)round($total * DEPOSIT_PCT / 100);
        $codigo   = generar_codigo_reserva();

        $st = db()->prepare(
            'INSERT INTO reservas (codigo, unidad_id, habitacion_id, huesped_nombre, documento, email, telefono, check_in, check_out, adultos, ninos, noches, precio_noche, extras, subtotal, total, anticipo, estado, metodo_pago, solicitudes)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $st->execute([
            $codigo, $unidadId, (int)$habitacion['id'], $nombre, $documento, $email, $telefono,
            $in, $out, $adultos, $ninos, $noches, $precioNoche,
            json_encode($snapshot, JSON_UNESCAPED_UNICODE), $subtotal, $total, $anticipo,
            'pendiente', 'online', $solic,
        ]);

        header('Location: ' . base_url('confirmacion.php?codigo=' . urlencode($codigo)));
        exit;
    }
    $post_error = implode(' ', $errores);
}

// ── Presets (GET) ─────────────────────────────────────────────────────────────
$pre_in  = fecha_valida($_GET['check_in'] ?? '')  ? $_GET['check_in']  : '';
$pre_out = fecha_valida($_GET['check_out'] ?? '') ? $_GET['check_out'] : '';
$pre_ad  = max(1, (int)($_GET['adultos'] ?? 2));
$pre_ni  = max(0, (int)($_GET['ninos'] ?? 0));
$pre_hab = null;
if (!empty($_GET['hab'])) {
    $h = get_habitacion_slug($_GET['hab']);
    if ($h) $pre_hab = (int)$h['id'];
}

$extras_cfg = array_map(fn($x) => [
    'id'=>(int)$x['id'],'nombre'=>$x['nombre'],'descripcion'=>$x['descripcion'],
    'precio'=>(int)$x['precio'],'tipo'=>$x['tipo'],'icono'=>$x['icono'],
], get_extras(true));

$cfg = [
    'apiUrl'     => base_url('api/disponibilidad.php'),
    'today'      => date('Y-m-d'),
    'depositPct' => DEPOSIT_PCT,
    'waNumber'   => WHATSAPP_NUMBER,
    'extras'     => $extras_cfg,
    'preset'     => ['checkIn'=>$pre_in,'checkOut'=>$pre_out,'adultos'=>$pre_ad,'ninos'=>$pre_ni,'habId'=>$pre_hab],
];

$titulo = 'Reservar · ' . SITE_NAME;
$activo = '';
require __DIR__ . '/includes/header.php';

$steps = ['Fechas', 'Habitación', 'Extras', 'Datos', 'Confirmar'];
$iconExtra = [
    'sun'  => 'M12 3v1.5m0 15V21m9-9h-1.5m-15 0H3m15.36-6.36-1.06 1.06M6.7 17.3l-1.06 1.06m12.72 0-1.06-1.06M6.7 6.7 5.64 5.64M12 7.5a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9Z',
    'car'  => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-6m6-13.5h-2.25M14.25 12V5.25',
    'boat' => 'M3 13.5 6 6h12l3 7.5M3 13.5h18M3 13.5l1.5 5.25a1.5 1.5 0 0 0 1.44 1.08h12.12a1.5 1.5 0 0 0 1.44-1.08L21 13.5M12 6V3',
    'heart'=> 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
    'clock'=> 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
];
?>

<section class="container-x pt-10 pb-6">
  <p class="eyebrow">Reserva en línea</p>
  <h1 class="font-display text-4xl sm:text-5xl text-navy mt-2">Diseña tu estadía frente al mar</h1>
  <p class="text-mute mt-3 max-w-2xl">Elige tus fechas, escoge tu habitación en el mapa y confirma en minutos. Sin comisiones ni intermediarios.</p>
</section>

<?php if ($post_error): ?>
<div class="container-x mb-4">
  <div class="card-soft border-l-4 border-l-red-400 p-4 text-sm text-red-700 bg-red-50/60" role="alert">
    <?= e($post_error) ?>
  </div>
</div>
<?php endif; ?>

<form id="wizard" method="post" action="<?= base_url('reservar.php') ?>"
      x-data="booking(<?= e(json_encode($cfg, JSON_UNESCAPED_UNICODE)) ?>)"
      class="container-x pb-24 scroll-mt-24">

  <!-- Campos ocultos (re-validados en el servidor) -->
  <input type="hidden" name="check_in"  :value="checkIn">
  <input type="hidden" name="check_out" :value="checkOut">
  <input type="hidden" name="adultos"   :value="adultos">
  <input type="hidden" name="ninos"     :value="ninos">
  <input type="hidden" name="unidad_id" :value="sel ? sel.unidadId : ''">
  <input type="hidden" name="extras"    :value="extrasIds">

  <!-- Stepper -->
  <div class="step-bar mb-8" role="tablist" aria-label="Pasos de la reserva">
    <?php foreach ($steps as $i => $s): $n = $i + 1; ?>
      <?php if ($i > 0): ?><div class="step-conn flex-1" :class="step > <?= $i ?> ? 'step--done' : ''"><div class="step-line" :class="step > <?= $i ?> ? '!bg-mar' : ''"></div></div><?php endif; ?>
      <button type="button" @click="goTo(<?= $n ?>)"
              class="flex items-center gap-2 group"
              :class="{ 'step--active': step === <?= $n ?>, 'step--done': step > <?= $n ?> }">
        <span class="step-num">
          <span x-show="step <= <?= $n ?>"><?= $n ?></span>
          <svg x-show="step > <?= $n ?>" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        </span>
        <span class="hidden sm:block text-xs font-semibold uppercase tracking-wider"
              :class="step === <?= $n ?> ? 'text-mar' : 'text-mute'"><?= e($s) ?></span>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="grid lg:grid-cols-[1fr_360px] gap-8 items-start">
    <!-- ===== Columna de pasos ===== -->
    <div>
      <!-- PASO 1: Fechas y huéspedes -->
      <div x-show="step === 1" x-transition.opacity class="card-soft p-6 sm:p-8">
        <h2 class="font-display text-2xl text-navy mb-1">¿Cuándo nos visitas?</h2>
        <p class="text-mute text-sm mb-6">Selecciona las fechas de entrada y salida.</p>

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="lbl" for="ci">Entrada (check-in)</label>
            <input id="ci" type="date" class="field" x-model="checkIn" :min="today" @change="onCheckInChange()">
          </div>
          <div>
            <label class="lbl" for="co">Salida (check-out)</label>
            <input id="co" type="date" class="field" x-model="checkOut" :min="minOut">
          </div>
        </div>

        <div class="mt-6 grid sm:grid-cols-2 gap-4">
          <div class="panel p-4 flex items-center justify-between">
            <div><p class="font-semibold text-navy text-sm">Adultos</p><p class="text-xs text-mute">Desde 13 años</p></div>
            <div class="flex items-center gap-3">
              <button type="button" class="counter-btn" @click="adultos = Math.max(1, adultos-1)" :disabled="adultos<=1" aria-label="Menos adultos">−</button>
              <span class="w-6 text-center font-semibold tabular" x-text="adultos"></span>
              <button type="button" class="counter-btn" @click="adultos = Math.min(8, adultos+1)" :disabled="adultos>=8" aria-label="Más adultos">+</button>
            </div>
          </div>
          <div class="panel p-4 flex items-center justify-between">
            <div><p class="font-semibold text-navy text-sm">Niños</p><p class="text-xs text-mute">0 a 12 años</p></div>
            <div class="flex items-center gap-3">
              <button type="button" class="counter-btn" @click="ninos = Math.max(0, ninos-1)" :disabled="ninos<=0" aria-label="Menos niños">−</button>
              <span class="w-6 text-center font-semibold tabular" x-text="ninos"></span>
              <button type="button" class="counter-btn" @click="ninos = Math.min(4, ninos+1)" :disabled="ninos>=4" aria-label="Más niños">+</button>
            </div>
          </div>
        </div>

        <div class="mt-6 flex items-center gap-2 text-sm text-mar" x-show="noches > 0" x-cloak>
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
          <span><strong x-text="noches"></strong> <span x-text="noches === 1 ? 'noche' : 'noches'"></span> · <span x-text="huespedes"></span> <span x-text="huespedes === 1 ? 'huésped' : 'huéspedes'"></span></span>
        </div>
        <p class="mt-3 text-sm text-red-600" x-show="dateError" x-text="dateError" x-cloak></p>

        <div class="mt-8 flex justify-end">
          <button type="button" class="btn btn-mar" :disabled="!step1Ok()" @click="goStep2()">Ver disponibilidad →</button>
        </div>
      </div>

      <!-- PASO 2: Mapa de habitaciones -->
      <div x-show="step === 2" x-transition.opacity>
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-display text-2xl text-navy">Elige tu habitación</h2>
          <button type="button" class="text-sm text-mar link-under" @click="back()">← Cambiar fechas</button>
        </div>

        <!-- Leyenda -->
        <div class="flex flex-wrap items-center gap-4 mb-5 text-xs text-mute">
          <span><span class="legend-dot" style="background:#fff;border:1.5px solid var(--borde-mar)"></span> Disponible</span>
          <span><span class="legend-dot" style="background:var(--mar)"></span> Seleccionada</span>
          <span><span class="legend-dot" style="background:var(--arena-2)"></span> Ocupada</span>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="card-soft p-10 text-center text-mute" x-cloak>
          <svg class="w-8 h-8 mx-auto animate-spin text-mar" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4z"/></svg>
          <p class="mt-3 text-sm">Consultando disponibilidad…</p>
        </div>

        <!-- Error -->
        <div x-show="loadError" x-cloak class="card-soft p-6 text-center" x-text="loadError"></div>

        <!-- Sin disponibilidad -->
        <template x-if="!loading && !loadError && tipos.length === 0">
          <div class="card-soft p-10 text-center">
            <p class="font-display text-2xl text-navy">Sin disponibilidad</p>
            <p class="text-mute text-sm mt-2 max-w-md mx-auto">No encontramos habitaciones para esas fechas y número de huéspedes. Prueba con otras fechas.</p>
            <button type="button" class="btn btn-ghost mt-5" @click="back()">Cambiar fechas</button>
          </div>
        </template>

        <!-- Tipos disponibles -->
        <div class="space-y-5" x-show="!loading && tipos.length > 0">
          <template x-for="t in tipos" :key="t.id">
            <div class="card-soft overflow-hidden" :class="sel && sel.habId === t.id ? 'ring-2 ring-mar' : ''">
              <div class="grid md:grid-cols-[200px_1fr]">
                <div class="aspect-[4/3] md:aspect-auto md:h-full overflow-hidden bg-arena-2">
                  <img :src="t.imagen" :alt="t.nombre" class="w-full h-full object-cover" loading="lazy">
                </div>
                <div class="p-5">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <span class="badge badge-mar" x-text="t.vista_label"></span>
                      <h3 class="font-display text-xl text-navy mt-1.5" x-text="t.nombre"></h3>
                      <p class="text-xs text-mute mt-1"><span x-text="t.camas"></span> · <span x-text="t.m2"></span> m² · hasta <span x-text="t.cap_adultos"></span> adultos</p>
                    </div>
                    <div class="text-right shrink-0">
                      <p class="text-xs text-mute">por noche</p>
                      <p class="text-xl font-semibold text-mar tabular" x-text="fmt(t.precio_noche)"></p>
                      <p class="text-[.7rem] text-mute tabular"><span x-text="fmt(t.subtotal)"></span> total</p>
                    </div>
                  </div>

                  <!-- Mapa de unidades (estilo cine) -->
                  <div class="mt-4 pt-4 border-t border-black/5">
                    <p class="text-xs text-mute mb-2">Elige tu habitación (<span x-text="t.disponibles"></span> disponibles):</p>
                    <template x-for="piso in [...new Set(t.unidades.map(u => u.piso))]" :key="piso">
                      <div class="flex items-center gap-3 mb-2">
                        <span class="text-[.65rem] text-mute uppercase tracking-wider w-12 shrink-0">Piso <span x-text="piso"></span></span>
                        <div class="flex flex-wrap gap-2">
                          <template x-for="u in t.unidades.filter(x => x.piso === piso)" :key="u.id">
                            <button type="button"
                                    class="seat"
                                    :class="{ 'seat--taken': !u.libre, 'seat--sel': isSel(u) }"
                                    :disabled="!u.libre"
                                    @click="u.libre && selectUnit(t, u)"
                                    :aria-label="'Habitación ' + u.numero + (u.libre ? '' : ' (ocupada)')">
                              <span x-text="u.numero"></span>
                              <small x-show="isSel(u)">Elegida</small>
                            </button>
                          </template>
                        </div>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>

        <div class="mt-8 flex justify-between" x-show="!loading && tipos.length > 0">
          <button type="button" class="btn btn-ghost" @click="back()">← Atrás</button>
          <button type="button" class="btn btn-mar" :disabled="!sel" @click="next()">Continuar →</button>
        </div>
      </div>

      <!-- PASO 3: Extras -->
      <div x-show="step === 3" x-transition.opacity class="card-soft p-6 sm:p-8">
        <h2 class="font-display text-2xl text-navy mb-1">Suma experiencias</h2>
        <p class="text-mute text-sm mb-6">Opcional. Personaliza tu estadía con servicios adicionales.</p>

        <div class="space-y-3">
          <template x-for="x in extras" :key="x.id">
            <label class="flex items-center gap-4 p-4 rounded-2xl border cursor-pointer transition-all"
                   :class="x.sel ? 'border-mar bg-espuma/40' : 'border-black/10 hover:border-mar/40'">
              <input type="checkbox" x-model="x.sel" class="sr-only">
              <span class="w-11 h-11 rounded-full grid place-items-center shrink-0"
                    :class="x.sel ? 'bg-mar text-white' : 'bg-arena-2 text-mar'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="({
                  <?php foreach ($iconExtra as $k => $d): ?>'<?= $k ?>': '<?= $d ?>',<?php endforeach; ?>
                })[x.icono] || '<?= $iconExtra['star'] ?? 'M11.48 3.5a.6.6 0 0 1 1.04 0l2.3 4.7 5.16.76a.6.6 0 0 1 .33 1.02l-3.74 3.66.88 5.16a.6.6 0 0 1-.87.63L12 17.3l-4.62 2.4a.6.6 0 0 1-.87-.63l.88-5.16-3.74-3.66a.6.6 0 0 1 .33-1.02l5.16-.76 2.3-4.7Z' ?>'"/></svg>
              </span>
              <span class="flex-1 min-w-0">
                <span class="block font-semibold text-navy text-sm" x-text="x.nombre"></span>
                <span class="block text-xs text-mute" x-text="x.descripcion"></span>
              </span>
              <span class="text-right shrink-0">
                <span class="block font-semibold text-mar tabular" x-text="fmt(x.precio)"></span>
                <span class="block text-[.65rem] text-mute" x-text="tipoLabel(x)"></span>
              </span>
            </label>
          </template>
        </div>

        <div class="mt-8 flex justify-between">
          <button type="button" class="btn btn-ghost" @click="back()">← Atrás</button>
          <button type="button" class="btn btn-mar" @click="next()">Continuar →</button>
        </div>
      </div>

      <!-- PASO 4: Datos del huésped -->
      <div x-show="step === 4" x-transition.opacity class="card-soft p-6 sm:p-8">
        <h2 class="font-display text-2xl text-navy mb-1">Datos del huésped</h2>
        <p class="text-mute text-sm mb-6">Usaremos estos datos para confirmar tu reserva.</p>

        <div class="grid sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="lbl" for="g-nombre">Nombre completo *</label>
            <input id="g-nombre" name="nombre" type="text" class="field" :class="errors.nombre ? 'field-err' : ''" x-model="guest.nombre" autocomplete="name">
            <p class="text-xs text-red-600 mt-1" x-show="errors.nombre" x-text="errors.nombre" x-cloak></p>
          </div>
          <div>
            <label class="lbl" for="g-doc">Documento</label>
            <input id="g-doc" name="documento" type="text" class="field" x-model="guest.documento" autocomplete="off">
          </div>
          <div>
            <label class="lbl" for="g-tel">Teléfono / WhatsApp *</label>
            <input id="g-tel" name="telefono" type="tel" class="field" :class="errors.telefono ? 'field-err' : ''" x-model="guest.telefono" autocomplete="tel">
            <p class="text-xs text-red-600 mt-1" x-show="errors.telefono" x-text="errors.telefono" x-cloak></p>
          </div>
          <div class="sm:col-span-2">
            <label class="lbl" for="g-email">Correo electrónico</label>
            <input id="g-email" name="email" type="email" class="field" :class="errors.email ? 'field-err' : ''" x-model="guest.email" autocomplete="email">
            <p class="text-xs text-red-600 mt-1" x-show="errors.email" x-text="errors.email" x-cloak></p>
          </div>
          <div class="sm:col-span-2">
            <label class="lbl" for="g-sol">Solicitudes especiales</label>
            <textarea id="g-sol" name="solicitudes" rows="3" class="field" x-model="guest.solicitudes" placeholder="Cama adicional, llegada tardía, celebración…"></textarea>
          </div>
        </div>

        <div class="mt-8 flex justify-between">
          <button type="button" class="btn btn-ghost" @click="back()">← Atrás</button>
          <button type="button" class="btn btn-mar" @click="next()">Revisar reserva →</button>
        </div>
      </div>

      <!-- PASO 5: Confirmar -->
      <div x-show="step === 5" x-transition.opacity class="card-soft p-6 sm:p-8">
        <h2 class="font-display text-2xl text-navy mb-1">Confirma tu reserva</h2>
        <p class="text-mute text-sm mb-6">Revisa el detalle antes de confirmar.</p>

        <div class="space-y-3 text-sm">
          <div class="flex justify-between"><span class="text-mute">Habitación</span><span class="font-semibold text-navy text-right" x-text="sel ? (sel.habNombre + ' · Hab. ' + sel.numero) : ''"></span></div>
          <div class="flex justify-between"><span class="text-mute">Fechas</span><span class="text-navy text-right" x-text="checkIn + ' → ' + checkOut"></span></div>
          <div class="flex justify-between"><span class="text-mute">Noches / huéspedes</span><span class="text-navy"><span x-text="noches"></span> · <span x-text="huespedes"></span></span></div>
          <div class="flex justify-between"><span class="text-mute">Huésped</span><span class="text-navy text-right" x-text="guest.nombre"></span></div>
        </div>

        <div class="mt-6 panel p-5">
          <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-mar shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3M3.75 19.5h16.5A2.25 2.25 0 0 0 22.5 17.25V6.75A2.25 2.25 0 0 0 20.25 4.5H3.75A2.25 2.25 0 0 0 1.5 6.75v10.5A2.25 2.25 0 0 0 3.75 19.5Z"/></svg>
            <div class="flex-1">
              <p class="font-semibold text-navy text-sm">Pago en línea seguro</p>
              <p class="text-xs text-mute mt-0.5">Anticipo sugerido del <?= DEPOSIT_PCT ?>%: <strong class="text-mar tabular" x-text="fmt(anticipo)"></strong>. Tras confirmar, coordinamos el pago por la pasarela o WhatsApp.</p>
              <button type="button" disabled class="btn btn-light mt-3 text-xs" title="Disponible próximamente">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                Pagar con Wompi (próximamente)
              </button>
            </div>
          </div>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row justify-between gap-3">
          <button type="button" class="btn btn-ghost" @click="back()">← Atrás</button>
          <button type="submit" class="btn btn-mar" :disabled="!canSubmit()" @click="if(!canSubmit()){$event.preventDefault()}">
            Confirmar reserva
          </button>
        </div>
      </div>
    </div>

    <!-- ===== Resumen lateral (sticky) ===== -->
    <aside class="lg:sticky lg:top-24">
      <div class="card-soft overflow-hidden">
        <div class="aspect-[16/10] bg-arena-2 relative">
          <template x-if="sel">
            <img :src="sel.imagen" :alt="sel.habNombre" class="w-full h-full object-cover">
          </template>
          <template x-if="!sel">
            <div class="w-full h-full grid place-items-center text-mute">
              <div class="text-center">
                <svg class="w-10 h-10 mx-auto opacity-40" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2 18c1.5 0 1.5-1 3-1s1.5 1 3 1 1.5-1 3-1 1.5 1 3 1 1.5-1 3-1 1.5 1 3 1M4 14V8a8 8 0 0 1 16 0v6"/></svg>
                <p class="text-xs mt-2">Tu selección aparecerá aquí</p>
              </div>
            </div>
          </template>
        </div>
        <div class="p-5">
          <h3 class="font-display text-lg text-navy" x-text="sel ? sel.habNombre : 'Resumen de reserva'"></h3>
          <p class="text-xs text-mute" x-show="sel" x-cloak><span x-text="sel ? ('Habitación ' + sel.numero + ' · ' + sel.vista) : ''"></span></p>

          <div class="mt-4 space-y-2 text-sm border-t border-black/5 pt-4">
            <div class="flex justify-between"><span class="text-mute">Entrada</span><span class="text-navy tabular" x-text="checkIn || '—'"></span></div>
            <div class="flex justify-between"><span class="text-mute">Salida</span><span class="text-navy tabular" x-text="checkOut || '—'"></span></div>
            <div class="flex justify-between"><span class="text-mute">Huéspedes</span><span class="text-navy"><span x-text="adultos"></span> ad · <span x-text="ninos"></span> niñ.</span></div>
          </div>

          <div class="mt-4 space-y-2 text-sm border-t border-black/5 pt-4" x-show="sel" x-cloak>
            <div class="flex justify-between">
              <span class="text-mute"><span x-text="fmt(sel ? sel.precioNoche : 0)"></span> × <span x-text="noches"></span> noches</span>
              <span class="text-navy tabular" x-text="fmt(subtotalHab)"></span>
            </div>
            <template x-for="x in extrasSel" :key="x.id">
              <div class="flex justify-between text-xs">
                <span class="text-mute" x-text="x.nombre"></span>
                <span class="text-navy tabular" x-text="fmt(extraCost(x))"></span>
              </div>
            </template>
          </div>

          <div class="mt-4 flex justify-between items-end border-t border-black/5 pt-4" x-show="sel" x-cloak>
            <span class="text-sm text-mute">Total</span>
            <span class="font-display text-2xl text-mar tabular" x-text="fmt(total)"></span>
          </div>
          <p class="text-[.7rem] text-mute mt-1 text-right" x-show="sel" x-cloak>Anticipo <?= DEPOSIT_PCT ?>%: <span class="tabular" x-text="fmt(anticipo)"></span></p>
        </div>
      </div>

      <p class="text-[.7rem] text-mute mt-4 flex items-center gap-2 justify-center">
        <svg class="w-4 h-4 text-mar" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        Reserva sin costo · Confirmación inmediata
      </p>
    </aside>
  </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
