<x-layouts.app title="Flags" header="Feature Flags">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">
                Manage your feature flags
            </p>
        </div>
        <x-button type="primary" :href="route('flags.create')">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Flag
        </x-button>
    </div>

    <x-card :padding="false">
        @if($flags->isEmpty())
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                </svg>
                <h3 class="mt-4 text-sm font-medium text-gray-900">No flags yet</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating your first feature flag.</p>
                <div class="mt-6">
                    <x-button type="primary" :href="route('flags.create')">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Flag
                    </x-button>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Key / Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Targeting
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Created
                            </th>
                            <th class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($flags as $flag)
                            <tr class="hover:bg-gray-50" x-data="{ loading: false }">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('flags.show', $flag) }}" class="font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ $flag->key }}
                                    </a>
                                    @if($flag->name)
                                        <p class="text-sm text-gray-500">{{ $flag->name }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                                $el.querySelector('span').textContent = data.is_enabled ? 'Enabled' : 'Disabled';
                                                $el.classList.toggle('bg-green-100', data.is_enabled);
                                                $el.classList.toggle('text-green-800', data.is_enabled);
                                                $el.classList.toggle('bg-gray-100', !data.is_enabled);
                                                $el.classList.toggle('text-gray-800', !data.is_enabled);
                                            })
                                            .catch(() => loading = false);
                                        "
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full transition-colors cursor-pointer hover:opacity-80 {{ $flag->is_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}"
                                        :class="{ 'opacity-50': loading }"
                                    >
                                        <svg x-show="loading" class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>{{ $flag->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($flag->hasTargeting())
                                        <x-badge type="primary">
                                            {{ $flag->targetingRules->count() }} group(s)
                                        </x-badge>
                                    @else
                                        <x-badge type="default">Global</x-badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $flag->created_at->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('flags.show', $flag) }}" class="text-gray-600 hover:text-gray-900" title="View">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('flags.edit', $flag) }}" class="text-gray-600 hover:text-gray-900" title="Edit">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('flags.destroy', $flag) }}" 
                                              onsubmit="return confirm('Are you sure you want to delete this flag?');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</x-layouts.app>
