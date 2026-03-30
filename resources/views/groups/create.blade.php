<x-layouts.app title="Create Group" header="Create Group">
    <div class="mb-6">
        <a href="{{ route('groups.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Groups
        </a>
    </div>

    <x-card title="Group Details" subtitle="Create a new targeting group" class="max-w-2xl">
        <form method="POST" action="{{ route('groups.store') }}" class="space-y-6">
            @csrf

            <x-input 
                name="identifier" 
                label="Identifier"
                placeholder="beta-testers"
                hint="Unique identifier used in API evaluation context (lowercase, hyphens allowed). This cannot be changed later."
                required
            />

            <x-input 
                name="name" 
                label="Name"
                placeholder="Beta Testers"
                hint="Human-readable name for display purposes"
            />

            <x-textarea 
                name="description" 
                label="Description"
                placeholder="Users enrolled in the beta testing program..."
                hint="Optional description to help you understand this group"
            />

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-button type="secondary" :href="route('groups.index')">
                        Cancel
                    </x-button>
                    <x-button type="primary">
                        Create Group
                    </x-button>
                </div>
            </x-slot:footer>
        </form>
    </x-card>
</x-layouts.app>
