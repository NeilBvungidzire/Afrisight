@component('mail::message')
# {{ __('email/general.greeting', ['name' => 'AfriSight member']) }},

{{ __('email/survey_invite.line_1', ['loi' => $loi, 'incentive' => number_format($incentive, 2)]) }}

{{ __('email/survey_invite.line_2') }}

@component('mail::button', ['url' => $url])
    {{ __('email/survey_invite.cta') }}
@endcomponent

{{ __('email/general.closing') }},<br>
{{ __('email/general.typed_name') }}
@endcomponent
