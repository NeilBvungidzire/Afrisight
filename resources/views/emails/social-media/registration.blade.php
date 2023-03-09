@component('mail::message')
# {{ __('email/new_registration_social_media.greeting', ['name' => $name]) }},

{{ __('email/new_registration_social_media.line_1') }}

{{ __('email/new_registration_social_media.line_2') }}

{{ __('email/new_registration_social_media.closing') }},<br>
{{ __('email/new_registration_social_media.typed_name') }}
@endcomponent
