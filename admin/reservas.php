<?php
require_once __DIR__ . '/auth.php';
require_admin();

// Cambiar estado (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nuevo = $_POST['estado'] ?? '';
    if ($id && in_array($nuevo, ['pendiente','confirmada','pagada','cancelada'], true)) {
        $st = db()->prepare('UPDATE reservas SET estado = ? WHERE id = ?');
        $st->execute([$nuevo, $id]);
    }
    $back = $_POST['back'] ?? base_url('admin/reservas.php');
    header('Location: ' . $back);
    exit;
}

$estado = $_GET['estado'] ?? '';
$q      = trim($_GET['q'] ?? '');
$codigo = trim($_GET['codigo'] ?? '');

// Detalle (panel) si viene ?codigo
$detalle = $codigo ? get_reserva_codigo($codigo) : null;

// Listado
$sql = "SELECT r.*, h.nombre AS hab, u.numero AS unidad
        FROM reservas r LEFT JOIN habitaciones h ON h.id=r.habitacion_id LEFT JOIN unidades u ON u.id=r.unidad_id
        WHERE 1=1";
$args = [];
if (in_array($estado, ['pendiente','confirmada','pagada','cancelada'], true)) { $sql .= ' AND r.estado = ?'; $args[] = $estado; }
if ($q !== '') { $sql .= ' AND (r.huesped_nombre LIKE ? OR r.codigo LIKE ? OR r.telefono LIKE ?)'; $args[]="%$q%"; $args[]="%$q%"; $args[]="%$q%"; }
$sql .= ' ORDER BY r.check_in DESC, r.id DESC';
$st = db()->prepare($sql);
$st->execute($args);
$reservas = $st->fetchAll();

$filtros = ['' => 'Todas', 'pendiente' => 'Pendientes', 'confirmada' => 'Confirmadas', 'pagada' => 'Pagadas', 'cancelada' => 'Canceladas'];
$selfUrl = base_url('admin/reservas.php');

admin_header('Reservas', 'reservas.php');
?>

<h1 class="font-display text-3xl text-navy mb-6">Reservas</h1>

<?php if ($detalle): $extras = json_col($detalle['extras']); ?>
<!-- Panel de detalle -->
<div class="card-soft p-6 mb-6 border-l-4 border-l-mar">
  <div class="flex items-start justify-between flex-wrap gap-3">
    <div>
      <p class="eyebrow">Reserva <?= e($detalle['codigo']) ?></p>
      <h2 class="font-display text-2xl text-navy"><?= e($detalle['huesped_nombre']) ?></h2>
      <p class="text-sm text-mute"><?= e($detalle['habitacion_nombre']) ?> · Hab. <?= e($detalle['unidad_numero']) ?> · Piso <?= e((string)$detalle['unidad_piso']) ?></p>
    </div>
    <?= estado_badge($detalle['estado']) ?>
  </div>

  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-3 text-sm mt-5">
    <div><p class="text-xs text-mute">Entrada</p><p class="text-navy font-medium"><?= fecha_humana($detalle['check_in']) ?></p></div>
    <div><p class="text-xs text-mute">Salida</p><p class="text-navy font-medium"><?= fecha_humana($detalle['check_out']) ?></p></div>
    <div><p class="text-xs text-mute">Noches / huéspedes</p><p class="text-navy"><?= (int)$detalle['noches'] ?> · <?= (int)$detalle['adultos'] ?>ad <?= (int)$detalle['ninos'] ?>niñ</p></div>
    <div><p class="text-xs text-mute">Total</p><p class="text-mar font-semibold tabular"><?= precio_cop($detalle['total']) ?></p></div>
    <div><p class="text-xs text-mute">Teléfono</p><p class="text-navy"><?= e($detalle['telefono'] ?: '—') ?></p></div>
    <div><p class="text-xs text-mute">Correo</p><p class="text-navy truncate"><?= e($detalle['email'] ?: '—') ?></p></div>
    <div><p class="text-xs text-mute">Documento</p><p class="text-navy"><?= e($detalle['documento'] ?: '—') ?></p></div>
    <div><p class="text-xs text-mute">Anticipo (<?= DEPOSIT_PCT ?>%)</p><p class="text-navy tabular"><?= precio_cop($detalle['anticipo']) ?></p></div>
  </div>

  <?php if ($extras): ?>
    <div class="mt-4 pt-4 border-t border-black/5">
      <p class="text-xs text-mute mb-1">Extras</p>
      <div class="flex flex-wrap gap-2">
        <?php foreach ($extras as $x): ?><span class="chip"><?= e($x['nombre']) ?> · <?= precio_cop($x['costo']) ?></span><?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
  <?php if ($detalle['solicitudes']): ?>
    <div class="mt-4 pt-4 border-t border-black/5"><p class="text-xs text-mute mb-1">Solicitudes</p><p class="text-sm text-navy"><?= e($detalle['solicitudes']) ?></p></div>
  <?php endif; ?>

  <!-- Acciones de estado -->
  <div class="mt-5 pt-4 border-t border-black/5 flex flex-wrap gap-2">
    <?php
    $acciones = ['confirmada' => 'Confirmar', 'pagada' => 'Marcar pagada', 'pendiente' => 'Pendiente', 'cancelada' => 'Cancelar'];
    foreach ($acciones as $val => $lbl):
        if ($detalle['estado'] === $val) continue;
        $danger = $val === 'cancelada';
    ?>
      <form method="post" onsubmit="return <?= $danger ? "confirm('¿Cancelar esta reserva? La habitación quedará disponible.')" : 'true' ?>">
        <input type="hidden" name="id" value="<?= (int)$detalle['id'] ?>">
        <input type="hidden" name="estado" value="<?= $val ?>">
        <input type="hidden" name="back" value="<?= e($selfUrl . '?codigo=' . urlencode($detalle['codigo'])) ?>">
        <button class="btn !py-2 !px-4 text-xs <?= $danger ? 'bg-red-500 text-white hover:bg-red-600' : 'btn-ghost' ?>"><?= e($lbl) ?></button>
      </form>
    <?php endforeach; ?>
    <a href="<?= $selfUrl ?>" class="btn btn-ghost !py-2 !px-4 text-xs ml-auto">Cerrar detalle</a>
  </div>
</div>
<?php endif; ?>

<!-- Filtros -->
<div class="flex flex-wrap items-center gap-3 mb-4">
  <div class="flex flex-wrap gap-1">
    <?php foreach ($filtros as $k => $lbl): ?>
      <a href="<?= $selfUrl . ($k ? '?estado=' . $k : '') ?>" class="px-3 py-2 rounded-lg text-sm <?= $estado === $k ? 'bg-mar text-white' : 'bg-white text-navy/70 hover:text-mar' ?>"><?= e($lbl) ?></a>
    <?php endforeach; ?>
  </div>
  <form method="get" class="ml-auto flex gap-2">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Buscar nombre, código o teléfono…" class="field !py-2 !text-sm w-64 max-w-full">
    <button class="btn btn-mar !py-2 !px-4 text-xs">Buscar</button>
  </form>
</div>

<!-- Tabla -->
<div class="card-soft overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-xs uppercase tracking-wider text-mute border-b border-black/5">
          <th class="p-4 font-semibold">Código</th>
          <th class="p-4 font-semibold">Huésped</th>
          <th class="p-4 font-semibold hidden md:table-cell">Habitación</th>
          <th class="p-4 font-semibold">Fechas</th>
          <th class="p-4 font-semibold text-right">Total</th>
          <th class="p-4 font-semibold">Estado</th>
          <th class="p-4"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$reservas): ?>
          <tr><td colspan="7" class="p-8 text-center text-mute">No hay reservas que coincidan.</td></tr>
        <?php else: foreach ($reservas as $r): ?>
          <tr class="border-b border-black/5 hover:bg-arena-2/50 transition-colors">
            <td class="p-4 font-mono text-xs text-navy"><?= e($r['codigo']) ?></td>
            <td class="p-4">
              <p class="font-semibold text-navy"><?= e($r['huesped_nombre']) ?></p>
              <p class="text-xs text-mute"><?= e($r['telefono'] ?: '') ?></p>
            </td>
            <td class="p-4 hidden md:table-cell text-mute"><?= e($r['hab']) ?> · <?= e($r['unidad']) ?></td>
            <td class="p-4 whitespace-nowrap"><?= fecha_humana($r['check_in'], false) ?> → <?= fecha_humana($r['check_out'], false) ?><span class="block text-xs text-mute"><?= (int)$r['noches'] ?> noches</span></td>
            <td class="p-4 text-right font-semibold text-mar tabular whitespace-nowrap"><?= precio_cop($r['total']) ?></td>
            <td class="p-4"><?= estado_badge($r['estado']) ?></td>
            <td class="p-4 text-right"><a href="<?= $selfUrl . '?codigo=' . urlencode($r['codigo']) ?>" class="text-mar link-under text-sm">Ver</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php admin_footer(); ?>
