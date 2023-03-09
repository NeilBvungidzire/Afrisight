@extends('layouts.clean')

@section('content')
    <h1>Results per survey</h1>

    <table class="table table-bordered">
        <thead>
        <tr>
            @foreach ($columns as $label => $count)
                @if ($count === 1)
                    <th>{{ $label }}</th>
                    @continue
                @endif

                @for ($i = 0; $i < $count; $i++)<th>{{ $label }}</th>@endfor
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach ($results as $record)
            <tr>
                @foreach ($columns as $label => $count)
                    @if ( ! array_key_exists($label, $record))
                        @if ($count === 1)
                            <td></td>
                            @continue
                        @endif

                        @for ($i = 0; $i < $count; $i++)<td></td>@endfor
                    @else
                        @if ($record[$label] === null)
                            <td></td>
                            @continue
                        @endif

                        @foreach ((array)$record[$label] as $value)
                            <td>{{ $value }}</td>
                        @endforeach
                    @endif
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
