# Desarrollo

Usa este documento para trabajar en el repositorio.

## Requisitos

- PHP 8.3 o superior.
- Composer.
- Node.js y npm.
- Base de datos local.
- Extensión PHP `GD` para ejecutar toda la suite.

## Instalación

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

## Servidor Local

```bash
php artisan serve
npm run dev
```

Ejecuta un worker si pruebas colas reales:

```bash
php artisan queue:work
```

## Tests

Ejecuta una prueba específica:

```bash
php artisan test --filter=DemoSeederTest
```

Ejecuta la suite completa:

```bash
php artisan test
```

Si falta `GD`, fallará la prueba de subida de foto.

## Estilo

Ejecuta Pint cuando modifiques PHP:

```bash
vendor/bin/pint --dirty
```

## Migraciones

Mantén migraciones compatibles con SQLite cuando existan tests con `RefreshDatabase`.

Si eliminas una columna con índice, elimina primero el índice.

## Reglas De Cambio

- Mantén cambios pequeños.
- Agrega tests cuando cambies reglas variables.
- No renombres entidades internas sin plan de migración.
- No agregues multitenancy.
- No edites secretos reales.
- Usa configuración antes de introducir condicionales por cliente.

## Comandos Útiles

```bash
php artisan demo:reset
php artisan appointments:cleanup-expired --dry-run
php artisan appointments:send-reminders --dry-run
php artisan schedule:list
php artisan deployment:check --profile=database --url=http://localhost
```
