<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ContactInquiryType;
use App\Enums\Role;
use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Mail;

final class ContactMessageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [];
    }

    public function create(): View
    {
        return view('contact.create', [
            'inquiryTypes' => ContactInquiryType::cases(),
        ]);
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $contactMessage = ContactMessage::query()->create(
            $request->safe()->except('company')
        );

        $adminEmails = User::query()
            ->where('role', Role::ADMIN->value)
            ->pluck('email')
            ->all();

        if ((bool) config('features.email_notifications', true) && $adminEmails !== []) {
            Mail::to($adminEmails)->send(new ContactMessageReceived($contactMessage));
        }

        return redirect()
            ->route('contact.create')
            ->with('success', __('app.contact.success'));
    }
}
