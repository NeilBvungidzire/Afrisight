<div class="container py-5">
    <p>{{ __('footer.about.heading') }}</p>
    <div class="row">
        <div class="col-lg-8">
            <p>
                <small>{{ __('footer.about.line_1') }}</small>
            </p>
            <p>
                <small>{{ __('footer.about.line_2') }}</small>
            </p>
        </div>
        <div class="col-lg-4 py-3 py-lg-0">
            <div class="row">
                @php
                    $links = [
                        ['routeName' => 'about', 'label' => __('pages.about.heading')],
                        ['routeName' => 'rewards', 'label' => __('pages.rewards.heading')],
                        ['routeName' => 'contacts', 'label' => __('pages.contacts.heading')],
                        ['routeName' => 'privacy-policy', 'label' => __('privacy_policy.heading')],
                        ['routeName' => 'terms-and-conditions', 'label' => __('terms_conditions.heading')],
                    ];
                @endphp

            @foreach($links as $link)
                    <div class="col-sm-6 col-md-4 col-lg-12">
                        <a href="{{ route($link['routeName']) }}" class="btn btn-link">{{ $link['label'] }}</a>
                    </div>
                @endforeach

                @auth
                    <div class="col-sm-6 col-md-4 col-lg-12">
                        <a class="btn btn-link" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('pages.logout.heading') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</div>
<div class="bg-dark text-white">
    <div class="container py-3">
        <small>
            © {{ date('Y') }} {{ __('footer.rights_note') }}
        </small>
    </div>
</div>
