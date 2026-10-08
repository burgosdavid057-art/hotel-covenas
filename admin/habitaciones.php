<?php
require_once __DIR__ . '/auth.php';
require_admin();

$self = base_url('admin/habitaciones.php');

// ── Acciones ─────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $st = db()->prepare("SELECT COUNT(*) c FROM reservas r JOIN unidades u ON u.id=r.unidad_id WHERE u.habitacion_id=?");
        $st->execute([$id]);
        if ((int)$st->fetch()['c'] > 0) {
            header('Location: ' . $self . '?msg=tiene_reservas'); exit;
        }
        db()->prepare('DELETE FROM habitaciones WHERE id=?')->execute([$id]);
        header('Location: ' . $self . '?msg=eliminada'); exit;
    }

    if ($accion === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare('UPDATE habitaciones SET activo = 1 - activo WHERE id=?')->execute([$id]);
        header('Location: ' . $self); exit;
    }

    if ($accion === 'save') {
        $id        = (int)($_POST['id'] ?? 0);
        $nombre    = trim($_POST['nombre'] ?? '');
        $desc      = trim($_POST['descripcion'] ?? '');
        $precio    = (int)($_POST['precio_noche'] ?? 0);
        $oferta    = $_POST['precio_oferta'] !== '' ? (int)$_POST['precio_oferta'] : null;
        $capA      = max(1, (int)($_POST['capacidad_adultos'] ?? 1));
        $capN      = max(0, (int)($_POST['capacidad_ninos'] ?? 0));
        $camas     = trim($_POST['camas'] ?? '');
        $m2        = (int)($_POST['m2'] ?? 0);
        $vista     = in_array($_POST['vista'] ?? '', ['mar','piscina','jardin'], true) ? $_POST['vista'] : 'jardin';
        $destacado = isset($_POST['destacado']) ? 1 : 0;
        $activo    = isset($_POST['activo']) ? 1 : 0;
        $amen      = array_values(array_filter(array_map('trim', explode("\n", $_POST['amenidades'] ?? ''))));
        $amenJson  = json_encode($amen, JSON_UNESCAPED_UNICODE);

        // imagen (opcional)
        $imagen = $_POST['imagen_actual'] ?? '';
        if (!empty($_FILES['imagen']['name']) && is_uploaded_file($_FILES['imagen']['tmp_name'])) {
            if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0775, true);
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','avif'], true)) {
                $fname = slugify($nombre ?: 'hab') . '-' . substr(bin2hex(random_bytes(3)), 0, 6) . '.' . $ext;
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], UPLOAD_DIR . '/' . $fname)) $imagen = $fname;
            }
        }

        if ($nombre !== '' && $precio > 0) {
            if ($id > 0) {
                $st = db()->prepare('UPDATE habitaciones SET nombre=?, descripcion=?, precio_noche=?, precio_oferta=?, capacidad_adultos=?, capacidad_ninos=?, camas=?, m2=?, vista=?, amenidades=?, destacado=?, activo=?, imagen=? WHERE id=?');
                $st->execute([$nombre,$desc,$precio,$oferta,$capA,$capN,$camas,$m2,$vista,$amenJson,$destacado,$activo,$imagen,$id]);
            } else {
                $slug = slugify($nombre) . '-' . substr(bin2hex(random_bytes(2)), 0, 3);
                $st = db()->prepare('INSERT INTO habitaciones (nombre, slug, descripcion, precio_noche, precio_oferta, capacidad_adultos, capacidad_ninos, camas, m2, vista, amenidades, destacado, activo, imagen) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $st->execute([$nombre,$slug,$desc,$precio,$oferta,$capA,$capN,$camas,$m2,$vista,$amenJson,$destacado,$activo,$imagen]);
            }
            header('Location: ' . $self . '?msg=guardada'); exit;
        }
        header('Location: ' . $self . '?msg=faltan_datos'); exit;
    }
}

$edit = null;
if (!empty($_GET['edit'])) $edit = get_habitacion((int)$_GET['edit']);
$lista = db()->query('SELECT * FROM habitaciones ORDER BY id DESC')->fetchAll();

$msgs = ['guardada'=>'Habitación guardada.','eliminada'=>'Habitación eliminada.','tiene_reservas'=>'No se puede eliminar: tiene reservas asociadas. Desactívala en su lugar.','faltan_datos'=>'Faltan datos obligatorios (nombre y precio).'];
$msg = $msgs[$_GET['msg'] ?? ''] ?? '';

admin_header('Habitaciones', 'habitaciones.php');
?>

<h1 class="font-display text-3xl text-navy mb-6">Habitaciones (tipos)</h1>

<?php if ($msg): ?><div class="mb-5 p-3 rounded-xl bg-espuma text-mar text-sm"><?= e($msg) ?></div><?php endif; ?>

<div class="grid lg:grid-cols-[1fr_360px] gap-6 items-start">
  <!-- Lista -->
  <div class="card-soft overflow-hidden order-2 lg:order-1">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-xs uppercase tracking-wider text-mute border-b border-black/5">
          <th class="p-4">Habitación</th><th class="p-4">Precio</th><th class="p-4">Cap.</th><th class="p-4">Estado</th><th class="p-4"></th>
        </tr></thead>
        <tbody>
          <?php foreach ($lista as $h): ?>
            <tr class="border-b border-black/5 hover:bg-arena-2/50">
              <td class="p-4">
                <p class="font-semibold text-navy"><?= e($h['nombre']) ?></p>
                <p class="text-xs text-mute"><?= e(vista_label($h['vista'])) ?> · <?= (int)$h['m2'] ?> m²<?= $h['destacado'] ? ' · ⭐ destacada' : '' ?></p>
              </td>
              <td class="p-4 tabular text-mar font-semibold whitespace-nowrap"><?= precio_cop(precio_final($h)) ?></td>
              <td class="p-4 text-mute whitespace-nowrap"><?= (int)$h['capacidad_adultos'] ?>+<?= (int)$h['capacidad_ninos'] ?></td>
              <td class="p-4">
                <form method="post" class="inline"><input type="hidden" name="accion" value="toggle"><input type="hidden" name="id" value="<?= (int)$h['id'] ?>">
                  <button class="state <?= $h['activo'] ? 'state-confirmada' : 'state-cancelada' ?>"><?= $h['activo'] ? 'Activa' : 'Inactiva' ?></button>
                </form>
              </td>
              <td class="p-4 text-right whitespace-nowrap">
                <a href="?edit=<?= (int)$h['id'] ?>" class="text-mar link-under">Editar</a>
                <form method="post" class="inline ml-3" onsubmit="return confirm('¿Eliminar esta habitación?')"><input type="hidden" name="accion" value="delete"><input type="hidden" name="id" value="<?= (int)$h['id'] ?>"><button class="text-red-500 hover:underline">Eliminar</button></form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Formulario -->
  <div class="card-soft p-5 order-1 lg:order-2 lg:sticky lg:top-24">
    <h2 class="font-display text-xl text-navy mb-4"><?= $edit ? 'Editar habitación' : 'Nueva habitación' ?></h2>
    <form method="post" enctype="multipart/form-data" class="space-y-3">
      <input type="hidden" name="accion" value="save">
      <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : '' ?>">
      <input type="hidden" name="imagen_actual" value="<?= e($edit['imagen'] ?? '') ?>">
      <div><label class="lbl" for="f-nombre">Nombre *</label><input id="f-nombre" name="nombre" class="field" value="<?= e($edit['nombre'] ?? '') ?>" required></div>
      <div><label class="lbl" for="f-desc">Descripción</label><textarea id="f-desc" name="descripcion" rows="3" class="field"><?= e($edit['descripcion'] ?? '') ?></textarea></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl" for="f-precio">Precio/noche *</label><input id="f-precio" name="precio_noche" type="number" min="0" class="field" value="<?= e((string)($edit['precio_noche'] ?? '')) ?>" required></div>
        <div><label class="lbl" for="f-oferta">Precio oferta</label><input id="f-oferta" name="precio_oferta" type="number" min="0" class="field" value="<?= e((string)($edit['precio_oferta'] ?? '')) ?>"></div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl" for="f-ca">Adultos máx.</label><input id="f-ca" name="capacidad_adultos" type="number" min="1" class="field" value="<?= e((string)($edit['capacidad_adultos'] ?? 2)) ?>"></div>
        <div><label class="lbl" for="f-cn">Niños máx.</label><input id="f-cn" name="capacidad_ninos" type="number" min="0" class="field" value="<?= e((string)($edit['capacidad_ninos'] ?? 0)) ?>"></div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl" for="f-m2">m²</label><input id="f-m2" name="m2" type="number" min="0" class="field" value="<?= e((string)($edit['m2'] ?? '')) ?>"></div>
        <div><label class="lbl" for="f-vista">Vista</label>
          <select id="f-vista" name="vista" class="field">
            <?php foreach (['mar'=>'Mar','piscina'=>'Piscina','jardin'=>'Jardín'] as $k=>$v): ?>
              <option value="<?= $k ?>" <?= ($edit['vista'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div><label class="lbl" for="f-camas">Camas</label><input id="f-camas" name="camas" class="field" value="<?= e($edit['camas'] ?? '') ?>" placeholder="1 cama King"></div>
      <div><label class="lbl" for="f-amen">Amenidades (una por línea)</label><textarea id="f-amen" name="amenidades" rows="4" class="field"><?= e(implode("\n", json_col($edit['amenidades'] ?? '[]'))) ?></textarea></div>
      <div><label class="lbl" for="f-img">Imagen</label><input id="f-img" name="imagen" type="file" accept="image/*" class="field !py-2 text-xs"><?php if (!empty($edit['imagen'])): ?><p class="text-xs text-mute mt-1">Actual: <?= e($edit['imagen']) ?></p><?php endif; ?></div>
      <div class="flex gap-4 pt-1">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="destacado" <?= !empty($edit['destacado']) ? 'checked' : '' ?>> Destacada</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="activo" <?= !isset($edit['activo']) || $edit['activo'] ? 'checked' : '' ?>> Activa</label>
      </div>
      <div class="flex gap-2 pt-2">
        <button class="btn btn-mar flex-1"><?= $edit ? 'Guardar cambios' : 'Crear habitación' ?></button>
        <?php if ($edit): ?><a href="<?= $self ?>" class="btn btn-ghost">Cancelar</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<?php admin_footer(); ?>
