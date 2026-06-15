<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../backend/config.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Query cart items with service details
try {
    $stmt = $pdo->prepare("SELECT c.*, s.name, s.price, s.image_url, s.category FROM cart c JOIN services s ON c.service_id = s.id WHERE c.user_id = ?");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();
} catch (PDOException $e) {
    $cartItems = [];
}

if (count($cartItems) === 0) {
    header("Location: cart.php");
    exit;
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$delivery = 2500;
$serviceFee = 1500;
$discount = count($cartItems) >= 2 ? 15000 : 0;
$total = $subtotal + $delivery + $serviceFee - $discount;

// Default event date to the first item's event date
$defaultEventDate = '';
foreach ($cartItems as $item) {
    if ($item['event_date']) {
        $defaultEventDate = $item['event_date'];
        break;
    }
}
if (!$defaultEventDate) {
    $defaultEventDate = date('Y-m-d', strtotime('+30 days'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout | Eventora</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .main-content { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .checkout-container { display: grid; grid-template-columns: 2fr 1.2fr; gap: 2.5rem; margin-top: 2rem; }
        .checkout-section { background: white; border-radius: var(--border-radius); padding: 1.8rem; box-shadow: var(--shadow-md); margin-bottom: 2rem; border: 1px solid var(--border-light); }
        .section-header { display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1.5rem; }
        .section-number { width: 30px; height: 30px; border-radius: 50%; background: var(--accent-gold); color: var(--primary-dark); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; }
        .section-title { font-size: 1.25rem; font-weight: 700; color: var(--primary-dark); margin: 0; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
        .input-group { display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 1rem; }
        .input-group label { font-size: 0.85rem; font-weight: 600; color: var(--text-dark); }
        .input-group input, .input-group select, .input-group textarea { padding: 0.75rem 1rem; border-radius: 30px; border: 1px solid var(--border-light); font-size: 0.9rem; outline: none; font-family: inherit; }
        .input-group input:focus { border-color: var(--accent-gold); }
        
        /* Payment methods styling */
        .payment-methods { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
        .payment-method { flex: 1; border: 2px solid var(--border-light); border-radius: var(--border-radius-sm); padding: 1rem; text-align: center; cursor: pointer; transition: all 0.3s; }
        .payment-method.selected { border-color: var(--accent-gold); background: #fefbeb; }
        .payment-method i { font-size: 1.5rem; color: var(--accent-amber); margin-bottom: 0.5rem; display: block; }
        
        .order-review-item { display: flex; gap: 1rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border-light); padding-bottom: 1rem; }
        .order-review-item:last-child { border-bottom: none; }
        .order-review-item img { width: 60px; height: 60px; object-fit: cover; border-radius: var(--border-radius-sm); }
        .order-review-details h4 { font-size: 0.95rem; font-weight: 600; color: var(--primary-dark); margin-bottom: 0.2rem; }
        .order-review-details p { font-size: 0.8rem; color: var(--text-muted); }
        .order-review-price { font-weight: 700; color: var(--accent-amber); font-size: 0.95rem; margin-left: auto; }

        .price-breakdown { display: flex; flex-direction: column; gap: 0.8rem; margin: 1.5rem 0; border-top: 1px solid var(--border-light); padding-top: 1.5rem; }
        .price-row { display: flex; justify-content: space-between; font-size: 0.9rem; }
        .price-row.discount { color: #10b981; font-weight: 600; }
        .price-row.total { border-top: 2px solid var(--border-light); padding-top: 1rem; font-size: 1.25rem; font-weight: 700; color: var(--primary-dark); }
        
        .place-order-btn { width: 100%; padding: 1rem; background: linear-gradient(135deg, var(--accent-gold), var(--accent-amber)); border: none; border-radius: 40px; color: var(--primary-dark); font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: var(--transition-bounce); margin-top: 1.5rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .place-order-btn:hover { transform: scale(1.02); filter: brightness(1.05); }

        .success-animation { text-align: center; padding: 2rem; }
        .success-animation i { font-size: 5rem; color: #10b981; animation: scaleUp 0.5s ease-out; }
    </style>
</head>
<body>
    <!-- Header (Minimal secure checkout) -->
    <header class="global-header" style="padding: 0.8rem 5%;">
        <div class="logo" onclick="window.location.href='index.php'" style="cursor: pointer; font-size: 24px; font-weight: 800; background: linear-gradient(135deg, #fbbf24, #f59e0b); -webkit-background-clip: text; background-clip: text; color: transparent;">Eventora</div>
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <span style="color: white; font-size: 0.9rem;"><i class="fas fa-lock" style="color:var(--accent-gold)"></i> Secure Checkout</span>
            <a href="cart.php" style="color: white; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 0.3rem;"><i class="fas fa-arrow-left"></i> Back to Cart</a>
        </div>
    </header>

    <main class="main-content">
        <div class="checkout-container">
            <!-- Left Column - Forms -->
            <div class="checkout-forms">
                <!-- Contact Information -->
                <div class="checkout-section">
                    <div class="section-header">
                        <span class="section-number">1</span>
                        <h3 class="section-title"><i class="fas fa-user"></i> Contact Information</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="input-group">
                            <label>First & Last Name *</label>
                            <input type="text" id="contactName" placeholder="John Doe" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" required>
                        </div>
                        <div class="input-group">
                            <label>Phone Number *</label>
                            <input type="tel" id="phone" placeholder="+94 77 123 4567" required>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label>Email Address *</label>
                        <input type="email" id="email" placeholder="john.doe@example.com" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" required>
                    </div>
                </div>

                <!-- Event Details -->
                <div class="checkout-section">
                    <div class="section-header">
                        <span class="section-number">2</span>
                        <h3 class="section-title"><i class="fas fa-calendar-alt"></i> Event Location & Venue</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="input-group">
                            <label>Event Date *</label>
                            <input type="date" id="eventDate" value="<?php echo $defaultEventDate; ?>" required>
                        </div>
                        <div class="input-group">
                            <label>Event Type</label>
                            <select id="eventType">
                                <option value="wedding">Wedding</option>
                                <option value="birthday">Birthday</option>
                                <option value="corporate">Corporate</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label>Event Venue Address *</label>
                        <input type="text" id="eventAddress" placeholder="Grand Ballroom, Hilton, Colombo" required>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="checkout-section">
                    <div class="section-header">
                        <span class="section-number">3</span>
                        <h3 class="section-title"><i class="fas fa-credit-card"></i> Payment Method</h3>
                    </div>
                    
                    <div class="payment-methods">
                        <div class="payment-method selected" onclick="selectPayment('card', this)">
                            <i class="fas fa-credit-card"></i>
                            <div>Credit / Debit Card</div>
                        </div>
                        <div class="payment-method" onclick="selectPayment('cash', this)">
                            <i class="fas fa-money-bill-wave"></i>
                            <div>Pay at Venue (Cash)</div>
                        </div>
                    </div>
                    
                    <div id="cardPayment">
                        <div class="input-group">
                            <label>Cardholder Name *</label>
                            <input type="text" id="cardName" placeholder="John Doe" value="John Doe">
                        </div>
                        
                        <div class="input-group">
                            <label>Card Number *</label>
                            <input type="text" id="cardNumber" placeholder="4242 4242 4242 4242" value="4242 4242 4242 4242">
                        </div>
                        
                        <div class="form-row">
                            <div class="input-group">
                                <label>Expiry Date *</label>
                                <input type="text" id="expiry" placeholder="MM/YY" value="09/29">
                            </div>
                            <div class="input-group">
                                <label>CVV *</label>
                                <input type="password" id="cvv" placeholder="***" value="123">
                            </div>
                        </div>
                    </div>
                    
                    <div id="cashPayment" style="display: none; padding: 1rem; background: #f1f5f9; border-radius: 12px; color: var(--text-dark); margin-top: 1rem;">
                        <p><strong>Cash Settlement:</strong> Pay a 10% booking advance now via bank transfer, and settle the remaining amount at the venue.</p>
                    </div>
                </div>
            </div>

            <!-- Right Column - Order Summary -->
            <div class="checkout-summary">
                <div class="checkout-section" style="position: sticky; top: 100px;">
                    <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; font-size: 1.15rem; font-weight: 700; color: var(--primary-dark); border-bottom: 2px solid var(--border-light); padding-bottom: 0.8rem;">
                        <i class="fas fa-shopping-bag" style="color: var(--accent-gold);"></i>
                        Order Items (<?php echo count($cartItems); ?>)
                    </h3>
                    
                    <!-- Order Items List -->
                    <div style="max-height: 250px; overflow-y: auto;">
                        <?php foreach ($cartItems as $item): ?>
                            <?php 
                            $details = json_decode($item['special_requests'] ?? '{}', true);
                            $packageName = $details['packageName'] ?? 'Standard';
                            ?>
                            <div class="order-review-item">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?: 'https://images.unsplash.com/photo-1519741497674-611481863552?w=200'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="order-review-details">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p><?php echo $item['event_date'] ? date('M d, Y', strtotime($item['event_date'])) : 'No date'; ?> · <?php echo $item['guests'] ?: '100'; ?> guests</p>
                                </div>
                                <div class="order-review-price">Rs <?php echo number_format($item['price'] * $item['quantity'], 0); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Price Breakdown -->
                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Subtotal</span>
                            <span>Rs <?php echo number_format($subtotal, 0); ?></span>
                        </div>
                        <div class="price-row">
                            <span>Delivery & Setup</span>
                            <span>Rs <?php echo number_format($delivery, 0); ?></span>
                        </div>
                        <div class="price-row">
                            <span>Service Fee</span>
                            <span>Rs <?php echo number_format($serviceFee, 0); ?></span>
                        </div>
                        <?php if ($discount > 0): ?>
                            <div class="price-row discount">
                                <span>Package Discount</span>
                                <span>-Rs <?php echo number_format($discount, 0); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="price-row total">
                            <span>Total</span>
                            <span>Rs <?php echo number_format($total, 0); ?></span>
                        </div>
                    </div>
                    
                    <button class="place-order-btn" id="placeOrderBtn" onclick="placeOrder()">
                        <i class="fas fa-lock"></i> Pay & Confirm Booking
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Template -->
    <?php include 'footer.php'; ?>

    <script>
        function selectPayment(method, element) {
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            
            document.getElementById('cardPayment').style.display = method === 'card' ? 'block' : 'none';
            document.getElementById('cashPayment').style.display = method === 'cash' ? 'block' : 'none';
        }

        async function placeOrder() {
            const btn = document.getElementById('placeOrderBtn');
            const eventDate = document.getElementById('eventDate').value;
            const eventAddress = document.getElementById('eventAddress').value;
            const eventType = document.getElementById('eventType').value;
            
            if (!eventDate || !eventAddress) {
                alert('Please fill in all required fields.');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Booking...';
            
            try {
                const response = await fetch('../backend/create_booking.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        event_date: eventDate,
                        event_address: eventAddress,
                        event_type: eventType
                    })
                });
                const result = await response.json();
                if (result.success) {
                    // Sync localStorage
                    localStorage.removeItem('cart');
                    
                    const checkoutContainer = document.querySelector('.checkout-container');
                    checkoutContainer.innerHTML = `
                        <div style="grid-column: 1/-1; text-align: center; padding: 4rem; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-md);">
                            <div class="success-animation">
                                <i class="fas fa-check-circle" style="font-size: 5rem; color: #10b981;"></i>
                                <h2 style="margin-bottom: 1rem; margin-top: 1.5rem; font-size: 2.2rem; color: var(--primary-dark);">Booking Confirmed!</h2>
                                <p style="color: var(--text-muted); margin-bottom: 2rem; font-size: 1.15rem;">Your booking has been placed successfully. You can manage it from your profile.</p>
                                <div style="background: #f1f5f9; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: inline-block; min-width: 320px;">
                                    <p><strong>Booking Confirmation Reference:</strong></p>
                                    <p style="font-size: 1.2rem; font-weight: 700; color: var(--accent-amber); margin-top: 0.5rem;">#EVT-${result.booking_ids ? result.booking_ids.join(', #EVT-') : Math.floor(100000 + Math.random() * 900000)}</p>
                                </div>
                                <div style="display: flex; gap: 1rem; justify-content: center;">
                                    <a href="index.php" class="btn btn-primary" style="border-radius: 40px; text-decoration: none; padding: 0.8rem 2rem; font-weight: 700;">Return to Home</a>
                                    <a href="profile.php" class="btn btn-outline" style="border-radius: 40px; text-decoration: none; padding: 0.8rem 2rem; font-weight: 600;">View My Bookings</a>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    alert(result.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-lock"></i> Retry';
                }
            } catch (err) {
                alert('Connection error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-lock"></i> Retry';
            }
        }
    </script>
</body>
</html>
