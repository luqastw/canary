@props([
    'label' => null,
    'name',
    'checked' => false,
    'disabled' => false,
    'hint' => null,
])

<div {{ $attributes->only('class')->merge(['class' => 'flex items-start']) }}>
    <div class="flex h-6 items-center">
        <input 
            type="checkbox"
            name="{{ $name }}"
            id="{{ $name }}"
            value="1"
            @checked(old($name, $checked))
            @disabled($disabled)
            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50"
        >
    </div>
    @if($label)
        <div class="ml-3">
            <label for="{{ $name }}" class="text-sm font-medium text-gray-700">
                {{ $label }}
            </label>
            @if($hint)
                <p class="text-xs text-gray-500">{{ $hint }}</p>
            @endif
        </div>
    @endif

    @error($name)
        <p class="ml-3 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
