# Configuracion

Appointments Kit se adapta por cliente mediante `.env`, archivos `config/*`, traducciones y datos.

No edites controladores ni servicios centrales para crear una instancia nueva.

## Modelo De Configuracion

Cada cliente necesita:

- `.env` propio;
- base de datos propia;
- assets propios;
- profesionales propios;
- reglas comerciales propias;
- credenciales externas propias.

No compartas base de datos entre clientes.

## Variables Minimas

| Variable | Uso | Ejemplo | Obligatoria |
| --- | --- | --- | --- |
| `APP_NAME` | Nombre interno de la app | `Client Appointments` | Si |
| `APP_ENV` | Entorno | `production` | Si |
| `APP_KEY` | Clave Laravel | `base64:...` | Si |
| `APP_URL` | URL publica | `https://example.com` | Si |
| `DB_CONNECTION` | Driver DB | `mysql` | Si |
| `DB_HOST` | Host DB | `mysql` | Si |
| `DB_DATABASE` | Base de datos | `appointments_client` | Si |
| `DB_USERNAME` | Usuario DB | `client_user` | Si |
| `DB_PASSWORD` | Password DB | `secret` | Si |
| `MAIL_MAILER` | Transporte email | `smtp` | Si |
| `MAIL_FROM_ADDRESS` | Remitente email | `no-reply@example.com` | Si |
| `QUEUE_CONNECTION` | Cola | `redis` o `database` | Si |

## Branding

Archivo: `config/branding.php`.

Variables:

```dotenv
BRANDING_NAME="Client Appointments"
BRANDING_LOGO=images/client-logo.png
BRANDING_FAVICON=favicon.png
BRANDING_HOME_HERO_IMAGE=images/client-home-hero.jpg
BRANDING_INFORMATION_HERO_IMAGE=images/client-information-hero.jpg
BRANDING_INTRO_VIDEO=videos/client-intro.mp4
BRANDING_WHATSAPP_NUMBER=5491111111111
BRANDING_INSTAGRAM_URL=https://www.instagram.com/client
```

Usa assets en `public/images` y `public/videos`.

No uses nombres de marca dentro del codigo.

## Terminologia

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

La terminologia configura textos genericos.

Si un texto visible no usa configuracion, muevelo a `lang/es` y `lang/en` antes de personalizarlo.

## Reglas De Booking

Archivo: `config/booking.php`.

Variables:

```dotenv
BOOKING_CANCELLATION_NOTICE_HOURS=24
BOOKING_DEFAULT_CURRENCY=ARS
BOOKING_SUPPORTED_CURRENCIES=ARS,USD
BOOKING_REFUND_NOTICE_HOURS=24
BOOKING_RESCHEDULE_NOTICE_HOURS=48
BOOKING_MAX_RESCHEDULES=2
BOOKING_PENDING_EXPIRATION_MINUTES=15
BOOKING_REMINDER_LEAD_HOURS=24
BOOKING_REMINDER_STALE_QUEUE_MINUTES=15
```

Estas variables controlan:

- cancelacion;
- reembolso;
- reprogramacion;
- expiracion de citas pendientes;
- ventana de recordatorios;
- reclamo de recordatorios atascados.

## Funciones Activas

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

Usa flags solo para funciones reales.

No uses flags para corregir excepciones de un cliente especifico.

## Email

Configura Laravel Mail:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Valida email con una cita demo o con tests focalizados.

## WhatsApp

WhatsApp tiene dos interruptores:

```dotenv
FEATURE_WHATSAPP=true
WHATSAPP_ENABLED=true
```

Luego configura Meta:

```dotenv
META_WHATSAPP_PHONE_NUMBER_ID=
META_WHATSAPP_BUSINESS_ACCOUNT_ID=
META_WHATSAPP_ACCESS_TOKEN=
META_WHATSAPP_WEBHOOK_VERIFY_TOKEN=
META_APP_SECRET=
```

Plantillas requeridas cuando WhatsApp esta activo:

```dotenv
META_WHATSAPP_TEMPLATE_PATIENT_CONFIRMATION_ES=
META_WHATSAPP_TEMPLATE_PATIENT_CONFIRMATION_EN=
META_WHATSAPP_TEMPLATE_PROFESSIONAL_CONFIRMATION_ES=
META_WHATSAPP_TEMPLATE_PROFESSIONAL_CONFIRMATION_EN=
META_WHATSAPP_TEMPLATE_PATIENT_REMINDER_ES=
META_WHATSAPP_TEMPLATE_PATIENT_REMINDER_EN=
META_WHATSAPP_TEMPLATE_PROFESSIONAL_REMINDER_ES=
META_WHATSAPP_TEMPLATE_PROFESSIONAL_REMINDER_EN=
```

Fuentes de idioma:

- cliente: `appointments.patient_locale`;
- profesional: `professionals.preferred_locale`.

No infieras idioma desde telefono, pais o zona horaria.

## Colas

Produccion recomendada:

```dotenv
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
```

Hosting sin worker persistente:

```dotenv
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
SCHEDULE_QUEUE_WORKER=true
```

## Checklist De Configuracion Por Cliente

1. Completa `.env`.
2. Cambia branding.
3. Carga logo, favicon, hero y video si aplica.
4. Ajusta terminologia.
5. Ajusta reglas de booking.
6. Activa o desactiva features.
7. Configura correo.
8. Configura WhatsApp si aplica.
9. Crea admin inicial.
10. Carga profesionales.
11. Carga servicios y duracion.
12. Carga disponibilidad.
13. Prueba una cita completa.
14. Prueba recordatorios.
15. Ejecuta `deployment:check`.
