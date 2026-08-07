<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Therapist;
use Illuminate\Contracts\View\View;

final class PublicTherapistDirectoryController extends Controller
{
    public function __invoke(): View
    {
        $therapists = Therapist::query()
            ->with('user')
            ->publiclyBookable()
            ->join('users', 'therapists.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('therapists.*')
            ->get();

        return view('therapists.index', [
            'therapists' => $therapists,
        ]);
    }
}
