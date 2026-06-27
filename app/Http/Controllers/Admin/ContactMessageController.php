<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ContactMessageStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContactMessageStatusRequest;
use App\Models\ContactMessage;
use App\Services\TimezoneService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final class ContactMessageController extends Controller implements HasMiddleware
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

    public function index(Request $request, TimezoneService $timezoneService): View
    {
        $status = (string) $request->query('status', 'all');
        $allowedStatuses = array_map(
            static fn (ContactMessageStatus $status): string => $status->value,
            ContactMessageStatus::cases(),
        );

        if ($status !== 'all' && ! in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        $contactMessages = ContactMessage::query()
            ->when($status !== 'all', function (Builder $query) use ($status): void {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.contact-messages.index', [
            'contactMessages' => $contactMessages,
            'statuses' => ContactMessageStatus::cases(),
            'selectedStatus' => $status,
            'timezoneService' => $timezoneService,
        ]);
    }

    public function update(
        UpdateContactMessageStatusRequest $request,
        ContactMessage $contactMessage,
    ): RedirectResponse {
        $contactMessage->update($request->validated());

        return back()->with('success', __('app.admin.contact_messages.updated'));
    }
}
