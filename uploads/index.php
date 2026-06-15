<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../backend/config.php';

// Fetch featured services from database
try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE is_featured = 1 LIMIT 3");
    $stmt->execute();
    $featuredServices = $stmt->fetchAll();
} catch (PDOException $e) {
    $featuredServices = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventora | Plan Your Perfect Event with Confidence</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Home page specific animations */
        .hero-section {
            position: relative;
            overflow: hidden;
            animation: heroReveal 1.2s ease-out;
        }

        @keyframes heroReveal {
            0% {
                opacity: 0;
                transform: scale(0.95);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .floating-element {
            position: absolute;
            font-size: 2rem;
            opacity: 0.2;
            animation: floatAround 20s linear infinite;
        }

        .floating-element:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 20%;
            right: 15%;
            animation-delay: 5s;
        }

        .floating-element:nth-child(3) {
            bottom: 15%;
            left: 20%;
            animation-delay: 10s;
        }

        .floating-element:nth-child(4) {
            bottom: 25%;
            right: 10%;
            animation-delay: 15s;
        }

        @keyframes floatAround {
            0% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(20px, -20px) rotate(90deg);
            }
            50% {
                transform: translate(0, -40px) rotate(180deg);
            }
            75% {
                transform: translate(-20px, -20px) rotate(270deg);
            }
            100% {
                transform: translate(0, 0) rotate(360deg);
            }
        }

        .trust-banner {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: var(--backdrop-blur);
            -webkit-backdrop-filter: var(--backdrop-blur);
            padding: 1.2rem;
            border-radius: 60px;
            margin: 2rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2.5rem;
            flex-wrap: wrap;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-sm);
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-dark);
            font-weight: 600;
            font-size: 0.92rem;
        }

        .trust-item i {
            color: var(--accent-amber);
            font-size: 1.2rem;
        }

        .cta-banner {
            background: var(--gradient-gold) !important;
            padding: 4.5rem 2rem;
            border-radius: var(--border-radius);
            text-align: center;
            margin: 4rem 0;
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.2);
            border: 1px solid rgba(251, 191, 36, 0.2);
        }

        .cta-banner h2 {
            font-size: 2.6rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--primary-dark);
            letter-spacing: -0.03em;
        }

        .cta-banner p {
            font-size: 1.2rem;
            color: rgba(15, 23, 42, 0.8);
            margin-bottom: 2rem;
            font-weight: 500;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .category-showcase {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin: 3rem 0;
        }

        .category-card {
            background: white;
            padding: 2rem 1rem;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--shadow-md);
            transition: var(--transition-bounce);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            animation: cardAppear 0.6s ease-out backwards;
            border: 1px solid var(--border-light);
        }

        .category-card:nth-child(1) { animation-delay: 0.1s; }
        .category-card:nth-child(2) { animation-delay: 0.2s; }
        .category-card:nth-child(3) { animation-delay: 0.3s; }
        .category-card:nth-child(4) { animation-delay: 0.4s; }

        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-gold), var(--accent-purple));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .category-card:hover::before {
            transform: scaleX(1);
        }

        .category-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-lg);
        }

        .category-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #fef3c7, #fed7aa);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            transition: var(--transition-bounce);
        }

        .category-card:hover .category-icon {
            transform: rotate(360deg) scale(1.1);
            background: linear-gradient(135deg, var(--accent-gold), var(--accent-amber));
        }

        .category-icon i {
            font-size: 2rem;
            color: var(--accent-amber);
            transition: var(--transition-bounce);
        }

        .category-card:hover .category-icon i {
            color: white;
        }

        .category-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--primary-dark);
        }

        .category-card p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .featured-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--accent-gold);
            color: var(--primary-dark);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            animation: pulse 2s ease-in-out infinite;
        }

        .video-showcase {
            margin: 4rem 0;
            position: relative;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .video-showcase video {
            width: 100%;
            height: auto;
            display: block;
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(15,23,42,0.7), rgba(30,41,59,0.7));
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
            text-align: center;
            padding: 2rem;
        }

        .video-overlay h2 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease-out;
        }

        .video-overlay p {
            color: #cbd5e1;
            font-size: 1.2rem;
            max-width: 600px;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .play-button {
            width: 80px;
            height: 80px;
            background: var(--accent-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary-dark);
            cursor: pointer;
            animation: pulse 2s ease-in-out infinite;
            transition: var(--transition-bounce);
        }

        .play-button:hover {
            transform: scale(1.1);
            background: white;
        }

        .instagram-feed {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin: 3rem 0;
        }

        .insta-post {
            position: relative;
            border-radius: var(--border-radius-sm);
            overflow: hidden;
            aspect-ratio: 1;
            cursor: pointer;
        }

        .insta-post img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .insta-post:hover img {
            transform: scale(1.1);
        }

        .insta-overlay {
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
            color: white;
            font-size: 1.5rem;
            gap: 1rem;
        }

        .insta-post:hover .insta-overlay {
            opacity: 1;
        }

        .insta-overlay span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .newsletter-section {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.92));
            backdrop-filter: var(--backdrop-blur);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 4.5rem 2rem;
            border-radius: var(--border-radius);
            margin: 4rem 0;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .newsletter-section::before {
            content: '📧';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(251,191,36,0.08) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
            font-size: 10rem;
            opacity: 0.1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .newsletter-section h2 {
            color: white;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
            letter-spacing: -0.02em;
        }

        .newsletter-section p {
            color: #cbd5e1;
            margin-bottom: 2rem;
            font-size: 1.1rem;
            position: relative;
            z-index: 2;
        }

        .newsletter-form {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border-radius: 60px;
            overflow: hidden;
        }

        .newsletter-form input {
            flex: 1;
            padding: 1rem 1.6rem;
            border: none;
            font-size: 0.95rem;
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary-dark);
            outline: none;
        }

        .newsletter-form button {
            padding: 1rem 2.2rem;
            border: none;
            background: var(--gradient-gold);
            color: var(--primary-dark);
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .newsletter-form button:hover {
            filter: brightness(1.1);
        }

        .partner-section {
            margin: 4rem 0;
            text-align: center;
        }

        .partner-logos {
            display: flex;
            justify-content: center;
            gap: 2.5rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .partner-logo {
            width: 130px;
            height: 85px;
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: var(--backdrop-blur);
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--primary-dark);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
            border: 1px solid var(--glass-border);
        }

        .partner-logo:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-color: rgba(251, 191, 36, 0.4);
        }

        .why-choose-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin: 3rem 0;
        }

        .why-card {
            background: white;
            padding: 2.5rem 2rem;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--shadow-md);
            transition: var(--transition-bounce);
            position: relative;
            overflow: hidden;
        }

        .why-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-gold), var(--accent-purple));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .why-card:hover::after {
            transform: scaleX(1);
        }

        .why-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .why-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #fef3c7, #fed7aa);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            transition: var(--transition-bounce);
        }

        .why-card:hover .why-icon {
            transform: rotate(360deg);
            background: linear-gradient(135deg, var(--accent-gold), var(--accent-amber));
        }

        .why-icon i {
            font-size: 2.5rem;
            color: var(--accent-amber);
        }

        .why-card:hover .why-icon i {
            color: white;
        }

        .why-card h3 {
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        .why-card p {
            color: var(--text-muted);
            line-height: 1.7;
        }

        @media (max-width: 1024px) {
            .category-showcase {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .why-choose-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .instagram-feed {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .category-showcase {
                grid-template-columns: 1fr;
            }
            
            .why-choose-grid {
                grid-template-columns: 1fr;
            }
            
            .instagram-feed {
                grid-template-columns: 1fr;
            }
            
            .trust-banner {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .newsletter-form {
                flex-direction: column;
                gap: 1rem;
            }
            
            .newsletter-form input,
            .newsletter-form button {
                border-radius: 60px;
            }
        }
    </style>
</head>
<body>
    <!-- Header Template -->
    <?php include 'header.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Floating Elements for Animation -->
        <div class="floating-elements">
            <div class="floating-element">✨</div>
            <div class="floating-element">🎉</div>
            <div class="floating-element">🎊</div>
            <div class="floating-element">💫</div>
        </div>

        <!-- Hero Section with Background Image -->
        <section class="hero-section" style="background: linear-gradient(135deg, rgba(15,23,42,0.95), rgba(30,41,59,0.95)), url('https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=1200'); background-size: cover; background-position: center;">
            <div class="hero-text">
                <h1>Plan your <span class="highlight">perfect event</span><br>with confidence</h1>
                <p class="hero-subtitle">Book venues, catering, decoration, photography – all in one place. Join 10,000+ happy customers who've created unforgettable moments.</p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="services.php" class="btn btn-large">✨ Explore Services</a>
                    <a href="about.php" class="btn btn-outline btn-large" style="color: white; border-color: white;">📖 How It Works</a>
                </div>
                
                <!-- Trust Badges -->
                <div style="display: flex; gap: 2rem; margin-top: 2rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-star" style="color: var(--accent-gold);"></i>
                        <span style="color: #cbd5e1;">4.8/5 (1.2k+ reviews)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-shield-alt" style="color: var(--accent-gold);"></i>
                        <span style="color: #cbd5e1;">100% secure booking</span>
                    </div>
                </div>
            </div>
            <div class="hero-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
        </section>

        <!-- Trust Banner -->
        <div class="trust-banner">
            <div class="trust-item">
                <i class="fas fa-trophy"></i>
                <span>Best Event Platform 2025</span>
            </div>
            <div class="trust-item">
                <i class="fas fa-users"></i>
                <span>10,000+ Happy Customers</span>
            </div>
            <div class="trust-item">
                <i class="fas fa-clock"></i>
                <span>24/7 Customer Support</span>
            </div>
            <div class="trust-item">
                <i class="fas fa-gem"></i>
                <span>Premium Vendors Only</span>
            </div>
        </div>

        <!-- Categories Showcase -->
        <section class="featured-section">
            <h2 class="section-title">Plan Any Event</h2>
            <div class="category-showcase">
                <div class="category-card" onclick="window.location.href='services.php?category=wedding'">
                    <span class="featured-badge">Most Popular</span>
                    <div class="category-icon">
                        <i class="fas fa-ring"></i>
                    </div>
                    <h3>Weddings</h3>
                    <p>From intimate ceremonies to grand celebrations</p>
                </div>
                <div class="category-card" onclick="window.location.href='services.php?category=birthday'">
                    <div class="category-icon">
                        <i class="fas fa-birthday-cake"></i>
                    </div>
                    <h3>Birthdays</h3>
                    <p>Make every birthday unforgettable</p>
                </div>
                <div class="category-card" onclick="window.location.href='services.php?category=corporate'">
                    <div class="category-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3>Corporate</h3>
                    <p>Professional events that impress</p>
                </div>
                <div class="category-card" onclick="window.location.href='services.php?category=entertainment'">
                    <div class="category-icon">
                        <i class="fas fa-music"></i>
                    </div>
                    <h3>Entertainment</h3>
                    <p>Parties, concerts & live bands</p>
                </div>
            </div>
        </section>

        <!-- Featured Services with Database integration -->
        <section class="featured-section">
            <h2 class="section-title">Featured Services</h2>
            <div class="card-grid">
                <?php if (count($featuredServices) > 0): ?>
                    <?php foreach ($featuredServices as $service): ?>
                    <div class="service-card" onclick="window.location.href='service-details.php?id=<?php echo $service['id']; ?>'" style="cursor: pointer;">
                        <?php if ($service['is_featured']): ?>
                            <span class="badge">Featured</span>
                        <?php endif; ?>
                        <div class="card-image">
                            <img src="<?php echo htmlspecialchars($service['image_url'] ?: 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=400'); ?>" alt="<?php echo htmlspecialchars($service['name']); ?>">
                        </div>
                        <i class="fas <?php 
                            switch ($service['category']) {
                                case 'wedding': echo 'fa-ring'; break;
                                case 'birthday': echo 'fa-birthday-cake'; break;
                                case 'corporate': echo 'fa-briefcase'; break;
                                case 'photography': echo 'fa-camera'; break;
                                case 'catering': echo 'fa-utensils'; break;
                                case 'entertainment': echo 'fa-music'; break;
                                case 'venue': echo 'fa-hotel'; break;
                                default: echo 'fa-calendar-alt';
                            }
                        ?> service-icon"></i>
                        <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                        <p><?php echo htmlspecialchars($service['short_description'] ?: substr($service['description'], 0, 100)); ?>...</p>
                        <div class="price">Rs <?php echo number_format($service['price'], 0); ?></div>
                        <a href="service-details.php?id=<?php echo $service['id']; ?>" class="btn">View Details →</a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; color: #aaa; padding: 2rem;">
                        No featured services found.
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Why Choose Us Grid -->
        <section class="featured-section">
            <h2 class="section-title">Why Eventora?</h2>
            <div class="why-choose-grid">
                <div class="why-card">
                    <div class="why-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Save Time</h3>
                    <p>Compare multiple vendors in minutes, not days. All in one platform.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure Booking</h3>
                    <p>Your payment is protected. Only release funds when you're satisfied.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Quality Assured</h3>
                    <p>Every vendor is vetted and reviewed by real customers.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>We're here to help, anytime. Live chat and phone support.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <h3>Best Price</h3>
                    <p>Price match guarantee. We'll beat any genuine quote.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Easy Management</h3>
                    <p>Manage all your bookings in one dashboard. Never miss a detail.</p>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="about-section" style="background: linear-gradient(135deg, #f1f5f9, #ffffff);">
            <div class="about-grid">
                <div class="about-content">
                    <h2 class="section-title">Trusted by Thousands</h2>
                    <p>Eventora provides a centralized digital marketplace where customers can easily compare, book and manage event services. Our platform ensures transparent pricing, secure booking and efficient event planning.</p>
                    <div class="stats-container">
                        <div class="stat-item">
                            <div class="stat-number">1,234+</div>
                            <div class="stat-label">Events Planned</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">50+</div>
                            <div class="stat-label">Partner Vendors</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">10k+</div>
                            <div class="stat-label">Happy Clients</div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=600" alt="Happy Event">
                </div>
            </div>
        </section>

        <!-- Video Showcase -->
        <div class="video-showcase">
            <video poster="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=1200">
                <source src="#" type="video/mp4">
            </video>
            <div class="video-overlay">
                <h2>See Eventora in Action</h2>
                <p>Watch how we helped Sarah & Michael plan their dream wedding in just 2 weeks</p>
                <div class="play-button" onclick="playVideo()">
                    <i class="fas fa-play"></i>
                </div>
            </div>
        </div>

        <!-- Partner Section -->
        <div class="partner-section">
            <h2 class="section-title">Trusted Partners</h2>
            <div class="partner-logos">
                <div class="partner-logo">Grand Hotel</div>
                <div class="partner-logo">Elite Catering</div>
                <div class="partner-logo">Dream Decors</div>
                <div class="partner-logo">Perfect Moments</div>
            </div>
        </div>

        <!-- Instagram Feed -->
        <section class="featured-section">
            <h2 class="section-title">Follow Us @eventora</h2>
            <div class="instagram-feed">
                <div class="insta-post">
                    <img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=400" alt="Wedding">
                    <div class="insta-overlay">
                        <span><i class="fas fa-heart"></i> 234</span>
                        <span><i class="fas fa-comment"></i> 45</span>
                    </div>
                </div>
                <div class="insta-post">
                    <img src="https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=400" alt="Wedding">
                    <div class="insta-overlay">
                        <span><i class="fas fa-heart"></i> 456</span>
                        <span><i class="fas fa-comment"></i> 67</span>
                    </div>
                </div>
                <div class="insta-post">
                    <img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=400" alt="Wedding">
                    <div class="insta-overlay">
                        <span><i class="fas fa-heart"></i> 789</span>
                        <span><i class="fas fa-comment"></i> 89</span>
                    </div>
                </div>
                <div class="insta-post">
                    <img src="https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=400" alt="Event">
                    <div class="insta-overlay">
                        <span><i class="fas fa-heart"></i> 567</span>
                        <span><i class="fas fa-comment"></i> 78</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <div class="newsletter-section">
            <h2>Get Event Planning Tips & Offers</h2>
            <p>Subscribe to our newsletter and get 10% off your first booking</p>
            <div class="newsletter-form">
                <input type="email" placeholder="Enter your email address">
                <button onclick="subscribeNewsletter()">Subscribe</button>
            </div>
        </div>

        <!-- CTA Banner -->
        <section class="cta-banner">
            <h2>Ready to Start Planning?</h2>
            <p>Join thousands of happy customers who've created unforgettable events with Eventora</p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="register.php" class="btn btn-large">Get Started Free</a>
                <a href="contact.php" class="btn btn-outline btn-large" style="border-color: var(--primary-dark);">Contact Us</a>
            </div>
        </section>
    </main>

    <!-- Footer Template -->
    <?php include 'footer.php'; ?>

    <script src="../js/script.js"></script>
    <script>
        function playVideo() {
            alert('Video would play here - showcasing how Eventora works!');
        }

        function subscribeNewsletter() {
            const email = document.querySelector('.newsletter-form input').value;
            if (email && email.includes('@')) {
                alert('Thank you for subscribing! Check your email for 10% off coupon.');
                document.querySelector('.newsletter-form input').value = '';
            } else {
                alert('Please enter a valid email address');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const stats = document.querySelectorAll('.stat-number');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = entry.target;
                        const value = parseInt(target.textContent.replace(/[^0-9]/g, ''));
                        animateValue(target, 0, value, 2000);
                        observer.unobserve(target);
                    }
                });
            }, { threshold: 0.5 });
            
            stats.forEach(stat => observer.observe(stat));
        });

        function animateValue(element, start, end, duration) {
            const range = end - start;
            const increment = range / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= end) {
                    element.textContent = end + '+';
                    clearInterval(timer);
                } else {
                    element.textContent = Math.round(current) + '+';
                }
            }, 16);
        }
    </script>
</body>
</html>
