<?php
require_once __DIR__ . '/includes/functions.php';

$vista = $_GET['vista'] ?? '';
$orden = $_GET['orden'] ?? '';
$opts = [];
if (in_array($vista, ['mar', 'piscina', 'jardin'], true)) $opts['vista'] = $vista;
if (in_array($orden, ['precio_asc', 'precio_desc', 'nombre'], true)) $opts['orden'] = $orden;
$habitaciones = get_habitaciones($opts);

$titulo = 'Habitaciones y suites · ' . SITE_NAME;
$activo = 'habitaciones';
require __DIR__ . '/includes/header.php';

$vistas = ['' => 'Todas', 'mar' => 'Vista al mar', 'piscina' => 'Vista a la piscina', 'jardin' => 'Vista al jardín'];
?>

<section class="container-x pt-10 pb-6">
  <p class="eyebrow">Alojamiento</p>
  <h1 class="font-display text-4xl sm:text-5xl text-navy mt-2">Habitaciones y suites</h1>
  <p class="text-mute mt-3 max-w-2xl">Cada espacio está diseñado para tu descanso, con la brisa y el color del Caribe. Elige el tuyo y reserva en línea.</p>
</section>

<!-- Filtros -->
<section class="container-x pb-8">
  <form method="get" class="flex flex-wrap items-end gap-4">
    <div>
      <label class="lbl" for="f-vista">Vista</label>
      <select id="f-vista" name="vista" class="field !py-2.5" onchange="this.form.submit()">
        <?php foreach ($vistas as $k => $lbl): ?>
          <option value="<?= e($k) ?>" <?= $vista === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="lbl" for="f-orden">Ordenar por</label>
      <select id="f-orden" name="orden" class="field !py-2.5" onchange="this.form.submit()">
        <option value="" <?= $orden === '' ? 'selected' : '' ?>>Recomendado</option>
        <option value="precio_asc" <?= $orden === 'precio_asc' ? 'selected' : '' ?>>Precio: menor a mayor</option>
        <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
        <option value="nombre" <?= $orden === 'nombre' ? 'selected' : '' ?>>Nombre</option>
      </select>
    </div>
    <p class="text-sm text-mute ml-auto"><?= count($habitaciones) ?> opciones</p>
  </form>
</section>

<section class="container-x pb-20">
  <?php if (!$habitaciones): ?>
    <div class="card-soft p-12 text-center text-mute">No hay habitaciones que coincidan con el filtro.</div>
  <?php else: ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6" data-reveal-group>
      <?php foreach ($habitaciones as $h) room_card($h); ?>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
