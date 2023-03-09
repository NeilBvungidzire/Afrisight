@component('mail::message')
# {{ __('email/general.greeting', ['name' => 'AfriSight member']) }},

{{ __('email/change_email.line_1', ['current_email' => $currentEmail, 'new_email' => $newEmail]) }}

@component('mail::button', ['url' => $temporarySignedRoute])
    {{ __('general.approve') }}
@endcomponent

{{ __('email/general.closing') }},<br>
{{ __('email/general.typed_name') }}
@endcomponent
