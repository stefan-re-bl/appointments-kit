<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        if ($user === null || $user->therapist === null) {
            return false;
        }

        $appointment = $this->route('appointment');

        return $appointment instanceof Appointment
            && $appointment->therapist_id === $user->therapist->id;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'payment_status' => ['required', Rule::enum(PaymentStatus::class)],
        ];
    }
}