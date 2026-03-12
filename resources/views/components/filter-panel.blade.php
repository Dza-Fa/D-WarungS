@props([
    'filters' => [],
    'activeFilters' => [],
])

<div 
    x-data="{ 
        filterOpen: false,
        activeFilters: {{ json_encode($activeFilters ?? []) }}
    }"
    class="space-y-4"
>
    <!-- Filter Toggle Button (Mobile) -->
    <div class="lg:hidden">
        <x-button 
            variant="outline" 
            class="w-full"
            @click="filterOpen = !filterOpen"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <span>Filters</span>
            @if($activeFilters)
                <x-badge variant="primary" size="sm">{{ count($activeFilters) }}</x-badge>
            @endif
        </x-button>
    </div>
    
    <!-- Filter Panel -->
    <div @class([
        'space-y-4 lg:block',
        'hidden' => !$activeFilters && !true,
        'block' => $activeFilters || true,
    ]) :class="{ 'block': filterOpen, 'hidden': !filterOpen }" class="lg:block">
        
        @forelse($filters as $filterGroup)
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-3">{{ $filterGroup['title'] ?? 'Filter' }}</h3>
                
                <div class="space-y-3">
                    @foreach($filterGroup['options'] ?? [] as $option)
                        @php
                            $isActive = in_array($option['value'], $activeFilters);
                        @endphp
                        
                        <label class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded transition-colors">
                            <input 
                                type="checkbox"
                                name="{{ $filterGroup['name'] ?? 'filter' }}"
                                value="{{ $option['value'] }}"
                                :checked="activeFilters.includes('{{ $option['value'] }}')"
                                @change="$dispatch('filter-change', { 
                                    name: '{{ $filterGroup['name'] }}',
                                    value: $event.target.value,
                                    checked: $event.target.checked 
                                })"
                                class="w-4 h-4 text-orange-600 rounded border-gray-300 focus:ring-orange-500 cursor-pointer"
                            />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $option['label'] ?? $option['value'] }}
                                </p>
                                @if($option['count'] ?? null)
                                    <p class="text-xs text-gray-500">
                                        ({{ $option['count'] }})
                                    </p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-gray-500 text-sm">No filters available</p>
        @endforelse
        
        <!-- Clear All Button -->
        @if($activeFilters)
            <x-button 
                variant="secondary" 
                fullWidth 
                @click="$dispatch('clear-filters')"
            >
                Clear All Filters
            </x-button>
        @endif
    </div>
</div>
