@props(['active'])

@php
$classes = ($active ?? false)
            ? 'group relative inline-flex items-center rounded-full border border-sky-400/25 bg-sky-400/10 px-4 py-2 text-sm font-semibold leading-5 text-sky-100 shadow-sm shadow-slate-950/20 transition duration-200 ease-in-out focus:outline-none'
            : 'group relative inline-flex items-center rounded-full border border-transparent px-4 py-2 text-sm font-medium leading-5 text-slate-400 transition duration-200 ease-in-out hover:border-slate-700 hover:bg-slate-800/70 hover:text-slate-100 focus:outline-none focus:text-slate-100';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    <span @class([
        'absolute inset-x-3 -bottom-px h-px rounded-full transition duration-200 ease-in-out',
        'bg-gradient-to-r from-sky-400/0 via-sky-300 to-sky-400/0 opacity-100' => ($active ?? false),
        'bg-gradient-to-r from-slate-500/0 via-slate-400 to-slate-500/0 opacity-0 group-hover:opacity-100' => ! ($active ?? false),
    ])></span>
    {{ $slot }}
</a>
