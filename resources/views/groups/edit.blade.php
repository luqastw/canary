<x-layouts.app title="Edit Group" header="Edit Group: {{ $group->identifier }}">
    <div class="mb-6">
        <a href="{{ route('groups.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Groups
        </a>
    </div>

    <x-card title="Group Details" subtitle="Update the targeting group settings" class="max-w-2xl">
        <form method="POST" action="{{ route('groups.update', $group) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <x-input 
                name="identifier" 
                label="Identifier"
                :value="$group->identifier"
                readonly
                hint="The group identifier cannot be changed"
            />

            <x-input 
                name="name" 
                label="Name"
                :value="$group->name"
                placeholder="Beta Testers"
                hint="Human-readable name for display purposes"
            />

            <x-textarea 
                name="description" 
                label="Description"
                :value="$group->description"
                placeholder="Users enrolled in the beta testing program..."
                hint="Optional description to help you understand this group"
            />

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-button type="secondary" :href="route('groups.index')">
                        Cancel
                    </x-button>
                    <x-button type="primary">
                        Update Group
                    </x-button>
                </div>
            </x-slot:footer>
        </form>
    </x-card>
</x-layouts.app>
