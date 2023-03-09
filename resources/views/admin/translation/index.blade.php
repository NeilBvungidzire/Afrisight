@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . config('app.name'))

@section('content')
    <h1>Overview translations</h1>

    <div class="row">
        <div class="col-sm-10 pb-3">
            <p>
                <span class="text-muted">Select by tag:</span>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['tags' => ''])) }}">All</a>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['tags' => 'page'])) }}">page</a>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['tags' => 'faq'])) }}">faq</a>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['tags' => 'email'])) }}">email</a>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['tags' => 'profiling'])) }}">profiling</a>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['tags' => 'model'])) }}">model</a>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['tags' => 'validation'])) }}">validation</a>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['tags' => 'payout'])) }}">payout</a>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['tags' => 'screening'])) }}">screening</a>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['tags' => 'contact'])) }}">contact</a>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['tags' => 'referral_management'])) }}">referral management</a>

                <span class="text-muted pl-3">Select by published:</span>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['published' => ''])) }}">All</a>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['published' => 1])) }}">Yes</a>
                <a class="btn btn-sm btn-outline-info"
                   href="{{ route('translation.index', array_merge(request()->query(), ['published' => 0])) }}">No</a>

                <span class="pl-3">Total found: {{ $translations->count() }}</span>
            </p>
        </div>

        @if(in_array(Auth::user()->role, ['SUPER_ADMIN', 'ADMIN']))
            <div class="col-sm-2 text-right pb-3">
                <a href="{{ route('translation.create') }}" class="btn btn-primary">Add new translation</a>
            </div>
        @endif
    </div>

    <table class="table table-hover">
        <thead>
        <tr>
            @if(in_array(Auth::user()->role, ['SUPER_ADMIN', 'ADMIN']))
                <th>Key</th>
            @endif
            <th>Tags</th>
            <th>Translations</th>
            <th>Published</th>
            <th>Param error</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($translations as $translation)
            <tr>
                @if(in_array(Auth::user()->role, ['SUPER_ADMIN', 'ADMIN']))
                    <td>{{ $translation->key }}</td>
                @endif
                <td>
                    @if ($translation->tags)
                        @foreach ($translation->tags as $tag)
                            <span class="badge badge-secondary">{{ $tag }}</span>
                        @endforeach
                    @else
                        Empty
                    @endif
                </td>
                <td>
                    @foreach($translation->text as $locale => $text)
                        <p>({{ $locale }}) {{ $text }}</p>
                    @endforeach
                </td>
                <td>{{ $translation->is_published ? 'Yes' : 'No' }}</td>
                <td>
                    @foreach($translation->text as $locale => $text)
                        @php($unusedElements = array_diff($elements[$translation->id]['list'], $elements[$translation->id]['by_locale'][$locale]))
                        @if (count($unusedElements))
                            <p>
                                @foreach($unusedElements as $param)
                                    <span class="badge badge-danger">{{ $param }}</span>
                                @endforeach
                            </p>
                        @endif
                    @endforeach
                </td>
                <td style="width: 200px;">
                    <a href="{{ route('translation.show', ['translation' => $translation->id]) }}"
                       class="btn btn-sm btn-outline-secondary">View</a>

                    <a href="{{ route('translation.edit', ['translation' => $translation->id]) }}"
                       class="btn btn-sm btn-outline-secondary">Edit</a>

                    @if(Auth::user()->role === 'SUPER_ADMIN')
                        @php($formId = "delete-translation-" . $translation->id)
                        <a href="#delete" class="btn btn-sm btn-outline-danger"
                           onclick="event.preventDefault(); document.getElementById('{{ $formId }}').submit();">
                            Delete
                        </a>
                        <form id="{{ $formId }}" method="post" style="display: none;"
                              action="{{ route('translation.destroy', ['translation' => $translation->id]) }}">
                            @csrf
                            @method('delete')
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
