<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Therapist;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

final class PublicTherapistProfileController extends Controller
{
    public function __invoke(string $slug): View|Response
    {
        $therapist = Therapist::query()
            ->with([
                'user',
                'sessionTypes' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('duration_minutes')
                    ->orderBy('price'),
            ])
            ->publiclyBookable()
            ->where('slug', $slug)
            ->first();

        if (! $therapist) {
            return response()
                ->view('therapists.not-found', [], 404);
        }

        return view('therapists.show', [
            'therapist' => $therapist,
        ]);
    }
}
