<?php
/**
 * Endpoint JSON de disponibilidad para el wizard de reserva.
 * GET ?check_in=Y-m-d&check_out=Y-m-d&adultos=2&ninos=0
 */
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$in  = $_GET['check_in']  ?? '';
$out = $_GET['check_out'] ?? '';
$adultos = max(1, (int)($_GET['adultos'] ?? 1));
$ninos   = max(0, (int)($_GET['ninos'] ?? 0));

if (!fecha_valida($in) || !fecha_valida($out)) {
    http_response_code(400);
    echo json_encode(['error' => 'Fechas no válidas.']);
    exit;
}
if ($in < date('Y-m-d')) {
    http_response_code(400);
    echo json_encode(['error' => 'La fecha de entrada no puede ser en el pasado.']);
    exit;
}
$noches = noches_entre($in, $out);
if ($noches <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'La salida debe ser posterior a la entrada.']);
    exit;
}

echo json_encode([
    'check_in'  => $in,
    'check_out' => $out,
    'noches'    => $noches,
    'adultos'   => $adultos,
    'ninos'     => $ninos,
    'tipos'     => disponibilidad($in, $out, $adultos, $ninos),
], JSON_UNESCAPED_UNICODE);
