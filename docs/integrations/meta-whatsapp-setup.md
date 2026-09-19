# Meta WhatsApp Cloud API

Esta integración envía notificaciones transaccionales de citas mediante Meta WhatsApp Cloud API. No envía marketing, soporte, cancelaciones, reprogramaciones ni contenido sensible.

Documentos complementarios:

- `docs/integrations/meta-whatsapp-owner-actions.md`
- `docs/integrations/meta-whatsapp-deployment.md`
- `docs/integrations/meta-whatsapp-testing.md`

## Configuración

Mantener `WHATSAPP_ENABLED=false` hasta tener credenciales, webhook firmado y las ocho plantillas aprobadas por Meta.

Variables requeridas cuando `WHATSAPP_ENABLED=true`:

- `META_WHATSAPP_GRAPH_VERSION`
- `META_WHATSAPP_PHONE_NUMBER_ID`
- `META_WHATSAPP_BUSINESS_ACCOUNT_ID`
- `META_WHATSAPP_ACCESS_TOKEN`
- `META_WHATSAPP_WEBHOOK_VERIFY_TOKEN`
- `META_APP_SECRET`
- `META_WHATSAPP_LANGUAGE_ES`
- `META_WHATSAPP_LANGUAGE_EN`
- Las ocho variables `META_WHATSAPP_TEMPLATE_*`

`deployment:check` valida estas claves solo cuando WhatsApp está habilitado.

## Webhook

Configurar en Meta:

- Callback URL: `https://dominio/webhooks/meta/whatsapp`
- Verify token: valor de `META_WHATSAPP_WEBHOOK_VERIFY_TOKEN`
- Firma: `X-Hub-Signature-256` validada con `META_APP_SECRET`

El POST responde rápido y encola `ProcessMetaWhatsAppWebhook`. Los eventos actualizan `submitted`, `delivered`, `read` y `failed` usando `provider_message_id`. El webhook no modifica locales.

## Plantillas

Crear las plantillas como categoría `Utility`. Los nombres sugeridos pueden ajustarse, pero deben configurarse en las variables de entorno correspondientes.

Los valores `patient` y `professional` son identificadores internos heredados.
En textos visibles usar cliente y profesional.

| Evento | Destinatario | Locale interno | Meta language code | Timezone | Env template | Variables |
| --- | --- | --- | --- | --- | --- | --- |
| `booking_confirmed` | `patient` | `es` | `META_WHATSAPP_LANGUAGE_ES` | `appointments.patient_timezone` | `META_WHATSAPP_TEMPLATE_PATIENT_CONFIRMATION_ES` | cliente, fecha, hora, URL de cita |
| `booking_confirmed` | `patient` | `en` | `META_WHATSAPP_LANGUAGE_EN` | `appointments.patient_timezone` | `META_WHATSAPP_TEMPLATE_PATIENT_CONFIRMATION_EN` | customer, date, time, My Appointment URL |
| `booking_confirmed` | `professional` | `es` | `META_WHATSAPP_LANGUAGE_ES` | `professionals.timezone` | `META_WHATSAPP_TEMPLATE_PROFESSIONAL_CONFIRMATION_ES` | cliente, servicio, fecha, hora, URL panel |
| `booking_confirmed` | `professional` | `en` | `META_WHATSAPP_LANGUAGE_EN` | `professionals.timezone` | `META_WHATSAPP_TEMPLATE_PROFESSIONAL_CONFIRMATION_EN` | customer, service, date, time, dashboard URL |
| `appointment_reminder` | `patient` | `es` | `META_WHATSAPP_LANGUAGE_ES` | `appointments.patient_timezone` | `META_WHATSAPP_TEMPLATE_PATIENT_REMINDER_ES` | cliente, hora, URL de cita |
| `appointment_reminder` | `patient` | `en` | `META_WHATSAPP_LANGUAGE_EN` | `appointments.patient_timezone` | `META_WHATSAPP_TEMPLATE_PATIENT_REMINDER_EN` | customer, time, My Appointment URL |
| `appointment_reminder` | `professional` | `es` | `META_WHATSAPP_LANGUAGE_ES` | `professionals.timezone` | `META_WHATSAPP_TEMPLATE_PROFESSIONAL_REMINDER_ES` | cliente, hora, URL panel |
| `appointment_reminder` | `professional` | `en` | `META_WHATSAPP_LANGUAGE_EN` | `professionals.timezone` | `META_WHATSAPP_TEMPLATE_PROFESSIONAL_REMINDER_EN` | customer, time, dashboard URL |

## Cuerpos sugeridos

`patient_booking_confirmed_es`: Hola {{1}}, tu cita quedó confirmada para el {{2}} a las {{3}}. Puedes ver los detalles en {{4}}. Te enviaremos un recordatorio 24 horas antes.

`patient_booking_confirmed_en`: Hi {{1}}, your appointment is confirmed for {{2}} at {{3}}. You can view details at {{4}}. We will send a reminder 24 hours before.

`professional_booking_confirmed_es`: Nueva cita con {{1}} para {{2}} el {{3}} a las {{4}}. Revisa tus citas en {{5}}. Recibirás un recordatorio 24 horas antes.

`professional_booking_confirmed_en`: New appointment with {{1}} for {{2}} on {{3}} at {{4}}. Review your appointments at {{5}}. You will receive a reminder 24 hours before.

`patient_appointment_reminder_es`: Hola {{1}}, tu cita es a las {{2}}. Ver detalles: {{3}}.

`patient_appointment_reminder_en`: Hi {{1}}, your appointment is at {{2}}. View details: {{3}}.

`professional_appointment_reminder_es`: Recordatorio: tenés una cita con {{1}} a las {{2}}. Ver panel: {{3}}.

`professional_appointment_reminder_en`: Reminder: you have an appointment with {{1}} at {{2}}. View dashboard: {{3}}.

No afirmar que una plantilla está aprobada hasta completar la revisión en Meta.

## Locales y versiones

- Cliente: `appointments.patient_locale`, persistido al confirmar la reserva.
- Profesional: `professionals.preferred_locale`, editable en `/profile`.
- El snapshot usado en cada entrega queda en `notification_deliveries.recipient_locale`.
- Recordatorios usan `event_version = appointments.reschedule_count`; al reprogramar se omiten entregas pendientes del horario anterior.

## Privacidad

Los logs no deben incluir tokens, teléfonos completos ni payloads completos. `recipient_address` queda oculto por el modelo. Los mensajes no incluyen motivos de contacto, datos sensibles, notas internas ni información privada no necesaria.
