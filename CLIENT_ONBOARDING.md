# Onboarding De Cliente

Usa esta checklist para crear una nueva instancia.

## Modelo De Instancia

```text
1 repositorio genérico
1 deployment por cliente
1 base de datos por cliente
1 archivo .env por cliente
```

No uses multitenancy en esta fase.

## Checklist Técnica

1. Crear base de datos.
2. Crear `.env` desde `.env.example`.
3. Configurar `APP_NAME`, `APP_URL` y `APP_KEY`.
4. Configurar `BRANDING_*`.
5. Configurar `TERM_*`.
6. Configurar `BOOKING_*`.
7. Configurar `FEATURE_*`.
8. Configurar email.
9. Configurar WhatsApp si aplica.
10. Cargar logo y favicon.
11. Cargar imagen hero si aplica.
12. Crear profesionales.
13. Crear servicios.
14. Crear disponibilidad.
15. Probar una cita completa.
16. Probar recordatorios.
17. Probar cancelación y reprogramación.
18. Ejecutar `deployment:check`.

## Datos Que Debe Entregar El Cliente

- Nombre comercial.
- Logo.
- Favicon.
- Colores principales.
- Texto breve de home.
- Datos de contacto.
- Redes sociales.
- Servicios.
- Duración de servicios.
- Precios y moneda.
- Profesionales.
- Horarios.
- Plazos de cancelación.
- Plazos de reprogramación.
- Política de pago.
- Canales de notificación.
- Credenciales SMTP.
- Credenciales WhatsApp, si aplica.

## Qué No Debe Cambiarse

No edites controladores para un cliente.

No edites servicios centrales para cambiar:

- nombre;
- logo;
- terminología;
- reglas básicas;
- canales activos;
- servicios;
- profesionales.

Usa configuración y datos.

## Validación Funcional

Antes de entregar:

```bash
php artisan migrate --force
php artisan db:seed
php artisan test --filter=CriticalBookingFlowTest
php artisan test --filter=SendAppointmentRemindersCommandTest
php artisan deployment:check --url=https://dominio
```

## Riesgos Actuales

Algunos nombres internos siguen vinculados al origen:

- `Professional`;
- `SessionType`;
- campos `patient_*`.

Esto no impide crear instancias.
Pero debe refactorizarse en una fase posterior.
