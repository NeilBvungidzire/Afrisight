@extends('layouts.profile')

@section('title', __('profile.sub_pages.general_info.heading') . ' - ' . $name)

@section('content')
    <h1 class="h4 py-3 px-4">{{ __('profile.sub_pages.general_info.heading') }}</h1>

    <div class="bg-light py-3 my-3 px-4">
        @foreach ($fields as $field)
            <dl class="row">
                <dt class="col-sm-5 col-xl-4">{{ $field['label'] }}</dt>
                <dd class="col-sm-7 col-xl-8">{{ $field['value'] }}</dd>
            </dl>
        @endforeach
        <div class="row">
            <div class="offset-0 col offset-md-6 col-md-6 offset-lg-8 col-lg-4">
                <a href="{{ route('profile.basic-info.edit') }}" class="btn btn-primary btn-block">
                    {{ __('profile.sub_pages.general_info.cta_text') }}
                </a>
            </div>
        </div>
    </div>
@endsection
