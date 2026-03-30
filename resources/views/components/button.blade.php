@props([
    'type' => 'primary',
    'size' => 'md',
    'href' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$typeClasses = match($type) {
    'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
    'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-indigo-500',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    default => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
};

$sizeClasses = match($size) {
    'sm' => 'px-3 py-1.5 text-sm gap-1.5',
    'md' => 'px-4 py-2 text-sm gap-2',
    'lg' => 'px-6 py-3 text-base gap-2',
    default => 'px-4 py-2 text-sm gap-2',
};

$classes = "$baseClasses $typeClasses $sizeClasses";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'submit']) }}>
        {{ $slot }}
    </button>
@endif
