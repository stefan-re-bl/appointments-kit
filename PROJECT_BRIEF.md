# PROJECT_BRIEF.md - Umbralia

Umbralia es una app Laravel para reservas online de turnos de psicología.

## Estado actual
Implementado:
- Auth Breeze.
- Roles con enum: admin, therapist.
- Perfil Therapist.
- SessionType.
- Availability.
- Appointment.
- TimezoneService.
- SlotGenerationService.
- BookingService con lockForUpdate.
- i18n ES/EN.
- Endpoint `/api/slots`.

## Reglas de dominio
- Paciente sin login.
- Terapeuta gestiona disponibilidad, sesiones y pagos manuales.
- Admin audita operación.
- Citas nuevas: status confirmed, payment_status pending.
- Pagos manuales: paid / waived / pending.
- DB en UTC.
- Backend formatea fechas.
- Frontend solo detecta timezone.
