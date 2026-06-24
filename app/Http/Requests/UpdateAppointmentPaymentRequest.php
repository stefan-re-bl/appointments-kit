<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class UpdateAppointmentPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        $appointment = $this->route('appointment');

        return $user instanceof User
            && $appointment instanceof Appointment
            && Gate::forUser($user)->allows('updatePayment', $appointment);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'payment_status' => [
                'required',
                Rule::enum(PaymentStatus::class),
            ],
        ];
    }
}