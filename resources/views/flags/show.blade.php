<x-layouts.app title="Flag: {{ $flag->key }}" header="{{ $flag->key }}">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('flags.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Flags
        </a>
        <div class="flex items-center gap-2">
            <x-button type="secondary" :href="route('flags.edit', $flag)">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </x-button>
            <form method="POST" action="{{ route('flags.destroy', $flag) }}" 
                  onsubmit="return confirm('Are you sure you want to delete this flag?');"
                  class="inline">
                @csrf
                @method('DELETE')
                <x-button type="danger">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </x-button>
            </form>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Flag Details -->
        <x-card title="Flag Details" class="lg:col-span-2">
            <dl class="grid gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Key</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded inline-block">
                        {{ $flag->key }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1" x-data="{ loading: false, enabled: {{ $flag->is_enabled ? 'true' : 'false' }} }">
                        <button 
                            type="button"
                            @click="
                                loading = true;
                                fetch('{{ route('flags.toggle', $flag) }}', {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(r => r.json())
                                .then(data => {
                                    loading = false;
                                    enabled = data.is_enabled;
                                })
                                .catch(() => loading = false);
                            "
                            class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-full transition-colors"
                            :class="enabled ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'"
                        >
                            <svg x-show="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="enabled ? 'Enabled' : 'Disabled'"></span>
                            <span class="text-xs opacity-75">(click to toggle)</span>
                        </button>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $flag->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $flag->created_at->format('M j, Y H:i') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $flag->description ?? 'No description provided.' }}</dd>
                </div>
            </dl>
        </x-card>

        <!-- API Usage -->
        <x-card title="API Usage">
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Evaluate</p>
                    <div class="bg-gray-900 rounded-lg p-3 text-sm">
                        <code class="text-green-400">POST</code>
                        <code class="text-gray-300"> /api/v1/evaluate</code>
                    </div>
                    <pre class="mt-2 bg-gray-900 rounded-lg p-3 text-xs text-gray-300 overflow-x-auto">{
  "flag_key": "{{ $flag->key }}",
  "context": {
    "user_id": "user-123",
    "role": "beta-tester"
  }
}</pre>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Targeting Rules -->
    <x-card title="Targeting Rules" class="mt-6">
        <x-slot:header>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Targeting Rules</h3>
                    <p class="text-sm text-gray-500">Configure which groups can access this flag</p>
                </div>
                <x-button type="secondary" size="sm" :href="route('flags.targeting.manage', $flag)">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Manage
                </x-button>
            </div>
        </x-slot:header>

        @if($flag->targetingRules->isEmpty())
            <div class="text-center py-6">
                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500">No targeting rules configured</p>
                <p class="mt-1 text-xs text-gray-400">
                    The flag will use global status ({{ $flag->is_enabled ? 'enabled' : 'disabled' }}) for all evaluations.
                </p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($flag->targetingRules as $rule)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100">
                                <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $rule->group->name }}</p>
                                <p class="text-xs text-gray-500 font-mono">{{ $rule->group->identifier }}</p>
                            </div>
                        </div>
                        <x-badge type="success">Active</x-badge>
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-xs text-gray-500">
                Users matching any of these groups will receive <span class="font-medium text-green-600">enabled</span> status.
            </p>
        @endif
    </x-card>
</x-layouts.app>
