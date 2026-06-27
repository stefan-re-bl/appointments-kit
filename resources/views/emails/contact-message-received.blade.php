# {{ __('app.contact.email.title') }}

{{ __('app.contact.email.intro') }}

**{{ __('app.contact.fields.name') }}:** {{ $contactMessage->name }}

**{{ __('app.contact.fields.email') }}:** {{ $contactMessage->email }}

**{{ __('app.contact.fields.inquiry_type') }}:** {{ __('app.contact.inquiry_types.' . $contactMessage->inquiry_type->value) }}

**{{ __('app.contact.fields.message') }}:**

{{ $contactMessage->message }}

<x-mail::button :url="$adminUrl">
{{ __('app.contact.email.admin_button') }}
</x-mail::button>
