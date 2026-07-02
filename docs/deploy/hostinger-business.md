# Deploy en Hostinger Business

Esta guía adapta Umbralia a Hostinger Business con SSH, PHP 8.3/8.4/8.5, MySQL y Cron. No asume Redis, Supervisor, Nginx manual ni Laravel Sail en producción.

## Decisiones para Hostinger Business

- Usar PHP 8.5 si Hostinger lo permite de forma estable. Si falla alguna extensión o Composer, usar PHP 8.4 o 8.3.
- Usar MySQL gestionado de Hostinger.
- Usar `database` para sesiones, cache y colas.
- Desactivar el monitoreo de worker basado en Redis al inicio.
- Ejecutar el scheduler por Cron cada minuto.
- Procesar colas desde el scheduler con `SCHEDULE_QUEUE_WORKER=true`, salvo que Hostinger permita un proceso persistente.

## Preparación local

Desde el entorno local con Sail:

```bash
./vendor/bin/sail composer install
./vendor/bin/sail npm ci
./vendor/bin/sail npm run build
./vendor/bin/sail artisan test --filter=CriticalBookingFlowTest
./vendor/bin/sail artisan schedule:list
```

El build genera `public/build`, que debe subirse junto con el código si no se compila en Hostinger.

## Configuración en Hostinger

1. Crear la base de datos MySQL desde hPanel.
2. Seleccionar PHP 8.5, 8.4 o 8.3 para el dominio.
3. Activar SSH.
4. Configurar el document root del sitio hacia la carpeta `public` de la app.

Si hPanel no permite apuntar el dominio directamente a `public`, mantener el código fuera de `public_html` y publicar solo el contenido de `public` con un `index.php` ajustado. Evitar exponer `.env`, `storage`, `vendor`, `database` o el resto del código como archivos navegables.

## Variables de entorno

Copiar `.env.hostinger.example` como `.env` en el servidor y completar:

- `APP_KEY`
- `APP_URL`
- credenciales `DB_*`
- credenciales SMTP
- `MAIL_FROM_ADDRESS`

Valores esperados para este entorno:

```dotenv
APP_ENV=production
APP_DEBUG=false
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
SCHEDULE_QUEUE_WORKER=true
MONITORING_QUEUE_ENABLED=false
SESSION_SECURE_COOKIE=true
```

Generar `APP_KEY` una sola vez:

```bash
/opt/alt/php85/usr/bin/php artisan key:generate --show
```

Pegar el valor resultante en `APP_KEY`.

## Primer despliegue por SSH

Ejecutar en la carpeta de la aplicación dentro de Hostinger:

```bash
/opt/alt/php85/usr/bin/php /usr/local/bin/composer install --no-dev --prefer-dist --optimize-autoloader
/opt/alt/php85/usr/bin/php artisan migrate --force
/opt/alt/php85/usr/bin/php artisan storage:link
/opt/alt/php85/usr/bin/php artisan config:cache
/opt/alt/php85/usr/bin/php artisan route:cache
/opt/alt/php85/usr/bin/php artisan view:cache
```

Si Hostinger tiene Node/npm disponible y se quiere compilar allí:

```bash
npm ci
npm run build
```

Si no, subir `public/build` ya compilado desde local.

## Cron

Configurar estos Cron Jobs en hPanel. En Hostinger Business puede no existir `crontab` por SSH, así que cargarlos desde la pantalla de Cron Jobs de hPanel. Ajustar la ruta y el binario PHP según indique Hostinger.

Scheduler de Laravel:

```bash
* * * * * /opt/alt/php85/usr/bin/php /home/USER/umbralia-app/artisan schedule:run >> /home/USER/umbralia-app/storage/logs/scheduler.log 2>&1
```

Con `SCHEDULE_QUEUE_WORKER=true`, este único Cron también procesa la cola por lotes cortos. No es tan robusto como Supervisor, pero es suficiente para bajo tráfico inicial si los jobs son cortos. Si Hostinger permite procesos persistentes, desactivar `SCHEDULE_QUEUE_WORKER` y usar un worker permanente.

## Verificación

Ejecutar por SSH:

```bash
/opt/alt/php85/usr/bin/php artisan migrate:status
/opt/alt/php85/usr/bin/php artisan schedule:list
/opt/alt/php85/usr/bin/php artisan deployment:check --profile=database --url=https://example.com
/opt/alt/php85/usr/bin/php artisan queue:work database --queue=default --stop-when-empty --tries=3 --timeout=90
```

Validar manualmente:

- Home pública.
- Registro de terapeuta.
- Aprobación y visibilidad pública del terapeuta.
- Flujo completo de reserva.
- Email de confirmación.
- Página pública de cita.
- Cancelación y reprogramación.
- Recordatorio de cita.
- Formulario de contacto.

## Deploy de nuevas versiones

En local:

```bash
./vendor/bin/sail npm run build
```

En Hostinger:

```bash
/opt/alt/php85/usr/bin/php artisan down
/opt/alt/php85/usr/bin/php /usr/local/bin/composer install --no-dev --prefer-dist --optimize-autoloader
/opt/alt/php85/usr/bin/php artisan migrate --force
/opt/alt/php85/usr/bin/php artisan config:cache
/opt/alt/php85/usr/bin/php artisan route:cache
/opt/alt/php85/usr/bin/php artisan view:cache
/opt/alt/php85/usr/bin/php artisan queue:restart
/opt/alt/php85/usr/bin/php artisan up
/opt/alt/php85/usr/bin/php artisan deployment:check --profile=database --url=https://example.com
```

Si se compilan assets localmente, subir también `public/build`.

## API token de Hostinger

No usar el token de API para el primer despliegue. SSH y Cron reducen variables y facilitan depurar. Una vez que el despliegue manual funcione, se puede evaluar automatizar subida, variables o redeploys con la API.
