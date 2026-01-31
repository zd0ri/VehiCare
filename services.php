<?php 
session_start();
require_once __DIR__ . '/includes/config.php';
include __DIR__ . '/includes/header.php';
?>

<style>
    .services-page {
        min-height: 80vh;
        background: #f8f9fa;
        padding: 60px 20px;
    }

    .services-header {
        text-align: center;
        margin-bottom: 60px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }

    .services-header h1 {
        font-size: 2.5em;
        color: #1a1a1a;
        margin: 0 0 15px 0;
        font-weight: 700;
    }

    .services-header p {
        font-size: 1em;
        color: #666;
        margin: 0;
        line-height: 1.6;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .service-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .service-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .service-image {
        width: 100%;
        height: 220px;
        background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #dc143c;
        font-size: 4em;
        position: relative;
        overflow: hidden;
    }

    .service-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .service-image-icon {
        font-size: 5em;
    }

    .service-content {
        padding: 25px;
    }

    .service-title {
        font-size: 1.3em;
        color: #1a1a1a;
        margin: 0 0 10px 0;
        font-weight: 700;
    }

    .service-description {
        font-size: 0.95em;
        color: #666;
        margin: 0 0 20px 0;
        line-height: 1.5;
        min-height: 50px;
    }

    .service-technician {
        background: #f5f5f5;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .tech-label {
        font-size: 0.8em;
        color: #999;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .tech-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .tech-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #dc143c;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1em;
    }

    .tech-details h4 {
        margin: 0;
        font-size: 0.95em;
        color: #1a1a1a;
        font-weight: 600;
    }

    .tech-details p {
        margin: 3px 0 0 0;
        font-size: 0.85em;
        color: #666;
    }

    .service-details {
        font-size: 0.9em;
        color: #666;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .detail-label {
        font-weight: 600;
        color: #1a1a1a;
    }

    .detail-value {
        color: #dc143c;
        font-weight: 600;
    }

    .service-actions {
        display: flex;
        gap: 10px;
    }

    .btn-view {
        flex: 1;
        background: white;
        color: #dc143c;
        border: 2px solid #dc143c;
        padding: 10px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9em;
        text-decoration: none;
        text-align: center;
    }

    .btn-view:hover {
        background: #dc143c;
        color: white;
    }

    .btn-book-service {
        flex: 1;
        background: #dc143c;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9em;
    }

    .btn-book-service:hover {
        background: #a01030;
    }

    .availability-badge {
        display: inline-block;
        background: #27ae60;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .availability-badge.unavailable {
        background: #e74c3c;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: white;
        padding: 40px;
        border-radius: 15px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
    }

    .close-modal {
        position: absolute;
        right: 20px;
        top: 20px;
        font-size: 28px;
        font-weight: bold;
        color: #999;
        cursor: pointer;
        transition: color 0.3s;
    }

    .close-modal:hover {
        color: #1a1a1a;
    }

    .modal-title {
        font-size: 1.8em;
        color: #1a1a1a;
        margin: 0 0 10px 0;
        font-weight: 700;
    }

    .modal-subtitle {
        font-size: 0.95em;
        color: #666;
        margin: 0 0 30px 0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 8px;
        font-size: 0.95em;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-size: 0.95em;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #dc143c;
        box-shadow: 0 0 0 3px rgba(220, 20, 60, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .btn-submit {
        width: 100%;
        background: #dc143c;
        color: white;
        border: none;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95em;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background: #a01030;
    }

    @media (max-width: 768px) {
        .services-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .services-header h1 {
            font-size: 2em;
        }

        .modal-content {
            padding: 30px 20px;
        }
    }
</style>

<div class="services-page">
    <div class="services-header">
        <h1>Our Services</h1>
        <p>Professional vehicle maintenance and repair services with experienced technicians. Choose the service you need and book with an available expert.</p>
    </div>

    <div class="services-grid">
        <?php
        // Sample services data
        $services = [
            [
                'id' => 1,
                'name' => 'Oil Change & Filter',
                'description' => 'Complete oil change with premium filters and fluid check',
                'icon' => '🛢️',
                'price' => 49.99,
                'duration' => '30 mins',
                'technician' => 'John Smith',
                'tech_initial' => 'JS',
                'available' => true
            ],
            [
                'id' => 2,
                'name' => 'Brake Service',
                'description' => 'Brake pad replacement, rotor inspection, and fluid flush',
                'icon' => '🛑',
                'price' => 199.99,
                'duration' => '1-2 hours',
                'technician' => 'Mike Johnson',
                'tech_initial' => 'MJ',
                'available' => true
            ],
            [
                'id' => 3,
                'name' => 'Tire Rotation',
                'description' => 'Wheel balancing and tire rotation for optimal performance',
                'icon' => '🛞',
                'price' => 79.99,
                'duration' => '45 mins',
                'technician' => 'David Wilson',
                'tech_initial' => 'DW',
                'available' => true
            ],
            [
                'id' => 4,
                'name' => 'Battery Replacement',
                'description' => 'Professional battery installation and system diagnostic',
                'icon' => '🔋',
                'price' => 129.99,
                'duration' => '30 mins',
                'technician' => 'Carlos Martinez',
                'tech_initial' => 'CM',
                'available' => false
            ],
            [
                'id' => 5,
                'name' => 'Engine Diagnostics',
                'description' => 'Complete engine scan and performance analysis',
                'icon' => '⚙️',
                'price' => 99.99,
                'duration' => '1 hour',
                'technician' => 'Robert Brown',
                'tech_initial' => 'RB',
                'available' => true
            ],
            [
                'id' => 6,
                'name' => 'Air Filter Replacement',
                'description' => 'Engine and cabin air filter replacement for better performance',
                'icon' => '💨',
                'price' => 59.99,
                'duration' => '20 mins',
                'technician' => 'James Anderson',
                'tech_initial' => 'JA',
                'available' => true
            ],
            [
                'id' => 7,
                'name' => 'Suspension Service',
                'description' => 'Shock absorber inspection and alignment adjustment',
                'icon' => '🚗',
                'price' => 249.99,
                'duration' => '2-3 hours',
                'technician' => 'Thomas Lee',
                'tech_initial' => 'TL',
                'available' => true
            ],
            [
                'id' => 8,
                'name' => 'Coolant Flush',
                'description' => 'Radiator flush and coolant replacement for engine health',
                'icon' => '❄️',
                'price' => 89.99,
                'duration' => '1 hour',
                'technician' => 'Patricia Garcia',
                'tech_initial' => 'PG',
                'available' => true
            ]
        ];

        foreach ($services as $service):
        ?>
        <div class="service-card">
            <div class="service-image">
                <span class="service-image-icon"><?php echo $service['icon']; ?></span>
            </div>
            <div class="service-content">
                <?php if ($service['available']): ?>
                    <span class="availability-badge">Available</span>
                <?php else: ?>
                    <span class="availability-badge unavailable">Unavailable</span>
                <?php endif; ?>
                
                <h3 class="service-title"><?php echo htmlspecialchars($service['name']); ?></h3>
                <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>

                <div class="service-technician">
                    <div class="tech-label">Assigned Technician</div>
                    <div class="tech-info">
                        <div class="tech-avatar"><?php echo $service['tech_initial']; ?></div>
                        <div class="tech-details">
                            <h4><?php echo htmlspecialchars($service['technician']); ?></h4>
                            <p><?php echo $service['available'] ? 'Available Now' : 'Busy'; ?></p>
                        </div>
                    </div>
                </div>

                <div class="service-details">
                    <div class="detail-row">
                        <span class="detail-label">Price</span>
                        <span class="detail-value">$<?php echo number_format($service['price'], 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Duration</span>
                        <span class="detail-value"><?php echo htmlspecialchars($service['duration']); ?></span>
                    </div>
                </div>

                <div class="service-actions">
                    <button class="btn-view" onclick="viewDetails(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['name']); ?>')">View Details</button>
                    <?php if ($service['available']): ?>
                        <button class="btn-book-service" onclick="openBookingModal(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['name']); ?>', <?php echo $service['price']; ?>)">Book Now</button>
                    <?php else: ?>
                        <button class="btn-book-service" disabled style="opacity: 0.5; cursor: not-allowed;">Unavailable</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Booking Modal -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeBookingModal()">&times;</span>
        <h2 class="modal-title" id="modalServiceName">Book Service</h2>
        <p class="modal-subtitle">Complete the form below to book your appointment</p>

        <form method="POST" action="/vehicare_db/appointment.php">
            <input type="hidden" id="serviceId" name="service_id" value="">

            <div class="form-group">
                <label>Service</label>
                <input type="text" id="serviceName" readonly style="background: #f5f5f5; cursor: not-allowed;">
            </div>

            <div class="form-group">
                <label>Price</label>
                <input type="text" id="servicePrice" readonly style="background: #f5f5f5; cursor: not-allowed;">
            </div>

            <div class="form-group">
                <label for="appointmentDate">Preferred Date *</label>
                <input type="date" id="appointmentDate" name="appointment_date" required>
            </div>

            <div class="form-group">
                <label for="appointmentTime">Preferred Time *</label>
                <input type="time" id="appointmentTime" name="appointment_time" required>
            </div>

            <div class="form-group">
                <label for="vehicleInfo">Vehicle Information *</label>
                <textarea id="vehicleInfo" name="vehicle_info" placeholder="Enter your vehicle make, model, year, and license plate" required></textarea>
            </div>

            <div class="form-group">
                <label for="notes">Additional Notes</label>
                <textarea id="notes" name="notes" placeholder="Any special requests or additional information"></textarea>
            </div>

            <button type="submit" class="btn-submit">Confirm Booking</button>
        </form>
    </div>
</div>

<script>
function openBookingModal(serviceId, serviceName, price) {
    document.getElementById('serviceId').value = serviceId;
    document.getElementById('serviceName').value = serviceName;
    document.getElementById('servicePrice').value = '$' + price.toFixed(2);
    document.getElementById('modalServiceName').textContent = 'Book ' + serviceName;
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('appointmentDate').min = today;
    
    document.getElementById('bookingModal').classList.add('show');
}

function closeBookingModal() {
    document.getElementById('bookingModal').classList.remove('show');
}

function viewDetails(serviceId, serviceName) {
    // Could redirect to a detailed service page or show more info
    alert('Viewing details for: ' + serviceName);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('bookingModal');
    if (event.target === modal) {
        closeBookingModal();
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

