<x-layouts.app title="Targeting: {{ $flag->key }}" header="Manage Targeting">
    <div class="mb-6">
        <a href="{{ route('flags.show', $flag) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to {{ $flag->key }}
        </a>
    </div>

    <!-- Flag Info -->
    <div class="mb-6 flex items-center gap-4 p-4 bg-white rounded-lg border border-gray-200">
        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100">
            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
            </svg>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $flag->key }}</h2>
            <p class="text-sm text-gray-500">{{ $flag->name ?? 'No name' }}</p>
        </div>
        <div class="ml-auto">
            @if($flag->is_enabled)
                <x-badge type="success">Enabled</x-badge>
            @else
                <x-badge type="default">Disabled</x-badge>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Assigned Groups -->
        <x-card title="Assigned Groups" subtitle="Groups that will receive this flag">
            @if($assignedGroups->isEmpty())
                <div class="text-center py-8">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No groups assigned</p>
                    <p class="text-xs text-gray-400">Flag uses global status for all evaluations</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($assignedGroups as $group)
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $group->name ?? $group->identifier }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $group->identifier }}</p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('flags.targeting.destroy', [$flag, $group]) }}"
                                  onsubmit="return confirm('Remove this group from targeting?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 p-1" title="Remove">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm text-blue-800">
                        <strong>Evaluation logic:</strong> Users matching any of these groups will receive 
                        <span class="font-semibold text-green-600">enabled</span> status.
                    </p>
                </div>
            @endif
        </x-card>

        <!-- Available Groups -->
        <x-card title="Available Groups" subtitle="Groups you can add to targeting">
            @if($availableGroups->isEmpty())
                <div class="text-center py-8">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">All groups are assigned</p>
                    <a href="{{ route('groups.create') }}" class="mt-2 inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create a new group
                    </a>
                </div>
            @else
                <form method="POST" action="{{ route('flags.targeting.store', $flag) }}" 
                      x-data="{ selected: [] }"
                      class="space-y-4">
                    @csrf
                    
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($availableGroups as $group)
                            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors"
                                   :class="{ 'ring-2 ring-indigo-500 bg-indigo-50 border-indigo-200': selected.includes('{{ $group->id }}') }">
                                <input type="checkbox" 
                                       name="group_ids[]" 
                                       value="{{ $group->id }}"
                                       x-model="selected"
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $group->name ?? $group->identifier }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $group->identifier }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="flex justify-end">
                        <x-button type="primary" x-bind:disabled="selected.length === 0">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Selected
                        </x-button>
                    </div>
                </form>
            @endif
        </x-card>
    </div>

    <!-- Quick Actions -->
    <div class="mt-6 flex justify-between items-center">
        <div>
            @if(!$assignedGroups->isEmpty())
                <form method="POST" action="{{ route('flags.targeting.replace', $flag) }}"
                      onsubmit="return confirm('This will remove ALL targeting rules for this flag. Continue?');">
                    @csrf
                    @method('PUT')
                    <x-button type="danger" size="sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Clear All Rules
                    </x-button>
                </form>
            @endif
        </div>
        <x-button type="secondary" :href="route('flags.show', $flag)">
            Done
        </x-button>
    </div>
</x-layouts.app>
