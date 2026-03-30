<x-layouts.app title="Create Flag" header="Create Flag">
    <div class="mb-6">
        <a href="{{ route('flags.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Flags
        </a>
    </div>

    <x-card title="Flag Details" subtitle="Create a new feature flag for your application" class="max-w-2xl">
        <form method="POST" action="{{ route('flags.store') }}" class="space-y-6">
            @csrf

            <x-input 
                name="key" 
                label="Key"
                placeholder="my-feature-flag"
                hint="Unique identifier (lowercase, hyphens allowed). This cannot be changed later."
                required
            />

            <x-input 
                name="name" 
                label="Name"
                placeholder="My Feature Flag"
                hint="Human-readable name for display purposes"
            />

            <x-textarea 
                name="description" 
                label="Description"
                placeholder="Describe what this feature flag controls..."
                hint="Optional description to help you and your team understand this flag"
            />

            <x-checkbox 
                name="is_enabled" 
                label="Enable flag"
                hint="When enabled, the flag will return true for all users (unless targeting rules are configured)"
                :checked="true"
            />

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-button type="secondary" :href="route('flags.index')">
                        Cancel
                    </x-button>
                    <x-button type="primary">
                        Create Flag
                    </x-button>
                </div>
            </x-slot:footer>
        </form>
    </x-card>
</x-layouts.app>
