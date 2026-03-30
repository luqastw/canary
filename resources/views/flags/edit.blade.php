<x-layouts.app title="Edit Flag" header="Edit Flag: {{ $flag->key }}">
    <div class="mb-6">
        <a href="{{ route('flags.show', $flag) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Flag
        </a>
    </div>

    <x-card title="Flag Details" subtitle="Update the feature flag settings" class="max-w-2xl">
        <form method="POST" action="{{ route('flags.update', $flag) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <x-input 
                name="key" 
                label="Key"
                :value="$flag->key"
                readonly
                hint="The flag key cannot be changed"
            />

            <x-input 
                name="name" 
                label="Name"
                :value="$flag->name"
                placeholder="My Feature Flag"
                hint="Human-readable name for display purposes"
            />

            <x-textarea 
                name="description" 
                label="Description"
                :value="$flag->description"
                placeholder="Describe what this feature flag controls..."
                hint="Optional description to help you and your team understand this flag"
            />

            <x-checkbox 
                name="is_enabled" 
                label="Enable flag"
                hint="When enabled, the flag will return true for all users (unless targeting rules are configured)"
                :checked="$flag->is_enabled"
            />

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-button type="secondary" :href="route('flags.show', $flag)">
                        Cancel
                    </x-button>
                    <x-button type="primary">
                        Update Flag
                    </x-button>
                </div>
            </x-slot:footer>
        </form>
    </x-card>
</x-layouts.app>
