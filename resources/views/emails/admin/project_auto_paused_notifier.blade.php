@component('mail::message')
# Dear admin,

Sample with code "{{ $projectCode }}" is auto-paused, because it reached the total completes limit as set.

You can open the project page by clicking the below button.

@component('mail::button', ['url' => $link])
Open Project Page
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
