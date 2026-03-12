@props([
    'placeholder' => 'Search...',
    'name' => 'search',
    'value' => '',
    'suggestions' => [],
])

<div 
    x-data="{ 
        search: '{{ $value }}',
        open: false,
        results: {{ json_encode($suggestions ?? []) }},
        filteredResults() {
            if (!this.search) return [];
            return this.results.filter(item => 
                item.toLowerCase().includes(this.search.toLowerCase())
            );
        }
    }"
    class="relative w-full"
>
    <div class="relative">
        <!-- Search Input -->
        <div class="relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            
            <input 
                type="text"
                name="{{ $name }}"
                x-model="search"
                @focus="open = true"
                @blur="setTimeout(() => open = false, 200)"
                @input.debounce.300ms="$dispatch('search-input', { value: search })"
                placeholder="{{ $placeholder }}"
                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
            />
            
            <!-- Clear Button -->
            <button 
                x-show="search"
                @click="search = ''"
                type="button"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
        
        <!-- Suggestions Dropdown -->
        <div 
            x-show="open && filteredResults().length > 0"
            x-transition
            class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-10"
        >
            <ul class="py-2">
                <template x-for="(result, index) in filteredResults()" :key="index">
                    <li 
                        @click="search = result; $dispatch('search-select', { value: result })"
                        class="px-4 py-2 hover:bg-gray-50 cursor-pointer transition-colors flex items-center gap-2"
                    >
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="result" class="text-sm text-gray-700"></span>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>
