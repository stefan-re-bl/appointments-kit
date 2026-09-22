<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Rules\ValidTimezone;
use App\Services\CountryTimezoneService;
use App\Services\Notifications\PhoneNumberNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, CountryTimezoneService $countryTimezoneService): View
    {
        /** @var User $user */
        $user = $request->user();
        $professionalTimezone = ValidTimezone::normalize($user->professional?->timezone) ?? 'UTC';
        $professionalCountry = $countryTimezoneService->countryForTimezone($professionalTimezone) ?? 'AR';
        $timezoneReferenceDate = now('UTC')->toDateString();
        $timezoneOptions = $countryTimezoneService->timezoneOptionsForCountryOnDate(
            $professionalCountry,
            $timezoneReferenceDate,
        );

        if (! array_key_exists($professionalTimezone, $timezoneOptions)) {
            $professionalTimezone = array_key_first($timezoneOptions) ?? $professionalTimezone;
        }

        return view('profile.edit', [
            'countries' => $countryTimezoneService->countries(),
            'countryTimezones' => $countryTimezoneService->countryTimezones(),
            'professionalCountry' => $professionalCountry,
            'professionalTimezone' => $professionalTimezone,
            'timezoneReferenceDate' => $timezoneReferenceDate,
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(
        ProfileUpdateRequest $request,
        CountryTimezoneService $countryTimezoneService,
        PhoneNumberNormalizer $phoneNumberNormalizer,
    ): RedirectResponse {
        $validated = $request->validated();

        $request->user()->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if ($request->user()->professional) {
            $previousTimezone = $request->user()->professional->timezone;
            $professionalTimezone = ValidTimezone::normalize(
                $countryTimezoneService->timezoneForLocation(
                    $validated['professional_country'] ?? null,
                    now('UTC')->toDateString(),
                    $validated['professional_timezone'] ?? null,
                )
            ) ?? $previousTimezone;
            $avatarUrl = $validated['avatar_url'] ?? null;
            $presentationVideoUrl = $request->user()->professional->presentation_video_url;

            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('professionals/avatars', 'public');
                $avatarUrl = '/storage/'.$avatarPath;
            }

            if ($request->hasFile('presentation_video')) {
                $presentationVideoPath = $request->file('presentation_video')->store('professionals/presentation-videos', 'public');
                $presentationVideoUrl = '/storage/'.$presentationVideoPath;
            }

            $request->user()->professional->update([
                'bio' => $validated['bio'] ?? null,
                'specialties' => $validated['specialties'] ?? null,
                'professional_approach' => $validated['professional_approach'] ?? null,
                'payment_instructions' => $validated['payment_instructions'] ?? null,
                'google_meet_link' => $validated['google_meet_link'] ?? null,
                'whatsapp_phone' => $phoneNumberNormalizer->normalize($validated['whatsapp_phone'] ?? null),
                'whatsapp_notifications_enabled' => $request->boolean('whatsapp_notifications_enabled'),
                'whatsapp_confirmations_enabled' => $request->boolean('whatsapp_confirmations_enabled'),
                'whatsapp_reminders_enabled' => $request->boolean('whatsapp_reminders_enabled'),
                'preferred_locale' => $validated['preferred_locale'],
                'avatar_url' => $avatarUrl,
                'presentation_video_url' => $presentationVideoUrl,
                'timezone' => $professionalTimezone,
            ]);

            if ($professionalTimezone !== $previousTimezone) {
                return Redirect::route('availabilities.index')
                    ->with('status', __('app.profile.timezone_changed_review_availability'));
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
