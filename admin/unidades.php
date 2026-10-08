<?php
require_once __DIR__ . '/auth.php';
require_admin();

$self = base_url('admin/unidades.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $st = db()->prepare('SELECT COUNT(*) c FROM reservas WHERE unidad_id=?');
        $st->execute([$id]);
        if ((int)$st->fetch()['c'] > 0) { header('Location: ' . $self . '?msg=tiene_reservas'); exit; }
        db()->prepare('DELETE FROM unidades WHERE id=?')->execute([$id]);
        header('Location: ' . $self . '?msg=eliminada'); exit;
    }
    if ($accion === 'toggle') {
        db()->prepare('UPDATE unidades SET activo = 1 - activo WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);
        header('Location: ' . $self); exit;
    }
    if ($accion === 'save') {
        $id     = (int)($_POST['id'] ?? 0);
        $habId  = (int)($_POST['habitacion_id'] ?? 0);
        $numero = trim($_POST['numero'] ?? '');
        $piso   = (int)($_POST['piso'] ?? 1);
        $orden  = (int)($_POST['orden'] ?? 0);
        $activo = isset($_POST['activo']) ? 1 : 0;
        if ($habId > 0 && $numero !== '') {
            if ($id > 0) {
                db()->prepare('UPDATE unidades SET habitacion_id=?, numero=?, piso=?, orden=?, activo=? WHERE id=?')
                    ->execute([$habId,$numero,$piso,$orden,$activo,$id]);
            } else {
                db()->prepare('INSERT INTO unidades (habitacion_id, numero, piso, orden, activo) VALUES (?,?,?,?,?)')
                    ->execute([$habId,$numero,$piso,$orden,$activo]);
            }
            header('Location: ' . $self . '?msg=guardada'); exit;
        }
        header('Location: ' . $self . '?msg=faltan_datos'); exit;
    }
}

$edit = null;
if (!empty($_GET['edit'])) {
    $st = db()->prepare('SELECT * FROM unidades WHERE id=?'); $st->execute([(int)$_GET['edit']]); $edit = $st->fetch() ?: null;
}
$habitaciones = db()->query('SELECT id, nombre FROM habitaciones ORDER BY nombre')->fetchAll();
$lista = db()->query(
    "SELECT u.*, h.nombre AS hab FROM unidades u JOIN habitaciones h ON h.id=u.habitacion_id ORDER BY h.nombre, u.numero"
)->fetchAll();

$msgs = ['guardada'=>'Unidad guardada.','eliminada'=>'Unidad eliminada.','tiene_reservas'=>'No se puede eliminar: tiene reservas. Desactívala en su lugar.','faltan_datos'=>'Selecciona una habitación e ingresa el número.'];
$msg = $msgs[$_GET['msg'] ?? ''] ?? '';

admin_header('Unidades', 'unidades.php');
?>

<h1 class="font-display text-3xl text-navy mb-1">Unidades (habitaciones físicas)</h1>
<p class="text-mute text-sm mb-6">Cada unidad es una habitación real con su número. Son las "butacas" que el huésped elige en el mapa.</p>

<?php if ($msg): ?><div class="mb-5 p-3 rounded-xl bg-espuma text-mar text-sm"><?= e($msg) ?></div><?php endif; ?>

<div class="grid lg:grid-cols-[1fr_320px] gap-6 items-start">
  <div class="card-soft overflow-hidden order-2 lg:order-1">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-xs uppercase tracking-wider text-mute border-b border-black/5">
          <th class="p-4">N°</th><th class="p-4">Tipo</th><th class="p-4">Piso</th><th class="p-4">Estado</th><th class="p-4"></th>
        </tr></thead>
        <tbody>
          <?php foreach ($lista as $u): ?>
            <tr class="border-b border-black/5 hover:bg-arena-2/50">
              <td class="p-4 font-semibold text-navy"><?= e($u['numero']) ?></td>
              <td class="p-4 text-mute"><?= e($u['hab']) ?></td>
              <td class="p-4 text-mute"><?= (int)$u['piso'] ?></td>
              <td class="p-4"><form method="post" class="inline"><input type="hidden" name="accion" value="toggle"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="state <?= $u['activo'] ? 'state-confirmada' : 'state-cancelada' ?>"><?= $u['activo'] ? 'Activa' : 'Inactiva' ?></button></form></td>
              <td class="p-4 text-right whitespace-nowrap">
                <a href="?edit=<?= (int)$u['id'] ?>" class="text-mar link-under">Editar</a>
                <form method="post" class="inline ml-3" onsubmit="return confirm('¿Eliminar unidad?')"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="text-red-500 hover:underline">Eliminar</button></form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card-soft p-5 order-1 lg:order-2 lg:sticky lg:top-24">
    <h2 class="font-display text-xl text-navy mb-4"><?= $edit ? 'Editar unidad' : 'Nueva unidad' ?></h2>
    <form method="post" class="space-y-3">
      <input type="hidden" name="accion" value="save">
      <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : '' ?>">
      <div><label class="lbl" for="u-hab">Tipo de habitación *</label>
        <select id="u-hab" name="habitacion_id" class="field" required>
          <option value="">Selecciona…</option>
          <?php foreach ($habitaciones as $h): ?><option value="<?= (int)$h['id'] ?>" <?= ($edit['habitacion_id'] ?? 0) == $h['id'] ? 'selected' : '' ?>><?= e($h['nombre']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl" for="u-num">Número *</label><input id="u-num" name="numero" class="field" value="<?= e($edit['numero'] ?? '') ?>" required></div>
        <div><label class="lbl" for="u-piso">Piso</label><input id="u-piso" name="piso" type="number" min="0" class="field" value="<?= e((string)($edit['piso'] ?? 1)) ?>"></div>
      </div>
      <div><label class="lbl" for="u-orden">Orden (en el mapa)</label><input id="u-orden" name="orden" type="number" min="0" class="field" value="<?= e((string)($edit['orden'] ?? 0)) ?>"></div>
      <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="activo" <?= !isset($edit['activo']) || $edit['activo'] ? 'checked' : '' ?>> Activa</label>
      <div class="flex gap-2 pt-2">
        <button class="btn btn-mar flex-1"><?= $edit ? 'Guardar' : 'Crear' ?></button>
        <?php if ($edit): ?><a href="<?= $self ?>" class="btn btn-ghost">Cancelar</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<?php admin_footer(); ?>
