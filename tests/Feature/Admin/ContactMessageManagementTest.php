<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\ContactInquiryType;
use App\Enums\ContactMessageStatus;
use App\Enums\Role;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ContactMessageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_contact_messages(): void
    {
        $admin = User::factory()->create([
            'role' => Role::ADMIN,
        ]);
        $contactMessage = ContactMessage::factory()->create([
            'name' => 'Consulta Admin',
            'email' => 'consulta@example.com',
            'inquiry_type' => ContactInquiryType::PAYMENT_PROBLEM,
            'status' => ContactMessageStatus::OPEN,
        ]);

        $this
            ->actingAs($admin)
            ->get(route('admin.contact-messages.index'))
            ->assertOk()
            ->assertSeeText('Consulta Admin')
            ->assertSeeText($contactMessage->email)
            ->assertSeeText(__('app.contact.inquiry_types.payment_problem'));
    }

    public function test_admin_can_update_contact_message_status(): void
    {
        $admin = User::factory()->create([
            'role' => Role::ADMIN,
        ]);
        $contactMessage = ContactMessage::factory()->create([
            'status' => ContactMessageStatus::OPEN,
        ]);

        $this
            ->actingAs($admin)
            ->patch(route('admin.contact-messages.update', $contactMessage), [
                'status' => ContactMessageStatus::IN_REVIEW->value,
            ])
            ->assertRedirect();

        $this->assertTrue($contactMessage->refresh()->status === ContactMessageStatus::IN_REVIEW);
    }
}
