@component('mail::message')
# Dear Medical Professional,

Thanks for participating in our previous survey. We are inviting you to take part in our follow-up questions which will take you less than 4 minutes and make the research complete. Please note that a copy of the research study will be shared with you once complete.

@component('mail::button', ['url' => $surveyLink])
    Start survey
@endcomponent

AfriSight will be happy to reward you for your time and internet data once the survey is completed.

Your participation is greatly appreciated, thank you!

{{ __('email/general.closing') }},<br>
{{ __('email/general.typed_name') }}
@endcomponent
