@props([
    'steps' => [],
    'currentStep' => 1,
])

<div class="mb-8">
    <div class="flex items-center justify-between">
        @forelse($steps as $index => $step)
            @php
                $stepNumber = $index + 1;
                $isCompleted = $stepNumber < $currentStep;
                $isCurrent = $stepNumber === $currentStep;
            @endphp
            
            <div class="flex-1">
                <!-- Step Circle -->
                <div class="flex items-center">
                    <div
                        @class([
                            'flex items-center justify-center w-10 h-10 rounded-full font-semibold text-sm transition-all duration-300',
                            'bg-orange-600 text-white' => $isCurrent || $isCompleted,
                            'bg-gray-200 text-gray-600' => !$isCurrent && !$isCompleted,
                        ])
                    >
                        @if($isCompleted)
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            {{ $stepNumber }}
                        @endif
                    </div>

                    <!-- Step Label -->
                    <div class="ml-3 hidden sm:block">
                        <p @class([
                            'font-semibold transition-colors duration-300',
                            'text-orange-600' => $isCurrent,
                            'text-gray-900' => $isCompleted,
                            'text-gray-600' => !$isCurrent && !$isCompleted,
                        ])>
                            {{ $step }}
                        </p>
                    </div>

                    <!-- Connector Line -->
                    @if($index < count($steps) - 1)
                        <div class="flex-1 mx-2 h-1 rounded-full transition-all duration-300"
                            @class([
                                'bg-orange-600' => $isCompleted,
                                'bg-gray-300' => !$isCompleted,
                            ])
                        ></div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-500">No steps provided</p>
        @endforelse
    </div>

    <!-- Mobile Step Indicator -->
    <div class="sm:hidden text-center mt-4">
        <p class="text-sm font-medium text-gray-900">
            Step {{ $currentStep }} of {{ count($steps) }}
        </p>
        @if(isset($steps[$currentStep - 1]))
            <p class="text-sm text-gray-600">
                {{ $steps[$currentStep - 1] }}
            </p>
        @endif
    </div>
</div>
