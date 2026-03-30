@props([
    'type' => 'default',
    'size' => 'md',
])

@php
$typeClasses = match($type) {
    'success' => 'bg-green-100 text-green-800',
    'danger' => 'bg-red-100 text-red-800',
    'warning' => 'bg-yellow-100 text-yellow-800',
    'info' => 'bg-blue-100 text-blue-800',
    'primary' => 'bg-indigo-100 text-indigo-800',
    default => 'bg-gray-100 text-gray-800',
};

$sizeClasses = match($size) {
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-xs',
    'lg' => 'px-3 py-1.5 text-sm',
    default => 'px-2.5 py-1 text-xs',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium rounded-full $typeClasses $sizeClasses"]) }}>
    {{ $slot }}
</span>
