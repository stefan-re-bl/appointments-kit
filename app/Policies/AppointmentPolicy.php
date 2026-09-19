<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\Appointment;
use App\Models\User;

final class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasRole($user, Role::ADMIN) || $user->professional !== null;
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if ($this->hasRole($user, Role::ADMIN)) {
            return true;
        }

        return $this->ownsAppointment($user, $appointment);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $this->ownsAppointment($user, $appointment);
    }

    public function updatePayment(User $user, Appointment $appointment): bool
    {
        return $this->ownsAppointment($user, $appointment);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return false;
    }

    private function ownsAppointment(User $user, Appointment $appointment): bool
    {
        $user->loadMissing('professional');

        if ($user->professional === null) {
            return false;
        }

        return (int) $user->professional->id === (int) $appointment->professional_id;
    }

    private function hasRole(User $user, Role $role): bool
    {
        if ($user->role instanceof Role) {
            return $user->role === $role;
        }

        return $user->role === $role->value;
    }
}
