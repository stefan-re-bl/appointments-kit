# AGENTS.md - Umbralia

Proyecto Laravel 13 / PHP 8.5 en WSL2 Debian con Laravel Sail.

## Reglas críticas
- Usar siempre comandos con `sail`.
- No usar `app/Http/Kernel.php`; middleware en `bootstrap/app.php`.
- En controladores usar `HasMiddleware`, no `__construct`.
- En modelos usar atributos PHP 8: `#[Fillable]`, `#[Hidden]`.
- No integrar Stripe ni Mercado Pago. Pagos manuales.
- No usar Day.js ni Moment.js.
- Fechas: DB en UTC; conversión/formato con `App\Services\TimezoneService`.
- Blade sin textos hardcodeados: usar `__('key')`.

## Contexto externo
La documentación larga del proyecto vive fuera del repo en Obsidian.
No asumir backlog completo dentro del proyecto.

## Flujo de trabajo
- Antes de modificar, inspeccionar solo los archivos necesarios.
- No hacer búsquedas globales salvo que sea imprescindible.
- Tocar pocos archivos por turno.
- Ejecutar tests específicos, no toda la suite salvo que se pida.
- Si falta contexto de negocio, pedirlo antes de inventar.
