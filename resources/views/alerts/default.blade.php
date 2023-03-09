<div class="alert alert-{{ $type }}" role="alert">
    @if (isset($heading))
        <h4 class="alert-heading">{!! $heading !!}</h4>
    @endif
    <p>{!! $body !!}</p>
</div>
