# Umbralia

Aplicación Laravel 13 / PHP 8.5 para reservas online de terapeutas, con pagos manuales, gestión de disponibilidad, emails, recordatorios, panel administrativo y soporte multiidioma.

## Stack

- Laravel 13, PHP 8.5
- Laravel Sail sobre Docker
- MySQL 8.4
- Redis para cache, sesiones y colas en producción/staging
- Blade, Tailwind CSS, Alpine.js y Vite

## Desarrollo Local

Todos los comandos de proyecto deben ejecutarse con Sail.

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Para ejecutar tests focalizados:

```bash
./vendor/bin/sail artisan test --filter=CriticalBookingFlowTest
```

Para compilar assets:

```bash
./vendor/bin/sail npm run build
```

## Flujos Principales

- Paciente: home pública, perfil de terapeuta, reserva multipaso, email de confirmación, página pública "Mi Cita", reprogramación y cancelación.
- Terapeuta: perfil profesional, instrucciones de pago, link de reunión, tipos de sesión, disponibilidad semanal, gestión de citas y pagos manuales.
- Admin: aprobación de terapeutas, listado global de citas, reportes CSV, soporte y auditoría de cambios de citas.

## Reglas Técnicas Clave

- DB en UTC; conversiones y formato con `App\Services\TimezoneService`.
- El paciente confirma país y región horaria efectiva cuando corresponde.
- No se usan Stripe ni Mercado Pago; los pagos son manuales.
- Los textos visibles en Blade deben usar traducciones.
- Middleware en `bootstrap/app.php`; no usar `app/Http/Kernel.php`.
- Controladores con middleware deben usar `HasMiddleware`.
- Modelos con atributos PHP 8: `#[Fillable]`, `#[Hidden]`.

## Operación

Las guías de deploy viven en `docs/deploy/vps.md` y `docs/deploy/hostinger-business.md`. Antes de publicar, validar:

- `.env` productivo con `APP_ENV=production` y `APP_DEBUG=false`.
- Migraciones ejecutadas.
- Assets compilados.
- SMTP real configurado.
- Worker de cola persistente activo.
- Scheduler cada minuto.
- HTTPS y dominio correctos.
- `php artisan deployment:check --url=https://dominio` en el VPS.
