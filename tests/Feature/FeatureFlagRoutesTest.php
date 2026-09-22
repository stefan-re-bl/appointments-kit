<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

final class FeatureFlagRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_information_pages_are_not_available_when_feature_is_disabled(): void
    {
        config(['features.public_information_pages' => false]);

        $this->get(route('information.how-it-works'))->assertNotFound();
        $this->get(route('information.patients'))->assertNotFound();
        $this->get(route('information.payment-and-cancellation'))->assertNotFound();
    }

    public function test_public_faq_is_not_available_when_faq_feature_is_disabled(): void
    {
        config([
            'features.public_information_pages' => true,
            'features.public_faq' => false,
        ]);

        $this->get(route('information.faq'))->assertNotFound();
    }

    public function test_public_contact_form_is_not_available_when_feature_is_disabled(): void
    {
        config(['features.public_contact_form' => false]);

        $this->get(route('contact.create'))->assertNotFound();
        $this->post(route('contact.store'), [])->assertNotFound();
    }

    public function test_public_provider_directory_is_not_available_when_feature_is_disabled(): void
    {
        config(['features.public_provider_directory' => false]);

        $this->get(route('professionals.index'))->assertNotFound();
        $this->get('/professionals/example')->assertNotFound();
    }

    public function test_public_rescheduling_is_not_available_when_feature_is_disabled(): void
    {
        config(['features.public_rescheduling' => false]);

        $appointment = Appointment::factory()->create();

        $url = URL::signedRoute('appointments.public.reschedule', [
            'token' => $appointment->token,
        ]);

        $this->get($url)->assertNotFound();
    }

    public function test_public_cancellation_is_not_available_when_feature_is_disabled(): void
    {
        config(['features.public_cancellation' => false]);

        $appointment = Appointment::factory()->create();

        $this->post(route('appointments.public.cancel', $appointment->token))->assertNotFound();
    }

    public function test_admin_reports_and_audit_are_not_available_when_features_are_disabled(): void
    {
        config([
            'features.reports' => false,
            'features.visible_audit' => false,
        ]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.reports.appointments.index'))
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('admin.activity-logs.index'))
            ->assertNotFound();
    }

    public function test_whatsapp_webhook_is_not_available_when_feature_is_disabled(): void
    {
        config(['features.whatsapp' => false]);

        $this->get(route('webhooks.meta-whatsapp.verify'))->assertNotFound();
        $this->post(route('webhooks.meta-whatsapp.receive'))->assertNotFound();
    }
}
