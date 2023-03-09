@component('mail::message')
# Dear AfriSight member,

The live {{ $data['STUDYSUBJECT'] }} study you signed up for starts in a few minutes!

**Access to your dedicated participant link is NOW LIVE:**<br>
*Please make sure to have the following code at hand* ***when entering the survey: {{ $data['TAGCODE'] }}***.<br>
@component('mail::button', ['url' => $data['REMESHLINK']])
    Click here to enter
@endcomponent

The reward you will receive for completing all steps is **{{ $data['INCENTIVEQUANT'] }}** and will be provided to you as: **{{ $data['INCENTIVETYPE'] }}**.

We encourage you to join now so that we can start the live {{ $data['STUDYSUBJECT'] }} study on time.

Here is what is going to happen next:

@component('mail::panel')
<img src="{{ asset('img/instant_labs/icon_3.png') }}" alt="Step 1" style="height: 35px; width: auto"> Step 1 – Click the participation link as soon as you receive it.
@endcomponent

@component('mail::panel')
<img src="{{ asset('img/instant_labs/icon_4.png') }}" alt="Step 2" style="height: 35px; width: auto"> Step 2 – You will enter a waiting room while we are waiting for all participants to join. Please do not navigate away from this page! Be advised that we have a fixed capacity and the live online study allows only a limited number of people to continue with participation. Five minutes before its start, the button „Join conversation“ will get activated. Make sure you don’t miss it so you can be part of our live online study.
@endcomponent

@component('mail::panel')
<img src="{{ asset('img/instant_labs/icon_5.png') }}" alt="Step 3" style="height: 35px; width: auto"> Step 3 – The live online study starts! A moderator will guide you and the rest of the participants through the conversation. Please speak your mind on the topic and stay focused. The more information you can provide, the more we can learn and put your opinions into action.
@endcomponent

@component('mail::panel')
<img src="{{ asset('img/instant_labs/icon_6.png') }}" alt="Step 4" style="height: 35px; width: auto"> Step 4 – Once the moderator ends the live online study, you’re done. Congratulations!
@endcomponent

@component('mail::panel')
<img src="{{ asset('img/instant_labs/icon_7.png') }}" alt="Step 5" style="height: 35px; width: auto"> Step 5 – It’s reward time! If you have participated in the live online study until the end, you are eligible for the reward! Please allow up to 15 business days after completing the project for processing your activity for the study.
@endcomponent

We are taking your participation as agreement that you have also read the Privacy Policy of our trusted partner, [Remesh]({{ $data['REMESHPRIVACYLINK'] }}). We also remind you that [the confidentiality terms]({{ $data['NDALINK'] }}) you already accepted apply to all stages of this live {{ $data['STUDYSUBJECT'] }} study.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
