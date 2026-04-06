@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md border-slate-700 bg-slate-900/90 text-slate-100 placeholder-slate-500 shadow-sm focus:border-sky-500 focus:ring-sky-500']) }}>
