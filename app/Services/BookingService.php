<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\SupportedLocale;
use App\Models\Appointment;
use App\Models\Professional;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class BookingService
{
    /**
     * Crea una cita de forma segura previniendo Race Conditions (Overlapping).
     * Utiliza SELECT FOR UPDATE (Pessimistic Locking) dentro de una transacción.
     *
     * @param array{
     *     professional_id: int,
     *     session_type_id: int,
     *     patient_name: string,
     *     patient_email: string,
     *     patient_phone?: string|null,
     *     patient_timezone: string,
     *     patient_locale?: string,
     *     terms_accepted_at: mixed,
     *     patient_whatsapp_opt_in_at?: mixed,
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
            $professionalId = (int) $data['professional_id'];
            $startsAt = $data['starts_at'];
            $endsAt = $data['ends_at'];

            $professionalIsBookable = Professional::query()
                ->publiclyBookable()
                ->whereKey($professionalId)
                ->exists();

            if (! $professionalIsBookable) {
                Log::warning("Booking failed: Professional {$professionalId} is not approved for public booking.");

                return false;
            }

            // 1. Buscamos citas solapadas y bloqueamos las filas (Pessimistic Locking).
            $overlappingAppointments = Appointment::overlappingSlot($professionalId, $startsAt, $endsAt)
                ->lockForUpdate()
                ->get();

            // 2. Si hay solapamiento, abortamos.
            if ($overlappingAppointments->isNotEmpty()) {
                Log::warning("Booking failed: Overlapping detected for professional {$professionalId} at {$startsAt}");

                return false;
            }

            // 3. Si no hay solapamiento, creamos la cita.
            // Los campos de sistema se asignan explícitamente con forceFill()
            // para no exponerlos mediante mass assignment.
            $appointment = Appointment::query()->create([
                'professional_id' => $data['professional_id'],
                'session_type_id' => $data['session_type_id'],
                'patient_name' => $data['patient_name'],
                'patient_email' => $data['patient_email'],
                'patient_phone' => $data['patient_phone'] ?? null,
                'patient_timezone' => $data['patient_timezone'],
                'patient_locale' => SupportedLocale::normalize($data['patient_locale'] ?? null),
                'terms_accepted_at' => $data['terms_accepted_at'],
                'patient_whatsapp_opt_in_at' => $data['patient_whatsapp_opt_in_at'] ?? null,
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
