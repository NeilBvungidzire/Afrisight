@component('mail::message')
# Dear AfriSight member,

The live {{ $data['STUDYSUBJECT'] }} study you signed up for starts in a few minutes! Please read through the entire invitation before proceeding.

**Access to your dedicated participant link for the first step is NOW LIVE:**<br>
*Please make sure to have the following code at hand* ***when entering the first stage live online study: {{ $data['TAGCODE'] }}.***<br>
@component('mail::button', ['url' => $data['REMESHLINK']])
    Click here to enter first stage
@endcomponent

**Access to your dedicated participant link for the second stage follow-up webcam focus-group is now live:**<br>
***Your unique code for the second stage follow-up webcam focus-group is {{ $data['SERIAL'] }}.*** *Please provide this code when asked in order to receive your reward.*<br>
@component('mail::button', ['url' => $data['QUALLINK']])
    Click here to enter second stage
@endcomponent

The reward you will get for completing all steps is **{{ $data['INCENTIVETOTAL'] }}** and will be provided to you as: {{ $data['INCENTIVETYPE'] }}.

In case you complete only the first stage live online study, you will receive **{{ $data['INCENTIVEQUANT'] }}**.

We encourage you to join now so that we can start the live {{ $data['STUDYSUBJECT'] }} study on time.

Here is what is going to happen next:

@component('mail::panel')
<img src="{{ asset('img/instant_labs/icon_3.png') }}" alt="Step 1" style="height: 35px; width: auto"> Step 1 – Click the first links to join as soon as you receive it.
@endcomponent

@component('mail::panel')
<img src="{{ asset('img/instant_labs/icon_4.png') }}" alt="Step 2" style="height: 35px; width: auto"> Step 2 – You will enter a waiting room while we are waiting for all participants to join. Please do not navigate away from this page! Be advised that we have a fixed capacity and the live online study allows only a limited number of people to continue with participation. Five minutes before its start, the button „Join conversation“ will get activated. Make sure you don’t miss it so you can be part of our live online study.
@endcomponent

@component('mail::panel')
<img src="{{ asset('img/instant_labs/icon_5.png') }}" alt="Step 3" style="height: 35px; width: auto"> Step 3 – The live online study starts! A moderator will guide you and the rest of the participants through the conversation. Please speak your mind on the topic and stay focused. The more information you can provide, the more we can learn and put your opinions into action.
@endcomponent

@component('mail::panel')
<img src="{{ asset('img/instant_labs/icon_6.png') }}" alt="Step 4" style="height: 35px; width: auto"> Step 4 – Once the moderator ends the live online survey, the first stage is complete. Return to the same e-mail to click on the second link and join the follow-up webcam focus-group.
@endcomponent

@component('mail::panel')
<img src="{{ asset('img/instant_labs/icon_8.png') }}" alt="Step 5" style="height: 35px; width: auto"> Step 5 – In focus-groups, we like to hear each person speak their mind. This is the reason why only 4 to 6 respondents will be selected for this phase. Please stay connected inside the meeting room. If you are not selected to participate, you will be automatically disconnected from the chat and we will make sure to follow up with a token of appreciation for your waiting time at the same time we process your activity for the live online study.
@endcomponent

@component('mail::panel')
<img src="{{ asset('img/instant_labs/icon_5.png') }}" alt="Step 6" style="height: 35px; width: auto"> Step 6 – If you are selected to participate, you will begin the focus-group and share your opinions in a conversation with the moderator and the other participants.
@endcomponent

@component('mail::panel')
<img src="{{ asset('img/instant_labs/icon_7.png') }}" alt="Step 7" style="height: 35px; width: auto"> Step 7 – Once the moderator ends the focus-group, you’re done. Congratulations! It’s reward time.
@endcomponent

If you have participated in the first stage live online study until the end and you have participated in the second stage follow-up webcam focus-group, you are eligible for the **{{ $data['INCENTIVETOTAL'] }}** reward, provided to you as: {{ $data['INCENTIVETYPE'] }}!

If you have participated in the first stage live online study until the end and you have only joined the meeting room for the second stage but were dismissed, you are eligible for the **{{ $data['INCENTIVEQUANT'] }}** plus a token of appreciation for your waiting time!

Please allow up to 15 business days after completing the project for processing your activity for the study.

We are taking your participation as agreement that you have also read the Privacy Policy of our trusted partner, [Remesh]({{ $data['REMESHPRIVACYLINK'] }}). We also remind you that [the confidentiality terms]({{ $data['NDALINK'] }}) you already accepted apply to all stages of this live {{ $data['STUDYSUBJECT'] }} study.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
