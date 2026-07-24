<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Rules\ValidTimezone;
use App\Services\CountryTimezoneService;
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
        $therapistTimezone = ValidTimezone::normalize($user->therapist?->timezone) ?? 'UTC';
        $therapistCountry = $countryTimezoneService->countryForTimezone($therapistTimezone) ?? 'AR';
        $timezoneReferenceDate = now('UTC')->toDateString();
        $timezoneOptions = $countryTimezoneService->timezoneOptionsForCountryOnDate(
            $therapistCountry,
            $timezoneReferenceDate,
        );

        if (! array_key_exists($therapistTimezone, $timezoneOptions)) {
            $therapistTimezone = array_key_first($timezoneOptions) ?? $therapistTimezone;
        }

        return view('profile.edit', [
            'countries' => $countryTimezoneService->countries(),
            'countryTimezones' => $countryTimezoneService->countryTimezones(),
            'therapistCountry' => $therapistCountry,
            'therapistTimezone' => $therapistTimezone,
            'timezoneReferenceDate' => $timezoneReferenceDate,
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, CountryTimezoneService $countryTimezoneService): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if ($request->user()->therapist) {
            $previousTimezone = $request->user()->therapist->timezone;
            $therapistTimezone = ValidTimezone::normalize(
                $countryTimezoneService->timezoneForLocation(
                    $validated['therapist_country'] ?? null,
                    now('UTC')->toDateString(),
                    $validated['therapist_timezone'] ?? null,
                )
            ) ?? $previousTimezone;
            $avatarUrl = $validated['avatar_url'] ?? null;
            $presentationVideoUrl = $request->user()->therapist->presentation_video_url;

            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('therapists/avatars', 'public');
                $avatarUrl = '/storage/'.$avatarPath;
            }

            if ($request->hasFile('presentation_video')) {
                $presentationVideoPath = $request->file('presentation_video')->store('therapists/presentation-videos', 'public');
                $presentationVideoUrl = '/storage/'.$presentationVideoPath;
            }

            $request->user()->therapist->update([
                'bio' => $validated['bio'] ?? null,
                'specialties' => $validated['specialties'] ?? null,
                'therapeutic_approach' => $validated['therapeutic_approach'] ?? null,
                'payment_instructions' => $validated['payment_instructions'] ?? null,
                'google_meet_link' => $validated['google_meet_link'] ?? null,
                'avatar_url' => $avatarUrl,
                'presentation_video_url' => $presentationVideoUrl,
                'timezone' => $therapistTimezone,
            ]);

            if ($therapistTimezone !== $previousTimezone) {
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
