@props([
    'count' => 1,
    'lines' => 3,
])

@for($i = 0; $i < $count; $i++)
    <div class="mb-4 p-4 bg-white rounded-lg border border-gray-200">
        @for($j = 0; $j < $lines; $j++)
            <div 
                class="h-4 bg-gray-200 rounded animate-pulse mb-3"
                style="width: {{ ($j % 3 === 0) ? '60%' : '100%' }}"
            ></div>
        @endfor
    </div>
@endfor
