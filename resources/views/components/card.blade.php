@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden']) }}>
    @if($title || isset($header))
        <div class="border-b border-gray-200 px-6 py-4">
            @if(isset($header))
                {{ $header }}
            @else
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                @if($subtitle)
                    <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
                @endif
            @endif
        </div>
    @endif

    <div @class(['px-6 py-4' => $padding])>
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
            {{ $footer }}
        </div>
    @endif
</div>
