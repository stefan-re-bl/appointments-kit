<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfessionalRequest;
use App\Models\Professional;
use App\Models\SessionType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

final class ProfessionalController extends Controller implements HasMiddleware
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

        $professionals = Professional::query()
            ->with('user')
            ->withCount('appointments')
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

        return view('admin.professionals.index', [
            'professionals' => $professionals,
            'search' => $search,
            'approvalStatus' => $approvalStatus,
        ]);
    }

    public function edit(Professional $professional): View
    {
        $professional->load('user');
        $defaultSessionType = $this->defaultSessionType($professional);

        return view('admin.professionals.edit', [
            'professional' => $professional,
            'defaultSessionType' => $defaultSessionType,
        ]);
    }

    public function update(UpdateProfessionalRequest $request, Professional $professional): RedirectResponse
    {
        $data = $request->validated();

        $professional->load('user');

        DB::transaction(function () use ($professional, $data, $request): void {
            $professional->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            $professional->update([
                'timezone' => $data['timezone'],
                'google_meet_link' => $data['google_meet_link'] ?? null,
                'bio' => $data['bio'] ?? null,
                'avatar_url' => $data['avatar_url'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'is_approved' => $request->boolean('is_approved'),
            ]);

            $this->updateDefaultSessionType(
                $professional,
                (float) $data['session_price'],
                (string) $data['session_currency'],
            );
        });

        return redirect()
            ->route('admin.professionals.index')
            ->with('success', __('app.admin.professionals.updated'));
    }

    public function approve(Professional $professional): RedirectResponse
    {
        $professional->forceFill([
            'is_approved' => true,
        ])->save();

        return back()->with('success', __('app.admin.professionals.approved'));
    }

    public function revokeApproval(Professional $professional): RedirectResponse
    {
        $professional->forceFill([
            'is_approved' => false,
        ])->save();

        return back()->with('success', __('app.admin.professionals.approval_revoked'));
    }

    private function defaultSessionType(Professional $professional): ?SessionType
    {
        return SessionType::query()
            ->where('professional_id', $professional->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->first()
            ?? SessionType::query()
                ->where('professional_id', $professional->id)
                ->orderBy('id')
                ->first();
    }

    private function updateDefaultSessionType(Professional $professional, float $price, string $currency): void
    {
        $sessionType = $this->defaultSessionType($professional);

        if (! $sessionType) {
            $professional->sessionTypes()->create([
                'name' => 'Sesión estándar',
                'duration_minutes' => 60,
                'price' => $price,
                'currency' => $currency,
                'is_active' => true,
            ]);

            return;
        }

        $sessionType->update([
            'name' => 'Sesión estándar',
            'price' => $price,
            'currency' => $currency,
            'is_active' => true,
        ]);

        SessionType::query()
            ->where('professional_id', $professional->id)
            ->where('id', '<>', $sessionType->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}
