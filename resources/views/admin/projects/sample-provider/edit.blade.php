@extends('admin.projects.sample-provider.layout')

@section('inner-content')

    <form action="{{ route('admin.sample-provider.update', ['id' => $record->id]) }}"
          method="post" class="form">
        @csrf
        @method('put')

        @include('admin.projects.sample-provider.form-fields')

        <div class="form-group row">
            <div class="col-12">
                <hr>
            </div>
            <div class="col-sm-3 col-md-2 offset-sm-6 offset-md-8">
                <a href="{{ route('admin.sample-provider.index') }}"
                   class="btn btn-outline-info btn-block">
                    Cancel
                </a>
            </div>
            <div class="col-sm-3 col-md-2">
                <button type="submit" class="btn btn-primary btn-block">Update</button>
            </div>
        </div>
    </form>

@endsection
