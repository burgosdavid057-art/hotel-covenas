<?php
/**
 * Crea el esquema y siembra datos demo del Hotel en Coveñas.
 * Ejecutar:  C:\xampp\php\php.exe database/seed.php
 */
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();

echo "Creando esquema...\n";

$pdo->exec('DROP TABLE IF EXISTS reservas');
$pdo->exec('DROP TABLE IF EXISTS unidades');
$pdo->exec('DROP TABLE IF EXISTS habitaciones');
$pdo->exec('DROP TABLE IF EXISTS extras');

$pdo->exec(<<<SQL
CREATE TABLE habitaciones (
    id                 INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre             TEXT    NOT NULL,
    slug               TEXT    NOT NULL UNIQUE,
    descripcion        TEXT    NOT NULL DEFAULT '',
    capacidad_adultos  INTEGER NOT NULL DEFAULT 2,
    capacidad_ninos    INTEGER NOT NULL DEFAULT 0,
    camas              TEXT    NOT NULL DEFAULT '',
    precio_noche       INTEGER NOT NULL,          -- COP, sin decimales
    precio_oferta      INTEGER,                   -- NULL si no hay oferta
    m2                 INTEGER,
    vista              TEXT    NOT NULL DEFAULT 'jardin',  -- mar | piscina | jardin
    imagen             TEXT,                      -- nombre de archivo (assets/img/habitaciones)
    galeria            TEXT    NOT NULL DEFAULT '[]',  -- JSON
    amenidades         TEXT    NOT NULL DEFAULT '[]',  -- JSON
    destacado          INTEGER NOT NULL DEFAULT 0,
    activo             INTEGER NOT NULL DEFAULT 1,
    created_at         TEXT    NOT NULL DEFAULT (datetime('now'))
)
SQL);

$pdo->exec(<<<SQL
CREATE TABLE unidades (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    habitacion_id INTEGER NOT NULL,
    numero        TEXT    NOT NULL,
    piso          INTEGER NOT NULL DEFAULT 1,
    orden         INTEGER NOT NULL DEFAULT 0,
    activo        INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE CASCADE
)
SQL);

$pdo->exec(<<<SQL
CREATE TABLE reservas (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo         TEXT    NOT NULL UNIQUE,
    unidad_id      INTEGER NOT NULL,
    habitacion_id  INTEGER NOT NULL,
    huesped_nombre TEXT    NOT NULL,
    documento      TEXT,
    email          TEXT,
    telefono       TEXT,
    check_in       TEXT    NOT NULL,   -- Y-m-d
    check_out      TEXT    NOT NULL,   -- Y-m-d
    adultos        INTEGER NOT NULL DEFAULT 1,
    ninos          INTEGER NOT NULL DEFAULT 0,
    noches         INTEGER NOT NULL,
    precio_noche   INTEGER NOT NULL,   -- bloqueado al reservar
    extras         TEXT    NOT NULL DEFAULT '[]',  -- JSON snapshot
    subtotal       INTEGER NOT NULL,
    total          INTEGER NOT NULL,
    anticipo       INTEGER NOT NULL DEFAULT 0,
    estado         TEXT    NOT NULL DEFAULT 'pendiente', -- pendiente|confirmada|pagada|cancelada
    metodo_pago    TEXT    NOT NULL DEFAULT 'online',
    solicitudes    TEXT,
    created_at     TEXT    NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (unidad_id)     REFERENCES unidades(id),
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id)
)
SQL);

$pdo->exec(<<<SQL
CREATE TABLE extras (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre      TEXT    NOT NULL,
    descripcion TEXT    NOT NULL DEFAULT '',
    precio      INTEGER NOT NULL,
    tipo        TEXT    NOT NULL DEFAULT 'por_estancia', -- por_noche|por_persona|por_estancia
    icono       TEXT    NOT NULL DEFAULT 'star',
    activo      INTEGER NOT NULL DEFAULT 1
)
SQL);

echo "Sembrando habitaciones...\n";

// nombre, desc, capA, capN, camas, precio, oferta, m2, vista, destacado, amenidades, [unidades: numero=>piso], imagen, galeria[]
$tipos = [
    [
        'Habitación Estándar Vista Jardín',
        'Acogedora habitación con aire acondicionado y balcón hacia los jardines tropicales del hotel. Ideal para parejas o viajeros que buscan descanso a pasos de la playa.',
        2, 1, '1 cama doble', 220000, null, 22, 'jardin', 0,
        ['Aire acondicionado','WiFi gratis','TV Smart 43"','Baño privado','Caja fuerte','Frigobar'],
        ['101'=>1,'102'=>1,'103'=>1,'104'=>1],
        'room-3.jpg', ['room-3.jpg','gal-3.jpg','hallway.jpg'],
    ],
    [
        'Habitación Superior Vista Mar',
        'Despierta con el sonido de las olas. Habitación superior con balcón frente al Golfo de Morrosquillo, cama King y amenidades premium.',
        2, 2, '1 cama King', 340000, 290000, 30, 'mar', 1,
        ['Balcón vista al mar','Aire acondicionado','WiFi gratis','TV Smart 50"','Cafetera','Frigobar','Caja fuerte'],
        ['201'=>2,'202'=>2,'203'=>2,'301'=>3,'302'=>3],
        'room-1.jpg', ['room-1.jpg','view-1.jpg','beach.jpg'],
    ],
    [
        'Suite Familiar',
        'Espacio amplio para toda la familia, con sala de estar y vista a la piscina. Capacidad para hasta 4 adultos y 2 niños.',
        4, 2, '1 cama King + 2 sencillas', 480000, null, 45, 'piscina', 1,
        ['Sala de estar','2 ambientes','Aire acondicionado','WiFi gratis','2 TV Smart','Cafetera','Frigobar','Bañera'],
        ['204'=>2,'205'=>2,'304'=>3],
        'room-2.jpg', ['room-2.jpg','pool-1.jpg','gal-1.jpg'],
    ],
    [
        'Suite Presidencial Frente al Mar',
        'Nuestra experiencia más exclusiva: suite de lujo con vista panorámica y acceso directo a la mejor zona de playa del hotel.',
        2, 2, '1 cama King', 850000, null, 70, 'mar', 1,
        ['Vista panorámica al mar','Sala de estar','Aire acondicionado','WiFi gratis','TV Smart 65"','Cafetera','Minibar premium','Amenities de autor'],
        ['401'=>4,'402'=>4],
        'room-4.jpg', ['room-4.jpg','pool-2.jpg','view-1.jpg'],
    ],
];

$insH = $pdo->prepare(
    'INSERT INTO habitaciones (nombre, slug, descripcion, capacidad_adultos, capacidad_ninos, camas, precio_noche, precio_oferta, m2, vista, imagen, galeria, amenidades, destacado, activo)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)'
);
$insU = $pdo->prepare('INSERT INTO unidades (habitacion_id, numero, piso, orden) VALUES (?,?,?,?)');

$unidadByNum = []; // numero => [id, habitacion_id]
foreach ($tipos as $t) {
    [$nombre,$desc,$capA,$capN,$camas,$precio,$oferta,$m2,$vista,$dest,$amen,$units,$imagen,$galeria] = $t;
    $insH->execute([$nombre, slugify($nombre), $desc, $capA, $capN, $camas, $precio, $oferta, $m2, $vista,
        $imagen, json_encode($galeria, JSON_UNESCAPED_UNICODE), json_encode($amen, JSON_UNESCAPED_UNICODE), $dest]);
    $hid = (int)$pdo->lastInsertId();
    $orden = 0;
    foreach ($units as $numero => $piso) {
        $insU->execute([$hid, (string)$numero, $piso, $orden++]);
        $unidadByNum[(string)$numero] = ['id' => (int)$pdo->lastInsertId(), 'hid' => $hid, 'precio' => $oferta ?: $precio];
    }
}

echo "Sembrando extras...\n";
$extras = [
    ['Desayuno buffet',          'Buffet caribeño con frutas, jugos naturales y estación de huevos.', 35000,  'por_noche',    'sun'],
    ['Traslado aeropuerto',      'Recogida en aeropuerto de Montería o Tolú (ida y vuelta).',          90000,  'por_estancia', 'car'],
    ['Tour Islas de San Bernardo','Paseo en lancha por el archipiélago con almuerzo incluido.',        120000, 'por_persona',  'boat'],
    ['Decoración romántica',     'Pétalos, velas y botella de vino espumoso a la llegada.',            150000, 'por_estancia', 'heart'],
    ['Late check-out (4:00 pm)', 'Disfruta tu última mañana sin prisa.',                               60000,  'por_estancia', 'clock'],
];
$insE = $pdo->prepare('INSERT INTO extras (nombre, descripcion, precio, tipo, icono) VALUES (?,?,?,?,?)');
foreach ($extras as $x) $insE->execute($x);

echo "Sembrando reservas demo...\n";
$insR = $pdo->prepare(
    'INSERT INTO reservas (codigo, unidad_id, habitacion_id, huesped_nombre, documento, email, telefono, check_in, check_out, adultos, ninos, noches, precio_noche, extras, subtotal, total, anticipo, estado, metodo_pago, solicitudes)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
);
$demo = [
    // numero unidad, dias_inicio, dias_fin, nombre, adultos, ninos, estado
    ['101', 3, 5,  'María Fernanda Ruiz', 2, 0, 'confirmada'],
    ['201', 5, 8,  'Carlos Andrés Gómez', 2, 1, 'pagada'],
    ['401', 10, 12,'Laura Restrepo',      2, 0, 'confirmada'],
];
foreach ($demo as $d) {
    [$num,$di,$df,$nombre,$ad,$ni,$estado] = $d;
    if (!isset($unidadByNum[$num])) continue;
    $u   = $unidadByNum[$num];
    $in  = date('Y-m-d', strtotime("+$di days"));
    $out = date('Y-m-d', strtotime("+$df days"));
    $noches = noches_entre($in, $out);
    $sub = $u['precio'] * $noches;
    $anticipo = (int)round($sub * DEPOSIT_PCT / 100);
    $insR->execute([
        generar_codigo_reserva(), $u['id'], $u['hid'], $nombre, '1.000.000.000',
        'huesped@example.com', '+57 300 000 0000', $in, $out, $ad, $ni, $noches,
        $u['precio'], json_encode([], JSON_UNESCAPED_UNICODE), $sub, $sub, $anticipo, $estado, 'online', null,
    ]);
}

// Resumen
$nh = $pdo->query('SELECT COUNT(*) c FROM habitaciones')->fetch()['c'];
$nu = $pdo->query('SELECT COUNT(*) c FROM unidades')->fetch()['c'];
$ne = $pdo->query('SELECT COUNT(*) c FROM extras')->fetch()['c'];
$nr = $pdo->query('SELECT COUNT(*) c FROM reservas')->fetch()['c'];

echo "\n✔ Base de datos lista: $nh tipos de habitación · $nu unidades · $ne extras · $nr reservas demo\n";
echo "  Archivo: " . DB_PATH . "\n";
echo "  Admin: usuario 'admin' · contraseña 'hotel2025'\n";
