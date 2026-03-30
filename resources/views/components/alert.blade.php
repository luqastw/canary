@props([
    'type' => 'success',
])

@php
$classes = match($type) {
    'success' => 'bg-green-50 border-green-200 text-green-800',
    'error' => 'bg-red-50 border-red-200 text-red-800',
    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
    'info' => 'bg-blue-50 border-blue-200 text-blue-800',
    default => 'bg-gray-50 border-gray-200 text-gray-800',
};

$iconClasses = match($type) {
    'success' => 'text-green-500',
    'error' => 'text-red-500',
    'warning' => 'text-yellow-500',
    'info' => 'text-blue-500',
    default => 'text-gray-500',
};
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border p-4 $classes"]) }}>
    <div class="flex items-start gap-3">
        @if($type === 'success')
            <svg class="h-5 w-5 flex-shrink-0 {{ $iconClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        @elseif($type === 'error')
            <svg class="h-5 w-5 flex-shrink-0 {{ $iconClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        @elseif($type === 'warning')
            <svg class="h-5 w-5 flex-shrink-0 {{ $iconClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        @else
            <svg class="h-5 w-5 flex-shrink-0 {{ $iconClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        @endif
        <div class="flex-1 text-sm">
            {{ $slot }}
        </div>
    </div>
</div>
