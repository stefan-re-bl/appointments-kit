<?php

return [
    'section_navigation' => 'Navegación de información para clientes',
    'booking_policy_link' => 'Ver información sobre coordinación, pagos y cambios',
    'nav' => [
        'patients' => 'Para clientes',
        'payment_and_cancellation' => 'Pago y políticas',
    ],
    'faq' => [
        'view_all' => 'Ver todas las preguntas',
    ],
    'pages' => [
        'how_it_works' => [
            'meta_title' => 'Cómo funciona Appointments Kit',
            'meta_description' => 'Conoce cada paso para elegir un servicio, coordinar una cita y recibir confirmaciones.',
            'eyebrow' => 'Cómo funciona',
            'title' => 'De la consulta inicial a la cita confirmada',
            'intro' => 'La plataforma organiza servicios, profesionales, horarios y comunicaciones en un flujo simple.',
            'sections' => [
                ['title' => 'Elige un servicio', 'description' => 'Revisa las opciones disponibles y selecciona la que corresponde a tu necesidad.'],
                ['title' => 'Elige un profesional', 'description' => 'Consulta perfiles públicos cuando el directorio esté activo.'],
                ['title' => 'Selecciona fecha y horario', 'description' => 'La disponibilidad se muestra según reglas de agenda y zona horaria.'],
                ['title' => 'Confirma tus datos', 'description' => 'Completa la información necesaria para registrar la cita.'],
                ['title' => 'Recibe la confirmación', 'description' => 'El sistema envía la información de la cita por los canales habilitados.'],
                ['title' => 'Gestiona cambios permitidos', 'description' => 'La plataforma aplica reglas de cancelación y reprogramación configurables.'],
            ],
            'notice' => [
                'title' => 'La cita puede requerir preparación',
                'description' => 'Revisa el canal, el horario y las instrucciones enviadas en la confirmación.',
            ],
            'cta' => [
                'title' => '¿Quieres reservar una cita?',
                'description' => 'Inicia el flujo de reserva y completa los datos solicitados.',
                'label' => 'Reservar cita',
            ],
        ],
        'patients' => [
            'meta_title' => 'Información para clientes | Appointments Kit',
            'meta_description' => 'Información práctica para reservar, pagar, cancelar o reprogramar una cita.',
            'eyebrow' => 'Para clientes',
            'title' => 'Qué necesitás saber antes de tu sesión',
            'intro' => 'Cada negocio define sus servicios, reglas de agenda, medios de pago y canales de comunicación.',
            'sections' => [
                [
                    'title' => 'Antes de empezar',
                    'description' => 'Revisa servicios, profesionales y horarios antes de confirmar.',
                    'items' => ['Ten tus datos de contacto actualizados.', 'Verifica la zona horaria mostrada.', 'Lee las reglas de cancelación del negocio.'],
                ],
                [
                    'title' => 'Durante la reserva',
                    'description' => 'El flujo muestra los datos necesarios para registrar la cita.',
                    'items' => ['Selecciona una opción disponible.', 'Completa los campos requeridos.', 'Confirma solo si los datos son correctos.'],
                ],
                [
                    'title' => 'Confirmación',
                    'description' => 'La plataforma registra la cita y envía la información según los canales activos.',
                    'items' => ['Guarda el enlace o las instrucciones.', 'Revisa tu correo o WhatsApp si aplica.', 'Contacta al negocio si necesitas ayuda.'],
                ],
                [
                    'title' => 'Cambios posteriores',
                    'description' => 'La cancelación y la reprogramación dependen de las reglas del negocio.',
                    'items' => ['Respeta los plazos configurados.', 'Usa los enlaces públicos si están habilitados.', 'Coordina excepciones con el negocio.'],
                ],
            ],
            'notice' => [
                'title' => 'Servicio sujeto a disponibilidad',
                'description' => 'La disponibilidad depende de cada profesional, servicio y configuración del negocio.',
            ],
            'cta' => [
                'title' => 'Reserva cuando encuentres un horario disponible',
                'description' => 'Elige el servicio, confirma tus datos y guarda la información enviada.',
                'label' => 'Reservar cita',
            ],
        ],
        'payment_and_cancellation' => [
            'meta_title' => 'Pago, cancelación y reprogramación | Appointments Kit',
            'meta_description' => 'Conoce cómo se coordinan pagos, cancelaciones y cambios de cita.',
            'eyebrow' => 'Pago y políticas',
            'title' => 'Coordinación clara antes de comenzar',
            'intro' => 'La plataforma permite seguimiento de pagos manuales. No procesa cobros dentro del sistema.',
            'sections' => [
                [
                    'title' => 'Pago manual',
                    'description' => 'El negocio define el medio de pago fuera de la plataforma.',
                    'items' => ['La plataforma no almacena tarjetas.', 'La administración puede registrar estados de pago.', 'El comprobante o acuerdo se gestiona fuera del sistema.'],
                ],
                [
                    'title' => 'Cancelación con derecho a reembolso',
                    'description' => 'La cancelación del turno con derecho a reembolso puede solicitarse hasta 24 horas antes del horario acordado.',
                    'items' => ['Pasado ese plazo, la cita puede no ser reembolsable.', 'Cualquier devolución se coordina fuera de la plataforma.', 'El acuerdo operativo queda entre cliente y negocio.'],
                ],
                [
                    'title' => 'Horarios',
                    'description' => 'Los horarios dependen de la disponibilidad del profesional y la zona horaria.',
                    'items' => ['La plataforma muestra horarios disponibles.', 'El profesional puede administrar su agenda.', 'Los cambios se rigen por la política configurada.'],
                ],
                [
                    'title' => 'Modificación de fecha u horario',
                    'description' => 'La modificación de fecha u horario puede solicitarse hasta 48 horas antes del turno.',
                    'items' => ['Pasado ese plazo, el cambio puede quedar bloqueado.', 'Cualquier excepción queda sujeta al acuerdo del negocio.', 'Usa el canal indicado para coordinar cambios.'],
                ],
                [
                    'title' => 'Registro operativo',
                    'description' => 'La plataforma puede usarse para citas públicas o cargadas por el equipo.',
                    'items' => ['Los datos operativos se usan para confirmar citas.', 'La administración puede revisar estados.', 'Cada negocio define su proceso interno.'],
                ],
            ],
            'notice' => [
                'title' => 'Política de pago y reembolso',
                'description' => 'La cancelación con derecho a reembolso puede solicitarse hasta 24 horas antes del horario acordado. La reprogramación puede solicitarse hasta 48 horas antes de la cita. Pasados esos plazos, cualquier excepción queda sujeta al acuerdo del negocio.',
            ],
            'cta' => [
                'title' => '¿Quieres reservar una cita?',
                'description' => 'Elige un servicio y revisa las condiciones antes de confirmar.',
                'label' => 'Reservar cita',
            ],
        ],
        'faq' => [
            'meta_title' => 'Preguntas frecuentes | Appointments Kit',
            'meta_description' => 'Respuestas sobre reservas, profesionales, pagos y cambios de horario.',
            'eyebrow' => 'Preguntas frecuentes',
            'title' => 'Respuestas antes de empezar',
            'intro' => 'Estas respuestas describen el proceso general de reserva.',
            'sections' => [
                ['title' => '¿Qué hace la plataforma?', 'description' => 'Organiza servicios, profesionales, horarios, reservas y comunicaciones.'],
                ['title' => '¿Cómo pido una cita?', 'description' => 'Usa el flujo de reserva y completa los datos solicitados.'],
                ['title' => '¿Cómo se define el profesional?', 'description' => 'El cliente elige un profesional disponible o el negocio lo asigna según su proceso.'],
                ['title' => '¿Puedo conocer a los profesionales?', 'description' => 'Sí. Puedes revisar perfiles públicos si el directorio está habilitado.'],
                ['title' => '¿Cómo se coordina el horario?', 'description' => 'El horario se toma desde la disponibilidad configurada.'],
                ['title' => '¿El pago se realiza en la plataforma?', 'description' => 'No. La plataforma registra pagos manuales, pero no procesa cobros.'],
                ['title' => '¿Las citas son online?', 'description' => 'Cada negocio define si sus citas son online, presenciales o mixtas.'],
                ['title' => '¿Qué hago si necesito cambiar una cita?', 'description' => 'Usa la reprogramación pública si está habilitada o contacta al negocio.'],
                ['title' => '¿Qué hago ante una urgencia?', 'description' => 'Usa los canales de emergencia correspondientes al servicio y a tu localidad.'],
            ],
            'notice' => [
                'title' => '¿No encontraste tu respuesta?',
                'description' => 'Usa el formulario de contacto o el correo indicado en el pie del sitio.',
            ],
            'cta' => [
                'title' => '¿Quieres empezar?',
                'description' => 'Revisa los servicios disponibles y reserva una cita.',
                'label' => 'Reservar cita',
            ],
        ],
    ],
];
