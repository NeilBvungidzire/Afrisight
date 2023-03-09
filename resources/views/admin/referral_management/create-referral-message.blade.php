@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    <h1>Draft Referral Engagement Message</h1>

    @alert

    <form action="{{ route('admin.referral_management.engagement.send', ['id' => $id, 'channel' => $channel]) }}" method="post" class="form">
        @csrf

        @php
            $fieldName = 'mobile_number';
            $value = old($fieldName) ?? $mobileNumber ?? '';
        @endphp
        <div class="form-group">
            <label for="{{ $fieldName }}">Mobile Number</label>
            <input type="text" class="form-control" id="{{ $fieldName }}" name="{{ $fieldName }}" value="{{ $value }}" />
            <small id="{{ $fieldName }}" class="form-text text-muted">Make sure it also includes the country code (starting with "00" or "+"), for example, 0031612345678 or +31612345678. For testing purposes you can also set your own mobile number to send to yourself and check the message. Make sure to only use this when really needed, because for each SMS message we are charged.</small>
        </div>

        @php
            $fieldName = 'message';
            $value = old($fieldName) ?? '';
        @endphp
        <div class="form-group">
            <label for="{{ $fieldName }}">Message <span class="badge badge-info">message length: <span id="count">0</span></span></label>
            <textarea class="form-control" id="{{ $fieldName }}" name="{{ $fieldName }}" rows="3" onchange="count(this)">{{ $value }}</textarea>
            <small id="{{ $fieldName }}" class="form-text text-muted">Try to stay below 160 message length. Params: {{ implode(", ", $params) }}</small>
        </div>

        <div class="form-group row">
            <div class="col-sm-4 col-lg-3 offset-sm-4 offset-lg-6">
                <a href="{{ route('admin.referral_management.overview') }}" class="btn btn-outline-info btn-block">Cancel</a>
            </div>
            <div class="col-sm-4 col-lg-3">
                <button type="submit" class="btn btn-primary btn-block">Send</button>
            </div>
        </div>
    </form>

    <script>
        function count(element) {
            document.getElementById('count').innerHTML = element.value.length;
        }
    </script>
@endsection
