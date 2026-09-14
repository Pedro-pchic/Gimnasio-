@props(['active' => false, 'activeLabel' => 'Activo', 'inactiveLabel' => 'Inactivo'])

<span {{ $attributes->class([
    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold',
    'bg-emerald-100 text-emerald-800' => $active,
    'bg-slate-200 text-slate-700' => ! $active,
]) }}>
    <span @class([
        'size-1.5 rounded-full',
        'bg-emerald-500' => $active,
        'bg-slate-400' => ! $active,
    ])></span>
    {{ $active ? $activeLabel : $inactiveLabel }}
</span>
