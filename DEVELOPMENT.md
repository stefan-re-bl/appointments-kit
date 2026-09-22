# Desarrollo

Usa este documento para trabajar en el repositorio local.

## Entorno Soportado

- WSL2 con Debian.
- Docker con Laravel Sail.
- PHP 8.5 dentro de Sail.
- Laravel 13.
- MySQL 8.4 y Redis.
- Node.js y npm para Vite.

Ejecuta comandos de Laravel, Composer y PHP mediante Sail.

```bash
./vendor/bin/sail artisan test
./vendor/bin/sail composer install
./vendor/bin/sail php vendor/bin/pint --dirty
```

Ejecuta `npm` en host local solo para Vite y dependencias frontend.

## Primer Arranque

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
npm install
npm run build
```

Si quieres datos demo, ejecuta:

```bash
./vendor/bin/sail artisan demo:reset
```

## Servidor Local

Levanta los servicios:

```bash
./vendor/bin/sail up -d
```

Compila assets en modo desarrollo:

```bash
npm run dev
```

La app queda disponible en el puerto configurado por Sail.

## Pruebas

Ejecuta una prueba focalizada:

```bash
./vendor/bin/sail artisan test --filter=CriticalBookingFlowTest
```

Ejecuta toda la suite:

```bash
./vendor/bin/sail artisan test
```

La suite cubre:

- reserva y solapamientos;
- disponibilidad;
- pagos manuales;
- emails;
- WhatsApp;
- reportes;
- auditoria;
- permisos;
- paginas publicas;
- demo;
- deploy checks.

## Calidad

Ejecuta Pint para archivos PHP modificados:

```bash
./vendor/bin/sail php vendor/bin/pint --dirty
```

Verifica whitespace antes de commitear:

```bash
git diff --check
```

Compila frontend antes de entregar cambios de vistas, CSS, JS o assets:

```bash
npm run build
```

## Reglas De Cambio

- Haz cambios pequenos y verificables.
- Agrega tests cuando cambies reglas de negocio.
- Mantén textos visibles en archivos `lang`.
- No hardcodees datos de cliente en vistas, controladores o servicios.
- Usa configuracion para branding, reglas, terminologia y funciones activas.
- No agregues Stripe ni Mercado Pago al MVP.
- No guardes fechas locales de citas en base de datos.
- No modifiques `app/Http/Kernel.php`; no existe en Laravel 13.
- Usa `HasMiddleware` en controladores que definan middleware propio.
- Usa atributos `#[Fillable]` y `#[Hidden]` en modelos nuevos.

## Comandos Utiles

```bash
./vendor/bin/sail artisan demo:reset
./vendor/bin/sail artisan appointments:cleanup-expired --dry-run
./vendor/bin/sail artisan appointments:send-reminders --dry-run
./vendor/bin/sail artisan schedule:list
./vendor/bin/sail artisan migrate:status
./vendor/bin/sail artisan deployment:check --profile=database --url=http://localhost
```
