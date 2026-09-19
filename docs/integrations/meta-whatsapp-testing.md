# Meta WhatsApp Testing

## Local Sin Meta

Con `WHATSAPP_ENABLED=false`:

- Reservas funcionan.
- Emails funcionan.
- Scheduler funciona.
- `deployment:check` no exige credenciales Meta.
- No se crean entregas WhatsApp.

## Local Con Mocks

Usar tests con fakes:

```bash
sail artisan test --filter='WhatsAppNotificationTest|ProfileTest|CriticalBookingFlowTest|SendAppointmentRemindersCommandTest|DeploymentCheckCommandTest'
```

Estos tests no verifican entrega real de Meta. Verifican:

- Persistencia de locale del cliente.
- Preferencia persistente de locale de profesional.
- Selección de plantillas por destinatario/locale.
- Timezone independiente por destinatario.
- Webhook firmado con fixtures.
- Reintentos recuperables.

Última verificación local registrada el 2026-07-30:

- Suite completa: 169 tests, 969 assertions.
- Focalizados WhatsApp/perfil/booking/recordatorios/deployment: 49 tests, 235 assertions.
- Fixtures webhook firmados: cubiertos por `WhatsAppNotificationTest`.

## Fixtures Webhook

Los fixtures viven en `tests/Fixtures/meta-whatsapp`:

- `delivered.json`
- `read.json`
- `failed.json`

La firma se calcula en test con `hash_hmac('sha256', $body, META_APP_SECRET_DE_PRUEBA)` y se envía como `X-Hub-Signature-256`.

## Pruebas Reales

No ejecutar llamadas reales a Meta sin autorización explícita del propietario.

Para una prueba real controlada registrar:

- ID interno de `notification_deliveries`.
- Evento.
- Destinatario lógico (`patient` o `professional`, identificadores internos heredados).
- Locale interno.
- Código de idioma Meta.
- Nombre de plantilla.
- Timezone usada.
- Estado reportado por Meta.

No registrar teléfonos completos, access tokens, app secret ni payloads completos.
