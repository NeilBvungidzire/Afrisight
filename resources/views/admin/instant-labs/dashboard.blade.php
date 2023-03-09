@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    @include('admin.instant-labs.header', ['header' => 'Dashboard'])

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
                                @if (isset($record->data['planned']) && $record->data['planned'])
                                    <span class="badge badge-success">Planned</span>
                                @endif
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
@endsection
