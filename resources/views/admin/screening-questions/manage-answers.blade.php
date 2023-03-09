@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . 'Manage answers' . ' - ' . config('app.name'))

@section('content')
    <h1>Manage answers</h1>

    @alert

    <div class="card my-3">
        <div class="card-body">
            <p class="font-weight-bold">Existing answers</p>
            <table class="table table-hover table-sm">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Value</th>
                    <th>Label</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($question->answer_options as $option)
                    <tr>
                        <td>{{ $option['id'] }}</td>
                        <td>{{ $option['value'] }}</td>
                        <td>{{ __($option['label']) }}</td>
                        <td>
                            <a href="{{ route('admin.screening.delete-answer', ['id' => $question->id, 'answer-id' => $option['id']]) }}"
                               class="btn btn-sm btn-danger m-1 delete-answer">
                                Delete answer
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <script>
                const deleteButtons = document.getElementsByClassName('delete-answer');
                const approve = function(event) {
                    event.preventDefault();
                    if (confirm('Are you sure you want to delete this answer?')) {
                        window.location = event.target.href;
                    }
                };

                for (let i = 0; i < deleteButtons.length; i++) {
                    deleteButtons[i].addEventListener('click', approve, false);
                }
            </script>
        </div>
    </div>

    <div class="card my-3">
        <div class="card-body">
            <p class="font-weight-bold">Add answer</p>
            <p class="small">To edit existing answer, just refer to the ID and set the updated info.</p>

            <form action="{{ route('admin.screening.update-answers', ['id' => $question->id]) }}" method="post">
                @csrf
                @method('put')

                {{-- ID --}}
                @php($attribute = 'answer_id')
                @php($key = $attribute)
                @php($value = old($attribute) ?? $nextId)
                <div class="form-group row">
                    <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $key }}">
                        Answer ID
                    </label>

                    <div class="col-sm-7 col-lg-8">
                        <input type="number" class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}"
                               id="{{ $key }}" name="{{ $attribute }}" value="{{ $value }}" autofocus>

                        @if ($errors->has($key))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first($key) }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Value --}}
                @php($attribute = 'answer_value')
                @php($key = $attribute)
                @php($value = old($attribute) ?? '')
                <div class="form-group row">
                    <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $key }}">
                        Value
                    </label>

                    <div class="col-sm-7 col-lg-8">
                        <input type="text" class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}"
                               id="{{ $key }}" name="{{ $attribute }}" value="{{ $value }}" required>

                        @if ($errors->has($key))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first($key) }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Value --}}
                @php($attribute = 'answer_label')
                @php($key = $attribute)
                @php($value = old($attribute) ?? '')
                <div class="form-group row">
                    <label class="col-sm-5 col-lg-4 col-form-label" for="{{ $key }}">
                        Label
                    </label>

                    <div class="col-sm-7 col-lg-8">
                        <input type="text" class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}"
                               id="{{ $key }}" name="{{ $attribute }}" value="{{ $value }}" required>

                        @if ($errors->has($key))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first($key) }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-4 offset-sm-4 col-lg-3 offset-lg-6">
                        <a href="{{ route('admin.screening.index') }}" class="btn btn-outline-secondary btn-block">Cancel</a>
                    </div>
                    <div class="col-sm-4 col-lg-3">
                        <button type="submit" class="btn btn-primary btn-block">Add / Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
