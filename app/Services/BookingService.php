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

            // 1. Buscar citas que se solapan en ese rango horario para ese terapeuta
            // 2. lockForUpdate() bloquea estas filas. Si otra transacción intenta leerlas
            //    al mismo tiempo, esperará hasta que esta transacción termine (commit o rollback).
            $overlappingAppointments = Appointment::overlappingSlot($therapistId, $startsAt, $endsAt)
                ->lockForUpdate()
                ->get();

            // 3. Si ya hay citas solapadas (no canceladas), abortamos la creación
            if ($overlappingAppointments->isNotEmpty()) {
                Log::warning("Booking failed: Overlapping detected for therapist {$therapistId} at {$startsAt}");
                return false;
            }

            // 4. Si no hay solapamiento, creamos la cita de forma segura.
            // Nadie más puede crear en este rango mientras la transacción no haya terminado.
            $data['status'] = AppointmentStatus::PENDING;
            $data['payment_status'] = PaymentStatus::PENDING;

            return Appointment::create($data);
        });
    }
}