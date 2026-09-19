<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = $this->createProfessionalUser();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->createProfessionalUser();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = $this->createProfessionalUser();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_professional_can_upload_local_profile_photo(): void
    {
        Storage::fake('public');

        $user = $this->createProfessionalUser();
        $professional = Professional::factory()->for($user)->create([
            'avatar_url' => null,
            'timezone' => 'America/Argentina/Buenos_Aires',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $professional->bio,
                'google_meet_link' => $professional->google_meet_link,
                'professional_country' => 'AR',
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 512, 512),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $avatarUrl = $professional->refresh()->avatar_url;

        $this->assertIsString($avatarUrl);
        $this->assertStringContainsString('/storage/professionals/avatars/', $avatarUrl);

        $storedPath = substr($avatarUrl, strpos($avatarUrl, '/storage/') + strlen('/storage/'));

        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_professional_can_upload_presentation_video(): void
    {
        Storage::fake('public');

        $user = $this->createProfessionalUser();
        $professional = Professional::factory()->for($user)->create([
            'presentation_video_url' => null,
            'timezone' => 'America/Argentina/Buenos_Aires',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $professional->bio,
                'google_meet_link' => $professional->google_meet_link,
                'avatar_url' => $professional->avatar_url,
                'professional_country' => 'AR',
                'presentation_video' => UploadedFile::fake()->create('presentation.mp4', 1024, 'video/mp4'),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $presentationVideoUrl = $professional->refresh()->presentation_video_url;

        $this->assertIsString($presentationVideoUrl);
        $this->assertStringContainsString('/storage/professionals/presentation-videos/', $presentationVideoUrl);

        $storedPath = substr($presentationVideoUrl, strpos($presentationVideoUrl, '/storage/') + strlen('/storage/'));

        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_professional_can_update_public_profile_details_and_payment_instructions(): void
    {
        $user = $this->createProfessionalUser();
        $professional = Professional::factory()->for($user)->create([
            'timezone' => 'America/Argentina/Buenos_Aires',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => 'Coordino procesos de servicio online.',
                'specialties' => 'Gestión, soporte y seguimiento.',
                'professional_approach' => 'Trabajo con un proceso claro y ordenado.',
                'payment_instructions' => 'Transferencia al alias de prueba antes del servicio.',
                'google_meet_link' => $professional->google_meet_link,
                'avatar_url' => $professional->avatar_url,
                'professional_country' => 'AR',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $professional->refresh();

        $this->assertSame('Gestión, soporte y seguimiento.', $professional->specialties);
        $this->assertSame('Trabajo con un proceso claro y ordenado.', $professional->therapeutic_approach);
        $this->assertSame('Transferencia al alias de prueba antes del servicio.', $professional->payment_instructions);
    }

    public function test_professional_can_update_and_keep_whatsapp_preferences_and_preferred_locale(): void
    {
        $user = $this->createProfessionalUser();
        $professional = Professional::factory()->for($user)->create([
            'timezone' => 'America/Argentina/Buenos_Aires',
            'preferred_locale' => 'es',
            'whatsapp_notifications_enabled' => false,
        ]);

        $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $professional->bio,
                'specialties' => $professional->specialties,
                'professional_approach' => $professional->therapeutic_approach,
                'payment_instructions' => $professional->payment_instructions,
                'google_meet_link' => $professional->google_meet_link,
                'avatar_url' => $professional->avatar_url,
                'professional_country' => 'AR',
                'whatsapp_phone' => '+54 9 11 2345 6789',
                'whatsapp_notifications_enabled' => '1',
                'whatsapp_confirmations_enabled' => '1',
                'whatsapp_reminders_enabled' => '1',
                'preferred_locale' => 'en',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $professional->refresh();

        $this->assertSame('+5491123456789', $professional->whatsapp_phone);
        $this->assertTrue($professional->whatsapp_notifications_enabled);
        $this->assertTrue($professional->whatsapp_confirmations_enabled);
        $this->assertTrue($professional->whatsapp_reminders_enabled);
        $this->assertSame('en', $professional->preferred_locale);

        $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => 'Nueva bio',
                'google_meet_link' => $professional->google_meet_link,
                'avatar_url' => $professional->avatar_url,
                'professional_country' => 'AR',
                'whatsapp_phone' => $professional->whatsapp_phone,
                'whatsapp_notifications_enabled' => '1',
                'whatsapp_confirmations_enabled' => '1',
                'whatsapp_reminders_enabled' => '1',
                'preferred_locale' => 'en',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertSame('en', $professional->refresh()->preferred_locale);
    }

    public function test_professional_timezone_change_redirects_to_availability_review(): void
    {
        $user = $this->createProfessionalUser();
        $professional = Professional::factory()->for($user)->create([
            'timezone' => 'America/Argentina/Buenos_Aires',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $professional->bio,
                'google_meet_link' => $professional->google_meet_link,
                'avatar_url' => $professional->avatar_url,
                'professional_country' => 'ES',
                'professional_timezone' => 'Europe/Madrid',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', __('app.profile.timezone_changed_review_availability'))
            ->assertRedirect(route('availabilities.index'));

        $this->assertSame('Europe/Madrid', $professional->refresh()->timezone);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = $this->createProfessionalUser();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = $this->createProfessionalUser();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    private function createProfessionalUser(): User
    {
        return User::factory()->create([
            'role' => Role::PROFESSIONAL,
        ]);
    }
}
