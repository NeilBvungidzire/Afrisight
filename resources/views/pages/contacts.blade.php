@extends('layouts.website')

@section('title', __('pages.contacts.heading') . ' - ' . config('app.name'))

@section('hero-unit')
    @component('hero-units.default', ['heroUnitVariant' => 'hero-unit--about'])
        <div class="row">
            <div class="col-lg-10">
                <h1 class="display-4">{{ __('pages.contacts.heading') }}</h1>
                <p class="lead">{{ __('pages.contacts.catchphrase') }}</p>
            </div>
        </div>
    @endcomponent()
@endsection

@section('content')
    <section class="py-5 bg-light">
        <div class="container">
            @if (session('status'))
                <div class="alert alert-info" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            @if(count($categories))
                <h2 class="display-4">{{ __('pages.faq.heading') }}</h2>
                <p>{{ __('pages.faq.subheading') }}</p>
                <ul>
                    @foreach($categories as $category)
                        <li>
                            <a href="#{{ $category->slug }}">{{ __($category->name) }}</a>
                        </li>
                    @endforeach
                </ul>

                @foreach($categories as $category)
                    <h2 class="mt-5 mb-3" id="{{ $category->slug }}">{{ __($category->name) }}</h2>

                    @foreach($category->questions as $question)
                        <article class="bg-white p-3 my-3" id="{{ $question->slug }}">
                            <div class="lead mb-1">{{ __($question->question) }}</div>
                            <div class="text-muted">{{ __($question->answer) }}</div>
                        </article>
                    @endforeach
                @endforeach
            @endif

            <h2 class="mt-5 mb-3">{{ __('pages.faq.answer_not_found.line_1') }}</h2>
            <p>{!! __('pages.faq.answer_not_found.line_2', ['url' => '<a href="' . route('contacts.form') . '">' . __('pages.attributes.contact_form') . '</a>']) !!}</p>
        </div>
    </section>
@endsection
