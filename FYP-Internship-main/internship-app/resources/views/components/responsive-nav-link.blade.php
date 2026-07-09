@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-xl bg-indigo-50 px-4 py-3 text-start text-sm font-bold text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition'
            : 'block w-full rounded-xl px-4 py-3 text-start text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
