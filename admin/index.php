<?php
require_once __DIR__ . '/auth.php';
require_admin();

$hoy = date('Y-m-d');
$mesIni = date('Y-m-01');
$mesFin = date('Y-m-t');

$totalUnidades = (int)db()->query("SELECT COUNT(*) c FROM unidades WHERE activo = 1")->fetch()['c'];

$ocupadasHoy = (int)db()->query(
    "SELECT COUNT(DISTINCT unidad_id) c FROM reservas
     WHERE estado != 'cancelada' AND check_in <= '$hoy' AND check_out > '$hoy'"
)->fetch()['c'];
$ocupacion = $totalUnidades ? round($ocupadasHoy / $totalUnidades * 100) : 0;

$st = db()->prepare("SELECT COUNT(*) c FROM reservas WHERE check_in = ? AND estado != 'cancelada'");
$st->execute([$hoy]);
$llegadasHoy = (int)$st->fetch()['c'];

$st = db()->prepare("SELECT COUNT(*) c FROM reservas WHERE estado != 'cancelada'");
$st->execute();
$reservasActivas = (int)$st->fetch()['c'];

$st = db()->prepare("SELECT COALESCE(SUM(total),0) s FROM reservas WHERE estado != 'cancelada' AND check_in BETWEEN ? AND ?");
$st->execute([$mesIni, $mesFin]);
$ingresosMes = (int)$st->fetch()['s'];

// Próximas llegadas
$st = db()->prepare(
    "SELECT r.*, h.nombre AS hab, u.numero AS unidad
     FROM reservas r LEFT JOIN habitaciones h ON h.id=r.habitacion_id LEFT JOIN unidades u ON u.id=r.unidad_id
     WHERE r.check_in >= ? AND r.estado != 'cancelada'
     ORDER BY r.check_in ASC LIMIT 8"
);
$st->execute([$hoy]);
$proximas = $st->fetchAll();

// Reservas recientes
$recientes = db()->query(
    "SELECT r.*, h.nombre AS hab, u.numero AS unidad
     FROM reservas r LEFT JOIN habitaciones h ON h.id=r.habitacion_id LEFT JOIN unidades u ON u.id=r.unidad_id
     ORDER BY r.id DESC LIMIT 6"
)->fetchAll();

admin_header('Dashboard', 'index.php');
?>

<div class="flex items-center justify-between flex-wrap gap-3 mb-6">
  <div>
    <h1 class="font-display text-3xl text-navy">Hola de nuevo</h1>
    <p class="text-mute text-sm"><?= dia_semana($hoy) . ', ' . (int)date('d') . ' de ' . mes_nombre((int)date('n')) . ' de ' . date('Y') ?></p>
  </div>
  <a href="<?= base_url('admin/calendario.php') ?>" class="btn btn-mar !py-2.5 text-xs">Ver calendario</a>
</div>

<!-- KPIs -->
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
  <?php
  $kpis = [
    ['Ocupación hoy', $ocupacion . '%', $ocupadasHoy . ' de ' . $totalUnidades . ' habitaciones', 'mar'],
    ['Llegadas hoy', $llegadasHoy, 'Check-ins programados', 'oro'],
    ['Reservas activas', $reservasActivas, 'Sin contar canceladas', 'mar'],
    ['Ingresos del mes', precio_cop($ingresosMes), ucfirst(mes_nombre((int)date('n'))), 'oro'],
  ];
  foreach ($kpis as $k): ?>
    <div class="card-soft p-5">
      <p class="text-xs uppercase tracking-wider text-mute"><?= e($k[0]) ?></p>
      <p class="font-display text-3xl mt-1 <?= $k[3] === 'mar' ? 'text-mar' : 'text-oro-dark' ?> tabular"><?= e((string)$k[1]) ?></p>
      <p class="text-xs text-mute mt-1"><?= e($k[2]) ?></p>
    </div>
  <?php endforeach; ?>
</div>

<div class="grid lg:grid-cols-2 gap-6">
  <!-- Próximas llegadas -->
  <div class="card-soft p-5">
    <h2 class="font-display text-xl text-navy mb-4">Próximas llegadas</h2>
    <?php if (!$proximas): ?>
      <p class="text-mute text-sm">No hay llegadas próximas.</p>
    <?php else: ?>
      <div class="space-y-2">
        <?php foreach ($proximas as $r): ?>
          <a href="<?= base_url('admin/reservas.php?codigo=' . urlencode($r['codigo'])) ?>" class="flex items-center gap-3 p-3 rounded-xl hover:bg-arena-2 transition-colors">
            <div class="w-11 h-11 rounded-xl bg-espuma text-mar grid place-items-center text-center leading-none shrink-0">
              <span class="block text-base font-bold"><?= date('d', strtotime($r['check_in'])) ?></span>
              <span class="block text-[.55rem] uppercase"><?= mes_nombre((int)date('n', strtotime($r['check_in'])), true) ?></span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-navy truncate"><?= e($r['huesped_nombre']) ?></p>
              <p class="text-xs text-mute truncate"><?= e($r['hab']) ?> · Hab. <?= e($r['unidad']) ?> · <?= (int)$r['noches'] ?> noches</p>
            </div>
            <?= estado_badge($r['estado']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Reservas recientes -->
  <div class="card-soft p-5">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-display text-xl text-navy">Reservas recientes</h2>
      <a href="<?= base_url('admin/reservas.php') ?>" class="text-sm text-mar link-under">Ver todas</a>
    </div>
    <?php if (!$recientes): ?>
      <p class="text-mute text-sm">Aún no hay reservas.</p>
    <?php else: ?>
      <div class="space-y-2">
        <?php foreach ($recientes as $r): ?>
          <a href="<?= base_url('admin/reservas.php?codigo=' . urlencode($r['codigo'])) ?>" class="flex items-center justify-between gap-3 p-3 rounded-xl hover:bg-arena-2 transition-colors">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-navy truncate"><?= e($r['huesped_nombre']) ?> <span class="text-mute font-normal">· <?= e($r['codigo']) ?></span></p>
              <p class="text-xs text-mute truncate"><?= e($r['check_in']) ?> → <?= e($r['check_out']) ?></p>
            </div>
            <span class="text-sm font-semibold text-mar tabular shrink-0"><?= precio_cop($r['total']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php admin_footer(); ?>
