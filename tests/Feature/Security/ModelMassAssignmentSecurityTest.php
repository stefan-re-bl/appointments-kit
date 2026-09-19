<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Professional;
use App\Models\SessionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ModelMassAssignmentSecurityTest extends TestCase
{
    #[Test]
    public function sensitive_fields_are_not_mass_assignable(): void
    {
        $this->assertFieldsAreNotFillable(User::class, [
            'role',
            'email_verified_at',
            'remember_token',
        ]);

        $this->assertFieldsAreNotFillable(Professional::class, [
            'user_id',
        ]);

        $this->assertFieldsAreNotFillable(SessionType::class, [
            'professional_id',
        ]);

        $this->assertFieldsAreNotFillable(Availability::class, [
            'professional_id',
        ]);

        $this->assertFieldsAreNotFillable(Appointment::class, [
            'token',
            'status',
            'payment_status',
            'paid_at',
            'reminder_sent_at',
            'reschedule_count',
        ]);

        $this->assertFieldsAreNotFillable(ActivityLog::class, [
            'id',
        ]);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, string>  $fields
     */
    private function assertFieldsAreNotFillable(string $modelClass, array $fields): void
    {
        $model = new $modelClass;

        foreach ($fields as $field) {
            $this->assertFalse(
                $model->isFillable($field),
                "{$modelClass} has dangerous fillable field [{$field}]."
            );
        }
    }
}
