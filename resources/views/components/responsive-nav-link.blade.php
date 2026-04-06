@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-xl border border-sky-400/20 bg-sky-400/10 py-2.5 ps-4 pe-4 text-start text-base font-semibold text-sky-100 transition duration-200 ease-in-out focus:outline-none'
            : 'block w-full rounded-xl border border-transparent py-2.5 ps-4 pe-4 text-start text-base font-medium text-slate-400 transition duration-200 ease-in-out hover:border-slate-700 hover:bg-slate-800/70 hover:text-slate-100 focus:outline-none focus:text-slate-100';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
