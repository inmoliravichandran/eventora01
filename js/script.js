// ============================================
// EVENTORA - Complete JavaScript File
// ============================================

// ============================================
// 1. NOTIFICATION SYSTEM
// ============================================

function showNotification(message, type = 'success') {
    // Remove existing notification
    const existingToast = document.querySelector('.custom-toast');
    if (existingToast) existingToast.remove();
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'custom-toast';
    
    // Set icon based on type
    let icon = '✓';
    if (type === 'error') icon = '✗';
    if (type === 'info') icon = 'ℹ';
    if (type === 'warning') icon = '⚠';
    
    toast.innerHTML = `
        <div style="
            position: fixed;
            top: 90px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : type === 'info' ? '#3b82f6' : '#f59e0b'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            max-width: 350px;
            animation: slideInRight 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        ">
            <div style="
                width: 24px;
                height: 24px;
                background: rgba(255,255,255,0.2);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                font-weight: bold;
            ">${icon}</div>
            <div style="flex: 1;">${message}</div>
            <div style="cursor: pointer; opacity: 0.7; font-size: 18px;" onclick="this.parentElement.parentElement.remove()">×</div>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast && toast.parentElement) toast.remove();
    }, 3000);
}

// Add animation style if not exists
if (!document.querySelector('#notification-style')) {
    const style = document.createElement('style');
    style.id = 'notification-style';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
}


// ============================================
// 2. AUTHENTICATION FUNCTIONS
// ============================================

// Check authentication status
function isAuthenticated() {
    return localStorage.getItem('isAuthenticated') === 'true';
}

// Get current user
function getCurrentUser() {
    const user = localStorage.getItem('currentUser');
    return user ? JSON.parse(user) : null;
}

// Handle Registration
async function handleRegister(event) {
    event.preventDefault();
    
    const name = document.getElementById('fullName')?.value;
    const email = document.getElementById('email')?.value;
    const phone = document.getElementById('phone')?.value;
    const password = document.getElementById('password')?.value;
    
    if (!name || !email || !password) {
        showNotification('Please fill in all required fields', 'error');
        return false;
    }
    
    if (password.length < 6) {
        showNotification('Password must be at least 6 characters', 'error');
        return false;
    }
    
    if (!email.includes('@') || !email.includes('.')) {
        showNotification('Please enter a valid email address', 'error');
        return false;
    }
    
    // Show loading state
    const btn = event.submitter;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
    btn.disabled = true;
    
    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone', phone || '');
    formData.append('password', password);
    
    try {
        const response = await fetch(backendURL('register.php'), {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showNotification('Registration successful! Please login.', 'success');
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 1500);
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Connection error. Please try again.', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
    
    return false;
}

function backendURL(path) {
    return `backend/${path}`;
}

// Handle Login
async function handleLogin(event) {
    event.preventDefault();
    
    const email = document.getElementById('email')?.value;
    const password = document.getElementById('password')?.value;
    
    if (!email || !password) {
        showNotification('Please fill in all fields', 'error');
        return false;
    }
    
    // Show loading state
    const btn = event.submitter;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
    btn.disabled = true;
    
    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);
    
    try {
        const response = await fetch(backendURL('login.php'), {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            localStorage.setItem('isAuthenticated', 'true');
            localStorage.setItem('currentUser', JSON.stringify(result.user));
            localStorage.setItem('userEmail', email);
            
            showNotification(result.message, 'success');
            
            // Check for redirect (default to services page)
            const redirect = localStorage.getItem('redirectAfterLogin') || 'services.php';
            localStorage.removeItem('redirectAfterLogin');

            setTimeout(() => {
                // If server returned an admin role, send to admin dashboard
                if (result.user && result.user.role === 'admin') {
                    window.location.href = 'admin-dashboard.php';
                } else {
                    window.location.href = redirect;
                }
            }, 1000);
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Connection error. Please try again.', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
    
    return false;
}

// Handle Logout
async function logout() {
    try {
        await fetch(backendURL('logout.php'), { method: 'POST' });
    } catch (error) {
        console.error('Logout error:', error);
    }
    
    localStorage.removeItem('isAuthenticated');
    localStorage.removeItem('currentUser');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('cart');
    localStorage.removeItem('wishlist');
    
    showNotification('Logged out successfully', 'success');
    setTimeout(() => {
        window.location.href = 'index.php';
    }, 1000);
}

function getRedirectUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('redirect') || null;
}

function redirectToLoginWithReturn(path) {
    localStorage.setItem('redirectAfterLogin', path);
    window.location.href = `login.php?redirect=${encodeURIComponent(path)}`;
}

function protectGuestPage() {
    const protectedPages = ['services.php', 'cart.php', 'checkout.php'];
    const currentPage = window.location.pathname.split('/').pop();

    if (!isAuthenticated() && protectedPages.includes(currentPage)) {
        const returnPath = currentPage + window.location.search;
        redirectToLoginWithReturn(returnPath);
    }
}

function enforceProtectedNavLinks() {
    if (isAuthenticated()) {
        return;
    }

    const guardedLinks = [
        { selector: 'a[href="services.php"]', redirect: 'services.php' },
        { selector: 'a[href="cart.php"]', redirect: 'cart.php' },
        { selector: 'a[href="checkout.php"]', redirect: 'checkout.php' }
    ];

    guardedLinks.forEach(linkInfo => {
        document.querySelectorAll(linkInfo.selector).forEach(link => {
            link.href = `login.php?redirect=${encodeURIComponent(linkInfo.redirect)}`;
            link.addEventListener('click', (event) => {
                event.preventDefault();
                redirectToLoginWithReturn(linkInfo.redirect);
            });
        });
    });
}

// Check authentication and update UI
function checkAuth() {
    const isAuth = isAuthenticated();
    const user = getCurrentUser();
    
    // Update navigation
    const navLinks = document.querySelector('.nav-links');
    if (navLinks) {
        const loginLink = navLinks.querySelector('a[href="login.php"]');
        const registerLink = navLinks.querySelector('a[href="register.php"]');
        const cartLink = navLinks.querySelector('a[href="cart.php"]');
        
        if (isAuth && user) {
            if (loginLink) {
                loginLink.innerHTML = `<i class="fas fa-user-circle"></i> ${user.name || user.email?.split('@')[0] || 'Account'}`;
                loginLink.href = '#';
                loginLink.onclick = (e) => {
                    e.preventDefault();
                    showUserMenu();
                };
            }
            if (registerLink) {
                registerLink.innerHTML = `<i class="fas fa-sign-out-alt"></i> Logout`;
                registerLink.href = '#';
                registerLink.onclick = (e) => {
                    e.preventDefault();
                    logout();
                };
            }
        } else {
            if (loginLink) {
                loginLink.innerHTML = `<i class="fas fa-sign-in-alt"></i> Login`;
                loginLink.href = 'login.php';
                loginLink.onclick = null;
            }
            if (registerLink) {
                registerLink.innerHTML = `<i class="fas fa-user-plus"></i> Register`;
                registerLink.href = 'register.php';
                registerLink.onclick = null;
            }
        }
        
        // Update cart link with badge
        if (cartLink) {
            updateCartCount();
        }
    }
}

// Show user menu dropdown
function showUserMenu() {
    const user = getCurrentUser();
    if (!user) return;
    
    // Remove existing menu
    const existingMenu = document.querySelector('.user-menu-dropdown');
    if (existingMenu) existingMenu.remove();
    
    const menu = document.createElement('div');
    menu.className = 'user-menu-dropdown';
    menu.style.cssText = `
        position: absolute;
        top: 70px;
        right: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        min-width: 200px;
        z-index: 1000;
        overflow: hidden;
        animation: slideInDown 0.3s ease;
        border: 1px solid var(--border-light);
    `;
    
    const isAdmin = user.role === 'admin';

    menu.innerHTML = `
        <div style="padding: 1rem; background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
            <div style="font-weight: 600;">${user.name || user.email}</div>
            <div style="font-size: 0.8rem; opacity: 0.9;">${user.email || ''}</div>
        </div>
        <a href="profile.php" style="display: flex; align-items: center; gap: 0.8rem; padding: 0.8rem 1rem; color: var(--text-dark); text-decoration: none; transition: background 0.3s;">
            <i class="fas fa-calendar-check" style="color: var(--accent-gold); width: 20px;"></i> My Bookings
        </a>
        <a href="profile.php" style="display: flex; align-items: center; gap: 0.8rem; padding: 0.8rem 1rem; color: var(--text-dark); text-decoration: none; transition: background 0.3s;">
            <i class="fas fa-user" style="color: var(--accent-gold); width: 20px;"></i> Profile Settings
        </a>
        <a href="profile.php" style="display: flex; align-items: center; gap: 0.8rem; padding: 0.8rem 1rem; color: var(--text-dark); text-decoration: none; transition: background 0.3s;">
            <i class="fas fa-heart" style="color: var(--accent-gold); width: 20px;"></i> Wishlist
        </a>
        ${isAdmin ? `<a href="admin-dashboard.php" style="display: flex; align-items: center; gap: 0.8rem; padding: 0.8rem 1rem; color: var(--text-dark); text-decoration: none; transition: background 0.3s;"><i class="fas fa-tachometer-alt" style="color: var(--accent-gold); width: 20px;"></i> Admin Dashboard</a>` : ''}
        <div style="border-top: 1px solid var(--border-light);"></div>
        <a href="#" onclick="logout(); return false;" style="display: flex; align-items: center; gap: 0.8rem; padding: 0.8rem 1rem; color: #ef4444; text-decoration: none; transition: background 0.3s;">
            <i class="fas fa-sign-out-alt" style="width: 20px;"></i> Logout
        </a>
    `;
    
    // Add hover effects
    const links = menu.querySelectorAll('a');
    links.forEach(link => {
        link.addEventListener('mouseenter', () => {
            link.style.background = '#f1f5f9';
        });
        link.addEventListener('mouseleave', () => {
            link.style.background = 'transparent';
        });
    });
    
    document.body.appendChild(menu);
    
    // Close menu when clicking outside
    setTimeout(() => {
        document.addEventListener('click', function closeMenu(e) {
            if (!menu.contains(e.target) && !e.target.closest('.nav-links a[href="#"]')) {
                menu.remove();
                document.removeEventListener('click', closeMenu);
            }
        });
    }, 100);
}


// ============================================
// 3. CART FUNCTIONS
// ============================================

let cartItems = {};
let wishlist = {};

// Load cart from localStorage
function loadCartFromStorage() {
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
        cartItems = JSON.parse(savedCart);
    }
    
    const savedWishlist = localStorage.getItem('wishlist');
    if (savedWishlist) {
        wishlist = JSON.parse(savedWishlist);
    }
}

// Save cart to localStorage
function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cartItems));
}

// Save wishlist to localStorage
function saveWishlist() {
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
}

// Add item to cart
function addToCart(item) {
    const id = item.id || 'item-' + Date.now();
    
    if (cartItems[id]) {
        cartItems[id].quantity += item.quantity || 1;
    } else {
        cartItems[id] = {
            ...item,
            id: id,
            quantity: item.quantity || 1
        };
    }
    
    saveCart();
    updateCartCount();
    showNotification(`${item.name} added to cart!`, 'success');
    
    return true;
}

// Remove from cart
function removeFromCart(itemId) {
    if (cartItems[itemId]) {
        delete cartItems[itemId];
        saveCart();
        updateCartCount();
        renderCart();
        showNotification('Item removed from cart', 'success');
    }
}

// Update quantity
function updateQuantity(itemId, change) {
    if (cartItems[itemId]) {
        cartItems[itemId].quantity = Math.max(1, cartItems[itemId].quantity + change);
        saveCart();
        renderCart();
        updateCartCount();
    }
}

// Move item to wishlist
function moveToWishlist(itemId) {
    if (cartItems[itemId]) {
        const newId = 'wish-' + Date.now();
        wishlist[newId] = { ...cartItems[itemId] };
        delete cartItems[itemId];
        saveCart();
        saveWishlist();
        renderCart();
        showNotification('Item moved to wishlist', 'success');
    }
}

// Move from wishlist to cart
function moveToCart(itemId) {
    if (wishlist[itemId]) {
        const newId = 'cart-' + Date.now();
        cartItems[newId] = { ...wishlist[itemId], quantity: 1 };
        delete wishlist[itemId];
        saveCart();
        saveWishlist();
        renderCart();
        showNotification('Item moved to cart', 'success');
    }
}

// Update cart count display
function updateCartCount() {
    const cartLink = document.querySelector('a[href="cart.php"]');
    if (!cartLink) return;
    
    const itemCount = Object.values(cartItems).reduce((sum, item) => sum + item.quantity, 0);
    
    let badge = cartLink.querySelector('.cart-badge');
    if (itemCount > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'cart-badge';
            badge.style.cssText = `
                position: absolute;
                top: -8px;
                right: -12px;
                background: var(--accent-gold);
                color: var(--primary-dark);
                border-radius: 50%;
                min-width: 18px;
                height: 18px;
                font-size: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                padding: 0 4px;
            `;
            cartLink.style.position = 'relative';
            cartLink.appendChild(badge);
        }
        badge.textContent = itemCount > 99 ? '99+' : itemCount;
        badge.style.display = 'flex';
    } else if (badge) {
        badge.style.display = 'none';
    }
}

// Render cart page
function renderCart() {
    const container = document.getElementById('cartContainer');
    if (!container) return;
    
    const itemCount = Object.keys(cartItems).length;
    
    if (itemCount === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any services to your cart yet.</p>
                <a href="services.php" class="btn-browse">
                    <i class="fas fa-search"></i> Browse Services
                </a>
            </div>
        `;
        return;
    }
    
    let cartHTML = `
        <div class="cart-container">
            <div class="cart-items-section">
                <div class="cart-header">
                    <h2><i class="fas fa-shopping-bag"></i> Cart Items</h2>
                    <span class="cart-count">${itemCount} ${itemCount === 1 ? 'item' : 'items'}</span>
                </div>
                <div id="cartItemsList">
    `;
    
    let subtotal = 0;
    
    for (let id in cartItems) {
        const item = cartItems[id];
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        cartHTML += `
            <div class="cart-item" data-id="${id}">
                <div class="cart-item-image">
                    <img src="${item.image || 'https://images.unsplash.com/photo-1519741497674-611481863552?w=200'}" alt="${item.name}">
                </div>
                <div class="cart-item-details">
                    <h3>${item.name}</h3>
                    <div class="cart-item-meta">
                        ${item.date ? `<span><i class="fas fa-calendar"></i> ${item.date}</span>` : ''}
                        ${item.guests ? `<span><i class="fas fa-users"></i> ${item.guests} guests</span>` : ''}
                        <span><i class="fas fa-tag"></i> ${item.category || 'Service'}</span>
                    </div>
                    <div class="cart-item-actions">
                        <div class="quantity-selector">
                            <button class="quantity-btn" onclick="updateQuantity('${id}', -1)">−</button>
                            <span class="quantity-value">${item.quantity}</span>
                            <button class="quantity-btn" onclick="updateQuantity('${id}', 1)">+</button>
                        </div>
                        <span class="save-for-later" onclick="moveToWishlist('${id}')">
                            <i class="far fa-heart"></i> Save for later
                        </span>
                    </div>
                </div>
                <div class="cart-item-price">
                    <div class="price">Rs ${itemTotal.toLocaleString()}</div>
                    <div class="original-price">Rs ${item.price.toLocaleString()} each</div>
                    <button class="btn-remove" onclick="removeFromCart('${id}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }
    
    const delivery = 2500;
    const serviceFee = 1500;
    const discount = itemCount >= 2 ? Math.min(15000, subtotal * 0.1) : 0;
    const total = subtotal + delivery + serviceFee - discount;
    
    cartHTML += `
                </div>
            </div>
            
            <div class="cart-summary">
                <div class="summary-header">
                    <i class="fas fa-receipt"></i>
                    <h3>Order Summary</h3>
                </div>
                
                <div class="price-breakdown">
                    <div class="price-row">
                        <span><i class="fas fa-tag"></i> Subtotal</span>
                        <span>Rs ${subtotal.toLocaleString()}</span>
                    </div>
                    <div class="price-row">
                        <span><i class="fas fa-truck"></i> Delivery & Setup</span>
                        <span>Rs ${delivery.toLocaleString()}</span>
                    </div>
                    <div class="price-row">
                        <span><i class="fas fa-shield-alt"></i> Service Fee</span>
                        <span>Rs ${serviceFee.toLocaleString()}</span>
                    </div>
                    ${discount > 0 ? `
                    <div class="price-row discount">
                        <span><i class="fas fa-gift"></i> Package Discount</span>
                        <span>-Rs ${discount.toLocaleString()}</span>
                    </div>
                    ` : ''}
                    <div class="price-row total">
                        <span>Total</span>
                        <span>Rs ${total.toLocaleString()}</span>
                    </div>
                </div>
                
                <div class="coupon-section">
                    <input type="text" id="couponCode" placeholder="Enter coupon code">
                    <button class="btn-coupon" onclick="applyCoupon()">
                        <i class="fas fa-ticket-alt"></i> Apply
                    </button>
                </div>
                
                <button class="btn-checkout" onclick="proceedToCheckout()">
                    <i class="fas fa-lock"></i> Proceed to Checkout
                </button>
                
                <div class="payment-icons">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-amex"></i>
                    <i class="fab fa-cc-discover"></i>
                    <i class="fab fa-paypal"></i>
                </div>
            </div>
        </div>
    `;
    
    // Add wishlist section if not empty
    const wishlistCount = Object.keys(wishlist).length;
    if (wishlistCount > 0) {
        cartHTML += `
            <div class="saved-section">
                <div class="saved-header">
                    <i class="fas fa-heart"></i>
                    <h3>Saved for Later (${wishlistCount})</h3>
                </div>
                <div class="saved-grid">
        `;
        
        for (let id in wishlist) {
            const item = wishlist[id];
            cartHTML += `
                <div class="saved-item">
                    <img src="${item.image || 'https://images.unsplash.com/photo-1519741497674-611481863552?w=200'}" alt="${item.name}">
                    <h4>${item.name}</h4>
                    <div class="saved-price">Rs ${item.price.toLocaleString()}</div>
                    <button class="btn-move" onclick="moveToCart('${id}')">
                        <i class="fas fa-shopping-cart"></i> Move to Cart
                    </button>
                </div>
            `;
        }
        
        cartHTML += `
                </div>
            </div>
        `;
    }
    
    container.innerHTML = cartHTML;
}

// Apply coupon
function applyCoupon() {
    const coupon = document.getElementById('couponCode')?.value;
    
    if (coupon && coupon.toUpperCase() === 'EVENTORA10') {
        showNotification('10% discount applied!', 'success');
        // Re-render with discount
        renderCart();
    } else if (coupon) {
        showNotification('Invalid coupon code', 'error');
    } else {
        showNotification('Please enter a coupon code', 'info');
    }
}

// Proceed to checkout
function proceedToCheckout() {
    if (Object.keys(cartItems).length === 0) {
        showNotification('Your cart is empty!', 'error');
        return;
    }
    
    if (!isAuthenticated()) {
        localStorage.setItem('redirectAfterLogin', 'checkout.php');
        showNotification('Please login to continue', 'info');
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 1500);
    } else {
        window.location.href = 'checkout.php';
    }
}


// ============================================
// 4. CHECKOUT FUNCTIONS
// ============================================

// Place order
function placeOrder() {
    const btn = document.getElementById('placeOrderBtn');
    if (!btn) return;
    
    // Validate required fields
    const firstName = document.getElementById('firstName')?.value;
    const lastName = document.getElementById('lastName')?.value;
    const email = document.getElementById('email')?.value;
    const phone = document.getElementById('phone')?.value;
    
    if (!firstName || !lastName || !email || !phone) {
        showNotification('Please fill in all contact information', 'error');
        return;
    }
    
    if (!email.includes('@') || !email.includes('.')) {
        showNotification('Please enter a valid email address', 'error');
        return;
    }
    
    // Validate card if selected
    const selectedPayment = document.querySelector('.payment-method.selected');
    if (selectedPayment && selectedPayment.querySelector('.fa-credit-card')) {
        const cardName = document.getElementById('cardName')?.value;
        const cardNumber = document.getElementById('cardNumber')?.value;
        const expiry = document.getElementById('expiry')?.value;
        const cvv = document.getElementById('cvv')?.value;
        
        if (!cardName || !cardNumber || !expiry || !cvv) {
            showNotification('Please fill in all card details', 'error');
            return;
        }
        
        const cardNumClean = cardNumber.replace(/\s/g, '');
        if (cardNumClean.length < 15) {
            showNotification('Please enter a valid card number', 'error');
            return;
        }
    }
    
    // Check terms
    const terms = document.getElementById('terms');
    if (terms && !terms.checked) {
        showNotification('Please agree to the Terms of Service', 'error');
        return;
    }
    
    // Show loading
    btn.classList.add('loading');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Payment...';
    btn.disabled = true;
    
    // Simulate payment processing
    setTimeout(() => {
        // Clear cart
        cartItems = {};
        saveCart();
        
        // Show success
        const checkoutContainer = document.querySelector('.checkout-container');
        if (checkoutContainer) {
            checkoutContainer.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 4rem; background: white; border-radius: var(--border-radius);">
                    <div class="success-animation">
                        <i class="fas fa-check-circle" style="font-size: 5rem; color: #10b981; margin-bottom: 1rem;"></i>
                        <h2>Payment Successful!</h2>
                        <p>Your booking has been confirmed. A confirmation email has been sent to ${email}</p>
                        <div style="background: #f1f5f9; padding: 1.5rem; border-radius: 12px; margin: 2rem auto; max-width: 300px;">
                            <p><strong>Order Number:</strong> #EVT-${Math.floor(100000 + Math.random() * 900000)}</p>
                        </div>
                        <a href="index.php" class="btn">Return to Home</a>
                    </div>
                </div>
            `;
        }
        
        showNotification('Order placed successfully!', 'success');
        
        // Update cart count
        updateCartCount();
    }, 2000);
}

// Select payment method
function selectPayment(method, element) {
    document.querySelectorAll('.payment-method').forEach(el => {
        el.classList.remove('selected');
    });
    element.classList.add('selected');
    
    // Show relevant payment form
    const cardPayment = document.getElementById('cardPayment');
    const paypalPayment = document.getElementById('paypalPayment');
    const bankPayment = document.getElementById('bankPayment');
    
    if (cardPayment) cardPayment.style.display = method === 'card' ? 'block' : 'none';
    if (paypalPayment) paypalPayment.style.display = method === 'paypal' ? 'block' : 'none';
    if (bankPayment) bankPayment.style.display = method === 'bank' ? 'block' : 'none';
}

// Update card preview
function updateCardPreview() {
    const cardName = document.getElementById('cardName')?.value || 'JOHN DOE';
    const previewName = document.getElementById('previewName');
    if (previewName) previewName.textContent = cardName.toUpperCase();
    
    const cardNumber = document.getElementById('cardNumber')?.value || '**** **** **** 4242';
    const previewNumber = document.getElementById('previewNumber');
    if (previewNumber) previewNumber.textContent = cardNumber;
    
    const expiry = document.getElementById('expiry')?.value || '05/28';
    const previewExpiry = document.getElementById('previewExpiry');
    if (previewExpiry) previewExpiry.textContent = expiry;
    
    // Detect card type
    const number = (cardNumber || '').replace(/\s/g, '');
    const cardType = document.getElementById('cardType');
    if (cardType) {
        const icon = cardType.querySelector('i');
        if (number.startsWith('4')) {
            icon.className = 'fab fa-cc-visa';
        } else if (number.startsWith('5')) {
            icon.className = 'fab fa-cc-mastercard';
        } else if (number.startsWith('3')) {
            icon.className = 'fab fa-cc-amex';
        } else {
            icon.className = 'fas fa-credit-card';
        }
    }
}

// Format card number
function formatCardNumber(input) {
    let value = input.value.replace(/\s/g, '').replace(/\D/g, '');
    let formatted = '';
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) formatted += ' ';
        formatted += value[i];
    }
    input.value = formatted;
}

// Format expiry date
function formatExpiry(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length >= 2) {
        input.value = value.slice(0, 2) + '/' + value.slice(2, 4);
    } else {
        input.value = value;
    }
}


// ============================================
// 5. SERVICE FILTERING FUNCTIONS
// ============================================

let currentCategory = 'all';
let currentSort = 'popular';

function filterByCategory(category, element) {
    currentCategory = category;
    
    // Update active pill
    document.querySelectorAll('.category-pill').forEach(pill => {
        pill.classList.remove('active');
    });
    if (element) element.classList.add('active');
    
    applyServiceFilters();
}

function sortServices() {
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        currentSort = sortSelect.value;
        applyServiceFilters();
    }
}

function filterByPrice() {
    applyServiceFilters();
}

function filterByRating() {
    applyServiceFilters();
}

function applyServiceFilters() {
    const cards = document.querySelectorAll('.service-card');
    const priceRange = document.getElementById('priceSelect')?.value || 'all';
    const minRating = parseFloat(document.getElementById('ratingSelect')?.value || 0);
    
    // Convert to array
    const cardsArray = Array.from(cards);
    
    // Filter
    const filteredCards = cardsArray.filter(card => {
        const category = card.dataset.category;
        const price = parseInt(card.dataset.price);
        const rating = parseFloat(card.dataset.rating);
        
        if (currentCategory !== 'all' && category !== currentCategory) return false;
        
        if (priceRange !== 'all') {
            const [min, max] = priceRange.split('-').map(v => v === '200000+' ? Infinity : parseInt(v));
            if (price < min || price > max) return false;
        }
        
        if (minRating > 0 && rating < minRating) return false;
        
        return true;
    });
    
    // Sort
    filteredCards.sort((a, b) => {
        const priceA = parseInt(a.dataset.price);
        const priceB = parseInt(b.dataset.price);
        const ratingA = parseFloat(a.dataset.rating);
        const ratingB = parseFloat(b.dataset.rating);
        const popA = parseInt(a.dataset.popularity || 0);
        const popB = parseInt(b.dataset.popularity || 0);
        
        switch(currentSort) {
            case 'price-low': return priceA - priceB;
            case 'price-high': return priceB - priceA;
            case 'rating': return ratingB - ratingA;
            case 'popular': return popB - popA;
            default: return 0;
        }
    });
    
    // Hide/show
    cardsArray.forEach(card => {
        card.style.display = 'none';
    });
    filteredCards.forEach(card => {
        card.style.display = 'block';
    });
    
    // Update active filters display
    updateActiveFiltersDisplay();
}

function updateActiveFiltersDisplay() {
    const container = document.getElementById('activeFilters');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (currentCategory !== 'all') {
        addFilterTag(container, `Category: ${currentCategory}`, () => {
            currentCategory = 'all';
            document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
            const allPill = document.querySelector('.category-pill[onclick*="all"]');
            if (allPill) allPill.classList.add('active');
            applyServiceFilters();
        });
    }
    
    const priceSelect = document.getElementById('priceSelect');
    if (priceSelect && priceSelect.value !== 'all') {
        addFilterTag(container, `Price: ${priceSelect.selectedOptions[0]?.text}`, () => {
            priceSelect.value = 'all';
            applyServiceFilters();
        });
    }
    
    const ratingSelect = document.getElementById('ratingSelect');
    if (ratingSelect && ratingSelect.value > 0) {
        addFilterTag(container, `Rating: ${ratingSelect.value}+ Stars`, () => {
            ratingSelect.value = '0';
            applyServiceFilters();
        });
    }
}

function addFilterTag(container, text, removeCallback) {
    const tag = document.createElement('span');
    tag.className = 'filter-tag';
    tag.innerHTML = `${text} <i class="fas fa-times"></i>`;
    tag.querySelector('i').onclick = () => {
        tag.remove();
        removeCallback();
    };
    container.appendChild(tag);
}

// Toggle wishlist on service card
function toggleWishlist(element, serviceName) {
    element.classList.toggle('active');
    const icon = element.querySelector('i');
    
    if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        showNotification(`${serviceName} added to wishlist`, 'success');
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        showNotification(`${serviceName} removed from wishlist`, 'info');
    }
}


// ============================================
// 6. UTILITY FUNCTIONS
// ============================================

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    const icon = event.currentTarget;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
function checkPasswordStrength() {
    const password = document.getElementById('password')?.value;
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    if (!password || !strengthBar) return;
    
    let strength = 0;
    if (password.length >= 8) strength += 25;
    if (password.match(/[a-z]/)) strength += 25;
    if (password.match(/[A-Z]/)) strength += 25;
    if (password.match(/[0-9]/)) strength += 15;
    if (password.match(/[^a-zA-Z0-9]/)) strength += 10;
    
    strengthBar.style.width = strength + '%';
    
    if (strength < 30) {
        strengthBar.style.backgroundColor = '#ef4444';
        if (strengthText) strengthText.textContent = 'Weak';
    } else if (strength < 60) {
        strengthBar.style.backgroundColor = '#f59e0b';
        if (strengthText) strengthText.textContent = 'Medium';
    } else if (strength < 80) {
        strengthBar.style.backgroundColor = '#10b981';
        if (strengthText) strengthText.textContent = 'Strong';
    } else {
        strengthBar.style.backgroundColor = '#059669';
        if (strengthText) strengthText.textContent = 'Very Strong';
    }
}

// Smooth scroll to element
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Format currency
function formatCurrency(amount) {
    return 'Rs ' + amount.toLocaleString('en-IN');
}

// Get URL parameter
function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

// Animate numbers (for stats)
function animateNumber(element, start, end, duration = 2000) {
    if (!element) return;
    
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= end) {
            element.textContent = Math.floor(end).toLocaleString() + '+';
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current).toLocaleString() + '+';
        }
    }, 16);
}

// Intersection Observer for animations
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    animatedElements.forEach(el => observer.observe(el));
}


// ============================================
// 7. CONTACT FORM FUNCTIONS
// ============================================

// Handle contact form submission
function handleContactSubmit(event) {
    event.preventDefault();
    
    const firstName = document.getElementById('firstName')?.value;
    const lastName = document.getElementById('lastName')?.value;
    const email = document.getElementById('email')?.value;
    const subject = document.getElementById('subject')?.value;
    const message = document.getElementById('message')?.value;
    
    if (!firstName || !lastName || !email || !subject || !message) {
        showNotification('Please fill in all required fields', 'error');
        return false;
    }
    
    if (!email.includes('@') || !email.includes('.')) {
        showNotification('Please enter a valid email address', 'error');
        return false;
    }
    
    const btn = document.getElementById('submitBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    btn.disabled = true;
    
    // Simulate sending
    setTimeout(() => {
        const formContainer = document.querySelector('.contact-form-container');
        if (formContainer) {
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <h4>Message Sent!</h4>
                <p>Thank you for contacting us. We'll respond within 1 hour.</p>
            `;
            formContainer.appendChild(successDiv);
            
            document.getElementById('contactForm')?.reset();
            
            setTimeout(() => successDiv.remove(), 5000);
        }
        
        showNotification('Message sent successfully!', 'success');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
    
    return false;
}

// Live chat function
function startLiveChat() {
    if (!isAuthenticated()) {
        localStorage.setItem('redirectAfterLogin', window.location.href);
        showNotification('Please login to start live chat', 'info');
        setTimeout(() => window.location.href = 'login.html', 1500);
    } else {
        showNotification('Connecting to live chat...', 'success');
    }
}

// Subscribe to newsletter
function subscribeNewsletter() {
    const emailInput = document.querySelector('.newsletter-form input, #newsletterEmail');
    const email = emailInput?.value;
    
    if (!email || !email.includes('@') || !email.includes('.')) {
        showNotification('Please enter a valid email address', 'error');
        return;
    }
    
    showNotification('Thank you for subscribing!', 'success');
    if (emailInput) emailInput.value = '';
}


// ============================================
// 8. IMAGE GALLERY FUNCTIONS
// ============================================

let currentImageIndex = 0;
let galleryImages = [];

function initGallery(images) {
    galleryImages = images;
    currentImageIndex = 0;
    updateMainImage();
}

function updateMainImage() {
    const mainImage = document.getElementById('mainImage');
    if (mainImage && galleryImages[currentImageIndex]) {
        mainImage.src = galleryImages[currentImageIndex];
    }
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach((thumb, i) => {
        if (i === currentImageIndex) {
            thumb.classList.add('active');
        } else {
            thumb.classList.remove('active');
        }
    });
}

function changeImage(index) {
    currentImageIndex = index;
    updateMainImage();
}

function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
    updateMainImage();
}

function prevImage() {
    currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
    updateMainImage();
}

function openLightbox(index) {
    currentImageIndex = index;
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightboxImage');
    
    if (lightbox && lightboxImage) {
        lightboxImage.src = galleryImages[currentImageIndex];
        lightbox.classList.add('active');
    }
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    if (lightbox) lightbox.classList.remove('active');
}

function navigateLightbox(direction) {
    currentImageIndex = (currentImageIndex + direction + galleryImages.length) % galleryImages.length;
    const lightboxImage = document.getElementById('lightboxImage');
    if (lightboxImage) {
        lightboxImage.src = galleryImages[currentImageIndex];
    }
}


// ============================================
// 9. INITIALIZATION
// ============================================

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Load cart from storage
    loadCartFromStorage();
    
    // Check authentication and protect guest access
    checkAuth();
    enforceProtectedNavLinks();
    protectGuestPage();
    
    // Update cart count
    updateCartCount();
    
    // Initialize date inputs with min date
    const today = new Date().toISOString().split('T')[0];
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.value) {
            input.min = today;
        }
    });
    
    // Initialize password strength checker
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', checkPasswordStrength);
    }
    
    // Initialize card preview
    const cardNameInput = document.getElementById('cardName');
    if (cardNameInput) {
        cardNameInput.addEventListener('input', updateCardPreview);
    }
    
    const cardNumberInput = document.getElementById('cardNumber');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', updateCardPreview);
    }
    
    const expiryInput = document.getElementById('expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', updateCardPreview);
    }
    
    // Initialize gallery if exists
    const galleryThumbnails = document.querySelectorAll('.thumbnail img');
    if (galleryThumbnails.length > 0) {
        galleryImages = Array.from(galleryThumbnails).map(img => img.src.replace('w=200', 'w=800'));
        initGallery(galleryImages);
    }
    
    // Initialize scroll animations
    initScrollAnimations();
    
    // Handle redirect after login
    const urlParams = new URLSearchParams(window.location.search);
    const redirect = urlParams.get('redirect');
    if (redirect) {
        localStorage.setItem('redirectAfterLogin', redirect);
    }
    
    // Render cart if on cart page
    if (document.getElementById('cartContainer')) {
        renderCart();
    }
    
    // Set up "same as billing" toggle
    const sameAsBilling = document.getElementById('sameAsBilling');
    const billingAddress = document.getElementById('billingAddress');
    if (sameAsBilling && billingAddress) {
        sameAsBilling.addEventListener('change', (e) => {
            billingAddress.style.display = e.target.checked ? 'none' : 'block';
        });
    }
    
    // Set default demo credentials on login page
    const loginEmail = document.getElementById('email');
    const loginPassword = document.getElementById('password');
    if (loginEmail && loginPassword && window.location.pathname.includes('login.html')) {
        loginEmail.value = 'demo@eventora.com';
        loginPassword.value = 'demo123';
    }
    
    // Close modals on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeLightbox();
            const modal = document.getElementById('quickViewModal');
            if (modal) modal.classList.remove('active');
        }
    });
});

// Export functions for global use
window.showNotification = showNotification;
window.handleRegister = handleRegister;
window.handleLogin = handleLogin;
window.logout = logout;
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.updateQuantity = updateQuantity;
window.moveToWishlist = moveToWishlist;
window.moveToCart = moveToCart;
window.proceedToCheckout = proceedToCheckout;
window.placeOrder = placeOrder;
window.selectPayment = selectPayment;
window.updateCardPreview = updateCardPreview;
window.formatCardNumber = formatCardNumber;
window.formatExpiry = formatExpiry;
window.togglePassword = togglePassword;
window.checkPasswordStrength = checkPasswordStrength;
window.filterByCategory = filterByCategory;
window.sortServices = sortServices;
window.filterByPrice = filterByPrice;
window.filterByRating = filterByRating;
window.toggleWishlist = toggleWishlist;
window.handleContactSubmit = handleContactSubmit;
window.startLiveChat = startLiveChat;
window.subscribeNewsletter = subscribeNewsletter;
window.applyCoupon = applyCoupon;
window.changeImage = changeImage;
window.nextImage = nextImage;
window.prevImage = prevImage;
window.openLightbox = openLightbox;
window.closeLightbox = closeLightbox;
window.navigateLightbox = navigateLightbox;