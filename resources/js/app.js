import './bootstrap';
import './realtime';

// Initialize real-time features when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    // Add update indicators to all pages
    if (!document.getElementById('update-indicator')) {
        const updateIndicator = document.createElement('div');
        updateIndicator.id = 'update-indicator';
        updateIndicator.className = 'fixed top-2 right-2 bg-green-500 text-white px-3 py-1 rounded-full text-xs z-50 hidden';
        updateIndicator.innerHTML = '🔄 Memperbarui...';
        document.body.appendChild(updateIndicator);
    }

    if (!document.getElementById('error-indicator')) {
        const errorIndicator = document.createElement('div');
        errorIndicator.id = 'error-indicator';
        errorIndicator.className = 'fixed top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full text-xs z-50 hidden';
        errorIndicator.innerHTML = '❌ Koneksi bermasalah';
        document.body.appendChild(errorIndicator);
    }

    // Initialize page-specific real-time features
    const currentPath = window.location.pathname;

    if (currentPath.includes('/admin/reservations')) {
        initAdminReservationsRealtime();
    } else if (currentPath.includes('/member/reservations')) {
        initMemberReservationsRealtime();
    } else if (currentPath.includes('/admin/tables')) {
        initAdminTablesRealtime();
    } else if (currentPath.includes('/admin/slots')) {
        initAdminSlotsRealtime();
    }
});

function initAdminReservationsRealtime() {
    // Auto-refresh reservation list every 10 seconds
    const updateReservations = async () => {
        const currentDate = new URLSearchParams(window.location.search).get('date') || new Date().toISOString().split('T')[0];
        await window.updateContent(`/admin/reservations?date=${currentDate}&ajax=1`, '.reservation-content');
    };

    window.realtimeUpdater.start(updateReservations, 10000);
}

function initMemberReservationsRealtime() {
    // Auto-refresh reservation status if on status page
    if (window.location.pathname.includes('/status')) {
        const reservationId = window.location.pathname.split('/').slice(-2, -1)[0];

        window.pollReservationStatus(reservationId, (data) => {
            if (data.status_changed) {
                window.showNotification(data.message, data.status === 'confirmed' ? 'success' : 'info');
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        });
    }

    // Auto-refresh reservation list
    if (window.location.pathname.endsWith('/reservations')) {
        const updateReservations = async () => {
            await window.updateContent('/member/reservations?ajax=1', '.reservations-content');
        };

        window.realtimeUpdater.start(updateReservations, 15000);
    }
}

function initAdminTablesRealtime() {
    const updateTables = async () => {
        await window.updateContent('/admin/tables?ajax=1', '.tables-content');
    };

    window.realtimeUpdater.start(updateTables, 20000);
}

function initAdminSlotsRealtime() {
    const updateSlots = async () => {
        const currentDate = new URLSearchParams(window.location.search).get('date') || new Date().toISOString().split('T')[0];
        await window.updateContent(`/admin/slots?date=${currentDate}&ajax=1`, '.slots-content');
    };

    window.realtimeUpdater.start(updateSlots, 30000);
}

// Global functions for manual refresh
window.manualRefresh = function () {
    window.location.reload();
};

window.toggleAutoRefresh = function () {
    if (window.realtimeUpdater.isActive) {
        window.realtimeUpdater.stop();
        window.showNotification('Auto-refresh dinonaktifkan', 'info');
    } else {
        window.location.reload(); // Restart with auto-refresh
    }
};