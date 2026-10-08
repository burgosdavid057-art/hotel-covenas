<?php
require_once __DIR__ . '/../config/db.php';

/** Escape para HTML */
function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

/** Formato de precio en COP: 250000 -> $250.000 */
function precio_cop($n): string {
    return '$' . number_format((int)$n, 0, ',', '.');
}

/** Slug (transliteración manual; iconv es poco fiable en Windows) */
function slugify(string $t): string {
    $map = ['á'=>'a','à'=>'a','ä'=>'a','â'=>'a','é'=>'e','è'=>'e','ë'=>'e','ê'=>'e',
            'í'=>'i','ì'=>'i','ï'=>'i','î'=>'i','ó'=>'o','ò'=>'o','ö'=>'o','ô'=>'o',
            'ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u','ñ'=>'n','ç'=>'c',
            'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ñ'=>'n'];
    $t = strtr($t, $map);
    $t = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $t));
    return trim($t, '-');
}

/** Decodifica una columna JSON a array (tolerante) */
function json_col($val): array {
    if (is_array($val)) return $val;
    if (!is_string($val) || $val === '') return [];
    $d = json_decode($val, true);
    return is_array($d) ? $d : [];
}

/** Etiqueta legible para la vista de la habitación */
function vista_label(string $v): string {
    return match ($v) {
        'mar'     => 'Vista al mar',
        'piscina' => 'Vista a la piscina',
        'jardin'  => 'Vista al jardín',
        default   => ucfirst($v),
    };
}

// ── Precios / ofertas ─────────────────────────────────────────────────────────
function precio_final(array $h): int {
    return !empty($h['precio_oferta']) ? (int)$h['precio_oferta'] : (int)$h['precio_noche'];
}
function tiene_oferta(array $h): bool {
    return !empty($h['precio_oferta']) && (int)$h['precio_oferta'] < (int)$h['precio_noche'];
}
function descuento_pct(array $h): int {
    if (!tiene_oferta($h)) return 0;
    return (int)round((1 - $h['precio_oferta'] / $h['precio_noche']) * 100);
}

/**
 * URL de imagen de la habitación. Si no hay archivo subido, genera un
 * placeholder SVG (degradado de mar) embebido como data-URI.
 */
function room_image(array $h): string {
    return foto_url($h['imagen'] ?? '', $h['nombre'] ?? SITE_NAME, isset($h['vista']) ? vista_label($h['vista']) : '');
}

/**
 * Resuelve una imagen por nombre de archivo: primero en uploads del admin,
 * luego en las fotos reales del hotel (assets/img/fotos), si no, placeholder.
 */
function foto_url(string $file, string $alt = '', string $sub = ''): string {
    if ($file !== '') {
        if (is_file(UPLOAD_DIR . '/' . $file))           return base_url(UPLOAD_URL . '/' . $file);
        if (is_file(__DIR__ . '/../assets/img/fotos/' . $file)) return base_url('assets/img/fotos/' . $file);
    }
    return placeholder_svg($alt ?: SITE_NAME, $sub);
}

/** Placeholder SVG (mar Caribe) como data-URI */
function placeholder_svg(string $nombre, string $sub = ''): string {
    $ini = strtoupper(mb_substr(trim($nombre), 0, 1));
    $sub = e($sub);
    $nom = e(mb_strtoupper($nombre));
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1000" height="700" viewBox="0 0 1000 700">
  <defs>
    <linearGradient id="sky" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#dff3f2"/>
      <stop offset="55%" stop-color="#a9e0df"/>
      <stop offset="100%" stop-color="#14b8a6"/>
    </linearGradient>
    <linearGradient id="sea" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#0e7c86"/>
      <stop offset="100%" stop-color="#0a5d65"/>
    </linearGradient>
  </defs>
  <rect width="1000" height="700" fill="url(#sky)"/>
  <circle cx="820" cy="150" r="70" fill="#ffd9a8" opacity="0.9"/>
  <path d="M0 470 Q250 430 500 470 T1000 470 V700 H0 Z" fill="url(#sea)"/>
  <path d="M0 520 Q250 488 500 520 T1000 520" fill="none" stroke="#bfeae6" stroke-width="3" opacity="0.6"/>
  <text x="500" y="330" font-family="'Manrope', Arial, sans-serif" font-size="200" font-weight="800"
        fill="#0c2230" text-anchor="middle" dominant-baseline="middle" opacity="0.9">$ini</text>
  <text x="500" y="615" font-family="'Manrope', Arial, sans-serif" font-size="26" font-weight="700" letter-spacing="3"
        fill="#ffffff" text-anchor="middle" opacity="0.95">$nom</text>
  <text x="500" y="652" font-family="'Manrope', Arial, sans-serif" font-size="18" letter-spacing="3"
        fill="#dff3f2" text-anchor="middle" opacity="0.9">$sub</text>
</svg>
SVG;
    return 'data:image/svg+xml;charset=utf-8,' . rawurlencode($svg);
}

// ── Consultas: habitaciones (tipos) ───────────────────────────────────────────
function get_habitaciones(array $opts = []): array {
    $sql = 'SELECT * FROM habitaciones WHERE activo = 1';
    $args = [];
    if (!empty($opts['vista'])) {
        $sql .= ' AND vista = ?';
        $args[] = $opts['vista'];
    }
    if (!empty($opts['huespedes'])) {
        $sql .= ' AND (capacidad_adultos + capacidad_ninos) >= ?';
        $args[] = (int)$opts['huespedes'];
    }
    if (!empty($opts['destacado'])) {
        $sql .= ' AND destacado = 1';
    }
    $orden = $opts['orden'] ?? 'recientes';
    $sql .= match ($orden) {
        'precio_asc'  => ' ORDER BY COALESCE(precio_oferta, precio_noche) ASC',
        'precio_desc' => ' ORDER BY COALESCE(precio_oferta, precio_noche) DESC',
        'nombre'      => ' ORDER BY nombre ASC',
        default       => ' ORDER BY destacado DESC, precio_noche ASC',
    };
    if (!empty($opts['limit'])) {
        $sql .= ' LIMIT ' . (int)$opts['limit'];
    }
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st->fetchAll();
}

function get_habitacion(int $id): ?array {
    $st = db()->prepare('SELECT * FROM habitaciones WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function get_habitacion_slug(string $slug): ?array {
    $st = db()->prepare('SELECT * FROM habitaciones WHERE slug = ? AND activo = 1');
    $st->execute([$slug]);
    return $st->fetch() ?: null;
}

function get_unidades(int $habitacionId): array {
    $st = db()->prepare('SELECT * FROM unidades WHERE habitacion_id = ? ORDER BY orden, numero');
    $st->execute([$habitacionId]);
    return $st->fetchAll();
}

function get_extras(bool $soloActivos = true): array {
    $sql = 'SELECT * FROM extras';
    if ($soloActivos) $sql .= ' WHERE activo = 1';
    $sql .= ' ORDER BY id ASC';
    return db()->query($sql)->fetchAll();
}

// ── Fechas / disponibilidad ───────────────────────────────────────────────────

/** Valida formato Y-m-d y que sea una fecha real */
function fecha_valida(?string $f): bool {
    if (!$f || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $f)) return false;
    [$y, $m, $d] = array_map('intval', explode('-', $f));
    return checkdate($m, $d, $y);
}

/** Nombre del mes en español (1-12) */
function mes_nombre(int $m, bool $corto = false): string {
    $largos = [1=>'enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $cortos = [1=>'ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    $m = max(1, min(12, $m));
    return $corto ? $cortos[$m] : $largos[$m];
}

/** Fecha legible en español: 2026-07-12 -> "12 jul 2026" */
function fecha_humana(string $ymd, bool $conAnio = true): string {
    if (!fecha_valida($ymd)) return $ymd;
    [$y, $m, $d] = array_map('intval', explode('-', $ymd));
    return $d . ' ' . mes_nombre($m, true) . ($conAnio ? ' ' . $y : '');
}

/** Día de la semana en español a partir de un Y-m-d */
function dia_semana(string $ymd): string {
    $dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    return $dias[(int)date('w', strtotime($ymd))];
}

/** Número de noches entre check-in y check-out (>=0) */
function noches_entre(string $in, string $out): int {
    $a = new DateTime($in);
    $b = new DateTime($out);
    $diff = (int)$a->diff($b)->days;
    return $b > $a ? $diff : 0;
}

/** IDs de unidades ocupadas (reserva no cancelada que solapa el rango) */
function unidades_ocupadas(string $in, string $out, int $excluirReserva = 0): array {
    $sql = "SELECT DISTINCT unidad_id FROM reservas
            WHERE estado != 'cancelada' AND check_in < ? AND check_out > ?";
    $args = [$out, $in];
    if ($excluirReserva > 0) { $sql .= ' AND id != ?'; $args[] = $excluirReserva; }
    $st = db()->prepare($sql);
    $st->execute($args);
    return array_map('intval', array_column($st->fetchAll(), 'unidad_id'));
}

/** ¿Está libre esta unidad concreta en el rango? */
function unidad_disponible(int $unidadId, string $in, string $out, int $excluirReserva = 0): bool {
    return !in_array($unidadId, unidades_ocupadas($in, $out, $excluirReserva), true);
}

/**
 * Disponibilidad por tipo de habitación para un rango y nº de huéspedes.
 * Devuelve sólo tipos con al menos una unidad libre, con su lista de unidades.
 */
function disponibilidad(string $in, string $out, int $adultos, int $ninos): array {
    $noches   = noches_entre($in, $out);
    $ocupadas = unidades_ocupadas($in, $out);
    $res = [];

    foreach (get_habitaciones() as $h) {
        if ((int)$h['capacidad_adultos'] < $adultos) continue;
        if ((int)$h['capacidad_ninos']   < $ninos)   continue;

        $unidades = [];
        $libres = 0;
        foreach (get_unidades((int)$h['id']) as $u) {
            if (!$u['activo']) continue;
            $libre = !in_array((int)$u['id'], $ocupadas, true);
            if ($libre) $libres++;
            $unidades[] = [
                'id'     => (int)$u['id'],
                'numero' => $u['numero'],
                'piso'   => (int)$u['piso'],
                'libre'  => $libre,
            ];
        }
        if ($libres === 0) continue;

        $precio = precio_final($h);
        $res[] = [
            'id'          => (int)$h['id'],
            'nombre'      => $h['nombre'],
            'slug'        => $h['slug'],
            'descripcion' => $h['descripcion'],
            'vista'       => $h['vista'],
            'vista_label' => vista_label($h['vista']),
            'camas'       => $h['camas'],
            'm2'          => (int)$h['m2'],
            'cap_adultos' => (int)$h['capacidad_adultos'],
            'cap_ninos'   => (int)$h['capacidad_ninos'],
            'amenidades'  => array_slice(json_col($h['amenidades']), 0, 6),
            'imagen'      => room_image($h),
            'precio_noche'=> $precio,
            'precio_lista'=> (int)$h['precio_noche'],
            'oferta'      => tiene_oferta($h),
            'subtotal'    => $precio * max(1, $noches),
            'disponibles' => $libres,
            'unidades'    => $unidades,
        ];
    }
    return $res;
}

// ── Reservas ──────────────────────────────────────────────────────────────────
function generar_codigo_reserva(): string {
    do {
        $code = 'HC-' . strtoupper(bin2hex(random_bytes(3))); // HC-XXXXXX
        $st = db()->prepare('SELECT 1 FROM reservas WHERE codigo = ?');
        $st->execute([$code]);
    } while ($st->fetch());
    return $code;
}

function get_reserva_codigo(string $codigo): ?array {
    $st = db()->prepare(
        'SELECT r.*, h.nombre AS habitacion_nombre, h.vista, u.numero AS unidad_numero, u.piso AS unidad_piso
         FROM reservas r
         LEFT JOIN habitaciones h ON h.id = r.habitacion_id
         LEFT JOIN unidades u ON u.id = r.unidad_id
         WHERE r.codigo = ?'
    );
    $st->execute([$codigo]);
    return $st->fetch() ?: null;
}

/** Calcula el costo de un extra según su tipo, para una estancia dada */
function costo_extra(array $extra, int $noches, int $huespedes): int {
    $p = (int)$extra['precio'];
    return match ($extra['tipo']) {
        'por_noche'   => $p * max(1, $noches),
        'por_persona' => $p * max(1, $huespedes),
        default       => $p, // por_estancia
    };
}

/** Etiqueta de estado para badges */
function estado_label(string $e): string {
    return match ($e) {
        'pendiente'  => 'Pendiente',
        'confirmada' => 'Confirmada',
        'pagada'     => 'Pagada',
        'cancelada'  => 'Cancelada',
        default      => ucfirst($e),
    };
}

/** Render de una tarjeta de habitación — estilo editorial, foto protagonista */
function room_card(array $h): void {
    $img    = room_image($h);
    $detalle= base_url('habitacion.php?slug=' . urlencode($h['slug']));
    $reserva= base_url('reservar.php?hab=' . urlencode($h['slug']));
    $final  = precio_final($h);
    $oferta = tiene_oferta($h);
    ?>
    <article class="tile reveal group aspect-[4/5]">
      <img src="<?= $img ?>" alt="<?= e($h['nombre']) ?>" loading="lazy" width="800" height="1000">
      <div class="tile__scrim"></div>
      <a href="<?= $detalle ?>" class="absolute inset-0 z-10" aria-label="Ver <?= e($h['nombre']) ?>"></a>

      <div class="absolute top-4 left-4 right-4 flex items-start justify-between z-20 pointer-events-none">
        <span class="badge badge-mar"><?= e(vista_label($h['vista'])) ?></span>
        <?php if ($oferta): ?><span class="badge badge-oferta">-<?= descuento_pct($h) ?>%</span><?php endif; ?>
      </div>

      <div class="absolute inset-x-0 bottom-0 p-5 z-20">
        <h3 class="font-display text-2xl font-bold text-white leading-tight"><?= e($h['nombre']) ?></h3>
        <p class="text-xs text-white/75 mt-1 font-medium"><?= e($h['camas']) ?> · <?= (int)$h['m2'] ?> m² · hasta <?= (int)$h['capacidad_adultos'] ?> adultos</p>
        <div class="flex items-end justify-between gap-3 mt-4">
          <div class="text-white">
            <?php if ($oferta): ?><span class="block text-xs text-white/60 line-through tabular"><?= precio_cop($h['precio_noche']) ?></span><?php endif; ?>
            <span class="font-display text-2xl font-extrabold tabular"><?= precio_cop($final) ?></span>
            <span class="text-xs text-white/70">/ noche</span>
          </div>
          <a href="<?= $reserva ?>" class="btn btn-coral !px-5 !py-2.5 text-xs relative z-20 pointer-events-auto" data-cursor>Reservar</a>
        </div>
      </div>
    </article>
    <?php
}

/** Link a WhatsApp con mensaje precargado */
function whatsapp_link(string $msg = ''): string {
    $msg = $msg !== '' ? $msg : 'Hola ' . SITE_NAME . ', quiero información sobre disponibilidad y reservas.';
    return 'https://wa.me/' . WHATSAPP_NUMBER . '?text=' . rawurlencode($msg);
}
