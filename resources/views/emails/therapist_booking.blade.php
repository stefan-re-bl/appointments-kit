<x-mail::message>
# {{ __('app.mail_therapist_subject') }}

Tienes una nueva solicitud de reserva.

**Paciente:** {{ $patientName }}
**Fecha y Hora:** {{ $localDate }}

Por favor, ponte en contacto con el paciente para coordinar el pago.

@if(config('app.name'))
<x-mail::button :url="url('/dashboard')">
Ir al Panel de Terapeuta
</x-mail::button>
@endif

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
