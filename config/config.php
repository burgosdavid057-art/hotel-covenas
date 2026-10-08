<?php
/**
 * Configuración global — Hotel en Coveñas
 * Hotel de playa · Caribe colombiano (Golfo de Morrosquillo)
 */

// ── Negocio ───────────────────────────────────────────────────────────────
const SITE_NAME    = 'Hotel Playa Coveñas';
const SITE_TAGLINE = '¡Descansa frente al mar!';
const SITE_DESC    = 'Hotel Playa Coveñas, en la Segunda Ensenada sobre el Golfo de Morrosquillo. Habitaciones frente al mar Caribe, piscina, gastronomía y excursiones. Siempre con la mejor actitud de servicio.';

// WhatsApp (formato internacional sin + ni espacios)
const WHATSAPP_NUMBER = '573186991222';
const WHATSAPP_LABEL  = '+57 318 699 1222';

const CONTACT_EMAIL   = 'reservas@hotelencovenas.com';
const CONTACT_PHONE   = '+57 318 699 1222';
const CONTACT_ADDRESS = 'Segunda Ensenada · Coveñas, Sucre, Colombia';
const CONTACT_HOURS   = 'Recepción 24 horas';

const SOCIAL_INSTAGRAM = 'https://instagram.com/hotelencovenas';
const SOCIAL_FACEBOOK  = 'https://facebook.com/hotelencovenas';
const SOCIAL_TIKTOK    = 'https://tiktok.com/@hotelencovenas';

// Políticas del hotel
const CHECKIN_HORA  = '15:00';
const CHECKOUT_HORA = '12:00';
const DEPOSIT_PCT   = 30;   // % de anticipo sugerido al reservar

// ── Admin (login simple) ────────────────────────────────────────────────────
// Usuario y hash de contraseña. Contraseña por defecto: hotel2025
const ADMIN_USER = 'admin';
// password_hash('hotel2025', PASSWORD_DEFAULT)  — se regenera en database/seed.php
const ADMIN_PASS_HASH = '$2y$10$3y9Yd0Lm0wq3jJ7XZ1Yk0xeYpYwq4Z2gqV1uPpV9cDqg3qg9Bqg2C';
// Fallback en texto plano (por si el hash difiere entre entornos PHP)
const ADMIN_PASS_PLAIN = 'hotel2025';

// ── Pago (estructura — sin integrar) ────────────────────────────────────────
// TODO: integrar pasarela real (Wompi) con llaves del cliente.
const PAYMENT_ENABLED  = false;          // cuando haya credenciales, poner true
const PAYMENT_PROVIDER = 'wompi';        // 'wompi' | 'mercadopago'
const CURRENCY         = 'COP';

// ── Rutas ────────────────────────────────────────────────────────────────────
const DB_PATH    = __DIR__ . '/../data/hotel.sqlite';
const UPLOAD_DIR = __DIR__ . '/../assets/img/habitaciones';
const UPLOAD_URL = 'assets/img/habitaciones';

// URL base del sitio (para enlaces relativos a la raíz, desde cualquier subcarpeta)
function base_url(string $path = ''): string {
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    // Si estamos dentro de /admin o /api, subir al raíz del sitio
    foreach (['/admin', '/api'] as $sub) {
        if (str_ends_with($dir, $sub)) {
            $dir = substr($dir, 0, -strlen($sub));
            break;
        }
    }
    return $dir . '/' . ltrim($path, '/');
}

// Zona horaria Colombia
date_default_timezone_set('America/Bogota');

// Sesiones
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
