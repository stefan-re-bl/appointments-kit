<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Models\Therapist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class BookingService
{
    /**
     * Crea una cita de forma segura previniendo Race Conditions (Overlapping).
     * Utiliza SELECT FOR UPDATE (Pessimistic Locking) dentro de una transacción.
     *
     * @param array{
     *     therapist_id: int,
     *     session_type_id: int,
     *     patient_name: string,
     *     patient_email: string,
     *     patient_timezone: string,
     *     starts_at: mixed,
     *     ends_at: mixed,
     *     price: mixed,
     *     currency: string
     * } $data
     * @return Appointment|false Retorna la cita creada o false si hubo solapamiento.
     */
    public function bookSlot(array $data): Appointment|false
    {
        return DB::transaction(function () use ($data): Appointment|false {
            $therapistId = (int) $data['therapist_id'];
            $startsAt = $data['starts_at'];
            $endsAt = $data['ends_at'];

            $therapistIsBookable = Therapist::query()
                ->publiclyBookable()
                ->whereKey($therapistId)
                ->exists();

            if (! $therapistIsBookable) {
                Log::warning("Booking failed: Therapist {$therapistId} is not approved for public booking.");

                return false;
            }

            // 1. Buscamos citas solapadas y bloqueamos las filas (Pessimistic Locking).
            $overlappingAppointments = Appointment::overlappingSlot($therapistId, $startsAt, $endsAt)
                ->lockForUpdate()
                ->get();

            // 2. Si hay solapamiento, abortamos.
            if ($overlappingAppointments->isNotEmpty()) {
                Log::warning("Booking failed: Overlapping detected for therapist {$therapistId} at {$startsAt}");

                return false;
            }

            // 3. Si no hay solapamiento, creamos la cita.
            // Los campos de sistema se asignan explícitamente con forceFill()
            // para no exponerlos mediante mass assignment.
            $appointment = Appointment::query()->create([
                'therapist_id' => $data['therapist_id'],
                'session_type_id' => $data['session_type_id'],
                'patient_name' => $data['patient_name'],
                'patient_email' => $data['patient_email'],
                'patient_timezone' => $data['patient_timezone'],
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'price' => $data['price'],
                'currency' => $data['currency'],
            ]);

            $appointment->forceFill([
                'status' => AppointmentStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PENDING,
                'paid_at' => null,
                'reschedule_count' => 0,
            ])->save();

            return $appointment->refresh();
        });
    }
}
