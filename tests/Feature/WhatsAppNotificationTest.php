<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationEvent;
use App\Enums\NotificationRecipientType;
use App\Jobs\Notifications\ProcessMetaWhatsAppWebhook;
use App\Jobs\Notifications\SendWhatsAppTemplateMessage;
use App\Models\Appointment;
use App\Models\NotificationDelivery;
use App\Models\Professional;
use App\Models\SessionType;
use App\Models\User;
use App\Services\Notifications\PhoneNumberNormalizer;
use App\Services\Notifications\RecipientLocaleResolver;
use App\Services\Notifications\WhatsAppDeliveryDispatcher;
use App\Services\Notifications\WhatsAppGateway;
use App\Services\Notifications\WhatsAppSendResult;
use App\Services\Notifications\WhatsAppTemplate;
use App\Services\Notifications\WhatsAppTemplateRegistry;
use App\Services\Notifications\WhatsAppVariableBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

final class WhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-30 12:00:00', 'UTC'));

        config([
            'features.whatsapp' => true,
            'services.meta_whatsapp.enabled' => true,
            'services.meta_whatsapp.phone_number_id' => '123456',
            'services.meta_whatsapp.access_token' => 'test-token',
            'services.meta_whatsapp.language_codes.es' => 'es',
            'services.meta_whatsapp.language_codes.en' => 'en_US',
            'services.meta_whatsapp.templates.customer_confirmation_es' => 'customer_booking_confirmed_es',
            'services.meta_whatsapp.templates.customer_confirmation_en' => 'customer_booking_confirmed_en',
            'services.meta_whatsapp.templates.professional_confirmation_es' => 'professional_booking_confirmed_es',
            'services.meta_whatsapp.templates.professional_confirmation_en' => 'professional_booking_confirmed_en',
            'services.meta_whatsapp.templates.customer_reminder_es' => 'customer_appointment_reminder_es',
            'services.meta_whatsapp.templates.customer_reminder_en' => 'customer_appointment_reminder_en',
            'services.meta_whatsapp.templates.professional_reminder_es' => 'professional_appointment_reminder_es',
            'services.meta_whatsapp.templates.professional_reminder_en' => 'professional_appointment_reminder_en',
        ]);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_booking_confirmation_creates_independent_customer_and_professional_deliveries_with_locale_snapshots(): void
    {
        Queue::fake();

        $appointment = $this->makeAppointment([
            'patient_locale' => 'en',
            'patient_phone' => '+14155552671',
            'patient_whatsapp_opt_in_at' => now('UTC'),
        ], [
            'preferred_locale' => 'es',
            'whatsapp_phone' => '+5491123456789',
            'whatsapp_notifications_enabled' => true,
        ]);

        app(WhatsAppDeliveryDispatcher::class)->dispatchBookingConfirmed($appointment);

        $this->assertDatabaseHas('notification_deliveries', [
            'appointment_id' => $appointment->id,
            'event' => NotificationEvent::BOOKING_CONFIRMED->value,
            'recipient_type' => NotificationRecipientType::CUSTOMER->value,
            'recipient_locale' => 'en',
            'status' => NotificationDeliveryStatus::QUEUED->value,
        ]);

        $this->assertDatabaseHas('notification_deliveries', [
            'appointment_id' => $appointment->id,
            'event' => NotificationEvent::BOOKING_CONFIRMED->value,
            'recipient_type' => NotificationRecipientType::PROFESSIONAL->value,
            'recipient_locale' => 'es',
            'status' => NotificationDeliveryStatus::QUEUED->value,
        ]);

        Queue::assertPushed(SendWhatsAppTemplateMessage::class, 2);
    }

    public function test_whatsapp_disabled_does_not_create_deliveries(): void
    {
        config(['services.meta_whatsapp.enabled' => false]);
        Queue::fake();

        $appointment = $this->makeAppointment([
            'patient_phone' => '+14155552671',
            'patient_whatsapp_opt_in_at' => now('UTC'),
        ], [
            'whatsapp_phone' => '+5491123456789',
            'whatsapp_notifications_enabled' => true,
        ]);

        app(WhatsAppDeliveryDispatcher::class)->dispatchBookingConfirmed($appointment);

        $this->assertDatabaseCount('notification_deliveries', 0);
        Queue::assertNothingPushed();
    }

    public function test_whatsapp_feature_flag_disabled_does_not_create_deliveries(): void
    {
        config(['features.whatsapp' => false]);
        Queue::fake();

        $appointment = $this->makeAppointment([
            'patient_phone' => '+14155552671',
            'patient_whatsapp_opt_in_at' => now('UTC'),
        ], [
            'whatsapp_phone' => '+5491123456789',
            'whatsapp_notifications_enabled' => true,
        ]);

        app(WhatsAppDeliveryDispatcher::class)->dispatchBookingConfirmed($appointment);

        $this->assertDatabaseCount('notification_deliveries', 0);
        Queue::assertNothingPushed();
    }

    public function test_phone_normalizer_accepts_e164_and_rejects_invalid_numbers(): void
    {
        $normalizer = app(PhoneNumberNormalizer::class);

        $this->assertSame('+14155552671', $normalizer->normalize('+1 415 555 2671'));
        $this->assertNull($normalizer->normalize('123'));
    }

    public function test_template_job_uses_snapshot_locale_and_restores_global_locale_between_deliveries(): void
    {
        $sentTemplates = new class
        {
            /**
             * @var array<int, array{phone: string, template: string, language: string}>
             */
            public array $items = [];
        };
        app()->setLocale('es');

        $this->app->instance(WhatsAppGateway::class, new class($sentTemplates) implements WhatsAppGateway
        {
            public function __construct(private readonly object $sentTemplates) {}

            public function sendTemplate(string $recipientPhone, WhatsAppTemplate $template): WhatsAppSendResult
            {
                $this->sentTemplates->items[] = [
                    'phone' => $recipientPhone,
                    'template' => $template->name,
                    'language' => $template->metaLanguageCode,
                ];

                return new WhatsAppSendResult(true, 'wamid.test.'.count($this->sentTemplates->items));
            }
        });

        $patientEnglish = $this->makeDelivery(
            $this->makeAppointment([
                'patient_locale' => 'en',
                'patient_phone' => '+14155552671',
                'patient_whatsapp_opt_in_at' => now('UTC'),
            ]),
            NotificationRecipientType::CUSTOMER,
            'en',
        );
        $professionalSpanish = $this->makeDelivery(
            $this->makeAppointment([], [
                'preferred_locale' => 'es',
                'whatsapp_phone' => '+5491123456789',
                'whatsapp_notifications_enabled' => true,
            ]),
            NotificationRecipientType::PROFESSIONAL,
            'es',
        );

        (new SendWhatsAppTemplateMessage($patientEnglish->id))->handle(
            app(RecipientLocaleResolver::class),
            app(WhatsAppVariableBuilder::class),
            app(WhatsAppTemplateRegistry::class),
            app(WhatsAppGateway::class),
        );
        (new SendWhatsAppTemplateMessage($professionalSpanish->id))->handle(
            app(RecipientLocaleResolver::class),
            app(WhatsAppVariableBuilder::class),
            app(WhatsAppTemplateRegistry::class),
            app(WhatsAppGateway::class),
        );

        $this->assertSame('es', app()->getLocale());
        $this->assertSame('customer_booking_confirmed_en', $sentTemplates->items[0]['template']);
        $this->assertSame('en_US', $sentTemplates->items[0]['language']);
        $this->assertSame('professional_booking_confirmed_es', $sentTemplates->items[1]['template']);
        $this->assertSame('es', $sentTemplates->items[1]['language']);
    }

    public function test_retryable_gateway_error_keeps_delivery_queued_until_queue_retries(): void
    {
        $this->app->instance(WhatsAppGateway::class, new class implements WhatsAppGateway
        {
            public function sendTemplate(string $recipientPhone, WhatsAppTemplate $template): WhatsAppSendResult
            {
                return new WhatsAppSendResult(
                    successful: false,
                    errorCode: 'http_429',
                    errorMessage: 'Rate limited.',
                    retryable: true,
                );
            }
        });

        $delivery = $this->makeDelivery(
            $this->makeAppointment([
                'patient_phone' => '+14155552671',
                'patient_whatsapp_opt_in_at' => now('UTC'),
            ]),
            NotificationRecipientType::CUSTOMER,
            'es',
        );

        $this->expectException(RuntimeException::class);

        try {
            (new SendWhatsAppTemplateMessage($delivery->id))->handle(
                app(RecipientLocaleResolver::class),
                app(WhatsAppVariableBuilder::class),
                app(WhatsAppTemplateRegistry::class),
                app(WhatsAppGateway::class),
            );
        } finally {
            $delivery->refresh();

            $this->assertSame(NotificationDeliveryStatus::QUEUED, $delivery->status);
            $this->assertSame('http_429', $delivery->last_error_code);
            $this->assertNull($delivery->failed_at);
            $this->assertSame(1, $delivery->attempts);
        }
    }

    public function test_meta_webhook_requires_valid_signature_and_updates_delivery_status(): void
    {
        Queue::fake();

        config(['services.meta_whatsapp.app_secret' => 'secret']);

        $delivery = $this->makeDelivery($this->makeAppointment(), NotificationRecipientType::CUSTOMER, 'es');
        $delivery->forceFill([
            'provider_message_id' => 'wamid.test',
            'status' => NotificationDeliveryStatus::SUBMITTED,
        ])->save();

        $payload = [
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'statuses' => [[
                            'id' => 'wamid.test',
                            'status' => 'delivered',
                        ]],
                    ],
                ]],
            ]],
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = 'sha256='.hash_hmac('sha256', $body, 'secret');

        $this
            ->postJson(route('webhooks.meta-whatsapp.receive'), $payload, ['X-Hub-Signature-256' => $signature])
            ->assertOk();

        Queue::assertPushed(ProcessMetaWhatsAppWebhook::class);

        (new ProcessMetaWhatsAppWebhook($payload))->handle();

        $this->assertSame(NotificationDeliveryStatus::DELIVERED, $delivery->refresh()->status);

        $this
            ->postJson(route('webhooks.meta-whatsapp.receive'), $payload, ['X-Hub-Signature-256' => 'sha256=bad'])
            ->assertForbidden();
    }

    public function test_signed_webhook_fixtures_update_delivery_states(): void
    {
        config(['services.meta_whatsapp.app_secret' => 'fixture-secret']);

        $appointment = $this->makeAppointment();
        $delivered = $this->makeDelivery($appointment, NotificationRecipientType::CUSTOMER, 'es');
        $read = $this->makeDelivery(
            $this->makeAppointment(['reschedule_count' => 1]),
            NotificationRecipientType::CUSTOMER,
            'es',
        );
        $failed = $this->makeDelivery(
            $this->makeAppointment(['reschedule_count' => 2]),
            NotificationRecipientType::CUSTOMER,
            'es',
        );

        $delivered->forceFill([
            'provider_message_id' => 'wamid.fixture.delivered',
            'status' => NotificationDeliveryStatus::SUBMITTED,
        ])->save();
        $read->forceFill([
            'provider_message_id' => 'wamid.fixture.read',
            'status' => NotificationDeliveryStatus::DELIVERED,
        ])->save();
        $failed->forceFill([
            'provider_message_id' => 'wamid.fixture.failed',
            'status' => NotificationDeliveryStatus::SUBMITTED,
        ])->save();

        foreach (['delivered', 'read', 'failed'] as $fixture) {
            $payload = $this->fixturePayload($fixture);
            $body = json_encode($payload, JSON_THROW_ON_ERROR);
            $signature = 'sha256='.hash_hmac('sha256', $body, 'fixture-secret');

            $this
                ->postJson(route('webhooks.meta-whatsapp.receive'), $payload, ['X-Hub-Signature-256' => $signature])
                ->assertOk();

            (new ProcessMetaWhatsAppWebhook($payload))->handle();
        }

        $this->assertSame(NotificationDeliveryStatus::DELIVERED, $delivered->refresh()->status);
        $this->assertSame(NotificationDeliveryStatus::READ, $read->refresh()->status);
        $this->assertSame(NotificationDeliveryStatus::FAILED, $failed->refresh()->status);
        $this->assertSame('131026', $failed->last_error_code);
    }

    /**
     * @param  array<string, mixed>  $appointmentOverrides
     * @param  array<string, mixed>  $professionalOverrides
     */
    private function makeAppointment(array $appointmentOverrides = [], array $professionalOverrides = []): Appointment
    {
        $user = User::factory()->create();
        $professional = Professional::factory()->for($user)->create(array_merge([
            'timezone' => 'America/Argentina/Buenos_Aires',
            'is_active' => true,
            'is_approved' => true,
            'google_meet_link' => 'https://meet.google.com/test-link',
        ], $professionalOverrides));
        $sessionType = SessionType::factory()->for($professional)->create([
            'name' => 'Servicio inicial',
            'duration_minutes' => 60,
            'price' => 100,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        return Appointment::factory()
            ->for($professional)
            ->for($sessionType, 'sessionType')
            ->create(array_merge([
                'status' => AppointmentStatus::CONFIRMED->value,
                'starts_at' => '2026-08-01 15:00:00',
                'ends_at' => '2026-08-01 16:00:00',
                'patient_name' => 'Customer Test',
                'patient_email' => 'patient@example.test',
                'patient_timezone' => 'America/New_York',
                'patient_locale' => 'es',
            ], $appointmentOverrides));
    }

    private function makeDelivery(Appointment $appointment, NotificationRecipientType $recipientType, string $locale): NotificationDelivery
    {
        $appointment->loadMissing(['professional.user', 'sessionType']);

        return NotificationDelivery::query()->create([
            'appointment_id' => $appointment->id,
            'event' => NotificationEvent::BOOKING_CONFIRMED->value,
            'channel' => 'whatsapp',
            'recipient_type' => $recipientType->value,
            'recipient_address' => $recipientType === NotificationRecipientType::CUSTOMER
                ? ($appointment->patient_phone ?: '+14155552671')
                : ($appointment->professional?->whatsapp_phone ?: '+5491123456789'),
            'recipient_locale' => $locale,
            'provider' => 'meta',
            'status' => NotificationDeliveryStatus::QUEUED->value,
            'event_version' => (int) $appointment->reschedule_count,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function fixturePayload(string $name): array
    {
        $path = base_path("tests/Fixtures/meta-whatsapp/{$name}.json");
        $json = file_get_contents($path);

        $this->assertIsString($json);

        $payload = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        $this->assertIsArray($payload);

        return $payload;
    }
}
