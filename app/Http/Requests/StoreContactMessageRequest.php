<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ContactInquiryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'inquiry_type' => ['required', Rule::enum(ContactInquiryType::class)],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'company' => ['prohibited'],
        ];
    }
}
