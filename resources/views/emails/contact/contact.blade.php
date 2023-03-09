@component('mail::message')
# {{ __('email/contact.salutation') }}

Subject Code: {{ $data['subject_code'] }}

{{ __('email/contact.subject') }}: {{ $data['subject'] }}

{{ __('email/contact.name') }}: {{ $data['name'] }}

{{ __('email/contact.email') }}: {{ $data['email_address'] }}

{{ __('email/contact.message') }}: {{ $data['message'] }}

{{ __('email/contact.country-code') }}: {{ $additionalData['country_code'] }}

{{ __('email/contact.date-registration') }}: {{ $additionalData['date_registration'] }}

{{ __('email/contact.has-cint-account') }}: {{ $additionalData['has_cint_account'] }}

@endcomponent
