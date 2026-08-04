<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Therapist;
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
        $user = $this->createTherapistUser();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->createTherapistUser();

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
        $user = $this->createTherapistUser();

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

    public function test_therapist_can_upload_local_profile_photo(): void
    {
        Storage::fake('public');

        $user = $this->createTherapistUser();
        $therapist = Therapist::factory()->for($user)->create([
            'avatar_url' => null,
            'timezone' => 'America/Argentina/Buenos_Aires',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $therapist->bio,
                'google_meet_link' => $therapist->google_meet_link,
                'therapist_country' => 'AR',
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 512, 512),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $avatarUrl = $therapist->refresh()->avatar_url;

        $this->assertIsString($avatarUrl);
        $this->assertStringContainsString('/storage/therapists/avatars/', $avatarUrl);

        $storedPath = substr($avatarUrl, strpos($avatarUrl, '/storage/') + strlen('/storage/'));

        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_therapist_can_upload_presentation_video(): void
    {
        Storage::fake('public');

        $user = $this->createTherapistUser();
        $therapist = Therapist::factory()->for($user)->create([
            'presentation_video_url' => null,
            'timezone' => 'America/Argentina/Buenos_Aires',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $therapist->bio,
                'google_meet_link' => $therapist->google_meet_link,
                'avatar_url' => $therapist->avatar_url,
                'therapist_country' => 'AR',
                'presentation_video' => UploadedFile::fake()->create('presentation.mp4', 1024, 'video/mp4'),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $presentationVideoUrl = $therapist->refresh()->presentation_video_url;

        $this->assertIsString($presentationVideoUrl);
        $this->assertStringContainsString('/storage/therapists/presentation-videos/', $presentationVideoUrl);

        $storedPath = substr($presentationVideoUrl, strpos($presentationVideoUrl, '/storage/') + strlen('/storage/'));

        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_therapist_can_update_public_profile_details_and_payment_instructions(): void
    {
        $user = $this->createTherapistUser();
        $therapist = Therapist::factory()->for($user)->create([
            'timezone' => 'America/Argentina/Buenos_Aires',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => 'Trabajo con procesos de ansiedad.',
                'specialties' => 'Ansiedad, duelos y crisis vitales.',
                'therapeutic_approach' => 'Enfoque integrativo con perspectiva contextual.',
                'payment_instructions' => 'Transferencia a alias UMBRALIA.TEST antes de la sesión.',
                'google_meet_link' => $therapist->google_meet_link,
                'avatar_url' => $therapist->avatar_url,
                'therapist_country' => 'AR',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $therapist->refresh();

        $this->assertSame('Ansiedad, duelos y crisis vitales.', $therapist->specialties);
        $this->assertSame('Enfoque integrativo con perspectiva contextual.', $therapist->therapeutic_approach);
        $this->assertSame('Transferencia a alias UMBRALIA.TEST antes de la sesión.', $therapist->payment_instructions);
    }

    public function test_therapist_can_update_and_keep_whatsapp_preferences_and_preferred_locale(): void
    {
        $user = $this->createTherapistUser();
        $therapist = Therapist::factory()->for($user)->create([
            'timezone' => 'America/Argentina/Buenos_Aires',
            'preferred_locale' => 'es',
            'whatsapp_notifications_enabled' => false,
        ]);

        $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $therapist->bio,
                'specialties' => $therapist->specialties,
                'therapeutic_approach' => $therapist->therapeutic_approach,
                'payment_instructions' => $therapist->payment_instructions,
                'google_meet_link' => $therapist->google_meet_link,
                'avatar_url' => $therapist->avatar_url,
                'therapist_country' => 'AR',
                'whatsapp_phone' => '+54 9 11 2345 6789',
                'whatsapp_notifications_enabled' => '1',
                'whatsapp_confirmations_enabled' => '1',
                'whatsapp_reminders_enabled' => '1',
                'preferred_locale' => 'en',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $therapist->refresh();

        $this->assertSame('+5491123456789', $therapist->whatsapp_phone);
        $this->assertTrue($therapist->whatsapp_notifications_enabled);
        $this->assertTrue($therapist->whatsapp_confirmations_enabled);
        $this->assertTrue($therapist->whatsapp_reminders_enabled);
        $this->assertSame('en', $therapist->preferred_locale);

        $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => 'Nueva bio',
                'google_meet_link' => $therapist->google_meet_link,
                'avatar_url' => $therapist->avatar_url,
                'therapist_country' => 'AR',
                'whatsapp_phone' => $therapist->whatsapp_phone,
                'whatsapp_notifications_enabled' => '1',
                'whatsapp_confirmations_enabled' => '1',
                'whatsapp_reminders_enabled' => '1',
                'preferred_locale' => 'en',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertSame('en', $therapist->refresh()->preferred_locale);
    }

    public function test_therapist_timezone_change_redirects_to_availability_review(): void
    {
        $user = $this->createTherapistUser();
        $therapist = Therapist::factory()->for($user)->create([
            'timezone' => 'America/Argentina/Buenos_Aires',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $therapist->bio,
                'google_meet_link' => $therapist->google_meet_link,
                'avatar_url' => $therapist->avatar_url,
                'therapist_country' => 'ES',
                'therapist_timezone' => 'Europe/Madrid',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', __('app.profile.timezone_changed_review_availability'))
            ->assertRedirect(route('availabilities.index'));

        $this->assertSame('Europe/Madrid', $therapist->refresh()->timezone);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = $this->createTherapistUser();

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
        $user = $this->createTherapistUser();

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

    private function createTherapistUser(): User
    {
        return User::factory()->create([
            'role' => Role::THERAPIST,
        ]);
    }
}
