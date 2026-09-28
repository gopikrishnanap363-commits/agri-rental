<?php
/**
 * Agricultural Equipment Rental Management Portal
 * Screen 1 — Welcome / Landing Page
 */

require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();

// Fetch Categories
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM `equipment_categories` WHERE `status` = 'active' ORDER BY `id` ASC");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    // Graceful fallback
}

// Fetch Featured Equipment
$featuredEquipment = [];
try {
    $stmt = $pdo->query("SELECT e.*, c.category_name, u.name as owner_name 
                         FROM `equipment` e 
                         JOIN `equipment_categories` c ON e.category_id = c.id 
                         JOIN `users` u ON e.owner_id = u.id 
                         WHERE e.approval_status = 'approved' AND e.availability_status = 'available'
                         ORDER BY e.rating_avg DESC LIMIT 6");
    $featuredEquipment = $stmt->fetchAll();
} catch (Exception $e) {
    // Graceful fallback
}
?>

<!-- Screen 1: Hero Section -->
<section class="hero-section">
    <div class="container position-relative z-1">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 text-center text-lg-start">
                <div class="hero-badge mb-3">
                    <i class="fa-solid fa-seedling text-warning"></i>
                    <span>Next-Gen Agricultural Mechanization Portal</span>
                </div>
                <h1 class="display-4 fw-extrabold text-white mb-3 lh-sm">
                    Rent Farm Machinery with <span class="text-warning">Zero Hassle</span>
                </h1>
                <p class="lead text-white-50 mb-4" style="max-width: 580px;">
                    Connecting farmers directly with trusted agricultural equipment owners. Book high-performance tractors, harvesters, rotavators, and implements at transparent daily rates.
                </p>

                <!-- Search Bar Form -->
                <div class="card p-2 border-0 shadow-lg rounded-4 mb-4" style="background: rgba(255, 255, 255, 0.95);">
                    <form action="<?= BASE_URL ?>/farmer/equipment.php" method="GET" class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-0 text-success"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" name="search" class="form-control border-0 bg-transparent" placeholder="Tractor, Harvester, Pump...">
                            </div>
                        </div>
                        <div class="col-md-4 border-start-md">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-0 text-success"><i class="fa-solid fa-location-dot"></i></span>
                                <input type="text" name="location" class="form-control border-0 bg-transparent" placeholder="City or District (e.g. Tiruchirappalli)">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-agri-primary w-100 py-2">
                                <i class="fa-solid fa-arrow-right me-1"></i>Search
                            </button>
                        </div>
                    </form>
                </div>

                <!-- CTAs & Stats -->
                <div class="d-flex flex-wrap align-items-center gap-3 justify-content-center justify-content-lg-start">
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-agri-accent btn-lg px-4 py-3 rounded-pill">
                        <i class="fa-solid fa-mobile-screen-button me-2"></i>Get Started (Login with OTP)
                    </a>
                    <a href="<?= BASE_URL ?>/farmer/equipment.php" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill">
                        <i class="fa-solid fa-list-check me-2"></i>Browse All Equipment
                    </a>
                </div>
            </div>

            <!-- Hero Illustration / Image -->
            <div class="col-lg-5 text-center">
                <div class="position-relative">
                    <img src="https://images.unsplash.com/photo-1592878904946-b3cd8ae243d0?auto=format&fit=crop&w=800&q=80" 
                         alt="Indian Agriculture Modern Tractor" 
                         class="img-fluid rounded-4 shadow-lg border border-3 border-white border-opacity-25"
                         style="max-height: 420px; object-fit: cover; width: 100%;">
                    
                    <!-- Floating Stat Badge -->
                    <div class="position-absolute bottom-0 start-0 translate-middle-y bg-white text-dark p-3 rounded-4 shadow-lg border text-start ms-n3 d-none d-sm-block">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success text-white rounded-circle p-2 fs-5">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">100% Verified Owners</h6>
                                <small class="text-muted">Instant Booking & Receipts</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Stats Bar -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3">
                <h3 class="fw-bold text-success mb-1">500+</h3>
                <p class="text-muted small mb-0">Active Farmers</p>
            </div>
            <div class="col-6 col-md-3">
                <h3 class="fw-bold text-success mb-1">150+</h3>
                <p class="text-muted small mb-0">Machinery Units</p>
            </div>
            <div class="col-6 col-md-3">
                <h3 class="fw-bold text-success mb-1">₹0</h3>
                <p class="text-muted small mb-0">Middleman Commission</p>
            </div>
            <div class="col-6 col-md-3">
                <h3 class="fw-bold text-success mb-1">4.9 / 5</h3>
                <p class="text-muted small mb-0">Farmer Satisfaction</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold text-uppercase">Machinery Categories</span>
            <h2 class="fw-bold mt-2">Explore Farm Machinery by Category</h2>
            <p class="text-muted">Select an equipment category to find available machines near you</p>
        </div>

        <div class="row g-3">
            <?php foreach ($categories as $cat): ?>
                <div class="col-6 col-md-4 col-lg-2-4" style="flex: 0 0 auto; width: 20%;">
                    <a href="<?= BASE_URL ?>/farmer/equipment.php?category=<?= (int)$cat['id'] ?>" class="category-pill h-100">
                        <div class="category-pill-icon">
                            <i class="fa-solid <?= e($cat['icon']) ?>"></i>
                        </div>
                        <h6 class="fw-bold mb-1 small"><?= e($cat['category_name']) ?></h6>
                        <small class="text-muted d-none d-sm-block" style="font-size: 0.75rem;">View Available</small>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Equipment Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold text-uppercase">Top Rented</span>
                <h2 class="fw-bold mt-2 mb-0">Featured Agricultural Machinery</h2>
            </div>
            <a href="<?= BASE_URL ?>/farmer/equipment.php" class="btn btn-agri-outline">
                View All <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php if (!empty($featuredEquipment)): ?>
                <?php foreach ($featuredEquipment as $eq): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="equipment-card">
                            <div class="equipment-img-wrapper">
                                <img src="<?= e($eq['primary_image']) ?>" 
                                     alt="<?= e($eq['equipment_name']) ?>" 
                                     class="equipment-img"
                                     onerror="this.src='https://images.unsplash.com/photo-1592878904946-b3cd8ae243d0?auto=format&fit=crop&w=600&q=80'">
                                <span class="badge-equipment-category"><?= e($eq['category_name']) ?></span>
                                <div class="badge-availability">
                                    <?= getStatusBadge($eq['availability_status']) ?>
                                </div>
                            </div>
                            <div class="equipment-body">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted small"><i class="fa-solid fa-tag me-1"></i><?= e($eq['brand']) ?> (<?= e($eq['manufacturing_year']) ?>)</span>
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-star me-1"></i><?= number_format((float)$eq['rating_avg'], 1) ?></span>
                                </div>
                                <h5 class="fw-bold mb-2 text-dark"><?= e($eq['equipment_name']) ?></h5>
                                <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot text-danger me-1"></i><?= e($eq['location']) ?></p>
                                
                                <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="equipment-price-tag"><?= formatPrice($eq['price_per_day']) ?></span>
                                        <span class="equipment-price-period">/ day</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="<?= BASE_URL ?>/farmer/equipment-details.php?id=<?= (int)$eq['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            Details
                                        </a>
                                        <a href="<?= BASE_URL ?>/farmer/booking.php?equipment_id=<?= (int)$eq['id'] ?>" class="btn btn-sm btn-agri-primary rounded-pill px-3">
                                            Book Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No equipment available currently.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold text-uppercase">Simple Process</span>
            <h2 class="fw-bold mt-2">How Agricultural Rental Works</h2>
            <p class="text-muted">Fast, transparent, and completely paperless farm equipment rental</p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="card card-agri p-4 h-100">
                    <div class="brand-icon mx-auto mb-3" style="width: 58px; height: 58px; font-size: 1.5rem;">
                        <i class="fa-solid fa-mobile-screen"></i>
                    </div>
                    <h5 class="fw-bold">1. Quick OTP Login</h5>
                    <p class="text-muted small mb-0">Enter your 10-digit mobile number and verify via instant SMS OTP. No complicated passwords required.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-agri p-4 h-100">
                    <div class="brand-icon mx-auto mb-3" style="width: 58px; height: 58px; font-size: 1.5rem;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <h5 class="fw-bold">2. Select Machinery</h5>
                    <p class="text-muted small mb-0">Search tractors, harvesters or sprayers in your district. Filter by price, brand, and live availability.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-agri p-4 h-100">
                    <div class="brand-icon mx-auto mb-3" style="width: 58px; height: 58px; font-size: 1.5rem;">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <h5 class="fw-bold">3. Choose Dates</h5>
                    <p class="text-muted small mb-0">Select your rental period. Our smart availability system instantly locks the dates and calculates the price.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-agri p-4 h-100">
                    <div class="brand-icon mx-auto mb-3" style="width: 58px; height: 58px; font-size: 1.5rem;">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <h5 class="fw-bold">4. Pay & Get Receipt</h5>
                    <p class="text-muted small mb-0">Pay securely via UPI, Card, or Cash on Delivery. Instantly generate, print, and download your tax receipt.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Owner Callout Banner -->
<section class="py-5" style="background: linear-gradient(135deg, #1b5e20, #0d3822); color: white;">
    <div class="container text-center py-4">
        <h2 class="display-6 fw-bold text-white mb-3">Own Agricultural Machinery? Earn Extra Income!</h2>
        <p class="lead text-white-50 mb-4 mx-auto" style="max-width: 650px;">
            List your tractors, combine harvesters, or implements when not in use. Connect with verified farmers in your district and earn guaranteed rental income.
        </p>
        <a href="<?= BASE_URL ?>/login.php?role=owner" class="btn btn-agri-accent btn-lg px-5 py-3 rounded-pill">
            <i class="fa-solid fa-plus-circle me-2"></i>Register Equipment Now
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
