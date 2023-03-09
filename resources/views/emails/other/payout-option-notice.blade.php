@component('mail::message')
# Dear AfriSight member,

Thank you for participating in our surveys. Good news for Ethiopia as we have now added new payout methods to redeem your rewards. You can receive your rewards as mobile money, BelCash, and soon as airtime.

Please make sure your mobile number is up to date.

@component('mail::button', ['url' => $updateMobileNumberLink])
Update my mobile number
@endcomponent

Thanks for your time and we look forward to your participation in future surveys. And to get more suitable survey opportunities, please also update your AfriSight account via this link: {{ $profilingLink }}.

{{ __('email/general.closing') }},<br>
{{ __('email/general.typed_name') }}
@endcomponent
