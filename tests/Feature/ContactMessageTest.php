<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ContactInquiryType;
use App\Enums\ContactMessageStatus;
use App\Enums\Role;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_contact_page_can_be_rendered(): void
    {
        $this
            ->get(route('contact.create', ['lang' => 'es']))
            ->assertOk()
            ->assertSeeText(__('app.contact.title'))
            ->assertSeeText(__('app.contact.inquiry_types.booking_problem'));
    }

    public function test_guest_can_submit_contact_message_and_admin_is_notified(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'role' => Role::ADMIN,
        ]);

        $this
            ->post(route('contact.store'), [
                'name' => 'Paciente Contacto',
                'email' => 'paciente@example.com',
                'inquiry_type' => ContactInquiryType::BOOKING_PROBLEM->value,
                'message' => 'Necesito ayuda con una reserva confirmada.',
            ])
            ->assertRedirect(route('contact.create'))
            ->assertSessionHas('success', __('app.contact.success'));

        $this->assertDatabaseHas(ContactMessage::class, [
            'name' => 'Paciente Contacto',
            'email' => 'paciente@example.com',
            'inquiry_type' => ContactInquiryType::BOOKING_PROBLEM->value,
            'status' => ContactMessageStatus::OPEN->value,
        ]);

        Mail::assertSent(
            ContactMessageReceived::class,
            fn (ContactMessageReceived $mail): bool => $mail->hasTo($admin->email)
        );
    }

    public function test_honeypot_rejects_spam_submission(): void
    {
        $this
            ->from(route('contact.create'))
            ->post(route('contact.store'), [
                'name' => 'Spam Bot',
                'email' => 'spam@example.com',
                'inquiry_type' => ContactInquiryType::GENERAL->value,
                'message' => 'Este mensaje no debería guardarse por el honeypot.',
                'company' => 'Spam Company',
            ])
            ->assertRedirect(route('contact.create'))
            ->assertSessionHasErrors('company');

        $this->assertDatabaseCount('contact_messages', 0);
    }
}
