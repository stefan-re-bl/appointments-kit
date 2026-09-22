# Onboarding De Cliente

Usa este documento para implementar una instancia nueva para un cliente.

## Modelo De Instancia

```text
1 codigo base
1 deployment por cliente
1 base de datos por cliente
1 .env por cliente
1 set de assets por cliente
```

No uses multitenancy en esta fase.

## Informacion Que Debe Entregar El Cliente

### Marca

- Nombre comercial.
- Logo.
- Favicon.
- Colores principales.
- Imagen principal.
- Video introductorio, si aplica.
- Redes sociales.
- Numero de WhatsApp publico.

### Operacion

- Lista de profesionales.
- Email de cada profesional.
- Zona horaria de cada profesional.
- Servicios.
- Duracion de servicios.
- Precio interno y moneda.
- Disponibilidad semanal.
- Reglas de cancelacion.
- Reglas de reprogramacion.
- Politica de pago.

### Comunicaciones

- Cuenta SMTP.
- Remitente aprobado.
- Credenciales Meta WhatsApp, si aplica.
- Plantillas WhatsApp aprobadas, si aplica.
- Textos legales propios.
- Textos de emergencia si el rubro lo requiere.

## Implementacion

1. Crear base de datos.
2. Crear `.env` desde `.env.production.example`.
3. Configurar `APP_*`.
4. Configurar `DB_*`.
5. Configurar `BRANDING_*`.
6. Configurar `TERM_*`.
7. Configurar `BOOKING_*`.
8. Configurar `FEATURE_*`.
9. Configurar email.
10. Configurar WhatsApp si aplica.
11. Subir assets.
12. Ejecutar migraciones.
13. Crear admin inicial.
14. Cargar profesionales.
15. Cargar servicios.
16. Cargar disponibilidad.
17. Probar cita completa.
18. Probar recordatorios.
19. Probar cancelacion y reprogramacion.
20. Ejecutar `deployment:check`.

## Personalizacion Permitida

Usa configuracion para:

- marca;
- assets;
- terminologia;
- reglas de booking;
- funciones activas;
- correo;
- WhatsApp;
- links publicos;
- datos de profesionales;
- servicios;
- disponibilidad.

Usa traducciones para textos visibles.

## Cambios Que Requieren Desarrollo

Requieren codigo y tests:

- nuevos estados de cita;
- nuevo proveedor de pago;
- nuevo canal de notificacion;
- reglas de disponibilidad diferentes;
- cambios en permisos;
- cambio de modelo de datos;
- multitenancy;
- integraciones externas nuevas.

## Validacion Funcional

Antes de entregar:

```bash
npm run build
php artisan migrate --force
php artisan test --filter=CriticalBookingFlowTest
php artisan test --filter=SendAppointmentRemindersCommandTest
php artisan deployment:check --url=https://dominio
```

En desarrollo con Sail:

```bash
npm run build
./vendor/bin/sail artisan test
```

## Checklist De Entrega

- Home muestra marca correcta.
- Logo y favicon cargan.
- Links publicos funcionan.
- Perfil publico de profesionales carga.
- Admin puede aprobar profesionales.
- Profesional puede ver su agenda.
- Cita interna puede crearse.
- Cliente recibe email de confirmacion.
- Pagos manuales pueden actualizarse.
- Reportes muestran totales.
- Reprogramacion funciona.
- Cancelacion funciona.
- Recordatorios se encolan.
- WhatsApp funciona o queda desactivado sin errores.
- `deployment:check` pasa.
- Hay backup inicial.
- Credenciales reales no estan commiteadas.

## Compatibilidad Legacy

El kit conserva algunos redirects y alias antiguos.

Solo mantenlos si el cliente migra desde una instalacion previa.

En clientes nuevos, puedes planificar su eliminacion en un ticket especifico.
