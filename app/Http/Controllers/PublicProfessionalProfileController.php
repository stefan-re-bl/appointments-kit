<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Professional;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

final class PublicProfessionalProfileController extends Controller
{
    public function __invoke(string $slug): View|Response
    {
        $professional = Professional::query()
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

        if (! $professional) {
            return response()
                ->view('professionals.not-found', [], 404);
        }

        return view('professionals.show', [
            'professional' => $professional,
        ]);
    }
}
