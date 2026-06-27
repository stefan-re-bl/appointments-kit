# Deploy en VPS

Esta guía deja el despliegue productivo de Umbralia en un VPS con Nginx, PHP-FPM, MySQL, Redis, Supervisor, Cron y HTTPS.

## Requisitos

- Ubuntu/Debian con Nginx, PHP 8.5 FPM, extensiones PHP requeridas por Laravel, MySQL 8.4, Redis, Supervisor y Certbot.
- Repositorio publicado en `/var/www/umbralia/current`.
- Usuario del proceso web con permisos de escritura en `storage` y `bootstrap/cache`.
- DNS del dominio apuntando al VPS antes de emitir certificados.

## Configuración inicial

1. Copiar `.env.production.example` a `.env` en el servidor.
2. Completar secretos reales: `APP_KEY`, credenciales MySQL, SMTP, `APP_URL=https://dominio`, `MAIL_FROM_ADDRESS` y `MONITORING_ALERT_EMAIL`.
3. Verificar que producción tenga:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `QUEUE_CONNECTION=redis`
   - `CACHE_STORE=redis`
   - `SESSION_DRIVER=redis`
4. Instalar dependencias y preparar assets:
   ```bash
   composer install --no-dev --prefer-dist --optimize-autoloader
   npm ci
   npm run build
   php artisan key:generate --show
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## Nginx y HTTPS

1. Copiar `deploy/nginx/umbralia.conf` a `/etc/nginx/sites-available/umbralia`.
2. Reemplazar `example.com` y `/var/www/umbralia/current` si el dominio o ruta son distintos.
3. Activar el sitio:
   ```bash
   ln -s /etc/nginx/sites-available/umbralia /etc/nginx/sites-enabled/umbralia
   nginx -t
   systemctl reload nginx
   ```
4. Emitir certificado:
   ```bash
   certbot --nginx -d example.com -d www.example.com
   ```

## Supervisor y colas

1. Copiar `deploy/supervisor/umbralia-worker.conf` a `/etc/supervisor/conf.d/umbralia-worker.conf`.
2. Ajustar ruta, usuario o cantidad de procesos si corresponde.
3. Recargar Supervisor:
   ```bash
   supervisorctl reread
   supervisorctl update
   supervisorctl start umbralia-worker:*
   supervisorctl status
   ```

## Cron y scheduler

1. Copiar `deploy/cron/umbralia-scheduler` a `/etc/cron.d/umbralia-scheduler`.
2. Confirmar que Cron esté activo:
   ```bash
   systemctl status cron
   ```

El scheduler ejecuta limpieza de reservas expiradas, recordatorios, heartbeat y monitoreo de colas según `routes/console.php`.

## Verificación productiva

Ejecutar desde el servidor:

```bash
php artisan deployment:check --url=https://example.com
php artisan schedule:list
supervisorctl status
```

Luego verificar manualmente:

- Home pública.
- `/how-it-works`, `/faq`, `/patients`, `/payment-and-cancellation`.
- `/legal`, `/terms`, `/privacy`, `/emergency-notice`.
- `/contact`.
- Registro de terapeuta nuevo: debe quedar pendiente de aprobación y no aparecer en `/book`.
- Flujo completo de reserva con una terapeuta activa, aprobada y con link de reunión cargado.
- Emails transaccionales: confirmación, recordatorios y soporte.

## Deploy de nuevas versiones

1. Poner la app en mantenimiento si hay migraciones sensibles:
   ```bash
   php artisan down
   ```
2. Actualizar código.
3. Ejecutar:
   ```bash
   composer install --no-dev --prefer-dist --optimize-autoloader
   npm ci
   npm run build
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan queue:restart
   ```
4. Levantar la app:
   ```bash
   php artisan up
   ```
5. Repetir `php artisan deployment:check --url=https://example.com`.
