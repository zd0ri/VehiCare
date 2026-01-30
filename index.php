<?php 
session_start();
require_once __DIR__ . '/includes/config.php';
include __DIR__ . '/includes/header.php';
?>

<!-- VehiCare Homepage -->

<?php include_once __DIR__ . '/includes/alert.php'; ?>

<!-- Hero Section -->
<section class="hero">
  <div class="hero-inner">
    <div>
      <h1>Your Vehicle <span class="highlight-text">Deserves</span> Expert Care</h1>
      <p>Professional vehicle maintenance and repair services. Trust VehiCare with your automotive needs.</p>
      <div class="hero-cta">
        <a href="#services" class="btn btn-dark btn-lg">Explore Services</a>
        <a href="#appointment" class="btn btn-outline-primary btn-lg">Book Now</a>
      </div>
    </div>
    <div class="hero-media">
      <div class="hero-icon-wrap">
        <i class="fas fa-car hero-icon"></i>
      </div>
    </div>
  </div>
</section>

<!-- Featured Services Section -->
<section class="featured-services" id="services">
  <div class="container">
    <h2 class="section-title">Our Services</h2>
    <div class="services-grid">
      <div class="service-card">
        <i class="fas fa-cogs"></i>
        <h3>Regular Maintenance</h3>
        <p>Keep your vehicle running smoothly with our comprehensive maintenance services including oil changes, filter replacements, and inspections.</p>
      </div>
      <div class="service-card">
        <i class="fas fa-tools"></i>
        <h3>Engine Repair</h3>
        <p>Expert engine diagnostics and repair services. We handle everything from minor tune-ups to major engine overhauls.</p>
      </div>
      <div class="service-card">
        <i class="fas fa-brake"></i>
        <h3>Brake Service</h3>
        <p>Safety is our priority. Professional brake inspection, repair, and replacement to ensure your vehicle stops safely.</p>
      </div>
      <div class="service-card">
        <i class="fas fa-oil-can"></i>
        <h3>Fluid Services</h3>
        <p>Complete fluid management including oil changes, coolant flushes, transmission fluid, and brake fluid services.</p>
      </div>
      <div class="service-card">
        <i class="fas fa-car"></i>
        <h3>Tire Services</h3>
        <p>Professional tire sales, installation, balancing, and rotation. Keep your tires in perfect condition.</p>
      </div>
      <div class="service-card">
        <i class="fas fa-battery-half"></i>
        <h3>Battery & Electrical</h3>
        <p>Battery testing, replacement, and electrical system diagnostics. We ensure your vehicle starts every time.</p>
      </div>
    </div>
  </div>
</section>

<!-- Why Choose Us -->
<section class="why-choose-us" id="about">
  <div class="container">
    <h2 class="section-title">Why Choose VehiCare?</h2>
    <div class="features-grid">
      <div class="feature-item">
        <i class="fas fa-star"></i>
        <h3>Expert Technicians</h3>
        <p>Our certified technicians bring years of experience and expertise to every service.</p>
      </div>
      <div class="feature-item">
        <i class="fas fa-check-circle"></i>
        <h3>Quality Guarantee</h3>
        <p>We stand behind our work with comprehensive warranties on all services and parts.</p>
      </div>
      <div class="feature-item">
        <i class="fas fa-clock"></i>
        <h3>Fast Service</h3>
        <p>Quick turnaround times without compromising on quality. Get back on the road faster.</p>
      </div>
      <div class="feature-item">
        <i class="fas fa-wallet"></i>
        <h3>Affordable Pricing</h3>
        <p>Competitive rates with transparent pricing. No hidden fees or surprise charges.</p>
      </div>
      <div class="feature-item">
        <i class="fas fa-tools"></i>
        <h3>Modern Equipment</h3>
        <p>State-of-the-art diagnostic and repair equipment for accurate service delivery.</p>
      </div>
      <div class="feature-item">
        <i class="fas fa-headset"></i>
        <h3>Customer Support</h3>
        <p>Dedicated support team ready to answer questions and assist you anytime.</p>
      </div>
    </div>
  </div>
</section>

<!-- Appointment Section -->
<section class="appointments-section" id="appointment">
  <div class="appointment-form">
    <h2>Book Your Service</h2>
    <form method="POST" action="/vehicare_db/admins/process_appointment.php">
      <div class="form-group">
        <label for="name">Full Name *</label>
        <input type="text" id="name" name="full_name" required>
      </div>
      <div class="form-group">
        <label for="phone">Phone Number *</label>
        <input type="tel" id="phone" name="phone" required>
      </div>
      <div class="form-group">
        <label for="email">Email Address *</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="vehicle">Vehicle Model *</label>
        <input type="text" id="vehicle" name="vehicle_model" placeholder="e.g., Toyota Camry 2020" required>
      </div>
      <div class="form-group">
        <label for="service">Select Service *</label>
        <select id="service" name="service_id" required>
          <option value="">Choose a service</option>
          <?php
          $serviceResult = $conn->query("SELECT * FROM services");
          if ($serviceResult) {
            while ($service = $serviceResult->fetch_assoc()) {
              echo "<option value='{$service['service_id']}'>{$service['service_name']} - \${$service['price']}</option>";
            }
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="date">Preferred Date *</label>
        <input type="date" id="date" name="appointment_date" required>
      </div>
      <div class="form-group">
        <label for="time">Preferred Time *</label>
        <input type="time" id="time" name="appointment_time" required>
      </div>
      <div class="form-group">
        <label for="notes">Additional Notes</label>
        <textarea id="notes" name="notes" placeholder="Tell us about your vehicle's issues or special requests..."></textarea>
      </div>
      <button type="submit" class="btn btn-dark btn-lg" style="width: 100%; margin-top: 10px;">Book Appointment</button>
    </form>
  </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section">
  <div class="container">
    <h2>What Our Customers Say</h2>
    <div class="testimonials-grid">
      <div class="testimonial-card">
        <div class="stars">
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
        </div>
        <p>"VehiCare provided exceptional service for my car. The technicians were knowledgeable and courteous. Highly recommended!"</p>
        <p class="author">- John M.</p>
      </div>
      <div class="testimonial-card">
        <div class="stars">
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
        </div>
        <p>"I've been bringing my vehicle here for 3 years. Great service, fair prices, and honest advice. You can trust them."</p>
        <p class="author">- Sarah K.</p>
      </div>
      <div class="testimonial-card">
        <div class="stars">
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
        </div>
        <p>"Best automotive service in town. They fixed my engine issue quickly and the pricing was very reasonable."</p>
        <p class="author">- Mike D.</p>
      </div>
    </div>
  </div>
</section>

<!-- Contact Section -->
<section class="contact-section" id="contact">
  <div class="container">
    <h2 class="section-title">Contact Information</h2>
    <div class="contact-grid">
      <div class="contact-item">
        <i class="fas fa-map-marker-alt"></i>
        <h3>Location</h3>
        <p>123 Auto Service Lane<br>Your City, State 12345</p>
      </div>
      <div class="contact-item">
        <i class="fas fa-phone"></i>
        <h3>Phone</h3>
        <p>+1 (555) 123-4567<br>+1 (555) 123-4568</p>
      </div>
      <div class="contact-item">
        <i class="fas fa-clock"></i>
        <h3>Hours</h3>
        <p>Mon - Fri: 8:00 AM - 6:00 PM<br>Sat: 9:00 AM - 4:00 PM</p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
      <?php
      $brandRows = [];
      mysqli_query($conn, "CREATE TABLE IF NOT EXISTS brands (
        brand_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(191) NOT NULL,
        description TEXT NULL,
        image VARCHAR(255) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
      $rb = mysqli_query($conn, "SELECT brand_id, name, image FROM brands ORDER BY name ASC LIMIT 10");
      if ($rb && mysqli_num_rows($rb) > 0) {
        while ($br = mysqli_fetch_assoc($rb)) $brandRows[] = $br;
      }
      if (count($brandRows) === 0) {
        
        $featuredBrands = ['Valentino','Creed','Perfume Dessert','Ian Darcy','Jo Malone', 'Dior', 'Tom Ford', 'Calvin Klein', 'Clinique', 'D&G'];
        foreach ($featuredBrands as $b) {
          echo '<a href="/essence_db/brand.php?brand=' . urlencode($b) . '"><img src="" alt="' . htmlspecialchars($b) . '" title="' . htmlspecialchars($b) . '" style="height:48px;object-fit:contain;" onerror="this.style.display=\'none\'" /></a>';
        }
      } else {
        foreach ($brandRows as $br) {
          if (!empty($br['image'])) {
            $img = htmlspecialchars('/essence_db/' . ltrim($br['image'], '/'));
            $url = '/essence_db/brand.php?brand=' . urlencode($br['name']);
            echo '<a href="' . htmlspecialchars($url) . '"><img src="' . $img . '" alt="' . htmlspecialchars($br['name']) . '" title="' . htmlspecialchars($br['name']) . '" style="height:48px;object-fit:contain;max-width:140px;" /></a>';
          }
        }
      }
      ?>
      </div>
    </div>
  </div>
</section>

<?php /* Popular Products section disabled - tables not in schema
<section id="popular-products" class="py-5">
  <div class="container text-center">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="mb-0">Popular Products</h2>
      <a href="/essence_db/brands.php" class="btn btn-sm btn-outline-secondary">Browse Brands</a>
    </div>


    <?php
    $search = '';
    $whereExtra = '';
    if (isset($_GET['search']) && trim($_GET['search']) !== '') {
      $search = trim($_GET['search']);
      $searchEsc = mysqli_real_escape_string($conn, strtolower($search));
      $whereExtra = " AND (LOWER(p.product_name) LIKE '%{$searchEsc}%' OR LOWER(p.scent_type) LIKE '%{$searchEsc}%' OR LOWER(p.description) LIKE '%{$searchEsc}%' OR LOWER(b.name) LIKE '%{$searchEsc}%')";
      echo '<div class="search-result">Showing results for <strong>' . htmlspecialchars($search) . '</strong></div>';
    }

    
    if (trim($whereExtra) === '') {
    
      $sql = "SELECT p.product_id AS productId, p.product_name, b.name as brand_name, p.scent_type AS scent_type, p.price, p.image, i.quantity,
      COALESCE(SUM(oi.quantity),0) AS sales_count
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    INNER JOIN inventory i ON p.product_id = i.product_id
    WHERE i.quantity > 0 AND p.status = 'available'
    GROUP BY p.product_id
    HAVING COALESCE(SUM(oi.quantity),0) > 0
    ORDER BY sales_count DESC, p.product_id ASC";
    } else {
      
      $sql = "SELECT p.product_id AS productId, p.product_name, b.name as brand_name, p.scent_type AS scent_type, p.price, p.image, i.quantity,
      COALESCE(SUM(oi.quantity),0) AS sales_count
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    INNER JOIN inventory i ON p.product_id = i.product_id
    WHERE i.quantity > 0 AND p.status = 'available' " . $whereExtra . "
    GROUP BY p.product_id
    ORDER BY sales_count DESC, p.product_id ASC";
    }

    $results = mysqli_query($conn, $sql);
    ?>

    <div class="products-grid">
    <?php
    if ($results && mysqli_num_rows($results) > 0) {
      while ($row = mysqli_fetch_assoc($results)) {
        $scent = htmlspecialchars($row['scent_type']);
        $brand = htmlspecialchars($row['brand_name']);
        $price = number_format($row['price'], 2);
        $rawImage = $row['image'];
        $maxQty = (int)$row['quantity'];

        $imgs = [];
        $qpi = mysqli_query($conn, "SELECT path FROM product_images WHERE product_id = {$row['productId']} ORDER BY product_image_id ASC");
        if ($qpi && mysqli_num_rows($qpi) > 0) {
          while ($rpi = mysqli_fetch_assoc($qpi)) {
            $imgs[] = $rpi['path'];
          }
        }
        if (empty($imgs) && !empty($rawImage)) {
          $imgs[] = $rawImage;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/essence_db/';
    ?>

      <div class="product-item">
        <div class="card shadow-sm product-card">
          
          <div style="position: relative; overflow: hidden; border-radius: 12px 12px 0 0;">
            <?php
            if (count($imgs) === 0) {
              echo '<div class="card-img-top missing">No image</div>';
            } elseif (count($imgs) === 1) {
              $p = str_replace('\\', '/', $imgs[0]);
              $imgUrl = preg_match('#^https?://#i', $p) ? $p : $baseUrl . ltrim($p, '/');
              echo '<img src="' . htmlspecialchars($imgUrl) . '" class="card-img-top img-fluid" alt="' . htmlspecialchars($brand) . '">';
            } else {
              $carouselId = 'homeCarousel_' . $row['productId'];
              echo '<div id="' . $carouselId . '" class="carousel slide" data-bs-ride="carousel">';
              echo '<div class="carousel-inner">';
              foreach ($imgs as $i => $pRaw) {
                $p = str_replace('\\', '/', $pRaw);
                $imgUrl = preg_match('#^https?://#i', $p) ? $p : $baseUrl . ltrim($p, '/');
                $active = $i === 0 ? ' active' : '';
                echo '<div class="carousel-item' . $active . '">';
                echo '<img src="' . htmlspecialchars($imgUrl) . '" class="d-block w-100" alt="' . htmlspecialchars($brand) . '">';
                echo '</div>';
              }
              echo '</div>';
              echo '</div>';
            }
            ?>
            
            <?php if (!empty($row['sales_count']) && (int)$row['sales_count'] >= 3): ?>
              <div class="badge-bestseller">Best seller</div>
            <?php endif; ?>
          </div>

        
          <div class="card-body">
            <h5 class="card-title product-name"><a href="/essence_db/brand.php?brand=<?php echo urlencode($brand); ?>"><?php echo $brand; ?></a></h5>
            <p class="product-scent"><?php echo $scent; ?></p>
            <p class="product-price">₱<?php echo $price; ?></p>

            <form method="POST" action="./cart/cart_update.php" class="mt-auto">
              <input type="hidden" name="item_id" value="<?php echo $row['productId']; ?>">
              <input type="hidden" name="type" value="add">
              <div class="product-qty">
                <input type="number" name="item_qty" value="1" min="1" max="<?php echo $maxQty; ?>" class="form-control form-control-sm">
              </div>
              <div class="product-actions">
                <button type="submit" class="btn btn-dark btn-sm">Add to Cart</button>
                <a href="/essence_db/product.php?id=<?php echo $row['productId']; ?>" class="btn btn-outline-secondary btn-sm">View</a>
              </div>
            </form>
          </div>
        </div>
      </div>

    <?php
      }
    } else {
      echo '<div style="grid-column: 1 / -1; text-align: center; padding: 40px;">No products found.</div>';
    }
    ?>
    </div>
  </div>
</section>
*/ ?>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
