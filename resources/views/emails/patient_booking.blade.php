<x-mail::message>
# {{ __('app.mail_patient_subject') }}

Hola {{ $appointment->patient_name }},

Tu cita ha sido confirmada exitosamente. Te hemos enviado los detalles a tu correo.

**Detalles:**
- Fecha y Hora: {{ $localDate }}

@if(config('app.name'))
<x-mail::button :url="url('/dashboard')">
Ir al Panel
</x-mail::button>
@endif

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
