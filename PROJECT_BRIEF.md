# PROJECT_BRIEF.md - Appointments Kit

Appointments Kit es una base Laravel para reservas y gestión de citas.

## Estado Actual

Implementado:

- Auth Breeze.
- Roles con enum.
- Perfil de profesional.
- Servicios mediante `SessionType`.
- Disponibilidad.
- Citas.
- TimezoneService.
- SlotGenerationService.
- BookingService con `lockForUpdate`.
- i18n ES/EN.
- Endpoint `/api/slots`.
- Configuración de branding.
- Configuración de reglas de booking.
- Feature flags iniciales.
- Terminología visible inicial.
- Demo ficticia con `Demo Services`.
- Comando `demo:reset`.

## Reglas De Dominio

- El cliente no requiere login.
- El profesional gestiona disponibilidad, citas y pagos manuales.
- Admin audita la operación.
- Las citas nuevas quedan `confirmed`.
- El pago inicial queda `pending`.
- Pagos manuales: `paid`, `waived`, `pending`.
- La base guarda fechas en UTC.
- Backend formatea fechas para la interfaz.
- Frontend solo detecta zona horaria.

## Límites Actuales

No hay multitenancy.

Algunos nombres internos siguen heredados:

- `Professional`.
- `SessionType`.
- `patient_*`.

Estos nombres deben refactorizarse en una fase separada.
