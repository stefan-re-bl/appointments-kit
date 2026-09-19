# Configuración

Appointments Kit se adapta por configuración.

No edites controladores ni servicios centrales para crear una instancia nueva.

## Branding

Archivo: `config/branding.php`.

Variables:

```dotenv
BRANDING_NAME="Appointments Kit"
BRANDING_LOGO=images/client-logo.png
BRANDING_FAVICON=favicon.png
BRANDING_HOME_HERO_IMAGE=images/client-home-hero.jpg
BRANDING_INFORMATION_HERO_IMAGE=images/client-information-hero.jpg
BRANDING_INTRO_VIDEO=videos/client-intro.mp4
BRANDING_WHATSAPP_NUMBER=5491111111111
BRANDING_INSTAGRAM_URL=https://www.instagram.com/client
```

Cambia estos valores por cliente.

## Terminología

Archivo: `config/terminology.php`.

Variables:

```dotenv
TERM_PROVIDER_SINGULAR=profesional
TERM_PROVIDER_PLURAL=profesionales
TERM_CUSTOMER_SINGULAR=cliente
TERM_CUSTOMER_PLURAL=clientes
TERM_SERVICE_SINGULAR=servicio
TERM_SERVICE_PLURAL=servicios
TERM_APPOINTMENT_SINGULAR=cita
TERM_APPOINTMENT_PLURAL=citas
```

La terminología visible todavía no cubre toda la interfaz.
Úsala como base para las siguientes fases.

## Booking

Archivo: `config/booking.php`.

Variables:

```dotenv
BOOKING_CANCELLATION_NOTICE_HOURS=24
BOOKING_REFUND_NOTICE_HOURS=24
BOOKING_RESCHEDULE_NOTICE_HOURS=48
BOOKING_MAX_RESCHEDULES=2
BOOKING_PENDING_EXPIRATION_MINUTES=15
BOOKING_REMINDER_LEAD_HOURS=24
BOOKING_REMINDER_STALE_QUEUE_MINUTES=15
```

Estos valores controlan:

- cancelación;
- reembolso;
- reprogramación;
- expiración de turnos pendientes;
- ventana de recordatorios;
- reclamo de recordatorios atascados.

## Feature Flags

Archivo: `config/features.php`.

Variables:

```dotenv
FEATURE_PUBLIC_INFORMATION_PAGES=true
FEATURE_PUBLIC_FAQ=true
FEATURE_PUBLIC_CONTACT_FORM=true
FEATURE_PUBLIC_PROVIDER_DIRECTORY=true
FEATURE_INTRO_VIDEO=true
FEATURE_WHATSAPP=false
FEATURE_EMAIL_NOTIFICATIONS=true
FEATURE_MANUAL_PAYMENTS=true
FEATURE_PUBLIC_RESCHEDULING=true
FEATURE_PUBLIC_CANCELLATION=true
FEATURE_REPORTS=true
FEATURE_VISIBLE_AUDIT=true
```

Solo uses flags para funciones reales.

## Email

Configura Laravel Mail:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"
```

## WhatsApp

WhatsApp requiere dos activaciones:

```dotenv
FEATURE_WHATSAPP=true
WHATSAPP_ENABLED=true
```

Luego completa las variables `META_WHATSAPP_*`.

Si `FEATURE_WHATSAPP=false`, el negocio sigue funcionando sin WhatsApp.

## Colas

Desarrollo simple:

```dotenv
QUEUE_CONNECTION=database
```

Producción recomendada:

```dotenv
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
```

## Monitoreo

Archivo: `config/monitoring.php`.

Variables principales:

```dotenv
MONITORING_ALERT_EMAIL=
MONITORING_QUEUE_ENABLED=true
MONITORING_QUEUE_CONNECTION=database
```
