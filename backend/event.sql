-- =============================================
-- Eventora Database - Complete Setup Script
-- =============================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS eventora_db;
USE eventora_db;

-- =============================================
-- 1. USERS TABLE (Authentication & Profiles)
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    profile_image VARCHAR(500) DEFAULT NULL,
    role ENUM('user', 'admin', 'vendor') DEFAULT 'user',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 2. SERVICES TABLE (Event Services)
-- =============================================
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT,
    name VARCHAR(200) NOT NULL,
    category ENUM('wedding', 'birthday', 'corporate', 'photography', 'catering', 'entertainment', 'venue', 'decoration', 'other') DEFAULT 'other',
    subcategory VARCHAR(100) DEFAULT NULL,
    price DECIMAL(12,2) NOT NULL,
    discount_price DECIMAL(12,2) DEFAULT NULL,
    min_guests INT DEFAULT 1,
    max_guests INT DEFAULT 500,
    duration_hours INT DEFAULT 4,
    description TEXT,
    short_description VARCHAR(500),
    features TEXT,
    image_url VARCHAR(500),
    gallery_images TEXT,
    rating DECIMAL(3,2) DEFAULT 0,
    total_reviews INT DEFAULT 0,
    popularity_score INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_price (price),
    INDEX idx_rating (rating),
    INDEX idx_featured (is_featured),
    FULLTEXT INDEX idx_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 3. BOOKINGS TABLE (Customer Orders)
-- =============================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME DEFAULT NULL,
    event_address TEXT,
    guests INT DEFAULT 1,
    special_requests TEXT,
    base_price DECIMAL(12,2) NOT NULL,
    addons_price DECIMAL(12,2) DEFAULT 0,
    total_price DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method ENUM('card', 'cash', 'bank_transfer') DEFAULT NULL,
    payment_id VARCHAR(255) DEFAULT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cancelled_at DATETIME DEFAULT NULL,
    cancellation_reason TEXT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_service (service_id),
    INDEX idx_status (status),
    INDEX idx_event_date (event_date),
    INDEX idx_booking_number (booking_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 4. CART TABLE (Shopping Cart)
-- =============================================
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    quantity INT DEFAULT 1,
    event_date DATE DEFAULT NULL,
    guests INT DEFAULT NULL,
    special_requests TEXT,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    UNIQUE KEY unique_cart_item (user_id, service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 5. WISHLIST TABLE (Saved Services)
-- =============================================
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, service_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 6. REVIEWS & RATINGS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    booking_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200),
    comment TEXT,
    images TEXT,
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_service (service_id),
    INDEX idx_user (user_id),
    INDEX idx_rating (rating),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 7. VENDORS TABLE (Service Providers)
-- =============================================
CREATE TABLE IF NOT EXISTS vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    business_name VARCHAR(200) NOT NULL,
    business_registration VARCHAR(100),
    tax_id VARCHAR(100),
    business_address TEXT,
    business_phone VARCHAR(20),
    business_email VARCHAR(100),
    website VARCHAR(255),
    categories TEXT,
    description TEXT,
    logo_url VARCHAR(500),
    cover_image VARCHAR(500),
    experience_years INT DEFAULT 0,
    total_services INT DEFAULT 0,
    total_bookings INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_verified (is_verified),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 8. PAYMENTS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('card', 'cash', 'bank_transfer', 'online') NOT NULL,
    payment_status ENUM('pending', 'successful', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(255) UNIQUE,
    payment_details TEXT,
    refund_amount DECIMAL(12,2) DEFAULT 0,
    refund_reason TEXT,
    processed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id),
    INDEX idx_transaction (transaction_id),
    INDEX idx_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 9. NOTIFICATIONS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('booking', 'payment', 'reminder', 'promotion', 'system') DEFAULT 'system',
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 10. USER_SESSIONS TABLE (For security)
-- =============================================
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (session_token),
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INSERT SAMPLE DATA
-- =============================================

-- Insert Admin User
INSERT INTO users (name, email, password, phone, role, email_verified, status) VALUES
('Administrator', 'admin@eventora.com', '$2y$10$g1o2y78m16WdXZZBI1sBnOpQuZwCfnK69RNNMAGLu0R/ZCJ/dR0ve', '+94 77 123 4567', 'admin', TRUE, 'active');

-- Insert Demo User
INSERT INTO users (name, email, password, phone, address, city, role, email_verified, status) VALUES
('Demo User', 'user@eventora.com', '$2y$10$t64WHBv7U8hegVfHSjh31u4kTiqzuL0yDRwibNN4HGNGm5cVjJWhi', '+94 71 234 5678', '123 Main Street', 'Colombo', 'user', TRUE, 'active');

-- Insert Sample Vendors
INSERT INTO users (name, email, password, phone, role, email_verified, status) VALUES
('Priya Events', 'priya@eventora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+94 77 888 9999', 'vendor', TRUE, 'active'),
('Luxury Catering', 'catering@eventora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+94 76 555 7777', 'vendor', TRUE, 'active');

-- Insert Vendor Details
INSERT INTO vendors (user_id, business_name, business_phone, business_email, description, experience_years, is_verified) VALUES
(3, 'Elegant Events by Priya', '+94 77 888 9999', 'priya@eventora.com', 'Professional wedding planner with 5+ years of experience creating magical moments.', 5, TRUE),
(4, 'Luxury Catering Services', '+94 76 555 7777', 'catering@eventora.com', 'Premium catering services for all types of events.', 8, TRUE);

-- Insert Services
INSERT INTO services (vendor_id, name, category, price, discount_price, min_guests, max_guests, duration_hours, description, short_description, features, rating, total_reviews, popularity_score, is_featured) VALUES
(3, 'Premium Wedding Package', 'wedding', 150000, 135000, 50, 300, 12, 'Complete wedding arrangement including venue decoration, catering for 100 guests, professional photography, live music, and full coordination. Everything you need for your perfect day!', 'All-inclusive wedding package with premium services', '["Venue decoration with premium flowers", "Professional photography (8 hours)", "5-course catering for 100 guests", "Wedding cake & champagne toast", "Live music or DJ (4 hours)", "Wedding coordinator on-site", "Bridal bouquet & groom\'s boutonniere", "Invitations & stationery"]', 4.8, 128, 1000, TRUE),

(3, 'Premium Floral Design', 'decoration', 35000, 32000, 1, 50, 6, 'Beautiful floral arrangements including bridal bouquet, centerpieces, stage decor, and entrance arrangements. Custom designs available.', 'Stunning floral decorations for weddings and events', '["Bridal bouquet", "Table centerpieces", "Stage decor", "Entrance arrangements", "Fresh flowers guaranteed"]', 4.7, 98, 670, FALSE),

(4, 'Ultimate Birthday Package', 'birthday', 75000, 68000, 20, 150, 8, 'Theme decorations, premium catering, custom cake, entertainment, and party favors for an unforgettable birthday celebration.', 'Complete birthday party planning', '["Theme decorations", "Catering for 50 guests", "Custom birthday cake", "Entertainment", "Party favors", "Photography coverage"]', 4.9, 256, 950, TRUE),

(4, 'Elite Catering Package', 'catering', 85000, 78000, 50, 500, 8, 'Premium catering service with customizable 5-course menu, professional staff, and elegant presentation. Perfect for weddings and corporate events.', 'Gourmet catering for special events', '["100 guests included", "5-course gourmet meal", "Professional serving staff", "Customizable menu", "Beverage service"]', 4.8, 156, 820, TRUE),

(3, 'Corporate Gala Package', 'corporate', 250000, 225000, 100, 500, 10, 'Full event management for corporate gatherings including venue, AV equipment, catering, and professional hosting.', 'Complete corporate event solution', '["Venue rental", "AV equipment", "Catering for 200 guests", "Professional host", "Event coordination", "Branding materials"]', 4.7, 89, 750, TRUE),

(3, 'Premium Photography', 'photography', 45000, 42000, 1, 100, 8, 'Professional photography and videography with 2 photographers, 8 hours coverage, edited photos, and highlight video.', 'Capture your special moments', '["8 hours coverage", "2 professional photographers", "Edited digital photos", "Highlight video", "Online gallery"]', 4.9, 312, 1100, TRUE),

(4, 'Kids Party Package', 'birthday', 55000, 50000, 10, 80, 6, 'Fun-filled birthday with themed decorations, entertainers, games, activities, and party favors for kids.', 'Perfect for children\'s celebrations', '["30 kids included", "Themed decorations", "Professional entertainers", "Games & activities", "Party favors for all kids"]', 4.9, 203, 890, TRUE),

(3, 'Live Entertainment Package', 'entertainment', 65000, 60000, 1, 500, 6, 'Professional DJ service or live band, sound system, lighting, and MC for your special event.', 'Make your event unforgettable with live entertainment', '["DJ or Live band (4 hours)", "Professional sound system", "Stage lighting", "MC services", "Song requests welcome"]', 4.8, 134, 720, TRUE),

(4, 'Premium Venue Package', 'venue', 200000, 180000, 100, 500, 12, 'Luxury venue rental including hall, furniture, basic decor, and parking for up to 300 guests.', 'Elegant venue for your special day', '["300 guests capacity", "Furniture included", "Basic decor", "Ample parking", "Air conditioned", "Bridal suite"]', 4.6, 76, 580, FALSE);

-- Insert Sample Bookings
INSERT INTO bookings (booking_number, user_id, service_id, event_date, guests, base_price, total_price, status, payment_status) VALUES
('BK-2026-00001', 2, 1, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 150, 150000, 150000, 'confirmed', 'paid'),
('BK-2026-00002', 2, 2, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 80, 75000, 75000, 'pending', 'pending'),
('BK-2026-00003', 2, 5, DATE_ADD(CURDATE(), INTERVAL 45 DAY), 200, 250000, 250000, 'confirmed', 'paid');

-- Insert Sample Reviews
INSERT INTO reviews (user_id, service_id, booking_id, rating, title, comment, is_verified_purchase, status) VALUES
(2, 1, 1, 5, 'Amazing Wedding Experience!', 'The team at Eventora made our wedding absolutely perfect. Everything was organized flawlessly and the decorations were stunning. Highly recommended!', TRUE, 'approved'),
(2, 5, 3, 4, 'Great Corporate Event', 'Professional service and excellent coordination. Will definitely use again for future events.', TRUE, 'approved');

-- Insert Sample Wishlist Items
INSERT INTO wishlist (user_id, service_id) VALUES
(2, 4),
(2, 6),
(2, 7);

-- Insert Sample Notifications
INSERT INTO notifications (user_id, title, message, type) VALUES
(2, 'Booking Confirmed', 'Your wedding package booking has been confirmed!', 'booking'),
(2, 'Payment Received', 'Your payment of Rs 150,000 has been successfully processed.', 'payment');

-- =============================================
-- CREATE STORED PROCEDURES
-- =============================================

DELIMITER //

-- Get user dashboard statistics
CREATE PROCEDURE GetUserDashboardStats(IN p_user_id INT)
BEGIN
    SELECT 
        COUNT(DISTINCT b.id) as total_bookings,
        COALESCE(SUM(b.total_price), 0) as total_spent,
        COUNT(DISTINCT w.id) as wishlist_count,
        COUNT(DISTINCT r.id) as review_count
    FROM users u
    LEFT JOIN bookings b ON u.id = b.user_id AND b.status NOT IN ('cancelled')
    LEFT JOIN wishlist w ON u.id = w.user_id
    LEFT JOIN reviews r ON u.id = r.user_id
    WHERE u.id = p_user_id;
END //

-- Get popular services
CREATE PROCEDURE GetPopularServices(IN p_limit INT)
BEGIN
    SELECT 
        s.*,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(DISTINCT b.id) as booking_count
    FROM services s
    LEFT JOIN bookings b ON s.id = b.service_id
    LEFT JOIN reviews r ON s.id = r.service_id AND r.status = 'approved'
    WHERE s.is_active = TRUE
    GROUP BY s.id
    ORDER BY booking_count DESC, avg_rating DESC
    LIMIT p_limit;
END //

-- Get service details with ratings
CREATE PROCEDURE GetServiceDetails(IN p_service_id INT)
BEGIN
    SELECT 
        s.*,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(DISTINCT r.id) as total_reviews,
        (SELECT COUNT(*) FROM bookings WHERE service_id = p_service_id AND status = 'completed') as total_bookings
    FROM services s
    LEFT JOIN reviews r ON s.id = r.service_id AND r.status = 'approved'
    WHERE s.id = p_service_id
    GROUP BY s.id;
END //

DELIMITER ;

-- =============================================
-- CREATE TRIGGERS
-- =============================================

DELIMITER //

-- Update service rating when new review is added
CREATE TRIGGER update_service_rating
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE services 
    SET rating = (
        SELECT COALESCE(AVG(rating), 0) 
        FROM reviews 
        WHERE service_id = NEW.service_id AND status = 'approved'
    ),
    total_reviews = (
        SELECT COUNT(*) 
        FROM reviews 
        WHERE service_id = NEW.service_id AND status = 'approved'
    )
    WHERE id = NEW.service_id;
END //

-- Generate booking number before insert
CREATE TRIGGER generate_booking_number
BEFORE INSERT ON bookings
FOR EACH ROW
BEGIN
    IF NEW.booking_number IS NULL THEN
        SET NEW.booking_number = CONCAT('BK-', YEAR(CURDATE()), '-', LPAD((
            SELECT COALESCE(MAX(CAST(SUBSTRING(booking_number, -5) AS UNSIGNED)), 0) + 1
            FROM bookings
            WHERE booking_number LIKE CONCAT('BK-', YEAR(CURDATE()), '-%')
        ), 5, '0'));
    END IF;
END //

DELIMITER ;

-- =============================================
-- CREATE INDEXES FOR PERFORMANCE
-- =============================================

CREATE INDEX idx_bookings_user_status ON bookings(user_id, status);
CREATE INDEX idx_bookings_date_status ON bookings(event_date, status);
CREATE INDEX idx_services_category_price ON services(category, price);
CREATE INDEX idx_reviews_service_rating ON reviews(service_id, rating);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);

-- =============================================
-- DISPLAY SUMMARY
-- =============================================

SELECT 'Database Setup Complete!' AS Message;
SELECT COUNT(*) AS Total_Users FROM users;
SELECT COUNT(*) AS Total_Services FROM services;
SELECT COUNT(*) AS Total_Bookings FROM bookings;
SELECT COUNT(*) AS Total_Reviews FROM reviews;

-- Show all tables
SHOW TABLES;