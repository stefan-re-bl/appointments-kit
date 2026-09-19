<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\SessionType;
use App\Models\User;
use App\Services\Reports\AppointmentReportService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AppointmentReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_service_calculates_manual_payment_totals(): void
    {
        app()->instance('user.timezone', 'UTC');

        $professional = Professional::factory()->create();
        $sessionType = SessionType::factory()->create([
            'professional_id' => $professional->id,
            'price' => 10000,
            'currency' => 'ARS',
        ]);

        Appointment::factory()->create([
            'professional_id' => $professional->id,
            'session_type_id' => $sessionType->id,
            'patient_name' => 'Paid Customer',
            'patient_email' => 'paid@example.com',
            'starts_at' => CarbonImmutable::parse('2026-06-10 12:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-06-10 13:00:00', 'UTC'),
            'status' => AppointmentStatus::CONFIRMED->value,
            'payment_status' => PaymentStatus::PAID->value,
            'paid_at' => CarbonImmutable::parse('2026-06-09 12:00:00', 'UTC'),
            'price' => 10000,
            'currency' => 'ARS',
        ]);

        Appointment::factory()->create([
            'professional_id' => $professional->id,
            'session_type_id' => $sessionType->id,
            'patient_name' => 'Pending Customer',
            'patient_email' => 'pending@example.com',
            'starts_at' => CarbonImmutable::parse('2026-06-11 12:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-06-11 13:00:00', 'UTC'),
            'status' => AppointmentStatus::CONFIRMED->value,
            'payment_status' => PaymentStatus::PENDING->value,
            'paid_at' => null,
            'price' => 20000,
            'currency' => 'ARS',
        ]);

        Appointment::factory()->create([
            'professional_id' => $professional->id,
            'session_type_id' => $sessionType->id,
            'patient_name' => 'Cancelled Customer',
            'patient_email' => 'cancelled@example.com',
            'starts_at' => CarbonImmutable::parse('2026-06-12 12:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-06-12 13:00:00', 'UTC'),
            'status' => AppointmentStatus::CANCELLED->value,
            'payment_status' => PaymentStatus::PENDING->value,
            'paid_at' => null,
            'price' => 30000,
            'currency' => 'ARS',
        ]);

        $report = app(AppointmentReportService::class)->build([
            'date_from' => '2026-06-01',
            'date_to' => '2026-06-30',
            'professional_id' => null,
        ]);

        $this->assertSame(3, $report['metrics']['total_appointments']);
        $this->assertSame(1, $report['metrics']['cancelled_appointments']);
        $this->assertSame(1, $report['metrics']['paid_appointments']);
        $this->assertSame(1, $report['metrics']['pending_payment_appointments']);
        $this->assertSame(50.0, $report['metrics']['payment_conversion_rate']);

        $currencySummary = $report['currency_summaries']->first();

        $this->assertSame('ARS', $currencySummary['currency']);
        $this->assertSame(30000.0, $currencySummary['estimated_amount']);
        $this->assertSame(10000.0, $currencySummary['collected_amount']);
        $this->assertSame(20000.0, $currencySummary['pending_amount']);
    }

    public function test_admin_can_export_appointment_report_as_csv(): void
    {
        app()->instance('user.timezone', 'UTC');

        $admin = User::factory()->create([
            'role' => Role::ADMIN->value,
            'email_verified_at' => now(),
        ]);

        $professional = Professional::factory()->create();
        $sessionType = SessionType::factory()->create([
            'professional_id' => $professional->id,
            'price' => 15000,
            'currency' => 'ARS',
        ]);

        Appointment::factory()->create([
            'professional_id' => $professional->id,
            'session_type_id' => $sessionType->id,
            'patient_name' => 'CSV Customer',
            'patient_email' => 'csv@example.com',
            'starts_at' => CarbonImmutable::parse('2026-06-15 12:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-06-15 13:00:00', 'UTC'),
            'status' => AppointmentStatus::CONFIRMED->value,
            'payment_status' => PaymentStatus::PAID->value,
            'paid_at' => CarbonImmutable::parse('2026-06-14 12:00:00', 'UTC'),
            'price' => 15000,
            'currency' => 'ARS',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.reports.appointments.export', [
                'date_from' => '2026-06-01',
                'date_to' => '2026-06-30',
            ]));

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('CSV Customer', $content);
        $this->assertStringContainsString('csv@example.com', $content);
        $this->assertStringContainsString('15000.00', $content);
        $this->assertStringContainsString('ARS', $content);
    }
}
