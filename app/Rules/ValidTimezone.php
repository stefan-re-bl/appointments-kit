<?php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTimezone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($value, timezone_identifiers_list())) {
            $fail("La zona horaria :attribute no es válida.");
        }
    }
}