<?php

return [
    'section_navigation' => 'Navegación de información para pacientes',
    'booking_policy_link' => 'Ver información sobre pago, cancelación y reprogramación',
    'nav' => [
        'patients' => 'Para pacientes',
        'payment_and_cancellation' => 'Pago y políticas',
    ],
    'faq' => [
        'view_all' => 'Ver todas las preguntas',
    ],
    'pages' => [
        'how_it_works' => [
            'meta_title' => 'Cómo funciona Umbralia',
            'meta_description' => 'Conocé cada paso para elegir terapeuta, reservar una sesión online y gestionar tu cita.',
            'eyebrow' => 'Cómo funciona',
            'title' => 'De la elección de terapeuta a tu sesión online',
            'intro' => 'Umbralia organiza la reserva y las comunicaciones de tu cita. La atención profesional y el pago se coordinan directamente con la terapeuta.',
            'sections' => [
                ['title' => 'Elegí una terapeuta', 'description' => 'Ingresá al flujo de reserva y revisá las profesionales activas disponibles antes de avanzar.'],
                ['title' => 'Elegí el tipo de sesión', 'description' => 'Cada terapeuta configura sus consultas, duración, precio y moneda. Seleccioná la opción adecuada para vos.'],
                ['title' => 'Seleccioná fecha y horario', 'description' => 'El sistema muestra disponibilidad real y convierte los horarios a la zona horaria detectada en tu dispositivo.'],
                ['title' => 'Confirmá tus datos', 'description' => 'Ingresá tu nombre y correo. Antes de reservar vas a ver terapeuta, fecha, horario, duración y precio.'],
                ['title' => 'Recibí la confirmación', 'description' => 'La cita queda confirmada y recibís el enlace a “Mi Cita”, el acceso online y la información operativa.'],
                ['title' => 'Coordiná el pago', 'description' => 'Umbralia no cobra dentro de la plataforma. La terapeuta acuerda el pago con vos y luego registra su estado.'],
            ],
            'notice' => [
                'title' => 'La sesión se realiza online',
                'description' => 'Usá un espacio privado, una conexión estable y el enlace informado en el correo o en “Mi Cita”.',
            ],
            'cta' => [
                'title' => '¿Querés buscar un horario?',
                'description' => 'Revisá terapeutas, tipos de sesión y disponibilidad antes de confirmar.',
                'label' => 'Comenzar reserva',
            ],
        ],
        'patients' => [
            'meta_title' => 'Información para pacientes | Umbralia',
            'meta_description' => 'Información práctica para preparar, reservar y gestionar una sesión psicológica online.',
            'eyebrow' => 'Para pacientes',
            'title' => 'Qué necesitás saber antes de tu sesión',
            'intro' => 'La plataforma simplifica la gestión del turno. La relación terapéutica, las indicaciones clínicas y los acuerdos de pago corresponden a la profesional elegida.',
            'sections' => [
                [
                    'title' => 'Antes de reservar',
                    'description' => 'Revisá el tipo de sesión, duración, precio y disponibilidad. Los horarios se muestran en tu zona horaria.',
                    'items' => ['Usá un correo al que tengas acceso.', 'Confirmá la fecha y hora.', 'Recordá que el pago se coordina externamente.'],
                ],
                [
                    'title' => 'Después de confirmar',
                    'description' => 'Vas a recibir un correo con los datos de la cita y un enlace personal a “Mi Cita”.',
                    'items' => ['Guardá el correo de confirmación.', 'No compartas el enlace personal.', 'Revisá el estado del pago y el enlace de videollamada.'],
                ],
                [
                    'title' => 'Para la videollamada',
                    'description' => 'Prepará un entorno donde puedas conversar con privacidad y sin interrupciones.',
                    'items' => ['Probá cámara, micrófono y conexión.', 'Ingresá unos minutos antes.', 'Contactá a la terapeuta si el enlace no funciona.'],
                ],
                [
                    'title' => 'Gestión de tu cita',
                    'description' => 'Desde “Mi Cita” podés consultar detalles y ver acciones según el estado y la anticipación.',
                    'items' => ['Reprogramación con al menos 24 horas.', 'Cancelación autoservicio con al menos 48 horas.', 'Máximo de dos reprogramaciones por cita.'],
                ],
            ],
            'notice' => [
                'title' => 'No es un servicio de emergencias',
                'description' => 'Umbralia no brinda atención inmediata de crisis. Ante riesgo inmediato, contactá a los servicios de emergencia de tu localidad.',
            ],
            'cta' => [
                'title' => 'Reservá cuando estés lista o listo',
                'description' => 'El flujo te permite revisar toda la información antes de confirmar.',
                'label' => 'Ver terapeutas',
            ],
        ],
        'payment_and_cancellation' => [
            'meta_title' => 'Pago, cancelación y reprogramación | Umbralia',
            'meta_description' => 'Conocé cómo se coordinan los pagos y las reglas de cancelación y reprogramación.',
            'eyebrow' => 'Pago y políticas',
            'title' => 'Reglas claras para gestionar tu cita',
            'intro' => 'Umbralia registra turnos y estados de pago, pero no procesa cobros ni devoluciones dentro de la plataforma.',
            'sections' => [
                [
                    'title' => 'Pago fuera de Umbralia',
                    'description' => 'Después de reservar, la terapeuta coordina directamente con vos el método y las condiciones de pago.',
                    'items' => ['La cita se confirma aunque el pago figure pendiente.', 'La terapeuta actualiza manualmente el estado.', 'Umbralia no almacena tarjetas ni procesa transferencias.'],
                ],
                [
                    'title' => 'Cancelación con 48 horas',
                    'description' => 'Podés cancelar desde “Mi Cita” cuando falten al menos 48 horas y la cita siga activa.',
                    'items' => ['Sin pago registrado no existe devolución pendiente.', 'Con pago registrado, cualquier devolución se coordina manualmente con la terapeuta.'],
                ],
                [
                    'title' => 'Reprogramación con 24 horas',
                    'description' => 'Podés elegir otro horario disponible cuando falten al menos 24 horas.',
                    'items' => ['Se mantienen tipo de sesión, precio y estado del pago.', 'Cada cita admite como máximo dos reprogramaciones.', 'Los enlaces firmados son personales y vencen en 24 horas.'],
                ],
                [
                    'title' => 'Fuera de los plazos',
                    'description' => 'Si faltan menos de 24 horas o alcanzaste el límite, las opciones automáticas dejan de estar disponibles.',
                    'items' => ['Contactá directamente a la terapeuta.', 'La plataforma no promete devoluciones fuera de la política.', 'Las excepciones se coordinan con la profesional.'],
                ],
            ],
            'notice' => [
                'title' => 'Las devoluciones no son automáticas',
                'description' => 'Aunque corresponda coordinar una devolución, el movimiento de dinero ocurre fuera de Umbralia.',
            ],
            'cta' => [
                'title' => 'Revisá las condiciones antes de reservar',
                'description' => 'El resumen final muestra precio, duración y la nota de coordinación de pago.',
                'label' => 'Buscar una sesión',
            ],
        ],
        'faq' => [
            'meta_title' => 'Preguntas frecuentes | Umbralia',
            'meta_description' => 'Respuestas sobre sesiones online, reservas, pagos, horarios, cancelaciones y reprogramaciones.',
            'eyebrow' => 'Preguntas frecuentes',
            'title' => 'Respuestas antes de reservar',
            'intro' => 'Estas respuestas describen el funcionamiento operativo de Umbralia. Para cuestiones clínicas o acuerdos particulares, consultá a la terapeuta.',
            'sections' => [
                ['title' => '¿Umbralia brinda terapia?', 'description' => 'Umbralia facilita la gestión del turno. La atención psicológica es brindada por la terapeuta elegida.'],
                ['title' => '¿Las sesiones son online?', 'description' => 'Sí. El enlace aparece en el correo de confirmación y en “Mi Cita”, cuando está disponible.'],
                ['title' => '¿Cómo elijo terapeuta?', 'description' => 'El flujo muestra profesionales activas y luego permite elegir sesión, fecha y horario.'],
                ['title' => '¿Los horarios están en mi zona horaria?', 'description' => 'Sí. El sistema detecta la zona del dispositivo y convierte los horarios automáticamente.'],
                ['title' => '¿El pago se realiza en Umbralia?', 'description' => 'No. Se coordina directamente con la terapeuta y ella registra manualmente su estado.'],
                ['title' => '¿Cuándo puedo cancelar?', 'description' => 'La cancelación autoservicio está disponible con al menos 48 horas para citas activas.'],
                ['title' => '¿Cuándo puedo reprogramar?', 'description' => 'Con al menos 24 horas y un máximo de dos cambios por cita.'],
                ['title' => '¿Qué hago si no recibo el correo?', 'description' => 'Revisá spam. Si continúa, contactá a la terapeuta o a la administración.'],
                ['title' => '¿Qué hago ante una emergencia?', 'description' => 'No uses Umbralia para crisis inmediatas. Contactá a los servicios de emergencia locales.'],
            ],
            'notice' => [
                'title' => '¿No encontraste tu respuesta?',
                'description' => 'Mientras se implementa el formulario de soporte, escribí al correo indicado en el pie del sitio.',
            ],
            'cta' => [
                'title' => '¿Tenés la información necesaria?',
                'description' => 'Iniciá la reserva y revisá disponibilidad sin compromiso.',
                'label' => 'Comenzar reserva',
            ],
        ],
    ],
];
