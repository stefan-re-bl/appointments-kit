<?php

namespace App\Listeners;

use App\Events\AppointmentBooked;
use App\Mail\BookingConfirmationForTherapist;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingNotificationToTherapist
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AppointmentBooked $event): void
    {
        $recipient = $event->appointment->therapist?->user?->email;

        if (empty($recipient)) {
            return;
        }

        Mail::to($recipient)
            ->send(new BookingConfirmationForTherapist($event->appointment));
    }
}
