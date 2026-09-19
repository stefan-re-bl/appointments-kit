# Demo

La demo usa datos ficticios.

No contiene datos reales del cliente original.

## Crear Demo

```bash
php artisan demo:reset
```

El comando ejecuta:

```bash
php artisan migrate:fresh --seed --seeder=Database\\Seeders\\DemoSeeder --force
```

En producción falla sin `--force`.

## Datos Creados

Empresa demostrativa:

- `Demo Services`

Admin:

- `Admin Demo Services`
- `admin@demo.test`
- contraseña `password`

Profesionales:

- `Ana Martínez`
- `Lucas Fernández`
- `Sofía Gómez`

Servicios:

- `Servicio inicial`
- `Servicio estándar`
- `Servicio extendido`

Clientes ficticios:

- `María Pérez`
- `Julián Torres`
- `Carla Ruiz`
- `Diego Molina`

## Estados Incluidos

La demo crea:

- turnos futuros;
- turnos pasados;
- turnos cancelados;
- turnos completados;
- turnos reprogramados;
- pagos pendientes;
- pagos registrados;
- pagos bonificados;
- disponibilidad semanal.

## Validar Demo

```bash
php artisan test --filter=DemoSeederTest
php artisan test --filter=DemoResetCommandTest
```

## Uso En Presentaciones

Usa la demo para mostrar:

- agenda de profesionales;
- estados de pago;
- auditoría;
- reportes;
- recordatorios;
- cancelación;
- reprogramación.

No uses datos reales en una demo comercial.
