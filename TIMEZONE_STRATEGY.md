# Estrategia De Zonas Horarias

## Regla Principal

Guarda fechas y horas de citas en UTC.

Convierte y formatea en backend.

Usa el frontend solo para detectar y autocompletar ubicacion.

## Modelo General

```text
Hora visible + timezone IANA
=
instante UTC persistido
```

Ejemplo:

```text
09:00 America/Argentina/Buenos_Aires
=
12:00 UTC
```

La base de datos debe guardar `12:00:00 UTC`.

## Responsabilidades

| Parte | Responsabilidad |
| --- | --- |
| Frontend | Detectar timezone del navegador y sugerir pais o region. |
| Backend | Resolver timezone, validar, convertir y formatear. |
| DB | Guardar instantes UTC. |
| `TimezoneService` | Convertir horas y formatear salidas. |
| `CountryTimezoneService` | Resolver timezone IANA desde pais y region efectiva. |

## Entrada De Datos

Cuando se recibe una fecha u hora local, convierte a UTC antes de guardar.

Usa `TimezoneService::toUtc()`.

Cuando el usuario selecciona un slot generado por backend, persiste `start_utc` y `end_utc` ya validados.

## Salida De Datos

No muestres fechas UTC crudas en Blade.

Usa `TimezoneService::formatForDisplay()`.

Prioriza la timezone persistida del contexto:

1. cita publica: `appointments.patient_timezone`;
2. emails al cliente: `appointments.patient_timezone`;
3. emails al profesional: `professionals.timezone`;
4. reportes administrativos: timezone operativa configurada;
5. fallback: `UTC`.

## Disponibilidad Semanal

`Availability` guarda horas sin fecha.

Esto evita interpretar mal horarios recurrentes durante cambios de DST.

Al generar slots:

1. toma la fecha solicitada;
2. usa la timezone del profesional;
3. convierte la ventana local a UTC;
4. excluye citas activas que se solapan;
5. devuelve `start_utc`, `end_utc` y `label`.

`SlotGenerationService` debe recibir la timezone de presentacion como argumento.

No debe leer `app('user.timezone')`.

## Pais Y Region Efectiva

El cliente o profesional no debe elegir una timezone tecnica cruda si el pais permite una opcion clara.

El flujo recomendado es:

1. seleccionar pais;
2. seleccionar region horaria solo si el pais tiene mas de un offset efectivo;
3. resolver una timezone IANA con `CountryTimezoneService`;
4. guardar la timezone resuelta.

## Perfil Profesional

Cuando cambia la timezone del profesional:

- guarda la nueva timezone IANA;
- redirige a revision de disponibilidad;
- no regeneres horarios automaticamente.

La disponibilidad semanal expresa intencion operativa. No la desplaces sin confirmacion humana.

## Endpoints Criticos

Endpoints que generan horarios visibles deben recibir timezone explicita.

Ejemplo:

```text
/api/slots?professional_id=1&date=2026-06-26&duration=30&timezone=America%2FArgentina%2FBuenos_Aires
```

Valida la timezone antes de usarla.

No dependas solo de cookies.

## Cookies

La cookie `user_timezone` sirve como ayuda de UX.

No es fuente de verdad para crear o modificar citas.

Si JavaScript la escribe manualmente, guarda el valor sin codificar:

```js
document.cookie = `user_timezone=${timezone}; path=/; SameSite=Lax`;
```

En query string, usa `URLSearchParams` o `encodeURIComponent()`.

## Tests Recomendados

Incluye tests para:

- conversion entre mes y UTC;
- cambios DST;
- slots en timezone del profesional;
- labels en timezone del cliente;
- reportes por limites de mes local;
- emails por timezone del destinatario.
