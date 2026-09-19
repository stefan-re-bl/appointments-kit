<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Professional;
use Illuminate\Contracts\View\View;

final class PublicProfessionalDirectoryController extends Controller
{
    public function __invoke(): View
    {
        $professionals = Professional::query()
            ->with('user')
            ->publiclyBookable()
            ->join('users', 'professionals.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('professionals.*')
            ->get();

        return view('professionals.index', [
            'professionals' => $professionals,
        ]);
    }
}
