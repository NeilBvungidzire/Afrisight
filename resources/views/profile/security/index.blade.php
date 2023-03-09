@extends('layouts.profile')

@section('title', __('profile.sub_pages.security.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3 px-4">{{ __('profile.sub_pages.security.heading') }}</h1>

    <div class=" bg-light my-3 py-3 px-4">
        <h2 class="h5">{{ __('profile.sub_pages.login_details.heading') }}</h2>
        <div class="row">
            <div class="col-sm-6">
                <dl class="row">
                    <dt class="col-xl-3">{{ __('profile.sub_pages.login_details.email.label') }}</dt>
                    <dd class="col-xl-9">
                        {{ auth()->user()->email }}
                        @if($canChangeEmail)
                            <a href="{{ route('profile.change-email.edit') }}" class="btn btn-link btn-sm">
                                {{ __('profile.sub_pages.login_details.password.change_password') }}
                            </a>
                        @else
                            <p class="small">{{ __('profile.sub_pages.login_details.email.info_text') }}</p>
                        @endif
                    </dd>
                </dl>
            </div>
            <div class="col-sm-6">
                <dl class="row">
                    <dt class="col-xl-3">{{ __('profile.sub_pages.login_details.password.label') }}</dt>
                    <dd class="col-xl-9">
                        ******
                        <a href="{{ route('profile.password.edit') }}" class="btn btn-link btn-sm">
                            {{ $hasPassword ? __('profile.sub_pages.login_details.password.change_password') : __('profile.sub_pages.login_details.password.set_password') }}
                        </a>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    @if ((config('services.facebook.enabled') && ! $isOperaMini) || config('services.google.enabled'))
        <div class=" bg-light my-3 py-3 px-4">
            <h2 class="h5">{{ __('profile.sub_pages.linked_accounts.heading') }}</h2>
            <div class="row">
                @if (config('services.facebook.enabled') && ! $isOperaMini)
                    <div class="col-sm-6">
                        <dl class="row">
                            <dt class="col-xl-3">{{ __('profile.sub_pages.linked_accounts.facebook') }}</dt>
                            <dd class="col-xl-9">
                                @isset ($socialAccounts['facebook'])
                                    {{ $socialAccounts['facebook']['email'] }}
                                    <a href="#" class="btn btn-link btn-sm"
                                       onclick="event.preventDefault(); unlinkFromFacebook()"
                                    >
                                        {{ __('profile.sub_pages.linked_accounts.unlink') }}
                                    </a>
                                    <form id="unlink-facebook-form" action="{{ route('facebook.unlink') }}"
                                          method="POST"
                                          style="display: none;">
                                        @csrf
                                        @method('delete')
                                    </form>
                                @else
                                    {{ __('profile.sub_pages.linked_accounts.not_linked') }}
                                    <a href="{{ route('facebook.link') }}" class="btn btn-link btn-sm">
                                        {{ __('profile.sub_pages.linked_accounts.link') }}
                                    </a>
                                @endif
                            </dd>
                        </dl>
                    </div>
                @endif
                @if (config('services.google.enabled'))
                    <div class="col-sm-6">
                        <dl class="row">
                            <dt class="col-xl-3">{{ __('profile.sub_pages.linked_accounts.google') }}</dt>
                            <dd class="col-xl-9">
                                @isset ($socialAccounts['google'])
                                    {{ $socialAccounts['google']['email'] }}
                                    <a href="#" class="btn btn-link btn-sm"
                                       onclick="event.preventDefault(); unlinkFromGoogle()"
                                    >
                                        {{ __('profile.sub_pages.linked_accounts.unlink') }}
                                    </a>
                                    <form id="unlink-google-form" action="{{ route('google.unlink') }}"
                                          method="POST"
                                          style="display: none;">
                                        @csrf
                                        @method('delete')
                                    </form>
                                @else
                                    {{ __('profile.sub_pages.linked_accounts.not_linked') }}
                                    <a href="{{ route('google.link') }}" class="btn btn-link btn-sm">
                                        {{ __('profile.sub_pages.linked_accounts.link') }}
                                    </a>
                                @endif
                            </dd>
                        </dl>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class=" bg-light my-3 py-3 px-4">
        <h2 class="h5">{{ __('profile.sub_pages.delete_account.heading') }}</h2>
        <p>{{ __('auth.delete-account') }}</p>
        <a class="btn btn-outline-danger btn-sm" href="{{ route('profile.delete-account.show') }}">
            {{ __('profile.sub_pages.delete_account.delete_link') }}
        </a>
    </div>
@endsection

<script>
    function confirmSubmit (message, formId) {
        var form = document.getElementById(formId);

        if ( ! form.length) {
            return;
        }

        if (confirm(message)) {
            form.submit();
        }
    }

    function unlinkFromFacebook () {
        confirmSubmit("{{ __('auth.unlink-account', ['name' => __('profile.sub_pages.linked_accounts.facebook')]) }}", 'unlink-facebook-form');
    }

    function unlinkFromGoogle () {
        confirmSubmit("{{ __('auth.unlink-account', ['name' => __('profile.sub_pages.linked_accounts.google')]) }}", 'unlink-google-form');
    }
</script>
