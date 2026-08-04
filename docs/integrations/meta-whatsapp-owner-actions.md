# Meta WhatsApp Owner Actions

Checklist operativo para activar WhatsApp Cloud API. No guardar secretos en este archivo.

## Estado Actual

- Meta Business Portfolio creado/verificado: pendiente de confirmación del propietario.
- Aplicación creada: pendiente de confirmación del propietario.
- WABA vinculada: pendiente de confirmación del propietario.
- Número verificado: pendiente de confirmación del propietario.
- Phone Number ID configurado: faltante en `.env` local verificado el 2026-07-30.
- WABA ID configurado: faltante en `.env` local verificado el 2026-07-30.
- Access token configurado: faltante en `.env` local verificado el 2026-07-30.
- App Secret configurado: faltante en `.env` local verificado el 2026-07-30.
- Verify token configurado: faltante en `.env` local verificado el 2026-07-30.
- Webhook configurado: pendiente de confirmación externa en Meta; fixtures firmados locales pasan.
- Suscripciones completadas: pendiente de confirmación externa en Meta.
- Plantillas ES aprobadas: pendiente de confirmación externa en Meta.
- Plantillas EN aprobadas: pendiente de confirmación externa en Meta.
- Códigos de idioma Meta: local `es` y `en_US` verificados el 2026-07-30.
- Facturación configurada: pendiente de confirmación externa en Meta.
- Migraciones local/test ejecutadas: `2026_07_30_000000_add_whatsapp_notifications` figura `Ran` localmente.
- Migraciones productivas ejecutadas: no asumir; verificar en producción antes de activar.
- Locales históricos verificados: base local sin `patient_locale` nulos ni locales inválidos el 2026-07-30.
- Config cache regenerada: pendiente en producción después de cambiar `.env`.
- `WHATSAPP_ENABLED`: `false` local verificado el 2026-07-30.
- Autorización de envío real: no otorgada en esta verificación.

## Acciones Manuales

1. Crear/verificar Business Portfolio.
2. Crear aplicación Meta y vincular WABA.
3. Verificar número de WhatsApp Cloud API.
4. Configurar `Phone Number ID`, `WABA ID`, token, app secret y verify token en el entorno.
5. Crear y aprobar las ocho plantillas Utility documentadas en `meta-whatsapp-setup.md`.
6. Configurar webhook público HTTPS `/webhooks/meta/whatsapp`.
7. Suscribir eventos de mensajes/estados necesarios.
8. Configurar facturación de WhatsApp Business Platform.
9. Ejecutar migraciones productivas con backup previo.
10. Regenerar cache de configuración.
11. Ejecutar `deployment:check` con `WHATSAPP_ENABLED=true`.
12. Autorizar explícitamente una prueba real controlada.

## Evidencia Permitida

Registrar solo presencia o estado, por ejemplo `configured`, `missing`, `approved`, `pending`. No registrar tokens, secretos, teléfonos completos ni payloads completos.
