@extends('layouts.profiling')

@section('content')
    <h1>{{ $country->name }}</h1>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Questions & Answers</th>
            <th>Answer options</th>
        </tr>
        </thead>
        <tbody>
        @foreach($cintQuestions->data as $category)
            <tr>
                <td colspan="2">{{ $category['category_title'] }}</td>
            </tr>

            @foreach($category['variables'] as $variable)
                <tr>
                    <td>{{ $variable['variable_label'] }}</td>
                    <td></td>
                </tr>

                @foreach($variable['values'] as $value)
                    <tr>
                        <td></td>
                        <td>{{ $value['value_label'] }}</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
        </tbody>
    </table>
@endsection
