<?php
require_once __DIR__ . '/auth.php';
require_admin();

$self = base_url('admin/extras.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'delete') {
        db()->prepare('DELETE FROM extras WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);
        header('Location: ' . $self . '?msg=eliminado'); exit;
    }
    if ($accion === 'toggle') {
        db()->prepare('UPDATE extras SET activo = 1 - activo WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);
        header('Location: ' . $self); exit;
    }
    if ($accion === 'save') {
        $id     = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $desc   = trim($_POST['descripcion'] ?? '');
        $precio = (int)($_POST['precio'] ?? 0);
        $tipo   = in_array($_POST['tipo'] ?? '', ['por_noche','por_persona','por_estancia'], true) ? $_POST['tipo'] : 'por_estancia';
        $icono  = in_array($_POST['icono'] ?? '', ['sun','car','boat','heart','clock','star'], true) ? $_POST['icono'] : 'star';
        $activo = isset($_POST['activo']) ? 1 : 0;
        if ($nombre !== '' && $precio >= 0) {
            if ($id > 0) db()->prepare('UPDATE extras SET nombre=?, descripcion=?, precio=?, tipo=?, icono=?, activo=? WHERE id=?')->execute([$nombre,$desc,$precio,$tipo,$icono,$activo,$id]);
            else db()->prepare('INSERT INTO extras (nombre, descripcion, precio, tipo, icono, activo) VALUES (?,?,?,?,?,?)')->execute([$nombre,$desc,$precio,$tipo,$icono,$activo]);
            header('Location: ' . $self . '?msg=guardado'); exit;
        }
        header('Location: ' . $self . '?msg=faltan_datos'); exit;
    }
}

$edit = null;
if (!empty($_GET['edit'])) { $st = db()->prepare('SELECT * FROM extras WHERE id=?'); $st->execute([(int)$_GET['edit']]); $edit = $st->fetch() ?: null; }
$lista = get_extras(false);

$tipoLabels = ['por_noche'=>'Por noche','por_persona'=>'Por persona','por_estancia'=>'Por estancia'];
$msgs = ['guardado'=>'Servicio guardado.','eliminado'=>'Servicio eliminado.','faltan_datos'=>'Faltan datos obligatorios.'];
$msg = $msgs[$_GET['msg'] ?? ''] ?? '';

admin_header('Extras', 'extras.php');
?>

<h1 class="font-display text-3xl text-navy mb-1">Servicios adicionales (extras)</h1>
<p class="text-mute text-sm mb-6">Estos se ofrecen como complementos en el paso 3 del flujo de reserva.</p>

<?php if ($msg): ?><div class="mb-5 p-3 rounded-xl bg-espuma text-mar text-sm"><?= e($msg) ?></div><?php endif; ?>

<div class="grid lg:grid-cols-[1fr_320px] gap-6 items-start">
  <div class="card-soft overflow-hidden order-2 lg:order-1">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-xs uppercase tracking-wider text-mute border-b border-black/5">
        <th class="p-4">Servicio</th><th class="p-4">Precio</th><th class="p-4">Tipo</th><th class="p-4">Estado</th><th class="p-4"></th>
      </tr></thead>
      <tbody>
        <?php foreach ($lista as $x): ?>
          <tr class="border-b border-black/5 hover:bg-arena-2/50">
            <td class="p-4"><p class="font-semibold text-navy"><?= e($x['nombre']) ?></p><p class="text-xs text-mute"><?= e($x['descripcion']) ?></p></td>
            <td class="p-4 tabular text-mar font-semibold whitespace-nowrap"><?= precio_cop($x['precio']) ?></td>
            <td class="p-4 text-mute whitespace-nowrap"><?= e($tipoLabels[$x['tipo']] ?? $x['tipo']) ?></td>
            <td class="p-4"><form method="post" class="inline"><input type="hidden" name="accion" value="toggle"><input type="hidden" name="id" value="<?= (int)$x['id'] ?>"><button class="state <?= $x['activo'] ? 'state-confirmada' : 'state-cancelada' ?>"><?= $x['activo'] ? 'Activo' : 'Inactivo' ?></button></form></td>
            <td class="p-4 text-right whitespace-nowrap">
              <a href="?edit=<?= (int)$x['id'] ?>" class="text-mar link-under">Editar</a>
              <form method="post" class="inline ml-3" onsubmit="return confirm('¿Eliminar servicio?')"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id" value="<?= (int)$x['id'] ?>"><button class="text-red-500 hover:underline">Eliminar</button></form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card-soft p-5 order-1 lg:order-2 lg:sticky lg:top-24">
    <h2 class="font-display text-xl text-navy mb-4"><?= $edit ? 'Editar servicio' : 'Nuevo servicio' ?></h2>
    <form method="post" class="space-y-3">
      <input type="hidden" name="accion" value="save">
      <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : '' ?>">
      <div><label class="lbl" for="x-nombre">Nombre *</label><input id="x-nombre" name="nombre" class="field" value="<?= e($edit['nombre'] ?? '') ?>" required></div>
      <div><label class="lbl" for="x-desc">Descripción</label><textarea id="x-desc" name="descripcion" rows="2" class="field"><?= e($edit['descripcion'] ?? '') ?></textarea></div>
      <div><label class="lbl" for="x-precio">Precio *</label><input id="x-precio" name="precio" type="number" min="0" class="field" value="<?= e((string)($edit['precio'] ?? '')) ?>" required></div>
      <div><label class="lbl" for="x-tipo">Cobro</label>
        <select id="x-tipo" name="tipo" class="field">
          <?php foreach ($tipoLabels as $k=>$v): ?><option value="<?= $k ?>" <?= ($edit['tipo'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
        </select>
      </div>
      <div><label class="lbl" for="x-icono">Icono</label>
        <select id="x-icono" name="icono" class="field">
          <?php foreach (['sun'=>'Sol / desayuno','car'=>'Auto / traslado','boat'=>'Lancha / tour','heart'=>'Corazón / romántico','clock'=>'Reloj / horario','star'=>'Estrella / general'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= ($edit['icono'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="activo" <?= !isset($edit['activo']) || $edit['activo'] ? 'checked' : '' ?>> Activo</label>
      <div class="flex gap-2 pt-2">
        <button class="btn btn-mar flex-1"><?= $edit ? 'Guardar' : 'Crear' ?></button>
        <?php if ($edit): ?><a href="<?= $self ?>" class="btn btn-ghost">Cancelar</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<?php admin_footer(); ?>
