<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContactInquiryType;
use App\Enums\ContactMessageStatus;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'inquiry_type',
    'message',
    'status',
])]
final class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'inquiry_type' => ContactInquiryType::class,
            'status' => ContactMessageStatus::class,
        ];
    }
}
