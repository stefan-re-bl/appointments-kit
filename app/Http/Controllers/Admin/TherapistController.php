<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTherapistRequest;
use App\Models\Therapist;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

final class TherapistController extends Controller implements HasMiddleware
{
    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('admin'),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $approvalStatus = (string) $request->query('approval_status', 'all');
        $allowedApprovalStatuses = ['all', 'pending', 'approved', 'inactive'];

        if (! in_array($approvalStatus, $allowedApprovalStatuses, true)) {
            $approvalStatus = 'all';
        }

        $therapists = Therapist::query()
            ->with('user')
            ->withCount(['appointments', 'sessionTypes'])
            ->when($approvalStatus === 'pending', function (Builder $query): void {
                $query->where('is_approved', false)->where('is_active', true);
            })
            ->when($approvalStatus === 'approved', function (Builder $query): void {
                $query->where('is_approved', true)->where('is_active', true);
            })
            ->when($approvalStatus === 'inactive', function (Builder $query): void {
                $query->where('is_active', false);
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->whereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhere('timezone', 'like', "%{$search}%")
                        ->orWhere('bio', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.therapists.index', [
            'therapists' => $therapists,
            'search' => $search,
            'approvalStatus' => $approvalStatus,
        ]);
    }

    public function edit(Therapist $therapist): View
    {
        $therapist->load('user');

        return view('admin.therapists.edit', [
            'therapist' => $therapist,
        ]);
    }

    public function update(UpdateTherapistRequest $request, Therapist $therapist): RedirectResponse
    {
        $data = $request->validated();

        $therapist->load('user');

        DB::transaction(function () use ($therapist, $data, $request): void {
            $therapist->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            $therapist->update([
                'timezone' => $data['timezone'],
                'google_meet_link' => $data['google_meet_link'] ?? null,
                'bio' => $data['bio'] ?? null,
                'avatar_url' => $data['avatar_url'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'is_approved' => $request->boolean('is_approved'),
            ]);
        });

        return redirect()
            ->route('admin.therapists.index')
            ->with('success', __('app.admin.therapists.updated'));
    }

    public function approve(Therapist $therapist): RedirectResponse
    {
        $therapist->forceFill([
            'is_approved' => true,
        ])->save();

        return back()->with('success', __('app.admin.therapists.approved'));
    }

    public function revokeApproval(Therapist $therapist): RedirectResponse
    {
        $therapist->forceFill([
            'is_approved' => false,
        ])->save();

        return back()->with('success', __('app.admin.therapists.approval_revoked'));
    }
}
