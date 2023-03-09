@component('mail::message')
# Dear fellow Health care worker,

It is with pleasure to invite you to participate in the Covid-19 Knowledge, Attitudes, and Practice (KAP) survey for healthcare professionals in Uganda. The study will take between 10-15 minutes to complete.

@component('mail::button', ['url' => $surveyLink])
    Start survey
@endcomponent

This study is a combined effort of researchers and medical professionals from the University of Amsterdam, and the Uganda Medical Association.

Besides receiving the full copy of the research report, the information filled herein will be used to carry out interventional projects relating to COVID 19 targeting health workers in Uganda.

Your participation is greatly appreciated, thank you!

{{ __('email/general.closing') }},<br>
{{ __('email/general.typed_name') }}
@endcomponent
