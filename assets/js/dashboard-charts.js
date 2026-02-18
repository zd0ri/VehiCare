/*!
 * VehiCare Dashboard Charts & Real-time Updates
 * Handles dashboard visualizations and live data
 */

class DashboardCharts {
    constructor() {
        this.charts = {};
        this.refreshInterval = 30000; // 30 seconds
        this.init();
    }

    init() {
        // Initialize charts when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeCharts());
        } else {
            this.initializeCharts();
        }

        // Start real-time updates
        this.startRealTimeUpdates();
    }

    async initializeCharts() {
        try {
            // Load Chart.js if not already loaded
            if (typeof Chart === 'undefined') {
                await this.loadChartJS();
            }

            // Get chart data from API
            const chartData = await this.fetchChartData();
            
            // Initialize all charts
            this.initStatusChart(chartData.appointments_by_status);
            this.initTrendChart(chartData.monthly_trend);
            this.initServicesChart(chartData.popular_services);
            
            console.log('Dashboard charts initialized successfully');
        } catch (error) {
            console.error('Failed to initialize charts:', error);
        }
    }

    async loadChartJS() {
        return new Promise((resolve, reject) => {
            if (document.querySelector('script[src*="chart.js"]')) {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async fetchChartData() {
        const response = await fetch('/vehicare_db/api/dashboard.php?action=charts');
        if (!response.ok) throw new Error('Failed to fetch chart data');
        return await response.json();
    }

    async fetchDashboardStats() {
        const response = await fetch('/vehicare_db/api/dashboard.php?action=stats');
        if (!response.ok) throw new Error('Failed to fetch dashboard stats');
        return await response.json();
    }

    initStatusChart(data) {
        const canvas = document.getElementById('statusChart');
        if (!canvas || !data) return;

        const ctx = canvas.getContext('2d');
        
        // Destroy existing chart if it exists
        if (this.charts.status) {
            this.charts.status.destroy();
        }

        this.charts.status = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels?.map(label => this.formatStatusLabel(label)) || [],
                datasets: [{
                    data: data.data || [],
                    backgroundColor: [
                        '#ffc107', // pending - yellow
                        '#17a2b8', // confirmed - info blue  
                        '#dc143c', // in-progress - primary red
                        '#28a745', // completed - success green
                        '#6c757d'  // cancelled - secondary gray
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    initTrendChart(data) {
        const canvas = document.getElementById('trendChart');
        if (!canvas || !data) return;

        const ctx = canvas.getContext('2d');
        
        if (this.charts.trend) {
            this.charts.trend.destroy();
        }

        this.charts.trend = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels || [],
                datasets: [{
                    label: 'Appointments',
                    data: data.data || [],
                    borderColor: '#dc143c',
                    backgroundColor: 'rgba(220, 20, 60, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#dc143c',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    initServicesChart(data) {
        const canvas = document.getElementById('servicesChart');
        if (!canvas || !data) return;

        const ctx = canvas.getContext('2d');
        
        if (this.charts.services) {
            this.charts.services.destroy();
        }

        this.charts.services = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels || [],
                datasets: [{
                    label: 'Bookings',
                    data: data.data || [],
                    backgroundColor: [
                        '#dc143c',
                        '#ff6b6b', 
                        '#ff8e53',
                        '#ff6b9d',
                        '#c44569'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    startRealTimeUpdates() {
        // Update dashboard stats every 30 seconds
        setInterval(() => {
            this.updateDashboardStats();
        }, this.refreshInterval);

        // Update charts every 5 minutes
        setInterval(() => {
            this.updateCharts();  
        }, 300000);

        // Initial update
        setTimeout(() => {
            this.updateDashboardStats();
        }, 2000);
    }

    async updateDashboardStats() {
        try {
            const stats = await this.fetchDashboardStats();
            
            // Update stat cards
            Object.entries(stats).forEach(([key, value]) => {
                const element = document.querySelector(`[data-stat="${key}"]`);
                if (element && typeof value === 'number') {
                    // Animate number change
                    this.animateNumber(element, parseInt(element.textContent.replace(/,/g, '')) || 0, value);
                }
            });

            // Update change indicators
            const changeElement = document.querySelector('[data-change="appointments"]');
            if (changeElement && stats.appointments_change !== undefined) {
                const change = stats.appointments_change;
                const isPositive = change >= 0;
                
                changeElement.className = `stat-card-change ${isPositive ? '' : 'negative'}`;
                changeElement.innerHTML = `
                    <i class="fas fa-${isPositive ? 'arrow-up' : 'arrow-down'}"></i>
                    ${Math.abs(change)}% from last month
                `;
            }

        } catch (error) {
            console.error('Failed to update dashboard stats:', error);
        }
    }

    async updateCharts() {
        try {
            const chartData = await this.fetchChartData();
            
            // Update status chart
            if (this.charts.status && chartData.appointments_by_status) {
                const chart = this.charts.status;
                chart.data.labels = chartData.appointments_by_status.labels?.map(label => this.formatStatusLabel(label));
                chart.data.datasets[0].data = chartData.appointments_by_status.data;
                chart.update('active');
            }

            // Update trend chart
            if (this.charts.trend && chartData.monthly_trend) {
                const chart = this.charts.trend;
                chart.data.labels = chartData.monthly_trend.labels;
                chart.data.datasets[0].data = chartData.monthly_trend.data;
                chart.update('active');
            }

            // Update services chart  
            if (this.charts.services && chartData.popular_services) {
                const chart = this.charts.services;
                chart.data.labels = chartData.popular_services.labels;
                chart.data.datasets[0].data = chartData.popular_services.data;
                chart.update('active');
            }

        } catch (error) {
            console.error('Failed to update charts:', error);
        }
    }

    animateNumber(element, startValue, endValue, duration = 1000) {
        const startTime = Date.now();
        const difference = endValue - startValue;

        const updateNumber = () => {
            const currentTime = Date.now();
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function for smooth animation
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const currentValue = Math.round(startValue + (difference * easeOutQuart));
            
            element.textContent = this.formatNumber(currentValue);
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            }
        };

        requestAnimationFrame(updateNumber);
    }

    formatNumber(num) {
        return num.toLocaleString();
    }

    formatStatusLabel(status) {
        const statusMap = {
            'pending': 'Pending',
            'confirmed': 'Confirmed', 
            'in-progress': 'In Progress',
            'completed': 'Completed',
            'cancelled': 'Cancelled'
        };
        return statusMap[status] || status;
    }

    // Public method to refresh all data
    refreshAll() {
        this.updateDashboardStats();
        this.updateCharts();
    }

    // Public method to destroy all charts (for cleanup)
    destroy() {
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        this.charts = {};
    }
}

// Real-time Notifications Handler
class NotificationManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadNotifications();
        
        // Refresh notifications every 30 seconds
        setInterval(() => {
            this.loadNotifications();
        }, 30000);

        // Attach event listeners
        this.attachEventListeners();
    }

    async loadNotifications() {
        try {
            const response = await fetch('/vehicare_db/api/notifications.php');
            if (!response.ok) throw new Error('Failed to fetch notifications');
            
            const data = await response.json();
            this.updateNotificationBadge(data.unread_count);
            this.updateNotificationPanel(data.notifications);
        } catch (error) {
            console.error('Failed to load notifications:', error);
        }
    }

    updateNotificationBadge(count) {
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    updateNotificationPanel(notifications) {
        const panel = document.getElementById('notificationList');
        if (!panel) return;

        if (notifications.length === 0) {
            panel.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-bell-slash"></i>
                    <p>No notifications</p>
                </div>
            `;
            return;
        }

        panel.innerHTML = notifications.map(notification => `
            <div class="notification-item ${notification.is_read ? 'read' : 'unread'}" data-id="${notification.notification_id}">
                <div class="notification-icon">
                    <i class="fas ${this.getNotificationIcon(notification.type)}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${this.escapeHtml(notification.title)}</div>
                    <div class="notification-message">${this.escapeHtml(notification.message)}</div>
                    <div class="notification-time">${notification.time_ago}</div>
                </div>
                ${!notification.is_read ? '<div class="notification-unread-dot"></div>' : ''}
            </div>
        `).join('');
    }

    attachEventListeners() {
        // Mark notification as read when clicked
        document.addEventListener('click', async (e) => {
            const notificationItem = e.target.closest('.notification-item');
            if (notificationItem && notificationItem.classList.contains('unread')) {
                const id = notificationItem.dataset.id;
                await this.markAsRead(id);
                notificationItem.classList.remove('unread');
                notificationItem.classList.add('read');
                
                // Remove unread dot
                const dot = notificationItem.querySelector('.notification-unread-dot');
                if (dot) dot.remove();
                
                // Update badge count
                const currentCount = parseInt(document.getElementById('notificationBadge')?.textContent || '0');
                this.updateNotificationBadge(Math.max(0, currentCount - 1));
            }
        });
    }

    async markAsRead(notificationId) {
        try {
            await fetch('/vehicare_db/api/notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'mark_read',
                    id: notificationId
                })
            });
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            await fetch('/vehicare_db/api/notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'mark_all_read'
                })
            });
            
            // Refresh notifications
            this.loadNotifications();
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
        }
    }

    getNotificationIcon(type) {
        const icons = {
            'appointment': 'fa-calendar-check',
            'payment': 'fa-credit-card',
            'vehicle': 'fa-car',
            'service': 'fa-tools',
            'system': 'fa-info-circle',
            'warning': 'fa-exclamation-triangle'
        };
        return icons[type] || 'fa-bell';
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize on dashboard pages
    if (document.body.classList.contains('dashboard-page') || 
        document.querySelector('.dashboard-content')) {
        
        window.dashboardCharts = new DashboardCharts();
        window.notificationManager = new NotificationManager();
        
        // Global function for marking all notifications as read
        window.markAllAsRead = () => {
            if (window.notificationManager) {
                window.notificationManager.markAllAsRead();
            }
        };
    }
});

// Export for module use if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { DashboardCharts, NotificationManager };
}