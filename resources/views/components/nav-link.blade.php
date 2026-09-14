@props([
    'active' => false,
    'icon' => 'grid',
])

<a {{ $attributes->class([
    'group flex items-center gap-2 rounded-xl border-l-2 px-3 py-2 text-sm font-medium transition duration-150',
    'border-blue-400 bg-slate-800 text-white shadow-lg shadow-blue-950/20' => $active,
    'border-transparent text-slate-300 hover:bg-white/8 hover:text-white' => ! $active,
]) }}>
    <span @class([
        'flex size-8 shrink-0 items-center justify-center rounded-lg transition',
        'bg-white/15 text-white' => $active,
        'bg-slate-800 text-slate-400 group-hover:bg-slate-700 group-hover:text-slate-100' => ! $active,
    ])>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4" aria-hidden="true">
            @switch($icon)
                @case('building')
                    <path d="M4 21h16M6 21V5.5A1.5 1.5 0 0 1 7.5 4h9A1.5 1.5 0 0 1 18 5.5V21M9 8h.01M12 8h.01M15 8h.01M9 12h.01M12 12h.01M15 12h.01M10 21v-4h4v4" stroke-linecap="round" />
                    @break
                @case('users')
                    <path d="M16 20v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM17 11a3 3 0 1 0 0-6M21 20v-1a4 4 0 0 0-3-3.87" stroke-linecap="round" stroke-linejoin="round" />
                    @break
                @case('card')
                    <rect x="3" y="5" width="18" height="14" rx="2" />
                    <path d="M3 10h18M7 15h3" stroke-linecap="round" />
                    @break
                @case('briefcase')
                    <rect x="3" y="7" width="18" height="12" rx="2" />
                    <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18M10 12v2h4v-2" stroke-linecap="round" stroke-linejoin="round" />
                    @break
                @case('scan')
                    <path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3M8.5 12a3.5 3.5 0 0 1 7 0v2a3.5 3.5 0 0 1-7 0v-2Z" stroke-linecap="round" stroke-linejoin="round" />
                    @break
                @case('calendar')
                    <rect x="3" y="5" width="18" height="16" rx="2" />
                    <path d="M16 3v4M8 3v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01" stroke-linecap="round" />
                    @break
                @case('box')
                    <path d="m21 8-9 5-9-5 9-5 9 5ZM3 8v8l9 5 9-5V8M12 13v8" stroke-linecap="round" stroke-linejoin="round" />
                    @break
                @case('chart')
                    <path d="M4 19V5M4 19h16M8 16v-4M12 16V8M16 16v-7" stroke-linecap="round" />
                    @break
                @case('settings')
                    <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.1 2.1-.06-.06A1.7 1.7 0 0 0 15.76 18a1.7 1.7 0 0 0-1.26 1.64v.09h-3v-.09A1.7 1.7 0 0 0 10.24 18a1.7 1.7 0 0 0-1.88.34l-.06.06-2.1-2.1.06-.06A1.7 1.7 0 0 0 6.6 15a1.7 1.7 0 0 0-1.64-1.26h-.09v-3h.09A1.7 1.7 0 0 0 6.6 9.48a1.7 1.7 0 0 0-.34-1.88L6.2 7.54l2.1-2.1.06.06A1.7 1.7 0 0 0 10.24 5a1.7 1.7 0 0 0 1.26-1.64v-.09h3v.09A1.7 1.7 0 0 0 15.76 5a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.1 2.1-.06.06a1.7 1.7 0 0 0-.34 1.88 1.7 1.7 0 0 0 1.64 1.26h.09v3h-.09A1.7 1.7 0 0 0 19.4 15Z" stroke-linecap="round" stroke-linejoin="round" />
                    @break
                @default
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="3" y="14" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
            @endswitch
        </svg>
    </span>
    <span class="min-w-0 truncate">{{ $slot }}</span>
</a>
