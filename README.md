<p align="center">
  <img src="assets/img/fotos/logo.png" alt="Hotel en Coveñas" height="90">
</p>

# Hotel en Coveñas — Sitio web y reservas en línea

Sitio web para un hotel de playa en Coveñas (Caribe colombiano) con **reservas tipo "cine"**: el huésped elige fechas, ve el mapa de habitaciones y escoge la suya, y el hotel administra todo desde un panel con calendario de ocupación.

<p>
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite">
  <img src="https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=0B0D12" alt="Alpine.js">
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=0B0D12" alt="JavaScript">
</p>

## Características

**Sitio público**
- Portada, habitaciones (listado y detalle), servicios, galería, preguntas frecuentes y contacto.
- **Wizard de reserva en 5 pasos** (`reservar.php`): fechas y huéspedes → mapa de habitaciones → extras → datos del huésped → confirmación.
- **Mapa de habitaciones**: cada unidad física se muestra disponible u ocupada según las fechas elegidas.
- **API de disponibilidad** en JSON (`api/disponibilidad.php?check_in=…&check_out=…&adultos=…&ninos=…`) que valida fechas y capacidad contra la base de datos.
- Página de confirmación de la reserva.

**Panel de administración** (`/admin`)
- Dashboard general.
- **Calendario de ocupación** por unidad.
- Gestión de reservas, tipos de habitación, unidades (habitaciones físicas) y extras.
- Acceso protegido con sesión.

## Stack

| Capa | Tecnología |
|------|------------|
| Backend | PHP puro (sin framework) + PDO |
| Base de datos | SQLite (`data/hotel.sqlite`, generada por el seed) |
| Frontend | Tailwind CSS (CDN, plugin forms) + Alpine.js |
| Tipografía e iconos | Montserrat + Material Symbols (Google Fonts) |

## Estructura del proyecto

```
Hotel/
├── index.php, habitaciones.php, habitacion.php, servicios.php,
│   galeria.php, faq.php, contacto.php
├── reservar.php          # wizard de reserva en 5 pasos
├── confirmacion.php
├── api/
│   └── disponibilidad.php
├── admin/                # dashboard, calendario, reservas, habitaciones, unidades, extras
├── config/               # config.php y conexión a la BD
├── includes/             # header, footer y helpers
├── database/
│   └── seed.php          # esquema + datos demo
└── assets/               # css, js e imágenes
```

## Cómo correrlo en local

Requisitos: PHP 8 con la extensión `pdo_sqlite` habilitada.

```bash
# 1. Clonar
git clone https://github.com/burgosdavid057-art/hotel-covenas.git
cd hotel-covenas

# 2. Crear la base de datos con datos demo (tablas: habitaciones, unidades, reservas, extras)
php database/seed.php

# 3. Levantar el servidor
php -S localhost:8000
```

Abrir http://localhost:8000. El panel queda en http://localhost:8000/admin/login.php.

> Volver a ejecutar `php database/seed.php` reinicia la base de datos con los datos de ejemplo.

## Acceso de demostración

| Usuario | Contraseña |
|---------|------------|
| `admin` | `hotel2025` |

Son credenciales **solo de demostración**; cámbialas en `config/config.php` antes de cualquier despliegue real.

## Autor

**David Burgos** — Desarrollador full-stack + IA, Medellín (Colombia).

- Portafolio: [davidburgos.dev](https://davidburgos.dev)
- GitHub: [@burgosdavid057-art](https://github.com/burgosdavid057-art)
- LinkedIn: [David Burgos](https://www.linkedin.com/in/david-burgos-ab673433a/)
