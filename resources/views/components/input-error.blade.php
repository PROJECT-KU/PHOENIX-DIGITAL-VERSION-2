@props(['messages'])

@if ($messages)
    @foreach ((array) $messages as $message)
        <small {{ $attributes->merge(['class' => 'form-text text-danger']) }}>{{ $message }}</small>
    @endforeach
@endif
