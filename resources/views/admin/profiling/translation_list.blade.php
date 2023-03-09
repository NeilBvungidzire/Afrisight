@php($languages = ['en','fr','pt'])
@foreach ($languages as $language)
    <small class="form-text text-muted">({{ $language }}) {{ __($value, [], $language) }}</small>
@endforeach
