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
        /* Overrides to fix card layout and conflicts from global style.css */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2.5rem;
            width: 100%;
            margin-top: 2rem;
        }

        .services-grid .service-card {
            width: 100% !important; /* Override forced 300px width from style.css */
            padding: 0 !important;   /* Override legacy 2rem padding so the image fits flush */
            background: var(--bg-card);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid var(--border-light);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            cursor: pointer;
        }

        .services-grid .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
            border-color: rgba(251, 191, 36, 0.4);
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
            transition: transform 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .services-grid .service-card:hover .card-image img {
            transform: scale(1.08);
        }

        .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .services-grid .service-card:hover .card-overlay {
            opacity: 1;
        }

        .card-overlay span {
            color: white;
            font-weight: 600;
            font-size: 1rem;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.6rem 1.4rem;
            border-radius: 40px;
            transform: translateY(15px);
            transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .services-grid .service-card:hover .card-overlay span {
            transform: translateY(0);
        }

        .badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--gradient-gold);
            color: var(--primary-dark);
            padding: 0.4rem 1.2rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
        }

        .card-content {
            padding: 1.8rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .service-icon {
            font-size: 1.8rem;
            color: var(--accent-amber);
            margin-bottom: 1rem;
            background: #fffbeb;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: var(--shadow-sm);
        }

        .services-grid .service-card:hover .service-icon {
            transform: scale(1.1) rotate(360deg);
            color: white;
            background: var(--gradient-gold);
        }

        .card-content h3 {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 0.6rem;
            color: var(--primary-dark);
            line-height: 1.3;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stars {
            color: var(--accent-gold);
            font-size: 1rem;
        }

        .rating span {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .description {
            color: var(--text-muted);
            font-size: 0.92rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            flex-grow: 1;
        }

        .price-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 1.2rem;
            border-top: 1px dashed var(--border-light);
        }

        .price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-dark);
            display: flex;
            flex-direction: column;
        }

        .price small {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-top: -2px;
        }

        .btn-card {
            padding: 0.7rem 1.4rem;
            background: var(--gradient-gold);
            color: var(--primary-dark);
            border-radius: 40px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2);
        }

        .btn-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(245, 158, 11, 0.4);
            color: var(--primary-dark);
        }

        /* Hero Section Styling */
        .services-hero {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
            color: white;
            padding: 4.5rem 2rem;
            border-radius: var(--border-radius);
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .services-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 80% 20%, rgba(251, 191, 36, 0.15) 0%, transparent 50%);
            pointer-events: none;
        }

        .services-hero h1 {
            color: white;
            font-size: clamp(2.2rem, 5vw, 3.2rem);
            font-weight: 800;
            margin-bottom: 1rem;
            letter-spacing: -0.03em;
            line-height: 1.1;
        }

        .services-hero p {
            color: #cbd5e1;
            font-size: 1.15rem;
            max-width: 650px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .floating-badge {
            background: rgba(251, 191, 36, 0.12);
            border: 1px solid rgba(251, 191, 36, 0.3);
            color: var(--accent-gold);
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Category Pills styling */
        .category-pills {
            display: flex;
            gap: 0.8rem;
            overflow-x: auto;
            padding: 0.5rem 0.2rem 1.5rem 0.2rem;
            margin-bottom: 2.5rem;
            scrollbar-width: none; /* Hide scrollbar for Firefox */
            -ms-overflow-style: none; /* Hide scrollbar for IE/Edge */
        }

        .category-pills::-webkit-scrollbar {
            display: none; /* Hide scrollbar for Chrome/Safari */
        }

        .category-pill {
            background: white;
            border: 1px solid var(--border-light);
            padding: 0.9rem 1.8rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            white-space: nowrap;
            font-weight: 600;
            color: var(--text-muted);
            box-shadow: var(--shadow-sm);
        }

        .category-pill i {
            font-size: 1rem;
            color: var(--text-muted);
            transition: color 0.3s;
        }

        .category-pill:hover {
            transform: translateY(-3px);
            color: var(--primary-dark);
            border-color: rgba(251, 191, 36, 0.5);
            box-shadow: var(--shadow-md);
        }

        .category-pill.active {
            background: var(--gradient-gold);
            color: var(--primary-dark);
            border-color: transparent;
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.25);
        }

        .category-pill.active i {
            color: var(--primary-dark);
        }

        /* Filter Bar styling */
        .filter-bar {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            flex: 1 1 200px;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 0.92rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
        }

        .filter-group label i {
            color: var(--accent-amber);
        }

        .filter-group select {
            width: 100%;
            padding: 0.7rem 1.2rem;
            border: 1px solid var(--border-light);
            border-radius: 30px;
            background: white;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-dark);
            cursor: pointer;
            outline: none;
            transition: all 0.3s;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1.2rem center;
            background-size: 1rem;
            padding-right: 2.8rem;
        }

        .filter-group select:focus, .filter-group select:hover {
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.15);
        }

        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            grid-column: 1 / -1;
            border: 1px solid var(--border-light);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--accent-gold);
            margin-bottom: 1.5rem;
            background: #fffbeb;
            width: 90px;
            height: 90px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: var(--shadow-sm);
        }

        .empty-state h3 {
            font-size: 1.6rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .services-hero {
                padding: 3rem 1.5rem;
            }
            .filter-bar {
                padding: 1.2rem 1.5rem;
            }
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
