// Real-time updates using Server-Sent Events (SSE) simulation
class RealtimeUpdater {
    constructor() {
        this.updateInterval = null;
        this.isActive = false;
        this.lastUpdateTime = null;
        this.retryCount = 0;
        this.maxRetries = 3;
    }

    start(updateFunction, interval = 5000) {
        if (this.isActive) {
            this.stop();
        }

        this.isActive = true;
        this.retryCount = 0;

        // Initial update
        this.performUpdate(updateFunction);

        // Set up periodic updates
        this.updateInterval = setInterval(() => {
            this.performUpdate(updateFunction);
        }, interval);

        // Add visibility change listener to pause/resume updates
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pause();
            } else {
                this.resume(updateFunction, interval);
            }
        });
    }

    async performUpdate(updateFunction) {
        try {
            await updateFunction();
            this.retryCount = 0;
            this.lastUpdateTime = new Date();
            this.showUpdateIndicator();
        } catch (error) {
            console.error('Real-time update failed:', error);
            this.retryCount++;

            if (this.retryCount >= this.maxRetries) {
                this.showErrorIndicator();
                this.stop();
            }
        }
    }

    stop() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
        this.isActive = false;
    }

    pause() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }

    resume(updateFunction, interval) {
        if (this.isActive && !this.updateInterval) {
            this.updateInterval = setInterval(() => {
                this.performUpdate(updateFunction);
            }, interval);
        }
    }

    showUpdateIndicator() {
        const indicator = document.getElementById('update-indicator');
        if (indicator) {
            indicator.classList.remove('hidden');
            indicator.classList.add('animate-pulse');
            setTimeout(() => {
                indicator.classList.add('hidden');
                indicator.classList.remove('animate-pulse');
            }, 1000);
        }
    }

    showErrorIndicator() {
        const errorIndicator = document.getElementById('error-indicator');
        if (errorIndicator) {
            errorIndicator.classList.remove('hidden');
            setTimeout(() => {
                errorIndicator.classList.add('hidden');
            }, 5000);
        }
    }
}

// Global realtime updater instance
window.realtimeUpdater = new RealtimeUpdater();

// Utility functions for AJAX updates
window.updateContent = async function (url, targetSelector, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                ...options.headers
            },
            ...options
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.html && targetSelector) {
            const targetElement = document.querySelector(targetSelector);
            if (targetElement) {
                targetElement.innerHTML = data.html;

                // Trigger custom event for updated content
                targetElement.dispatchEvent(new CustomEvent('contentUpdated', { detail: data }));
            }
        }

        return data;
    } catch (error) {
        console.error('Failed to update content:', error);
        throw error;
    }
};

// Auto-refresh specific elements
window.autoRefreshElement = function (selector, url, interval = 10000) {
    const element = document.querySelector(selector);
    if (!element) return;

    const updateFunction = async () => {
        try {
            const data = await window.updateContent(url, selector);

            // Add smooth transition effect
            element.style.opacity = '0.7';
            setTimeout(() => {
                element.style.opacity = '1';
            }, 200);

        } catch (error) {
            console.error('Auto-refresh failed:', error);
        }
    };

    window.realtimeUpdater.start(updateFunction, interval);
};

// Notification system
window.showNotification = function (message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-black' :
                'bg-blue-500 text-white'
        }`;

    notification.innerHTML = `
        <div class="flex items-center">
            <span class="mr-2">
                ${type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️'}
            </span>
            <span>${message}</span>
            <button class="ml-4 text-lg font-bold" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    // Auto remove
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, duration);
};

// Status polling for reservations
window.pollReservationStatus = function (reservationId, callback) {
    const pollFunction = async () => {
        try {
            const response = await fetch(`/member/reservations/${reservationId}/status-check`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (callback) {
                    callback(data);
                }

                // Stop polling if status is final
                if (['paid', 'rejected', 'cancelled'].includes(data.status)) {
                    window.realtimeUpdater.stop();
                }
            }
        } catch (error) {
            console.error('Status polling failed:', error);
        }
    };

    window.realtimeUpdater.start(pollFunction, 3000);
};