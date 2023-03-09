@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.instant-labs.header', ['header' => 'Plan'])

    <div class="row">
        <div class="col-12">
            <p>Open to be planned</p>
            <div class="accordion" id="records">
                @foreach ($data as $index => $record)
                    <div class="card">
                        <div class="card-header" id="heading{{ $index }}">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapse{{ $index }}" aria-expanded="false"
                                        aria-controls="collapse{{ $index }}">
                                    {{ $record->data['ID'] ?? $record->id }}
                                    @if (isset($record->data['reference_datetime']) && isset($record->data['reference_timezone']))
                                        ({{ $record->data['reference_datetime'] }}, {{ $record->data['reference_timezone'] }})
                                    @else
                                        (Incorrect data)
                                    @endif
                                </button>
                            </h2>
                        </div>
                        <div id="collapse{{ $index }}" class="collapse" aria-labelledby="heading{{ $index }}">
                            <div class="card-body">
                                <dl class="row">
                                    @foreach ($record->data as $key => $value)
                                        <dt class="col-sm-3 col-md-2">{{ $key }}</dt>
                                        <dd class="col-sm-9 col-md-10">{{ $value }}</dd>
                                    @endforeach

                                    <dt class="col-sm-3 col-md-2">Created</dt>
                                    <dd class="col-sm-9 col-md-10">{{ $record->created_at }}</dd>

                                    <dt class="col-sm-3 col-md-2">Updated</dt>
                                    <dd class="col-sm-9 col-md-10">{{ $record->updated_at }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-12">
            <hr>
        </div>
        <div class="col-6">
            <a href="{{ route('admin.instant_labs.dashboard') }}" class="btn btn-danger btn-block">Cancel</a>
        </div>
        <div class="col-6">
            <a href="{{ route('admin.instant_labs.plan.queue') }}" class="btn btn-success btn-block">Plan</a>
        </div>
    </div>
@endsection
