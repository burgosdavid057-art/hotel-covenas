<?php
require_once __DIR__ . '/auth.php';
require_admin();

// Mes seleccionado
$ym = $_GET['ym'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $ym)) $ym = date('Y-m');
[$y, $m] = array_map('intval', explode('-', $ym));
$diasMes  = (int)date('t', mktime(0, 0, 0, $m, 1, $y));
$primer   = sprintf('%04d-%02d-01', $y, $m);
$ultimo   = sprintf('%04d-%02d-%02d', $y, $m, $diasMes);
$hoy      = date('Y-m-d');

$prev = date('Y-m', mktime(0, 0, 0, $m - 1, 1, $y));
$next = date('Y-m', mktime(0, 0, 0, $m + 1, 1, $y));

// Unidades con su tipo
$unidades = db()->query(
    "SELECT u.id, u.numero, u.piso, h.nombre AS hab
     FROM unidades u JOIN habitaciones h ON h.id = u.habitacion_id
     WHERE u.activo = 1 ORDER BY h.nombre, u.numero"
)->fetchAll();

// Reservas que tocan el mes
$st = db()->prepare(
    "SELECT id, codigo, unidad_id, huesped_nombre, check_in, check_out, estado
     FROM reservas WHERE estado != 'cancelada' AND check_in <= ? AND check_out > ?"
);
$st->execute([$ultimo, $primer]);
$reservas = $st->fetchAll();

// Mapa: unidad_id => [dia(1..n) => reserva]
$ocup = [];
foreach ($reservas as $r) {
    for ($d = 1; $d <= $diasMes; $d++) {
        $fecha = sprintf('%04d-%02d-%02d', $y, $m, $d);
        if ($r['check_in'] <= $fecha && $r['check_out'] > $fecha) {
            $ocup[$r['unidad_id']][$d] = $r;
        }
    }
}

admin_header('Calendario', 'calendario.php');
?>

<div class="flex items-center justify-between flex-wrap gap-3 mb-6">
  <h1 class="font-display text-3xl text-navy">Calendario de ocupación</h1>
  <div class="flex items-center gap-2">
    <a href="?ym=<?= $prev ?>" class="w-10 h-10 grid place-items-center rounded-xl bg-white border border-black/5 hover:border-mar text-navy" aria-label="Mes anterior">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
    </a>
    <span class="font-display text-xl text-navy w-44 text-center"><?= ucfirst(mes_nombre($m)) . ' ' . $y ?></span>
    <a href="?ym=<?= $next ?>" class="w-10 h-10 grid place-items-center rounded-xl bg-white border border-black/5 hover:border-mar text-navy" aria-label="Mes siguiente">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
    </a>
  </div>
</div>

<div class="flex items-center gap-4 mb-4 text-xs text-mute">
  <span><span class="legend-dot" style="background:#fff;border:1px solid var(--borde)"></span> Libre</span>
  <span><span class="legend-dot" style="background:var(--mar)"></span> Ocupada</span>
  <span><span class="legend-dot" style="background:var(--oro)"></span> Hoy</span>
</div>

<div class="card-soft overflow-hidden">
  <div class="overflow-x-auto">
    <table class="border-collapse text-xs" style="min-width:max-content">
      <thead>
        <tr>
          <th class="sticky left-0 z-10 bg-arena-2 p-2 text-left font-semibold text-navy border-b border-black/5" style="min-width:170px">Habitación</th>
          <?php for ($d = 1; $d <= $diasMes; $d++):
            $fecha = sprintf('%04d-%02d-%02d', $y, $m, $d);
            $dow = (int)date('w', strtotime($fecha));
            $finde = ($dow === 0 || $dow === 6);
            $esHoy = $fecha === $hoy;
          ?>
            <th class="p-1.5 text-center border-b border-l border-black/5 <?= $finde ? 'bg-arena-2/60' : '' ?> <?= $esHoy ? '!bg-oro/20' : '' ?>" style="min-width:30px">
              <span class="block text-[.6rem] text-mute"><?= ['D','L','M','M','J','V','S'][$dow] ?></span>
              <span class="block font-semibold text-navy"><?= $d ?></span>
            </th>
          <?php endfor; ?>
        </tr>
      </thead>
      <tbody>
        <?php $habActual = ''; foreach ($unidades as $u): ?>
          <?php if ($u['hab'] !== $habActual): $habActual = $u['hab']; ?>
            <tr><td class="sticky left-0 z-10 bg-espuma/60 px-2 py-1 font-semibold text-mar text-[.7rem] uppercase tracking-wider border-b border-black/5" colspan="<?= $diasMes + 1 ?>"><?= e($habActual) ?></td></tr>
          <?php endif; ?>
          <tr>
            <td class="sticky left-0 z-10 bg-white px-2 py-1.5 text-navy border-b border-black/5 whitespace-nowrap">Hab. <?= e($u['numero']) ?> <span class="text-mute">· P<?= (int)$u['piso'] ?></span></td>
            <?php for ($d = 1; $d <= $diasMes; $d++):
              $fecha = sprintf('%04d-%02d-%02d', $y, $m, $d);
              $esHoy = $fecha === $hoy;
              $r = $ocup[$u['id']][$d] ?? null;
            ?>
              <?php if ($r): ?>
                <td class="border-b border-l border-white/40 p-0">
                  <a href="<?= base_url('admin/reservas.php?codigo=' . urlencode($r['codigo'])) ?>"
                     class="block w-full h-7 hover:opacity-80" style="background:var(--mar)"
                     title="<?= e($r['huesped_nombre'] . ' · ' . $r['codigo']) ?>"></a>
                </td>
              <?php else: ?>
                <td class="border-b border-l border-black/5 h-7 <?= $esHoy ? 'bg-oro/10' : '' ?>"></td>
              <?php endif; ?>
            <?php endfor; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<p class="text-xs text-mute mt-3">Haz clic en una barra ocupada para ver la reserva. Las reservas canceladas no aparecen.</p>

<?php admin_footer(); ?>
