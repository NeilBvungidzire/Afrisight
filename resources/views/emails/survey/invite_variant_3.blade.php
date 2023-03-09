@component('mail::message')
# {{ __('email/general.greeting', ['name' => 'AfriSight member']) }},

{{ __('email/survey_invite_variant_3.line_1', ['loi' => $incentive['loi'], 'usd_amount' => number_format($incentive['usd_amount'], 2), 'local_amount' => number_format($incentive['local_amount'], 2), 'local_currency' => $incentive['local_currency']]) }}

{{ __('email/survey_invite_variant_1.line_2') }}

@component('mail::button', ['url' => $url])
    {{ __('email/survey_invite_variant_1.cta') }}
@endcomponent

{{ __('email/general.closing') }},<br>
{{ __('email/general.typed_name') }}
@endcomponent
