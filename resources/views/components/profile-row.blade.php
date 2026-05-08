@props(['label', 'value'])

<div class="flex items-center justify-between px-4 py-3">
    <p class="text-white/40 text-xs font-medium w-32 shrink-0">{{ $label }}</p>
    <p class="text-white text-sm font-medium text-right">{{ $value }}</p>
</div>
