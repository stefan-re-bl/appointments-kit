# Meta WhatsApp Deployment

## Precondiciones

- Backup reciente de base de datos.
- Código desplegado con migración `2026_07_30_000000_add_whatsapp_notifications`.
- Worker de cola operativo.
- Scheduler ejecutando `appointments:send-reminders` cada minuto.
- Plantillas Meta aprobadas.
- Webhook HTTPS configurado.

## Orden Seguro

1. Desplegar código con `WHATSAPP_ENABLED=false`.
2. Ejecutar migraciones.
3. Verificar `migrate:status`.
4. Configurar variables `META_WHATSAPP_*` sin activar aún.
5. Regenerar cache de configuración.
6. Ejecutar `deployment:check`.
7. Activar `WHATSAPP_ENABLED=true`.
8. Regenerar cache de configuración otra vez.
9. Ejecutar `deployment:check --profile=database --url=https://dominio` en Hostinger o `deployment:check --url=https://dominio` en VPS/Redis.
10. Probar webhook con verify token desde Meta.
11. Ejecutar prueba real solo con autorización.

## Hostinger Business

Usar el perfil `database`:

```bash
php artisan deployment:check --profile=database --url=https://dominio
```

Confirmar que `SCHEDULE_QUEUE_WORKER=true` procese la cola corta desde scheduler. No usar `DatabaseSeeder` en producción.

## Rollback

Si WhatsApp falla:

1. Cambiar `WHATSAPP_ENABLED=false`.
2. Regenerar cache de configuración.
3. Mantener emails y reservas operativos.
4. No borrar `notification_deliveries`; conserva auditoría.

Solo revertir migraciones con autorización explícita y backup validado.

## Verificaciones Sin Secretos

- `WHATSAPP_ENABLED`: `true` o `false`.
- Credenciales: `configured` o `missing`.
- Plantillas: nombre configurado o `missing`, sin imprimir tokens.
- Webhook: respuesta GET challenge y POST firmado.
- Cola: jobs procesados sin entradas nuevas en `failed_jobs`.
