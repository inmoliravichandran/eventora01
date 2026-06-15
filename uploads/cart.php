<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../backend/config.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=cart.php");
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

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$delivery = count($cartItems) > 0 ? 2500 : 0;
$serviceFee = count($cartItems) > 0 ? 1500 : 0;
$discount = count($cartItems) >= 2 ? 15000 : 0;
$total = $subtotal + $delivery + $serviceFee - $discount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Eventora</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .main-content { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .cart-container { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 1.5rem; }
        .cart-items-section { background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-md); }
        .cart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border-light); padding-bottom: 0.8rem; }
        .cart-item { display: grid; grid-template-columns: 100px 1fr auto; gap: 1.5rem; padding: 1.5rem 0; border-bottom: 1px solid var(--border-light); align-items: center; }
        .cart-item:last-child { border-bottom: none; }
        .cart-item-image img { width: 100px; height: 100px; object-fit: cover; border-radius: var(--border-radius-sm); }
        .cart-item-details h3 { font-size: 1.15rem; color: var(--primary-dark); margin-bottom: 0.4rem; }
        .cart-item-meta { display: flex; flex-wrap: wrap; gap: 0.8rem; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.8rem; }
        .cart-item-meta span { display: flex; align-items: center; gap: 0.3rem; }
        .quantity-selector { display: flex; align-items: center; gap: 0.5rem; background: #f1f5f9; padding: 0.3rem 0.6rem; border-radius: 30px; width: fit-content; }
        .quantity-btn { border: none; background: none; font-size: 1.1rem; cursor: pointer; color: var(--primary-dark); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; }
        .quantity-value { font-weight: 600; font-size: 0.95rem; min-width: 20px; text-align: center; }
        .btn-remove { border: none; background: none; color: #ef4444; cursor: pointer; font-size: 1.1rem; padding: 0.5rem; transition: transform 0.2s; }
        .btn-remove:hover { transform: scale(1.1); }
        .cart-summary { background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-md); height: fit-content; }
        .summary-header { display: flex; align-items: center; gap: 0.5rem; font-size: 1.25rem; font-weight: 700; border-bottom: 2px solid var(--border-light); padding-bottom: 0.8rem; margin-bottom: 1.5rem; }
        .price-breakdown { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem; }
        .price-row { display: flex; justify-content: space-between; font-size: 0.95rem; color: var(--text-dark); }
        .price-row.discount { color: #10b981; font-weight: 600; }
        .price-row.total { border-top: 2px solid var(--border-light); padding-top: 1rem; font-size: 1.25rem; font-weight: 700; color: var(--primary-dark); }
        .btn-checkout { width: 100%; padding: 0.9rem; background: linear-gradient(135deg, var(--accent-gold), var(--accent-amber)); border: none; border-radius: 40px; color: var(--primary-dark); font-size: 1.05rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: var(--transition-bounce); }
        .btn-checkout:hover { transform: scale(1.02); filter: brightness(1.05); }
        .empty-cart { text-align: center; padding: 4rem 2rem; background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-md); }
        .empty-cart i { font-size: 5rem; color: var(--accent-gold); margin-bottom: 1.5rem; }
        .empty-cart h3 { font-size: 1.6rem; margin-bottom: 0.8rem; }
        .empty-cart p { color: var(--text-muted); margin-bottom: 2rem; }
        .btn-browse { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.8rem 1.8rem; background: linear-gradient(135deg, var(--accent-gold), var(--accent-amber)); color: var(--primary-dark); text-decoration: none; border-radius: 40px; font-weight: 600; transition: var(--transition-bounce); }
        .btn-browse:hover { transform: scale(1.05); }
    </style>
</head>
<body>
    <!-- Header Template -->
    <?php include 'header.php'; ?>

    <main class="main-content">
        <div class="breadcrumb">
            <a href="index.php">Home</a> / <strong>Cart</strong>
        </div>

        <h1 style="font-size: 2rem; color: var(--primary-dark); margin-top: 0.5rem;">Your Shopping Cart</h1>

        <?php if (count($cartItems) > 0): ?>
            <div class="cart-container">
                <!-- Cart Items List -->
                <div class="cart-items-section">
                    <div class="cart-header">
                        <h2><i class="fas fa-shopping-bag"></i> Cart Items</h2>
                        <span><?php echo count($cartItems); ?> <?php echo count($cartItems) === 1 ? 'item' : 'items'; ?></span>
                    </div>
                    
                    <div id="cartItemsList">
                        <?php foreach ($cartItems as $item): ?>
                            <?php 
                            $details = json_decode($item['special_requests'] ?? '{}', true);
                            $packageName = $details['packageName'] ?? 'Standard';
                            $customizations = $details['customizations'] ?? [];
                            ?>
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    <img src="<?php echo htmlspecialchars($item['image_url'] ?: 'https://images.unsplash.com/photo-1519741497674-611481863552?w=200'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="cart-item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <div class="cart-item-meta">
                                        <?php if ($item['event_date']): ?>
                                            <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($item['event_date'])); ?></span>
                                        <?php endif; ?>
                                        <?php if ($item['guests']): ?>
                                            <span><i class="fas fa-users"></i> <?php echo $item['guests']; ?> guests</span>
                                        <?php endif; ?>
                                        <span><i class="fas fa-box"></i> <?php echo htmlspecialchars($packageName); ?></span>
                                        <?php if (count($customizations) > 0): ?>
                                            <span><i class="fas fa-cog"></i> <?php echo htmlspecialchars(implode(', ', $customizations)); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="quantity-selector">
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['service_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">−</button>
                                        <span class="quantity-value"><?php echo $item['quantity']; ?></span>
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['service_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                                    </div>
                                </div>
                                <div class="cart-item-price" style="text-align: right; display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                                    <div style="font-weight: 700; font-size: 1.15rem; color: var(--accent-amber);">Rs <?php echo number_format($item['price'] * $item['quantity'], 0); ?></div>
                                    <button class="btn-remove" onclick="removeFromCart(<?php echo $item['service_id']; ?>)"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Order Summary Panel -->
                <div class="cart-summary">
                    <div class="summary-header">
                        <i class="fas fa-receipt"></i>
                        <h3>Order Summary</h3>
                    </div>
                    
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
                    
                    <button class="btn-checkout" onclick="window.location.href='checkout.php'">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any services to your cart yet.</p>
                <a href="services.php" class="btn-browse">
                    <i class="fas fa-search"></i> Browse Services
                </a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer Template -->
    <?php include 'footer.php'; ?>

    <script>
        async function updateQuantity(serviceId, newQty) {
            const formData = new FormData();
            formData.append('service_id', serviceId);
            formData.append('quantity', newQty);

            try {
                const response = await fetch('../backend/update_cart_quantity.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (err) {
                alert('Connection error');
            }
        }

        async function removeFromCart(serviceId) {
            if (!confirm('Remove this item from cart?')) return;
            const formData = new FormData();
            formData.append('service_id', serviceId);

            try {
                const response = await fetch('../backend/remove_from_cart.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (err) {
                alert('Connection error');
            }
        }
    </script>
</body>
</html>
