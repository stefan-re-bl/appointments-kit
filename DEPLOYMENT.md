# Despliegue

Appointments Kit usa un despliegue independiente por cliente.

Cada cliente necesita:

- código desplegado;
- `.env` propio;
- base de datos propia;
- assets de marca propios;
- credenciales externas propias.

No uses una base compartida entre clientes.

## Preparación

1. Crea `.env` desde `.env.example`.
2. Completa `APP_KEY`.
3. Configura base de datos.
4. Configura correo.
5. Configura branding.
6. Configura reglas de booking.
7. Configura feature flags.
8. Configura WhatsApp solo si el cliente lo usa.

## Build

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Colas

Producción con Redis:

```dotenv
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
```

Ejecuta un worker persistente:

```bash
php artisan queue:work redis --queue=default --sleep=3 --tries=3 --timeout=90
```

Producción sin worker persistente:

```dotenv
QUEUE_CONNECTION=database
SCHEDULE_QUEUE_WORKER=true
```

## Scheduler

Ejecuta cada minuto:

```bash
* * * * * php /path/to/app/artisan schedule:run >> /dev/null 2>&1
```

El scheduler procesa:

- limpieza de turnos pendientes expirados;
- recordatorios;
- heartbeat;
- monitoreo de colas.

## Plantillas

El directorio `deploy` incluye plantillas genéricas:

- `deploy/nginx/appointments-kit.conf`
- `deploy/supervisor/appointments-kit-worker.conf`
- `deploy/cron/appointments-kit-scheduler`

Reemplaza `example.com` por el dominio real.
Reemplaza `/var/www/appointments-kit/current` por la ruta real.

## Verificación

```bash
php artisan deployment:check --profile=redis --url=https://example.com
php artisan schedule:list
php artisan migrate:status
```

Usa `--profile=database` si no usas Redis.

## Demo En Producción

No ejecutes `demo:reset` en producción.

El comando falla en producción sin `--force`.
Usa `--force` solo en entornos desechables.

## Guías Existentes

También existen guías específicas:

- `docs/deploy/vps.md`
- `docs/deploy/hostinger-business.md`

Las plantillas genéricas son la referencia principal.
