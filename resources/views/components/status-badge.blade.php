@props(['active' => false, 'activeLabel' => 'Activo', 'inactiveLabel' => 'Inactivo'])

<span {{ $attributes->class([
    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
    'bg-emerald-100 text-emerald-800' => $active,
    'bg-slate-200 text-slate-700' => ! $active,
]) }}>
    {{ $active ? $activeLabel : $inactiveLabel }}
</span>
