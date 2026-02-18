            </main>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner"></div>
            <p>Loading...</p>
        </div>
    </div>

    <!-- Notification Container -->
    <div class="notification-container" id="notificationContainer"></div>

    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" style="display: none;"></div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Action</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage">Are you sure you want to perform this action?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
                <button class="btn btn-danger" id="confirmButton">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Quick Action Modal -->
    <div id="quickActionModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="quickActionTitle">Quick Action</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body" id="quickActionBody">
                <!-- Dynamic content loaded here -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeQuickActionModal()">Cancel</button>
                <button class="btn btn-primary" id="quickActionSubmit">Submit</button>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <!-- jQuery (optional for compatibility) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    
    <!-- Main Admin JavaScript -->
    <script src="/vehicare_db/assets/js/admin.js"></script>
    
    <!-- Additional page-specific scripts -->
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo htmlspecialchars($js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Inline JavaScript for page-specific functionality -->
    <script>
        // Page-specific configuration
        window.VehiCareConfig = {
            user: {
                id: <?php echo $current_user['id']; ?>,
                role: '<?php echo htmlspecialchars($current_user['role']); ?>',
                name: '<?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?>'
            },
            csrf_token: '<?php echo $csrf_token; ?>',
            api_base: '/vehicare_db/api/',
            page: '<?php echo basename($_SERVER['PHP_SELF'], '.php'); ?>'
        };

        // Initialize page-specific functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-refresh certain data every 30 seconds
            if (window.VehiCareConfig.page === 'dashboard') {
                setInterval(refreshDashboardData, 30000);
            }
            
            // Handle logout confirmation
            document.querySelectorAll('a[href*="logout"]').forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to logout?')) {
                        e.preventDefault();
                    }
                });
            });

            // Initialize real-time features if supported
            if (window.WebSocket) {
                initializeWebSocket();
            }
        });

        // Utility functions
        function refreshDashboardData() {
            if (typeof window.vehicareAdmin !== 'undefined') {
                window.vehicareAdmin.refreshDashboardStats();
                window.vehicareAdmin.loadNotifications();
            }
        }

        function initializeWebSocket() {
            // WebSocket connection for real-time updates (if implemented)
            try {
                const ws = new WebSocket('ws://localhost:8080/vehicare');
                ws.onmessage = function(event) {
                    const data = JSON.parse(event.data);
                    handleRealTimeUpdate(data);
                };
            } catch (error) {
                console.log('WebSocket not available');
            }
        }

        function handleRealTimeUpdate(data) {
            switch (data.type) {
                case 'new_appointment':
                    showNotification('New appointment received!', 'info');
                    if (window.vehicareAdmin) {
                        window.vehicareAdmin.loadNotifications();
                    }
                    break;
                case 'status_update':
                    // Handle status updates
                    break;
                case 'notification':
                    showNotification(data.message, data.level || 'info');
                    break;
            }
        }

        function showNotification(message, type = 'info') {
            if (window.vehicareAdmin) {
                window.vehicareAdmin.showNotification(message, type);
            }
        }

        function markAllAsRead() {
            fetch('/vehicare_db/api/notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.VehiCareConfig.csrf_token
                },
                body: JSON.stringify({ action: 'mark_all_read' })
            }).then(() => {
                if (window.vehicareAdmin) {
                    window.vehicareAdmin.loadNotifications();
                }
            });
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
        }

        function closeQuickActionModal() {
            document.getElementById('quickActionModal').style.display = 'none';
        }

        // Handle mobile sidebar
        document.addEventListener('click', function(e) {
            if (e.target.matches('.sidebar-toggle')) {
                toggleMobileSidebar();
            }
            
            if (e.target.matches('.sidebar-overlay')) {
                hideMobileSidebar();
            }
        });

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar.classList.contains('show')) {
                hideMobileSidebar();
            } else {
                showMobileSidebar();
            }
        }

        function showMobileSidebar() {
            document.getElementById('sidebar').classList.add('show');
            document.getElementById('sidebarOverlay').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function hideMobileSidebar() {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').style.display = 'none';
            document.body.style.overflow = '';
        }

        // Handle form submissions with loading states
        document.addEventListener('submit', function(e) {
            if (e.target.hasAttribute('data-loading')) {
                const submitBtn = e.target.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
                }
            }
        });

        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
            // Optionally send to error logging service
        });

        // Handle AJAX errors globally
        document.addEventListener('ajaxError', function(e) {
            showNotification('An error occurred. Please try again.', 'error');
        });
    </script>

    <?php if (isset($inline_js)): ?>
    <script>
        <?php echo $inline_js; ?>
    </script>
    <?php endif; ?>

    <!-- Performance monitoring (optional) -->
    <script>
        if ('performance' in window) {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    if (perfData.loadEventEnd - perfData.loadEventStart > 3000) {
                        console.warn('Slow page load detected:', perfData.loadEventEnd - perfData.loadEventStart + 'ms');
                    }
                }, 0);
            });
        }
    </script>
</body>
</html>

<?php
// Clean up any output buffers and ensure proper session handling
if (ob_get_level()) {
    ob_end_flush();
}

// Save session data
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}
?>