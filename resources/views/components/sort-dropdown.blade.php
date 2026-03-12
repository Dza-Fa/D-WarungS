@props([
    'options' => [],
    'current' => null,
    'name' => 'sort',
])

<div 
    x-data="{ 
        open: false,
        selected: '{{ $current }}'
    }"
    class="relative w-full"
>
    <!-- Sort Dropdown Button -->
    <button 
        @click="open = !open"
        @click.outside="open = false"
        type="button"
        class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-200 flex items-center justify-between gap-2 text-left"
    >
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <span class="text-gray-700">
                <template x-if="selected">
                    <span x-text="$options.find(o => o.value === selected)?.label || 'Sort by'"></span>
                </template>
                <template x-if="!selected">
                    <span>Sort by</span>
                </template>
            </span>
        </div>
        
        <svg class="w-5 h-5 text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
        </svg>
    </button>
    
    <!-- Sort Options Dropdown -->
    <div 
        x-show="open"
        x-transition
        class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-10"
    >
        <ul class="py-2" x-data="{ $options: {{ json_encode($options) }} }">
            @forelse($options as $option)
                <li>
                    <button 
                        type="button"
                        @click="selected = '{{ $option['value'] }}'; open = false; $dispatch('sort-change', { value: '{{ $option['value'] }}' })"
                        :class="{ 'bg-orange-50 text-orange-600': selected === '{{ $option['value'] }}' }"
                        class="w-full px-4 py-2 text-left hover:bg-gray-50 transition-colors flex items-center justify-between gap-2"
                    >
                        <span class="text-sm">{{ $option['label'] }}</span>
                        <svg x-show="selected === '{{ $option['value'] }}'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </li>
            @empty
                <li class="px-4 py-2 text-sm text-gray-500">No options</li>
            @endforelse
        </ul>
    </div>
</div>
