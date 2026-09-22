# Demo

La demo usa datos ficticios.

No debe contener datos reales de ningun cliente.

## Crear Demo

En desarrollo:

```bash
./vendor/bin/sail artisan demo:reset
```

Sin Sail:

```bash
php artisan demo:reset
```

El comando ejecuta migraciones limpias y carga `Database\\Seeders\\DemoSeeder`.

En produccion falla sin `--force`.

No uses `--force` en una instancia real.

## Accesos

Admin:

- email: `admin@demo.test`
- password: `password`

Profesionales:

- `ana.martinez@demo.test`
- `lucas.fernandez@demo.test`
- `sofia.gomez@demo.test`
- password: `password`

## Datos Creados

La demo crea:

- marca ficticia `Demo Salud`;
- admin;
- profesionales `Ana Martínez`, `Lucas Fernández` y `Sofía Gómez`;
- servicios `Consulta inicial`, `Consulta estándar` y `Consulta extendida`;
- disponibilidad semanal;
- clientes ficticios;
- citas futuras;
- citas pasadas;
- citas canceladas;
- citas completadas;
- citas reprogramadas;
- pagos pendientes;
- pagos registrados;
- pagos bonificados.

## Usos

Usa la demo para mostrar:

- home publica;
- directorio profesional;
- perfil profesional;
- agenda del profesional;
- calendario admin;
- estados de pago;
- auditoria;
- reportes;
- recordatorios;
- cancelacion;
- reprogramacion.

## Validar Demo

```bash
./vendor/bin/sail artisan test --filter=DemoSeederTest
./vendor/bin/sail artisan test --filter=DemoResetCommandTest
```

## Reglas

- No uses datos reales en demos comerciales.
- No compartas passwords reales.
- No ejecutes reset demo en produccion.
- No uses la demo como backup.
