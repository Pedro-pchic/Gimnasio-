@props([
    'name' => null,
    'field' => null,
])

@php($errorKey = $name ?? $field)

@if ($errorKey && $errors->has($errorKey))
    <p {{ $attributes->merge(['class' => 'mt-1 text-sm text-red-600']) }}>{{ $errors->first($errorKey) }}</p>
@endif
