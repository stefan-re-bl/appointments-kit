# Appointments Kit

Appointments Kit es una plataforma Laravel para reservas y gestión de citas.

El objetivo es reutilizar el mismo código en despliegues independientes por cliente.
Cada cliente usa su propio `.env`, base de datos, branding y datos operativos.

No implementa multitenancy.

## Stack

- PHP 8.3 o superior.
- Laravel 13.
- MySQL o SQLite para desarrollo.
- Redis opcional para producción.
- Blade, Tailwind CSS, Alpine.js y Vite.
- Colas de Laravel para emails, recordatorios y notificaciones.

## Instalación Local

Usa Composer, Node y PHP local, o Laravel Sail si está disponible.

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

Para reconstruir una demo:

```bash
php artisan demo:reset
```

El comando está protegido en producción.
Usa `--force` solo si entiendes el impacto.

## Accesos Demo

La demo crea datos ficticios de `Demo Services`.

- Admin: `admin@demo.test`
- Profesionales: `ana.martinez@demo.test`, `lucas.fernandez@demo.test`, `sofia.gomez@demo.test`
- Contraseña: `password`

## Configuración Principal

La configuración variable vive en archivos dedicados:

- `config/branding.php`
- `config/booking.php`
- `config/features.php`
- `config/terminology.php`
- `config/services.php`

Consulta [CONFIGURATION.md](CONFIGURATION.md) para el detalle.

## Flujos Principales

- Cliente: consulta información pública y recibe comunicaciones de una cita.
- Profesional: configura perfil, disponibilidad y gestiona citas.
- Admin: aprueba profesionales, consulta agenda global, reportes y auditoría.
- Sistema: procesa recordatorios, emails, colas, limpieza y notificaciones.

## Estado Del Dominio Interno

El producto ya tiene configuración base para branding, reglas y funciones.

Algunos nombres internos todavía conservan el origen del proyecto:

- `Professional`
- `SessionType`
- campos `patient_*`

La interfaz debe moverse primero mediante configuración y traducciones.
El renombrado interno debe hacerse después, en commits pequeños.

## Pruebas

Ejecuta pruebas focalizadas:

```bash
php artisan test --filter=DemoSeederTest
```

Ejecuta la suite completa:

```bash
php artisan test
```

La prueba `ProfileTest::test_therapist_can_upload_local_profile_photo` requiere la extensión PHP `GD`.

## Documentación

- [ARCHITECTURE.md](ARCHITECTURE.md)
- [CONFIGURATION.md](CONFIGURATION.md)
- [DEVELOPMENT.md](DEVELOPMENT.md)
- [DEPLOYMENT.md](DEPLOYMENT.md)
- [CLIENT_ONBOARDING.md](CLIENT_ONBOARDING.md)
- [DEMO.md](DEMO.md)
