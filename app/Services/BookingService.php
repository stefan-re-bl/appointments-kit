<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    /**
     * Crea una cita de forma segura previniendo Race Conditions (Overlapping).
     * Utiliza SELECT FOR UPDATE (Pessimistic Locking) dentro de una transacción.
     *
     * @return Appointment|false Retorna la cita creada o false si hubo solapamiento.
     */
    public function bookSlot(array $data): Appointment|false
    {
        return DB::transaction(function () use ($data) {
            $therapistId = $data['therapist_id'];
            $startsAt = $data['starts_at'];
            $endsAt = $data['ends_at'];

            // 1. Buscamos citas solapadas y bloqueamos las filas (Pessimistic Locking)
            $overlappingAppointments = Appointment::overlappingSlot($therapistId, $startsAt, $endsAt)
                ->lockForUpdate()
                ->get();

            // 2. Si hay solapamiento, abortamos
            if ($overlappingAppointments->isNotEmpty()) {
                Log::warning("Booking failed: Overlapping detected for therapist {$therapistId} at {$startsAt}");
                return false;
            }

            // 3. Si no hay solapamiento, creamos la cita
            $data['status'] = AppointmentStatus::CONFIRMED; // Ticket #10
            $data['payment_status'] = PaymentStatus::PENDING; // Ticket #10

            return Appointment::create($data);
        });
    }
}