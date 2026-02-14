/**
 * Realtime Notifications Client (Polling Version)
 * Menggantikan SSE dengan AJAX Polling yang lebih stabil dan hemat resource server.
 */
class RealtimeNotifications {
    constructor(options) {
        this.endpoint = options.endpoint || '/D-WarungS/api/realtime-notifications.php';
        this.interval = options.reconnectInterval || 3000; // Default polling setiap 3 detik
        this.onNotification = options.onNotification || function() {};
        this.onError = options.onError || function() {};
        
        this.timer = null;
        this.lastId = 0;
        this.isPolling = false;
    }

    connect() {
        // Mulai polling pertama kali
        this.poll();
        
        // Set interval untuk polling berikutnya
        this.timer = setInterval(() => {
            this.poll();
        }, this.interval);
        
        console.log('✅ Realtime notifications started (Polling mode)');
    }

    disconnect() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
            console.log('🛑 Realtime notifications stopped');
        }
    }

    async poll() {
        // Mencegah polling tumpang tindih jika request lambat
        if (this.isPolling) return;
        this.isPolling = true;

        try {
            // Kirim last_id agar server hanya mengirim data baru
            const response = await fetch(`${this.endpoint}?last_id=${this.lastId}`);
            
            if (!response.ok) {
                // Jika session expired (401), stop polling
                if (response.status === 401) {
                    this.disconnect();
                    console.warn('Session expired, stopping notifications.');
                    return;
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success && data.notifications.length > 0) {
                // Update lastId agar tidak mengambil data yang sama lagi
                this.lastId = data.last_id;

                // Trigger callback untuk setiap notifikasi baru
                data.notifications.forEach(notif => {
                    this.onNotification(notif);
                    this.handleNotificationAction(notif);
                });
            }
        } catch (error) {
            console.error('Polling error:', error);
            this.onError(error);
        } finally {
            this.isPolling = false;
        }
    }

    /**
     * Handle notifikasi berdasarkan type
     */
    handleNotificationAction(notification) {
        // Show audio/visual notification
        this.playNotificationSound();
        // Browser notification opsional, bisa diaktifkan jika perlu
        // this.showBrowserNotification(notification);
    }
    
    /**
     * Mainkan suara notifikasi
     */
    playNotificationSound() {
        try {
            // Suara 'ting' sederhana (base64 encoded wav)
            const audio = new Audio('data:audio/wav;base64,UklGRiYAAABXQVZFZm10IBAAAAABAAEAQB8AAAB9AAACABAAZGF0YQIAAAAAAA==');
            audio.play().catch(e => console.log('Auto-play blocked'));
        } catch (e) {
            // Audio không available
        }
    }
    
    /**
     * Request browser notification permission
     */
    static requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                console.log('Notification permission:', permission);
            });
        }
    }
}
