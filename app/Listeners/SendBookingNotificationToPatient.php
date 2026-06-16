<?php

namespace App\Listeners;

use App\Events\AppointmentBooked;
use App\Mail\BookingConfirmationForPatient;
use Illuminate\Support\Facades\Mail;

class SendBookingNotificationToPatient
{
    public function handle(AppointmentBooked $event): void
    {
        Mail::to($event->appointment->patient_email)
            ->send(new BookingConfirmationForPatient($event->appointment));
    }
}