# Despliegue

Appointments Kit usa un despliegue independiente por cliente.

Cada cliente necesita codigo desplegado, `.env`, base de datos, assets, dominio y credenciales propias.

## Preparacion

1. Crea base de datos.
2. Crea `.env` desde `.env.production.example`.
3. Configura `APP_NAME`, `APP_ENV=production`, `APP_KEY` y `APP_URL`.
4. Configura base de datos.
5. Configura correo.
6. Configura branding.
7. Configura reglas de booking.
8. Configura feature flags.
9. Configura WhatsApp solo si el cliente lo usa.
10. Sube assets de marca.

## Build Productivo

Ejecuta en el servidor:

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

## Permisos

El servidor web debe poder escribir en:

```text
storage
bootstrap/cache
```

No expongas el proyecto completo como document root.

El document root debe apuntar a:

```text
public
```

## Colas Con Redis

Recomendado para VPS o hosting con worker persistente.

```dotenv
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
```

Worker:

```bash
php artisan queue:work redis --queue=default --sleep=3 --tries=3 --timeout=90
```

Usa Supervisor, systemd, Forge, Ploi o equivalente.

Plantilla:

```text
deploy/supervisor/appointments-kit-worker.conf
```

## Colas Sin Worker Persistente

Usa este modo en hosting que solo permite Cron.

```dotenv
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
SCHEDULE_QUEUE_WORKER=true
MONITORING_QUEUE_ENABLED=false
```

El scheduler ejecuta un worker corto.

## Scheduler

Configura un Cron por minuto:

```bash
* * * * * php /path/to/app/artisan schedule:run >> /dev/null 2>&1
```

El scheduler procesa:

- limpieza de citas pendientes expiradas;
- recordatorios;
- heartbeat;
- monitoreo de colas;
- worker corto si `SCHEDULE_QUEUE_WORKER=true`.

Plantilla:

```text
deploy/cron/appointments-kit-scheduler
```

## Nginx

Plantilla:

```text
deploy/nginx/appointments-kit.conf
```

Reemplaza:

- `example.com`;
- `/var/www/appointments-kit/current`;
- version de PHP-FPM si aplica.

## Verificacion

Con Redis:

```bash
php artisan deployment:check --profile=redis --url=https://example.com
```

Con database queue:

```bash
php artisan deployment:check --profile=database --url=https://example.com
```

Tambien ejecuta:

```bash
php artisan schedule:list
php artisan migrate:status
```

## Checklist Pre Entrega

1. HTTPS activo.
2. `APP_URL` correcto.
3. Migraciones aplicadas.
4. Assets compilados.
5. Storage link creado.
6. Correo validado.
7. Scheduler activo.
8. Worker activo o `SCHEDULE_QUEUE_WORKER=true`.
9. Admin creado.
10. Profesionales cargados.
11. Disponibilidad cargada.
12. Cita completa probada.
13. Recordatorio probado.
14. Cancelacion y reprogramacion probadas.
15. Backup inicial creado.
16. `deployment:check` pasa.

## Demo En Produccion

No ejecutes `demo:reset` en produccion real.

El comando falla en produccion sin `--force`.

Usa `--force` solo en entornos desechables.

## Guías Especificas

- `docs/deploy/vps.md`
- `docs/deploy/hostinger-business.md`

Estas guias complementan este documento.
