<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../backend/config.php';

$serviceId = intval($_GET['id'] ?? 0);
if ($serviceId <= 0) {
    $idStr = $_GET['id'] ?? '';
    if ($idStr === 'wedding') $serviceId = 1;
    elseif ($idStr === 'birthday') $serviceId = 3;
    elseif ($idStr === 'photography') $serviceId = 6;
    else {
        header("Location: services.php");
        exit;
    }
}

// Handle Post Actions (AJAX calls inside this page)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'add_review') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please login first']);
            exit;
        }
        $rating = intval($_POST['rating'] ?? 5);
        $comment = $_POST['comment'] ?? '';
        
        if (empty($comment)) {
            echo json_encode(['success' => false, 'message' => 'Review comment cannot be empty']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (user_id, service_id, rating, comment, status) VALUES (?, ?, ?, ?, 'approved')");
            $stmt->execute([$_SESSION['user_id'], $serviceId, $rating, $comment]);
            echo json_encode(['success' => true, 'message' => 'Review added successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to save review: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Fetch service details
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1");
$stmt->execute([$serviceId]);
$service = $stmt->fetch();

if (!$service) {
    header("Location: services.php");
    exit;
}

// Parse features JSON or text
$features = json_decode($service['features'] ?? '[]', true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($features)) {
    $features = $service['features'] ? explode(',', $service['features']) : [];
}

// Build gallery
$gallery = [];
if ($service['image_url']) {
    $gallery[] = $service['image_url'];
}
$dbGallery = json_decode($service['gallery_images'] ?? '[]', true);
if (is_array($dbGallery)) {
    $gallery = array_merge($gallery, $dbGallery);
}
$gallery = array_unique($gallery);

// Fetch reviews
$stmt = $pdo->prepare("SELECT r.*, u.name AS user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.service_id = ? AND r.status = 'approved' ORDER BY r.id DESC");
$stmt->execute([$serviceId]);
$reviews = $stmt->fetchAll();

$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo htmlspecialchars($service['name']); ?> | Eventora</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .main-content { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        .breadcrumb { margin-bottom: 1.5rem; font-size: 0.9rem; color: var(--text-muted); }
        .breadcrumb a { color: var(--accent-amber); text-decoration: none; }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2.5rem;
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            margin-bottom: 2.5rem;
        }

        .gallery-section { background: #f1f5f9; padding: 1rem; }
        .main-image { width: 100%; height: 380px; object-fit: cover; border-radius: var(--border-radius-sm); }
        .thumbnail-grid { display: flex; gap: 0.8rem; margin-top: 1rem; flex-wrap: wrap; }
        .thumbnail { width: 80px; height: 80px; object-fit: cover; border-radius: 12px; cursor: pointer; border: 2px solid transparent; }
        .thumbnail.active { border-color: var(--accent-gold); }

        .info-section { padding: 1.8rem; }
        .service-category { background: var(--accent-gold); padding: 0.2rem 1rem; border-radius: 30px; font-size: 0.8rem; font-weight: 600; display: inline-block; margin-bottom: 1rem; text-transform: uppercase; }
        .service-title { font-size: 2rem; margin-bottom: 0.5rem; }
        .rating-row { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .stars { color: var(--accent-gold); letter-spacing: 2px; }
        .price-tag { font-size: 1.8rem; font-weight: 700; color: var(--accent-amber); margin: 1rem 0; }
        .feature-list { display: flex; flex-wrap: wrap; gap: 0.8rem; margin-bottom: 1.5rem; }
        .feature-item { background: #f1f5f9; padding: 0.4rem 1rem; border-radius: 30px; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; }

        .package-options { margin: 1rem 0; padding: 1rem; background: #f8fafc; border-radius: var(--border-radius-sm); }
        .package-select { width: 100%; padding: 0.6rem; border-radius: 30px; border: 1px solid var(--border-light); margin-top: 0.5rem; }

        .action-buttons { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem; }
        .btn-primary, .btn-cart { background: linear-gradient(135deg, var(--accent-gold), var(--accent-amber)); color: var(--primary-dark); border: none; padding: 0.8rem 1.8rem; border-radius: 40px; font-weight: 700; cursor: pointer; transition: var(--transition-bounce); display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-outline { background: white; border: 2px solid var(--border-light); padding: 0.8rem 1.8rem; border-radius: 40px; font-weight: 600; cursor: pointer; }
        .btn-outline:hover { border-color: var(--accent-gold); transform: translateY(-2px); }

        .detail-tabs { background: white; border-radius: var(--border-radius); padding: 1.5rem; margin-top: 2rem; box-shadow: var(--shadow-md); }
        .review-item { border-bottom: 1px solid var(--border-light); padding: 1rem 0; }
        .review-item:last-child { border-bottom: none; }
        .review-user { font-weight: 600; margin-bottom: 0.3rem; }
        .review-comment { color: var(--text-dark); font-size: 0.95rem; margin-bottom: 0.3rem; }
        .review-date { color: var(--text-muted); font-size: 0.8rem; }

        .toast { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: #1e293b; color: white; padding: 0.75rem 1.5rem; border-radius: 40px; z-index: 2000; border-left: 4px solid var(--accent-gold); box-shadow: var(--shadow-lg); }

        @media (max-width: 900px) {
            .detail-grid { grid-template-columns: 1fr; }
            .info-section { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <!-- Header Template -->
    <?php include 'header.php'; ?>

    <main class="main-content">
        <div class="breadcrumb">
            <a href="services.php">Services</a> / 
            <a href="services.php?category=<?php echo $service['category']; ?>"><?php echo ucfirst($service['category']); ?></a> / 
            <strong><?php echo htmlspecialchars($service['name']); ?></strong>
        </div>

        <div class="detail-grid">
            <div class="gallery-section">
                <img id="mainImage" class="main-image" src="<?php echo htmlspecialchars($service['image_url'] ?: 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600'); ?>">
                <div class="thumbnail-grid" id="thumbnailContainer">
                    <?php foreach ($gallery as $index => $img): ?>
                        <img class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" src="<?php echo htmlspecialchars($img); ?>" onclick="changeImage(this)">
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="info-section">
                <span class="service-category"><?php echo htmlspecialchars($service['category']); ?></span>
                <h1 class="service-title"><?php echo htmlspecialchars($service['name']); ?></h1>
                
                <div class="rating-row">
                    <div class="stars"><?php echo str_repeat('★', floor($service['rating'])) . str_repeat('☆', 5 - floor($service['rating'])); ?></div>
                    <span>(<?php echo $service['total_reviews']; ?> reviews)</span>
                </div>
                
                <div class="price-tag" id="priceDisplay">Rs <?php echo number_format($service['price'], 0); ?></div>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
                
                <div class="feature-list" style="margin-top: 1.5rem;">
                    <?php foreach ($features as $f): ?>
                        <span class="feature-item"><i class="fas fa-check-circle" style="color:var(--accent-gold)"></i> <?php echo htmlspecialchars($f); ?></span>
                    <?php endforeach; ?>
                </div>
                
                <!-- Package selection & Customization -->
                <div class="package-options">
                    <label><strong>📦 Select Package:</strong></label>
                    <select id="packageSelect" class="package-select" onchange="updatePrice()">
                        <option value="standard" data-price="<?php echo $service['price']; ?>">Standard Package - Rs <?php echo number_format($service['price'], 0); ?></option>
                        <option value="premium" data-price="<?php echo $service['price'] * 1.4; ?>">Premium Package - Rs <?php echo number_format($service['price'] * 1.4, 0); ?> (+DJ, extra decor)</option>
                        <option value="luxury" data-price="<?php echo $service['price'] * 2.0; ?>">Luxury Package - Rs <?php echo number_format($service['price'] * 2.0, 0); ?> (Live band, drone)</option>
                    </select>
                    
                    <label style="margin-top:0.8rem; display:block;"><strong>📅 Select Event Date:</strong></label>
                    <input type="date" id="eventDate" style="width: 100%; padding: 0.6rem; border-radius: 30px; border: 1px solid var(--border-light); margin-top: 0.5rem;" required>

                    <label style="margin-top:0.8rem; display:block;"><strong>👥 Guests Count:</strong></label>
                    <input type="number" id="guestsCount" style="width: 100%; padding: 0.6rem; border-radius: 30px; border: 1px solid var(--border-light); margin-top: 0.5rem;" value="<?php echo $service['min_guests']; ?>" min="<?php echo $service['min_guests']; ?>" max="<?php echo $service['max_guests']; ?>">

                    <label style="margin-top:0.8rem; display:block;"><strong>➕ Customization (optional):</strong></label>
                    <div><label><input type="checkbox" id="customCatering" onchange="updatePrice()"> Extra Catering (+Rs 25,000)</label></div>
                    <div><label><input type="checkbox" id="customPhotography" onchange="updatePrice()"> Extended Photography (+Rs 15,000)</label></div>
                </div>

                <div class="action-buttons">
                    <button class="btn-cart" onclick="handleAddToCart()"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                    <button class="btn-outline" onclick="toggleWishlist()"><i class="far fa-heart"></i> Wishlist</button>
                    <button class="btn-outline" onclick="requestQuote()"><i class="fas fa-file-invoice"></i> Quote</button>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="detail-tabs">
            <h3>⭐ Customer Reviews</h3>
            <div id="reviewsList" style="margin-top: 1rem;">
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $r): ?>
                        <div class="review-item">
                            <div class="review-user">
                                <?php echo htmlspecialchars($r['user_name']); ?>
                                <span style="color: var(--accent-gold); margin-left: 10px;"><?php echo str_repeat('★', $r['rating']); ?></span>
                            </div>
                            <div class="review-comment"><?php echo htmlspecialchars($r['comment']); ?></div>
                            <div class="review-date">Reviewed on: <?php echo date('M d, Y', strtotime($r['created_at'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No reviews yet. Be the first to review!</p>
                <?php endif; ?>
            </div>
            
            <?php if ($isLoggedIn): ?>
                <div style="margin-top: 2rem; border-top: 1px solid var(--border-light); padding-top: 1.5rem;">
                    <h4>Write a Review</h4>
                    <textarea id="newReviewComment" placeholder="Share your experience with this service..." rows="3" style="width:100%; margin:1rem 0; padding:1rem; border-radius:12px; border: 1px solid var(--border-light); font-family: inherit;"></textarea>
                    
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div>
                            <label>Rating: </label>
                            <select id="newReviewRating" style="padding: 0.5rem; border-radius: 8px; border: 1px solid var(--border-light);">
                                <option value="5">★★★★★ (5)</option>
                                <option value="4">★★★★☆ (4)</option>
                                <option value="3">★★★☆☆ (3)</option>
                                <option value="2">★★☆☆☆ (2)</option>
                                <option value="1">★☆☆☆☆ (1)</option>
                            </select>
                        </div>
                        <button class="btn-primary" onclick="submitReview()">Submit Review</button>
                    </div>
                </div>
            <?php else: ?>
                <p style="margin-top: 2rem; color: var(--text-muted); font-style: italic;">
                    Please <a href="login.php" style="color: var(--accent-amber);">login</a> to write a review.
                </p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer Template -->
    <?php include 'footer.php'; ?>

    <script src="../js/script.js"></script>
    <script>
        const serviceId = <?php echo $serviceId; ?>;
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        const basePrice = <?php echo $service['price']; ?>;

        function changeImage(element) {
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('mainImage').src = element.src;
        }

        function calculateTotalPrice() {
            const selectEl = document.getElementById('packageSelect');
            const selectedOption = selectEl.options[selectEl.selectedIndex];
            let price = parseFloat(selectedOption.dataset.price);
            
            if (document.getElementById('customCatering').checked) {
                price += 25000;
            }
            if (document.getElementById('customPhotography').checked) {
                price += 15000;
            }
            return price;
        }

        function updatePrice() {
            const total = calculateTotalPrice();
            document.getElementById('priceDisplay').innerText = `Rs ${total.toLocaleString()}`;
        }

        function showToast(msg, isError = false) {
            const existing = document.querySelector('.toast');
            if (existing) existing.remove();
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.background = isError ? '#b91c1c' : '#1e293b';
            toast.textContent = msg;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2500);
        }

        async function handleAddToCart() {
            if (!isLoggedIn) {
                showToast('Please login to add services to your cart.', true);
                setTimeout(() => {
                    window.location.href = `login.php?redirect=service-details.php?id=${serviceId}`;
                }, 1500);
                return;
            }

            const eventDate = document.getElementById('eventDate').value;
            if (!eventDate) {
                showToast('Please select an event date.', true);
                document.getElementById('eventDate').focus();
                return;
            }

            const guests = document.getElementById('guestsCount').value;
            const packageSelect = document.getElementById('packageSelect');
            const packageName = packageSelect.options[packageSelect.selectedIndex].text;
            
            const customizations = [];
            if (document.getElementById('customCatering').checked) customizations.push('Extra Catering');
            if (document.getElementById('customPhotography').checked) customizations.push('Extended Photography');
            
            const specialRequests = JSON.stringify({
                packageName: packageName,
                customizations: customizations
            });

            const formData = new FormData();
            formData.append('service_id', serviceId);
            formData.append('quantity', 1);
            formData.append('event_date', eventDate);
            formData.append('guests', guests);
            formData.append('special_requests', specialRequests);

            try {
                const response = await fetch('../backend/add_to_cart.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    showToast('✅ Added to cart successfully!');
                    if (typeof updateCartCount === 'function') {
                        // Refresh cart linkage
                        setTimeout(() => window.location.href = 'cart.php', 1000);
                    }
                } else {
                    showToast(`❌ ${result.message}`, true);
                }
            } catch (err) {
                showToast('❌ Connection error. Please try again.', true);
            }
        }

        function toggleWishlist() {
            showToast('❤️ Added to Wishlist (Simulation)');
        }

        function requestQuote() {
            showToast('📩 Quote request sent to vendor successfully!');
        }

        async function submitReview() {
            const comment = document.getElementById('newReviewComment').value.trim();
            const rating = document.getElementById('newReviewRating').value;

            if (!comment) {
                showToast('Please enter a comment.', true);
                return;
            }

            const formData = new FormData();
            formData.append('action', 'add_review');
            formData.append('rating', rating);
            formData.append('comment', comment);

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    showToast('✅ Review submitted successfully!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(`❌ ${result.message}`, true);
                }
            } catch (err) {
                showToast('❌ Failed to submit review.', true);
            }
        }
    </script>
</body>
</html>
