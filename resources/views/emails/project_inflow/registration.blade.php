@component('mail::message')
# {{ __('email/general.greeting', ['name' => 'AfriSight member']) }},

@lang('email/inflow.registration.line_1', ['password' => $password])

@component('mail::button', ['url' => $url])
@lang('email/inflow.registration.login_cta')
@endcomponent

{{ __('email/general.closing') }},<br>
{{ __('email/general.typed_name') }}
@endcomponent
