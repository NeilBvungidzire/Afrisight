@extends('layouts.website')

@section('title', __('profile.sub_pages.delete_account.heading') . ' - ' . $name)

@section('content')
    <section class="py-5">
        <div class="container py-5">
            <div class="row">
                <div class="offset-sm-1 col-sm-10 offset-md-2 col-md-8 offset-lg-3 col-lg-6">
                    <h1 class="display-3 mb-3">
                        {{ __('profile.sub_pages.delete_account.heading') }}
                    </h1>

                    @if (session('status'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('status') }}
                        </div>

                        <hr/>
                    @endif

                    <p>{{ __('auth.delete-account-confirmation-m1') }}</p>
                    <p>{{ __('auth.delete-account-confirmation-m2') }}</p>
                    <p class="font-weight-bold">{{ __('auth.delete-account-confirmation-m3') }}</p>
                    <hr/>

                    <form method="POST" action="{{ route('profile.delete-account.delete') }}" id="delete-account">
                        @csrf
                        @method('delete')

                        <div class="form-row">
                            <div class="form-group col-sm-6">
                                <a class="btn btn-primary btn-block" href="{{ route('profile.security') }}">
                                    {{ __('profile.sub_pages.delete_account.cancel_text') }}
                                </a>
                            </div>
                            <div class="form-group col-sm-6">
                                <button type="submit" class="btn btn-danger btn-block"
                                        onclick="event.preventDefault(); deleteAccount()">
                                    {{ __('profile.sub_pages.delete_account.confirm_text') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

<script>
    function deleteAccount () {
        var form = document.getElementById('delete-account');

        if ( ! form.length) {
            return;
        }

        if (confirm("{{ __('auth.delete-account-last-confirmation') }}")) {
            form.submit();
        }
    }
</script>
