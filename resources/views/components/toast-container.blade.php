<!-- Toast Notification Container -->
<div 
    x-data="toastManager()"
    class="fixed top-4 right-4 z-50 space-y-3 pointer-events-none"
>
    <template x-for="(toast, index) in toasts" :key="index">
        <div 
            x-transition
            @class([
                'px-4 py-3 rounded-lg text-white shadow-lg pointer-events-auto flex items-start gap-3 max-w-sm animate-in slide-in-from-right-2 fade-in',
                'bg-green-500' => 'type === "success"',
                'bg-red-500' => 'type === "danger"',
                'bg-yellow-500' => 'type === "warning"',
                'bg-blue-500' => 'type === "info"',
            ])
        >
            <svg 
                x-show="toast.type === 'success'"
                class="w-5 h-5 flex-shrink-0 mt-0.5" 
                fill="currentColor" 
                viewBox="0 0 20 20"
            >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            
            <svg 
                x-show="toast.type === 'danger'"
                class="w-5 h-5 flex-shrink-0 mt-0.5" 
                fill="currentColor" 
                viewBox="0 0 20 20"
            >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>

            <span class="text-sm font-medium flex-1" x-text="toast.message"></span>
            
            <button 
                @click="removeToast(index)"
                class="flex-shrink-0 opacity-75 hover:opacity-100 transition-opacity p-1"
                type="button"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </template>
</div>

<script>
function toastManager() {
    return {
        toasts: [],
        addToast(message, type = 'info', duration = 3000) {
            const id = Math.random();
            this.toasts.push({ id, message, type });
            
            if (duration > 0) {
                setTimeout(() => this.removeToast(this.toasts.findIndex(t => t.id === id)), duration);
            }
        },
        removeToast(index) {
            this.toasts.splice(index, 1);
        },
    };
}

// Global toast function
function showToast(message, type = 'info', duration = 3000) {
    // Dispatch custom event that Alpine.js will catch
    window.dispatchEvent(new CustomEvent('show-toast', { 
        detail: { message, type, duration } 
    }));
}

// Alternative: Direct Alpine interaction
document.addEventListener('alpine:init', () => {
    window.addEventListener('show-toast', (event) => {
        const { message, type, duration } = event.detail;
        // This can be connected to your Alpine component
    });
});
</script>
