# Appointments Kit

Appointments Kit es una plataforma Laravel para gestionar citas, profesionales, disponibilidad, pagos manuales y notificaciones.

El producto se despliega como una instancia independiente por cliente. Cada cliente tiene su propio `.env`, base de datos, marca, assets, profesionales y reglas operativas.

No usa multitenancy.

## Uso Previsto

- Sitios de servicios con agenda profesional.
- Equipos que necesitan carga interna de citas.
- Negocios que coordinan pago fuera de la plataforma.
- Proyectos que requieren emails, recordatorios, WhatsApp opcional, reportes y auditoria.

## Stack

- Laravel 13.
- PHP 8.5.
- Laravel Sail sobre Docker para desarrollo.
- MySQL 8.4 y Redis.
- Blade, Tailwind CSS, Alpine.js y Vite.
- Colas de Laravel para emails, recordatorios y WhatsApp.

## Inicio Rápido

Usa WSL y Sail en desarrollo local.

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
npm install
npm run build
```

Para crear datos ficticios:

```bash
./vendor/bin/sail artisan demo:reset
```

## Documentación Principal

Lee los documentos en este orden:

1. [DEVELOPMENT.md](DEVELOPMENT.md): entorno local, comandos y reglas de cambio.
2. [CONFIGURATION.md](CONFIGURATION.md): variables por cliente y personalizacion.
3. [ARCHITECTURE.md](ARCHITECTURE.md): modelo de dominio y servicios principales.
4. [TIMEZONE_STRATEGY.md](TIMEZONE_STRATEGY.md): regla de UTC y conversiones.
5. [DEPLOYMENT.md](DEPLOYMENT.md): despliegue, colas, scheduler y verificacion.
6. [CLIENT_ONBOARDING.md](CLIENT_ONBOARDING.md): checklist de entrega a cliente.
7. [DEMO.md](DEMO.md): datos demo y validacion.

## Flujos Principales

- Cliente: consulta informacion publica y recibe comunicaciones de su cita.
- Profesional: configura perfil, disponibilidad y gestiona citas propias.
- Admin: aprueba profesionales, gestiona agenda global, reportes y auditoria.
- Sistema: envia emails, recordatorios, WhatsApp opcional y limpieza programada.

## Reglas Centrales

- Guarda fechas y horas de citas en UTC.
- Convierte y formatea horarios en backend.
- Usa `TimezoneService` y `CountryTimezoneService` para zonas horarias.
- Usa pagos manuales. No integres Stripe ni Mercado Pago en el MVP.
- Usa traducciones para textos visibles en Blade.
- Usa configuracion para adaptar clientes. No agregues condicionales por cliente.
- Conserva cada instancia con base de datos y `.env` propios.

## Validacion Antes De Entregar

```bash
npm run build
./vendor/bin/sail artisan migrate:fresh --seed --force
./vendor/bin/sail artisan test
./vendor/bin/sail php vendor/bin/pint --test --dirty
git diff --check
```

## Compatibilidad Legacy

El kit conserva redirects para URLs antiguas:

- `/therapists`
- `/therapists/{slug}`
- `/therapist/appointments`

Tambien acepta alias antiguos de formulario:

- `therapist_country`
- `therapist_timezone`

Puedes quitarlos en una instancia nueva si no existe trafico o integraciones que dependan de esas rutas.
