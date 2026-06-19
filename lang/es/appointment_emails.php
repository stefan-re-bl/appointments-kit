<?php

declare(strict_types=1);

return [
    'common' => [
        'greeting' => 'Hola :name,',
        'patient' => 'Paciente',
        'therapist' => 'Terapeuta',
        'session_type' => 'Tipo de sesión',
        'timezone' => 'Zona horaria',
        'view_appointment' => 'Ver cita',
        'thanks' => 'Gracias',
        'time_range' => ':start - :end',
    ],

    'rescheduled' => [
        'subject' => 'Tu cita fue reprogramada',
        'title' => 'Cita reprogramada',
        'intro' => 'Te avisamos que la cita fue reprogramada.',
        'previous_time' => 'Horario anterior',
        'new_time' => 'Nuevo horario',
        'footer' => 'Los horarios se muestran en la zona horaria indicada.',
    ],

    'cancelled' => [
        'subject' => 'Tu cita fue cancelada',
        'title' => 'Cita cancelada',
        'intro' => 'Te avisamos que la cita fue cancelada.',
        'appointment_time' => 'Horario de la cita',
        'footer' => 'Si necesitás coordinar una nueva cita, podés iniciar una nueva reserva.',
    ],
];