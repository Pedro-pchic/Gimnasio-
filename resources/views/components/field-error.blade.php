@props([
    'name' => null,
    'field' => null,
])

@php($errorKey = $name ?? $field)

@if ($errorKey && $errors->has($errorKey))
    <p {{ $attributes->merge(['class' => 'mt-2 flex items-center gap-1.5 rounded-lg bg-red-50 px-2.5 py-2 text-sm font-medium text-red-700']) }}><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4 shrink-0" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 8v4m0 4h.01" stroke-linecap="round" /></svg>{{ $errors->first($errorKey) }}</p>
@endif
