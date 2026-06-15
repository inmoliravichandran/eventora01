<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../backend/config.php';

// Fetch filter parameters from GET request
$category = $_GET['category'] ?? 'all';
$sort = $_GET['sort'] ?? 'popular';
$priceRange = $_GET['price'] ?? 'all';
$rating = floatval($_GET['rating'] ?? 0);

// Build dynamic SQL query
$sql = "SELECT * FROM services WHERE is_active = 1";
$params = [];

if ($category !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if ($priceRange !== 'all') {
    if ($priceRange === '200000+') {
        $sql .= " AND price >= 200000";
    } else {
        $parts = explode('-', $priceRange);
        if (count($parts) === 2) {
            $sql .= " AND price BETWEEN ? AND ?";
            $params[] = floatval($parts[0]);
            $params[] = floatval($parts[1]);
        }
    }
}

if ($rating > 0) {
    $sql .= " AND rating >= ?";
    $params[] = $rating;
}

// Order by sorting selection
switch ($sort) {
    case 'price-low':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price-high':
        $sql .= " ORDER BY price DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY rating DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY id DESC";
        break;
    case 'popular':
    default:
        $sql .= " ORDER BY popularity_score DESC";
        break;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
}

// Icon helper
function getServiceIcon($cat) {
    $icons = [
        'wedding' => 'fa-ring',
        'birthday' => 'fa-birthday-cake',
        'corporate' => 'fa-briefcase',
        'photography' => 'fa-camera',
        'catering' => 'fa-utensils',
        'entertainment' => 'fa-music'
    ];
    return $icons[$cat] ?? 'fa-calendar-alt';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover Services | Eventora</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .service-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: var(--shadow-lg);
        }

        .card-image {
            position: relative;
            height: 220px;
            overflow: hidden;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .service-card:hover .card-image img {
            transform: scale(1.1);
        }

        .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(251,191,36,0.8), rgba(245,158,11,0.8));
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .service-card:hover .card-overlay {
            opacity: 1;
        }

        .card-overlay span {
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .service-card:hover .card-overlay span {
            transform: translateY(0);
        }

        .badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--accent-gold);
            color: var(--primary-dark);
            padding: 0.4rem 1rem;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
            box-shadow: var(--shadow-sm);
        }

        .wishlist-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ef4444;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 2;
            transition: var(--transition-bounce);
            box-shadow: var(--shadow-sm);
        }

        .wishlist-badge:hover {
            transform: scale(1.2);
            background: #ef4444;
            color: white;
        }

        .wishlist-badge.active {
            background: #ef4444;
            color: white;
        }

        .card-content {
            padding: 1.5rem;
        }

        .service-icon {
            font-size: 2rem;
            color: var(--accent-amber);
            margin-bottom: 0.8rem;
            transition: var(--transition-bounce);
            display: inline-block;
        }

        .service-card:hover .service-icon {
            transform: rotate(360deg) scale(1.2);
            color: var(--accent-gold);
        }

        .card-content h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--primary-dark);
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            margin-bottom: 0.8rem;
        }

        .stars {
            color: var(--accent-gold);
            letter-spacing: 2px;
        }

        .rating span {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .description {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .features-mini {
            display: flex;
            gap: 0.8rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .feature-mini {
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .feature-mini i {
            color: var(--accent-gold);
        }

        .price-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px dashed var(--border-light);
        }

        .price {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--accent-amber);
        }

        .price small {
            font-size: 0.75rem;
            font-weight: 400;
            color: var(--text-muted);
        }

        .btn-card {
            padding: 0.5rem 1.2rem;
            background: linear-gradient(135deg, var(--accent-gold), var(--accent-amber));
            color: var(--primary-dark);
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: var(--transition-bounce);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-card:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-gold);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 4rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 5rem;
            color: var(--accent-gold);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--text-muted);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <!-- Header Template -->
    <?php include 'header.php'; ?>

    <main class="main-content">
        <!-- Hero Section -->
        <div class="services-hero">
            <div class="floating-badge">
                <i class="fas fa-tag"></i> 50+ Premium Services
            </div>
            <h1>Discover Premium Event Services</h1>
            <p>From intimate gatherings to grand celebrations, find the perfect services for your special moments</p>
        </div>

        <!-- Category Pills -->
        <div class="category-pills" id="categoryPills">
            <div class="category-pill <?php echo $category === 'all' ? 'active' : ''; ?>" data-category="all">
                <i class="fas fa-th-large"></i> All Services
            </div>
            <div class="category-pill <?php echo $category === 'wedding' ? 'active' : ''; ?>" data-category="wedding">
                <i class="fas fa-ring"></i> Weddings
            </div>
            <div class="category-pill <?php echo $category === 'birthday' ? 'active' : ''; ?>" data-category="birthday">
                <i class="fas fa-birthday-cake"></i> Birthdays
            </div>
            <div class="category-pill <?php echo $category === 'corporate' ? 'active' : ''; ?>" data-category="corporate">
                <i class="fas fa-briefcase"></i> Corporate
            </div>
            <div class="category-pill <?php echo $category === 'photography' ? 'active' : ''; ?>" data-category="photography">
                <i class="fas fa-camera"></i> Photography
            </div>
            <div class="category-pill <?php echo $category === 'catering' ? 'active' : ''; ?>" data-category="catering">
                <i class="fas fa-utensils"></i> Catering
            </div>
            <div class="category-pill <?php echo $category === 'entertainment' ? 'active' : ''; ?>" data-category="entertainment">
                <i class="fas fa-music"></i> Entertainment
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> Sort By:</label>
                <select id="sortSelect" onchange="applyFilters()">
                    <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="price-low" <?php echo $sort === 'price-low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price-high" <?php echo $sort === 'price-high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-dollar-sign"></i> Price Range:</label>
                <select id="priceSelect" onchange="applyFilters()">
                    <option value="all" <?php echo $priceRange === 'all' ? 'selected' : ''; ?>>All Prices</option>
                    <option value="0-50000" <?php echo $priceRange === '0-50000' ? 'selected' : ''; ?>>Under Rs 50,000</option>
                    <option value="50000-100000" <?php echo $priceRange === '50000-100000' ? 'selected' : ''; ?>>Rs 50,000 - 100,000</option>
                    <option value="100000-200000" <?php echo $priceRange === '100000-200000' ? 'selected' : ''; ?>>Rs 100,000 - 200,000</option>
                    <option value="200000+" <?php echo $priceRange === '200000+' ? 'selected' : ''; ?>>Above Rs 200,000</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-star"></i> Minimum Rating:</label>
                <select id="ratingSelect" onchange="applyFilters()">
                    <option value="0" <?php echo $rating === 0.0 ? 'selected' : ''; ?>>Any Rating</option>
                    <option value="4" <?php echo $rating === 4.0 ? 'selected' : ''; ?>>4+ Stars</option>
                    <option value="4.5" <?php echo $rating === 4.5 ? 'selected' : ''; ?>>4.5+ Stars</option>
                    <option value="5" <?php echo $rating === 5.0 ? 'selected' : ''; ?>>5 Stars Only</option>
                </select>
            </div>
        </div>

        <!-- Services Grid -->
        <div class="services-grid" id="servicesGrid">
            <?php if (count($services) > 0): ?>
                <?php foreach ($services as $service): ?>
                    <?php 
                    $starsCount = floor($service['rating']);
                    $halfStar = ($service['rating'] - $starsCount) >= 0.5 ? 1 : 0;
                    $stars = str_repeat('★', $starsCount) . ($halfStar ? '½' : '');
                    ?>
                    <div class="service-card" onclick="window.location.href='service-details.php?id=<?php echo $service['id']; ?>'">
                        <div class="card-image">
                            <img src="<?php echo htmlspecialchars($service['image_url'] ?: 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=400'); ?>" alt="<?php echo htmlspecialchars($service['name']); ?>">
                            <div class="card-overlay">
                                <span>Quick View</span>
                            </div>
                            <?php if ($service['is_featured']): ?>
                                <span class="badge">Featured</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-content">
                            <i class="fas <?php echo getServiceIcon($service['category']); ?> service-icon"></i>
                            <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                            <div class="rating">
                                <div class="stars"><?php echo $stars; ?></div>
                                <span>(<?php echo $service['total_reviews']; ?> reviews)</span>
                            </div>
                            <p class="description"><?php echo htmlspecialchars($service['short_description'] ?: substr($service['description'], 0, 100)); ?>...</p>
                            
                            <div class="price-section">
                                <div class="price">Rs <?php echo number_format($service['price'], 0); ?> <small>+tax</small></div>
                                <button class="btn-card" onclick="event.stopPropagation(); window.location.href='service-details.php?id=<?php echo $service['id']; ?>'">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>No services found</h3>
                    <p>Try adjusting your search criteria or price filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer Template -->
    <?php include 'footer.php'; ?>

    <script src="../js/script.js"></script>
    <script>
        function applyFilters() {
            const activePill = document.querySelector('.category-pill.active');
            const category = activePill ? activePill.dataset.category : 'all';
            const sort = document.getElementById('sortSelect').value;
            const price = document.getElementById('priceSelect').value;
            const rating = document.getElementById('ratingSelect').value;
            
            window.location.href = `services.php?category=${category}&sort=${sort}&price=${price}&rating=${rating}`;
        }

        document.querySelectorAll('.category-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                applyFilters();
            });
        });
    </script>
</body>
</html>
