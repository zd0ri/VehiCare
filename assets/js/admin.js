/*!
 * VehiCare Admin Dashboard JavaScript
 * Handles sidebar navigation, search, notifications, and interactive features
 */

class VehiCareAdmin {
    constructor() {
        this.init();
        this.attachEventListeners();
        this.loadNotifications();
    }

    init() {
        // Initialize sidebar state
        this.initSidebar();
        
        // Initialize tooltips and popovers
        this.initTooltips();
        
        // Initialize data tables
        this.initDataTables();
        
        // Initialize form validation
        this.initFormValidation();
        
        // Initialize search functionality
        this.initSearch();
        
        // Initialize auto-refresh for certain components
        this.initAutoRefresh();
    }

    initSidebar() {
        const currentPath = window.location.pathname;
        const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
        
        // Set active menu item based on current path
        sidebarLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && (currentPath.includes(href) || currentPath === href)) {
                link.classList.add('active');
                
                // Expand parent menu if nested
                const parentMenu = link.closest('.menu-section');
                if (parentMenu) {
                    parentMenu.classList.add('expanded');
                }
            }
        });
    }

    initTooltips() {
        // Initialize tooltips for elements with data-tooltip attribute
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });
            
            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    }

    initDataTables() {
        const tables = document.querySelectorAll('.data-table');
        tables.forEach(table => {
            // Add sorting functionality
            this.addTableSorting(table);
            
            // Add row selection
            this.addRowSelection(table);
            
            // Add search filtering
            this.addTableSearch(table);
        });
    }

    initFormValidation() {
        const forms = document.querySelectorAll('[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
            
            // Real-time validation
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
            });
        });
    }

    initSearch() {
        const searchInputs = document.querySelectorAll('[data-search]');
        searchInputs.forEach(input => {
            let debounceTimer;
            input.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    this.performSearch(e.target.value, e.target.dataset.search);
                }, 300);
            });
        });
    }

    initAutoRefresh() {
        // Auto-refresh notifications every 30 seconds
        setInterval(() => {
            this.loadNotifications();
        }, 30000);

        // Auto-refresh dashboard stats every 5 minutes
        if (document.querySelector('.stats-grid')) {
            setInterval(() => {
                this.refreshDashboardStats();
            }, 300000);
        }
    }

    attachEventListeners() {
        // Mobile sidebar toggle
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                this.toggleMobileSidebar();
            });
        }

        // Notification bell
        const notificationBtn = document.querySelector('.notification-btn');
        if (notificationBtn) {
            notificationBtn.addEventListener('click', () => {
                this.toggleNotificationPanel();
            });
        }

        // Status update buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('update-status-btn')) {
                this.updateStatus(e.target);
            }
            
            if (e.target.classList.contains('delete-btn')) {
                this.confirmDelete(e.target);
            }
            
            if (e.target.classList.contains('quick-action-btn')) {
                this.performQuickAction(e.target);
            }
        });

        // Global search
        const globalSearch = document.querySelector('#globalSearch');
        if (globalSearch) {
            let debounceTimer;
            globalSearch.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    this.performGlobalSearch(e.target.value);
                }, 300);
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            this.handleKeyboardShortcuts(e);
        });

        // Auto-logout warning
        this.initAutoLogoutWarning();
    }

    // ====================================
    // SIDEBAR FUNCTIONALITY
    // ====================================

    toggleMobileSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (sidebar) {
            sidebar.classList.toggle('show');
            
            if (!overlay) {
                const overlayEl = document.createElement('div');
                overlayEl.className = 'sidebar-overlay';
                overlayEl.addEventListener('click', () => {
                    this.toggleMobileSidebar();
                });
                document.body.appendChild(overlayEl);
            }
        }
    }

    // ====================================
    // NOTIFICATION SYSTEM
    // ====================================

    async loadNotifications() {
        try {
            const response = await fetch('/vehicare_db/api/notifications.php');
            const notifications = await response.json();
            
            this.updateNotificationBadge(notifications.unread_count);
            this.updateNotificationPanel(notifications.items);
        } catch (error) {
            console.error('Failed to load notifications:', error);
        }
    }

    updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-badge');
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
        const panel = document.querySelector('#notificationPanel');
        if (panel) {
            panel.innerHTML = notifications.map(notification => `
                <div class="notification-item ${notification.read ? '' : 'unread'}" data-id="${notification.id}">
                    <div class="notification-icon">
                        <i class="fas ${this.getNotificationIcon(notification.type)}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-message">${notification.message}</div>
                        <div class="notification-time">${this.formatTime(notification.created_at)}</div>
                    </div>
                </div>
            `).join('');
        }
    }

    toggleNotificationPanel() {
        const panel = document.querySelector('#notificationPanel');
        if (panel) {
            panel.classList.toggle('show');
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

    // ====================================
    // TABLE FUNCTIONALITY
    // ====================================

    addTableSorting(table) {
        const headers = table.querySelectorAll('thead th[data-sort]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                this.sortTable(table, header.dataset.sort, header);
            });
        });
    }

    sortTable(table, column, headerEl) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const currentDirection = headerEl.dataset.direction || 'asc';
        const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        
        // Clear other sort indicators
        table.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });
        
        // Add sort indicator to current column
        headerEl.classList.add(`sort-${newDirection}`);
        headerEl.dataset.direction = newDirection;
        
        // Sort rows
        rows.sort((a, b) => {
            const aValue = this.getCellValue(a, column);
            const bValue = this.getCellValue(b, column);
            
            if (newDirection === 'asc') {
                return aValue > bValue ? 1 : -1;
            } else {
                return aValue < bValue ? 1 : -1;
            }
        });
        
        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));
    }

    getCellValue(row, column) {
        const cell = row.querySelector(`[data-sort="${column}"]`);
        return cell ? cell.textContent.trim() : '';
    }

    addRowSelection(table) {
        const selectAllCheckbox = table.querySelector('th input[type="checkbox"]');
        const rowCheckboxes = table.querySelectorAll('tbody input[type="checkbox"]');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', () => {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                this.updateBulkActions();
            });
        }
        
        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.updateBulkActions();
            });
        });
    }

    addTableSearch(table) {
        const searchInput = document.querySelector(`[data-table="${table.id}"]`);
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterTable(table, e.target.value);
            });
        }
    }

    filterTable(table, searchTerm) {
        const rows = table.querySelectorAll('tbody tr');
        const term = searchTerm.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    }

    updateBulkActions() {
        const selectedCheckboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');
        const bulkActions = document.querySelector('.bulk-actions');
        
        if (bulkActions) {
            if (selectedCheckboxes.length > 0) {
                bulkActions.style.display = 'block';
                bulkActions.querySelector('.selected-count').textContent = selectedCheckboxes.length;
            } else {
                bulkActions.style.display = 'none';
            }
        }
    }

    // ====================================
    // FORM VALIDATION
    // ====================================

    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    validateField(input) {
        const value = input.value.trim();
        const type = input.type;
        let isValid = true;
        let errorMessage = '';
        
        // Clear previous errors
        this.clearFieldError(input);
        
        // Required validation
        if (input.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'This field is required';
        }
        
        // Type-specific validation
        if (value && isValid) {
            switch (type) {
                case 'email':
                    if (!this.isValidEmail(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address';
                    }
                    break;
                    
                case 'tel':
                    if (!this.isValidPhone(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid phone number';
                    }
                    break;
                    
                case 'number':
                    const min = input.getAttribute('min');
                    const max = input.getAttribute('max');
                    if (min && parseFloat(value) < parseFloat(min)) {
                        isValid = false;
                        errorMessage = `Value must be at least ${min}`;
                    }
                    if (max && parseFloat(value) > parseFloat(max)) {
                        isValid = false;
                        errorMessage = `Value must not exceed ${max}`;
                    }
                    break;
            }
        }
        
        // Custom validation patterns
        const pattern = input.getAttribute('pattern');
        if (pattern && value && !new RegExp(pattern).test(value)) {
            isValid = false;
            errorMessage = input.getAttribute('data-pattern-error') || 'Invalid format';
        }
        
        if (!isValid) {
            this.showFieldError(input, errorMessage);
        }
        
        return isValid;
    }

    showFieldError(input, message) {
        input.classList.add('is-invalid');
        
        let errorElement = input.parentNode.querySelector('.field-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'field-error';
            input.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }

    clearFieldError(input) {
        input.classList.remove('is-invalid');
        const errorElement = input.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }

    isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    isValidPhone(phone) {
        const regex = /^[\+]?[1-9][\d]{0,15}$/;
        return regex.test(phone.replace(/[\s\-\(\)]/g, ''));
    }

    // ====================================
    // SEARCH FUNCTIONALITY
    // ====================================

    async performSearch(query, target) {
        if (!query || query.length < 2) return;
        
        try {
            const response = await fetch(`/vehicare_db/api/search.php?q=${encodeURIComponent(query)}&target=${target}`);
            const results = await response.json();
            
            this.displaySearchResults(results, target);
        } catch (error) {
            console.error('Search failed:', error);
        }
    }

    async performGlobalSearch(query) {
        if (!query || query.length < 2) {
            this.hideGlobalSearchResults();
            return;
        }
        
        try {
            const response = await fetch(`/vehicare_db/api/global-search.php?q=${encodeURIComponent(query)}`);
            const results = await response.json();
            
            this.displayGlobalSearchResults(results);
        } catch (error) {
            console.error('Global search failed:', error);
        }
    }

    displaySearchResults(results, target) {
        const container = document.querySelector(`#${target}-results`);
        if (container) {
            container.innerHTML = results.map(item => {
                return `<div class="search-result-item" data-id="${item.id}">${item.title}</div>`;
            }).join('');
            container.style.display = results.length > 0 ? 'block' : 'none';
        }
    }

    displayGlobalSearchResults(results) {
        const container = document.querySelector('#globalSearchResults');
        if (container) {
            container.innerHTML = Object.entries(results).map(([category, items]) => `
                <div class="search-category">
                    <h4>${category}</h4>
                    ${items.map(item => `
                        <a href="${item.url}" class="search-result-item">
                            <div class="search-result-title">${item.title}</div>
                            <div class="search-result-description">${item.description}</div>
                        </a>
                    `).join('')}
                </div>
            `).join('');
            container.style.display = 'block';
        }
    }

    hideGlobalSearchResults() {
        const container = document.querySelector('#globalSearchResults');
        if (container) {
            container.style.display = 'none';
        }
    }

    // ====================================
    // STATUS MANAGEMENT
    // ====================================

    async updateStatus(button) {
        const id = button.dataset.id;
        const status = button.dataset.status;
        const type = button.dataset.type;
        
        if (!confirm(`Are you sure you want to change the status to "${status}"?`)) {
            return;
        }
        
        this.showLoading(button);
        
        try {
            const response = await fetch('/vehicare_db/api/update-status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.getCSRFToken()
                },
                body: JSON.stringify({ id, status, type })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Status updated successfully', 'success');
                this.refreshRow(id, type);
            } else {
                this.showNotification(result.message || 'Update failed', 'error');
            }
        } catch (error) {
            console.error('Status update failed:', error);
            this.showNotification('Update failed. Please try again.', 'error');
        } finally {
            this.hideLoading(button);
        }
    }

    async confirmDelete(button) {
        const id = button.dataset.id;
        const type = button.dataset.type;
        const name = button.dataset.name || 'this item';
        
        if (!confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
            return;
        }
        
        this.showLoading(button);
        
        try {
            const response = await fetch('/vehicare_db/api/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.getCSRFToken()
                },
                body: JSON.stringify({ id, type })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Item deleted successfully', 'success');
                this.removeRow(id);
            } else {
                this.showNotification(result.message || 'Delete failed', 'error');
            }
        } catch (error) {
            console.error('Delete failed:', error);
            this.showNotification('Delete failed. Please try again.', 'error');
        } finally {
            this.hideLoading(button);
        }
    }

    // ====================================
    // UI UTILITIES
    // ====================================

    showLoading(element) {
        element.disabled = true;
        const originalText = element.textContent;
        element.dataset.originalText = originalText;
        element.innerHTML = '<span class="spinner"></span> Loading...';
    }

    hideLoading(element) {
        element.disabled = false;
        element.textContent = element.dataset.originalText;
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${this.getNotificationTypeIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close" onclick="this.parentNode.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        const container = document.querySelector('.notification-container') || this.createNotificationContainer();
        container.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    createNotificationContainer() {
        const container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
        return container;
    }

    getNotificationTypeIcon(type) {
        const icons = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        return icons[type] || 'fa-info-circle';
    }

    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = `${rect.left + rect.width / 2}px`;
        tooltip.style.top = `${rect.top - tooltip.offsetHeight - 5}px`;
        
        setTimeout(() => tooltip.classList.add('show'), 10);
    }

    hideTooltip() {
        const tooltips = document.querySelectorAll('.tooltip');
        tooltips.forEach(tooltip => tooltip.remove());
    }

    // ====================================
    // DASHBOARD SPECIFIC
    // ====================================

    async refreshDashboardStats() {
        try {
            const response = await fetch('/vehicare_db/api/dashboard-stats.php');
            const stats = await response.json();
            
            Object.entries(stats).forEach(([key, value]) => {
                const element = document.querySelector(`[data-stat="${key}"]`);
                if (element) {
                    element.textContent = value;
                }
            });
        } catch (error) {
            console.error('Failed to refresh dashboard stats:', error);
        }
    }

    // ====================================
    // UTILITY FUNCTIONS
    // ====================================

    getCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'Just now';
        if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`;
        if (diff < 86400000) return `${Math.floor(diff / 3600000)}h ago`;
        return date.toLocaleDateString();
    }

    refreshRow(id, type) {
        // Refresh specific table row after update
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (row) {
            // Could implement row refresh logic here
            window.location.reload();
        }
    }

    removeRow(id) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (row) {
            row.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => row.remove(), 300);
        }
    }

    handleKeyboardShortcuts(e) {
        // Ctrl/Cmd + K for global search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const globalSearch = document.querySelector('#globalSearch');
            if (globalSearch) {
                globalSearch.focus();
            }
        }
        
        // Escape to close modals/panels
        if (e.key === 'Escape') {
            const openPanels = document.querySelectorAll('.panel.show, .modal.show');
            openPanels.forEach(panel => panel.classList.remove('show'));
        }
    }

    initAutoLogoutWarning() {
        // Warning 5 minutes before session expires
        const sessionTimeout = 30 * 60 * 1000; // 30 minutes
        const warningTime = sessionTimeout - (5 * 60 * 1000); // 25 minutes
        
        setTimeout(() => {
            if (confirm('Your session will expire in 5 minutes. Do you want to extend it?')) {
                // Extend session
                fetch('/vehicare_db/api/extend-session.php', { method: 'POST' });
            }
        }, warningTime);
    }

    async performQuickAction(button) {
        const action = button.dataset.action;
        const id = button.dataset.id;
        
        // Implementation depends on specific quick actions needed
        console.log('Quick action:', action, id);
    }
}

// Initialize the admin dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.vehicareAdmin = new VehiCareAdmin();
});

// Export for module use if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VehiCareAdmin;
}