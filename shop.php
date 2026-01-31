<?php 
session_start();
require_once __DIR__ . '/includes/config.php';
include __DIR__ . '/includes/header.php';
?>

<style>
    .shop-container {
        display: flex;
        gap: 30px;
        padding: 30px;
        max-width: 1400px;
        margin: 0 auto;
        min-height: 80vh;
    }

    .shop-sidebar {
        width: 220px;
        flex-shrink: 0;
    }

    .shop-content {
        flex: 1;
    }

    .category-menu {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .category-menu h3 {
        font-size: 1.1em;
        color: #1a1a1a;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .category-item {
        padding: 12px 15px;
        border-left: 3px solid transparent;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #666;
        font-size: 0.95em;
    }

    .category-item:hover {
        border-left-color: #dc143c;
        background: #f9f9f9;
        color: #dc143c;
    }

    .category-item.active {
        border-left-color: #dc143c;
        color: #dc143c;
        font-weight: 600;
    }

    .promo-banner {
        background: linear-gradient(135deg, #dc143c 0%, #a01030 100%);
        border-radius: 12px;
        padding: 30px;
        color: white;
        margin-bottom: 30px;
        text-align: center;
    }

    .promo-banner h2 {
        font-size: 1.8em;
        margin: 0 0 10px 0;
        font-weight: 700;
    }

    .promo-banner p {
        margin: 0 0 15px 0;
        font-size: 0.95em;
        opacity: 0.95;
    }

    .promo-banner .btn-promo {
        background: white;
        color: #dc143c;
        border: none;
        padding: 10px 25px;
        border-radius: 50px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .promo-banner .btn-promo:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .shop-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .shop-header h1 {
        font-size: 2em;
        color: #1a1a1a;
        margin: 0;
    }

    .shop-controls {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
    }

    .search-box input {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-size: 0.95em;
    }

    .search-box input:focus {
        outline: none;
        border-color: #dc143c;
        box-shadow: 0 0 0 3px rgba(220, 20, 60, 0.1);
    }

    .sort-select {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
    }

    .sort-select:focus {
        outline: none;
        border-color: #dc143c;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 30px;
    }

    .product-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .product-image {
        width: 100%;
        height: 200px;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #dc143c;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: 600;
    }

    .product-info {
        padding: 15px;
    }

    .product-category {
        font-size: 0.8em;
        color: #999;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .product-name {
        font-size: 0.95em;
        color: #1a1a1a;
        margin: 0 0 8px 0;
        font-weight: 600;
        line-height: 1.3;
        min-height: 2.6em;
    }

    .product-rating {
        color: #dc143c;
        font-size: 0.85em;
        margin-bottom: 8px;
    }

    .product-price {
        font-size: 1.3em;
        color: #dc143c;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .product-actions {
        display: flex;
        gap: 8px;
    }

    .btn-cart {
        flex: 1;
        background: #dc143c;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85em;
        transition: all 0.3s ease;
    }

    .btn-cart:hover {
        background: #a01030;
    }

    .btn-view {
        flex: 1;
        background: transparent;
        color: #dc143c;
        border: 1px solid #dc143c;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85em;
        transition: all 0.3s ease;
    }

    .btn-view:hover {
        background: #dc143c;
        color: white;
    }

    .section-title {
        font-size: 1.5em;
        color: #1a1a1a;
        margin: 40px 0 20px 0;
        font-weight: 700;
        padding-bottom: 15px;
        border-bottom: 2px solid #dc143c;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state i {
        font-size: 3em;
        color: #ddd;
        margin-bottom: 15px;
    }

    @media (max-width: 768px) {
        .shop-container {
            flex-direction: column;
        }

        .shop-sidebar {
            width: 100%;
        }

        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        .shop-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .shop-controls {
            width: 100%;
            flex-direction: column;
        }

        .search-box {
            min-width: 100%;
        }
    }
</style>

<div class="shop-container">
    <!-- Sidebar -->
    <div class="shop-sidebar">
        <div class="category-menu">
            <h3>Shop Categories</h3>
            <div class="category-item active" onclick="filterByCategory('')">All Products</div>
            <div class="category-item" onclick="filterByCategory('engine')">Engine Parts</div>
            <div class="category-item" onclick="filterByCategory('brake')">Brake Systems</div>
            <div class="category-item" onclick="filterByCategory('suspension')">Suspension</div>
            <div class="category-item" onclick="filterByCategory('electrical')">Electrical</div>
            <div class="category-item" onclick="filterByCategory('cooling')">Cooling System</div>
            <div class="category-item" onclick="filterByCategory('transmission')">Transmission</div>
            <div class="category-item" onclick="filterByCategory('wheel')">Wheels & Tires</div>
            <div class="category-item" onclick="filterByCategory('filter')">Filters</div>
        </div>

        <div class="promo-banner">
            <h2>Get 15% Off</h2>
            <p>On quality auto parts and gears</p>
            <button class="btn-promo">Shop Now</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="shop-content">
        <div class="shop-header">
            <h1>Auto Parts & Gears</h1>
            <div class="shop-controls">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search parts...">
                </div>
                <select class="sort-select" id="sortSelect">
                    <option value="">Sort By</option>
                    <option value="popular">Most Popular</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="newest">Newest</option>
                </select>
            </div>
        </div>

        <h2 class="section-title">Featured Products</h2>

        <div class="products-grid" id="productsGrid">
            <?php
            // Sample auto parts data
            $parts = [
                [
                    'id' => 1,
                    'name' => 'High Performance Air Filter',
                    'category' => 'filter',
                    'price' => 45.99,
                    'rating' => 5,
                    'badge' => 'Bestseller',
                    'image' => 'https://via.placeholder.com/200x200?text=Air+Filter'
                ],
                [
                    'id' => 2,
                    'name' => 'Ceramic Brake Pads',
                    'category' => 'brake',
                    'price' => 89.99,
                    'rating' => 4.8,
                    'badge' => 'Popular',
                    'image' => 'https://via.placeholder.com/200x200?text=Brake+Pads'
                ],
                [
                    'id' => 3,
                    'name' => 'Oil Filter Kit',
                    'category' => 'filter',
                    'price' => 34.50,
                    'rating' => 4.9,
                    'badge' => 'Sale',
                    'image' => 'https://via.placeholder.com/200x200?text=Oil+Filter'
                ],
                [
                    'id' => 4,
                    'name' => 'Engine Spark Plugs (Set of 4)',
                    'category' => 'engine',
                    'price' => 125.00,
                    'rating' => 4.7,
                    'badge' => 'New',
                    'image' => 'https://via.placeholder.com/200x200?text=Spark+Plugs'
                ],
                [
                    'id' => 5,
                    'name' => 'Suspension Coil Springs',
                    'category' => 'suspension',
                    'price' => 250.00,
                    'rating' => 4.6,
                    'badge' => '',
                    'image' => 'https://via.placeholder.com/200x200?text=Coil+Springs'
                ],
                [
                    'id' => 6,
                    'name' => 'Radiator Cooling Unit',
                    'category' => 'cooling',
                    'price' => 185.99,
                    'rating' => 4.8,
                    'badge' => 'Bestseller',
                    'image' => 'https://via.placeholder.com/200x200?text=Radiator'
                ],
                [
                    'id' => 7,
                    'name' => 'LED Headlight Assembly',
                    'category' => 'electrical',
                    'price' => 299.99,
                    'rating' => 4.9,
                    'badge' => 'New',
                    'image' => 'https://via.placeholder.com/200x200?text=Headlight'
                ],
                [
                    'id' => 8,
                    'name' => 'Automatic Transmission Fluid',
                    'category' => 'transmission',
                    'price' => 42.50,
                    'rating' => 4.5,
                    'badge' => '',
                    'image' => 'https://via.placeholder.com/200x200?text=Transmission+Fluid'
                ],
                [
                    'id' => 9,
                    'name' => 'Premium Alloy Wheels (17")',
                    'category' => 'wheel',
                    'price' => 450.00,
                    'rating' => 4.7,
                    'badge' => 'Popular',
                    'image' => 'https://via.placeholder.com/200x200?text=Alloy+Wheels'
                ],
                [
                    'id' => 10,
                    'name' => 'Engine Gasket Set',
                    'category' => 'engine',
                    'price' => 75.00,
                    'rating' => 4.6,
                    'badge' => '',
                    'image' => 'https://via.placeholder.com/200x200?text=Gasket+Set'
                ],
                [
                    'id' => 11,
                    'name' => 'Brake Rotors (Pair)',
                    'category' => 'brake',
                    'price' => 165.00,
                    'rating' => 4.8,
                    'badge' => 'Sale',
                    'image' => 'https://via.placeholder.com/200x200?text=Brake+Rotors'
                ],
                [
                    'id' => 12,
                    'name' => 'Power Steering Pump',
                    'category' => 'electrical',
                    'price' => 210.00,
                    'rating' => 4.4,
                    'badge' => '',
                    'image' => 'https://via.placeholder.com/200x200?text=Steering+Pump'
                ]
            ];

            foreach ($parts as $part) {
                $stars = '';
                for ($i = 0; $i < floor($part['rating']); $i++) {
                    $stars .= '★';
                }
                echo '
                <div class="product-card" data-category="' . htmlspecialchars($part['category']) . '">
                    <div class="product-image">
                        <img src="' . htmlspecialchars($part['image']) . '" alt="' . htmlspecialchars($part['name']) . '">
                        ' . (!empty($part['badge']) ? '<span class="product-badge">' . htmlspecialchars($part['badge']) . '</span>' : '') . '
                    </div>
                    <div class="product-info">
                        <div class="product-category">' . htmlspecialchars($part['category']) . '</div>
                        <h3 class="product-name">' . htmlspecialchars($part['name']) . '</h3>
                        <div class="product-rating">' . $stars . ' <span style="color: #999;">(' . $part['rating'] . ')</span></div>
                        <div class="product-price">$' . number_format($part['price'], 2) . '</div>
                        <div class="product-actions">
                            <button class="btn-cart">Add to Cart</button>
                            <button class="btn-view">View</button>
                        </div>
                    </div>
                </div>
                ';
            }
            ?>
        </div>
    </div>
</div>

<script>
function filterByCategory(category) {
    const cards = document.querySelectorAll('.product-card');
    const items = document.querySelectorAll('.category-item');
    
    items.forEach(item => item.classList.remove('active'));
    event?.target?.classList.add('active');
    
    cards.forEach(card => {
        if (category === '' || card.dataset.category === category) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.product-card');
    
    cards.forEach(card => {
        const name = card.querySelector('.product-name').textContent.toLowerCase();
        if (name.includes(searchTerm)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});

document.getElementById('sortSelect')?.addEventListener('change', function(e) {
    const grid = document.getElementById('productsGrid');
    const cards = Array.from(document.querySelectorAll('.product-card'));
    
    cards.sort((a, b) => {
        switch(e.target.value) {
            case 'price_low':
                return parseFloat(a.querySelector('.product-price').textContent.replace('$', '')) - 
                       parseFloat(b.querySelector('.product-price').textContent.replace('$', ''));
            case 'price_high':
                return parseFloat(b.querySelector('.product-price').textContent.replace('$', '')) - 
                       parseFloat(a.querySelector('.product-price').textContent.replace('$', ''));
            default:
                return 0;
        }
    });
    
    cards.forEach(card => grid.appendChild(card));
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
