@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => route('home')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            <table>
                <tbody>
                <tr>
                    <td>
                        <p>
                            <a href="{{ route('privacy-policy') }}" target="_blank">{{ __('email/general.privacy_cookie_link_text') }}</a> | <a
                                href="{{ route('terms-and-conditions') }}" target="_blank">{{ __('email/general.terms_conditions_link_text') }}</a>
                        </p>
                    </td>
                </tr>
                @if (isset($unsubscribeUrl))
                    <tr>
                        <td>
                            <p>
                                <a href="{{ $unsubscribeUrl }}" target="_blank">{{ __('email/general.unsubscribe_link_text') }}</a>
                            </p>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td><p>© {{ date('Y') }} {{ config('app.name') }}. @lang('email/general.rights_note').</p></td>
                </tr>
                </tbody>
            </table>
        @endcomponent
    @endslot
@endcomponent
