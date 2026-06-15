<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../backend/config.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=profile.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Process AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $location = trim($_POST['location'] ?? '');
        
        if (empty($name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Name and email are required']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, city = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $location, $userId]);
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'cancel_booking') {
        $bookingId = intval($_POST['booking_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
            $stmt->execute([$bookingId, $userId]);
            echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to cancel booking: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $user = [];
}

// Fetch bookings
try {
    $stmt = $pdo->prepare("SELECT b.*, s.name AS service_name, s.category, s.image_url FROM bookings b JOIN services s ON b.service_id = s.id WHERE b.user_id = ? ORDER BY b.booking_date DESC");
    $stmt->execute([$userId]);
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $bookings = [];
}

// Fetch wishlist
try {
    $stmt = $pdo->prepare("SELECT w.*, s.id AS service_id, s.name, s.price, s.image_url, s.category, s.rating FROM wishlist w JOIN services s ON w.service_id = s.id WHERE w.user_id = ?");
    $stmt->execute([$userId]);
    $wishlist = $stmt->fetchAll();
} catch (PDOException $e) {
    $wishlist = [];
}

$totalSpent = 0;
foreach ($bookings as $b) {
    if ($b['status'] !== 'cancelled') {
        $totalSpent += $b['total_price'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Eventora</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .profile-container { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; }
        
        .welcome-banner {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.92)) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: var(--border-radius);
            padding: 2.2rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-content { display: flex; justify-content: space-between; align-items: center; z-index: 2; position: relative; }
        .welcome-text h1 { font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.5rem; letter-spacing: -0.02em; }
        .role-badge { display: flex; align-items: center; gap: 0.5rem; background: var(--gradient-gold); color: var(--primary-dark); padding: 0.5rem 1.2rem; border-radius: 30px; font-weight: 700; font-size: 0.8rem; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.25); text-transform: uppercase; letter-spacing: 0.5px; }
        
        .dashboard-grid { display: grid; grid-template-columns: 280px 1fr; gap: 2.5rem; }
        
        .profile-sidebar, .stat-card, .bookings-list, .info-card, .modal-content {
            background: var(--glass-bg) !important;
            backdrop-filter: var(--backdrop-blur) !important;
            -webkit-backdrop-filter: var(--backdrop-blur) !important;
            border: 1px solid var(--glass-border) !important;
            border-radius: var(--border-radius) !important;
            box-shadow: var(--shadow-md) !important;
        }

        .profile-sidebar { padding: 2rem 1.5rem; text-align: center; height: fit-content; }
        .avatar-container { position: relative; display: inline-block; margin-bottom: 1.5rem; }
        .profile-avatar { width: 130px; height: 130px; border-radius: 50%; object-fit: cover; border: 4px solid var(--accent-gold); box-shadow: var(--shadow-md); background: #fef3c7; }
        .profile-name { font-size: 1.4rem; color: var(--primary-dark); margin-bottom: 0.25rem; font-weight: 800; letter-spacing: -0.01em; }
        .profile-email { color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem; font-weight: 500; }
        
        .profile-stats { display: flex; justify-content: space-around; margin: 1.5rem 0; padding: 1rem 0; border-top: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light); }
        .stat-item { text-align: center; }
        .stat-number { font-size: 1.4rem; font-weight: 800; color: var(--accent-amber); }
        
        .sidebar-menu { display: flex; flex-direction: column; gap: 0.6rem; margin-top: 1.5rem; }
        .menu-item { display: flex; align-items: center; gap: 1rem; padding: 0.85rem 1.2rem; border-radius: 30px; color: var(--text-dark); text-decoration: none; transition: all 0.3s; cursor: pointer; font-weight: 600; text-align: left; }
        .menu-item i { width: 20px; color: var(--text-muted); transition: color 0.3s; }
        .menu-item:hover { background: rgba(255, 255, 255, 0.4); transform: translateX(6px); }
        .menu-item:hover i { color: var(--accent-gold); }
        
        .menu-item.active { background: var(--gradient-gold) !important; color: var(--primary-dark) !important; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25); }
        .menu-item.active i { color: var(--primary-dark); }
        
        .main-content { min-width: 0; }
        .panel { display: none; animation: fadeIn 0.4s ease-out; }
        .panel.active-panel { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { padding: 1.5rem; display: flex; align-items: center; gap: 1.2rem; }
        .stat-icon { width: 60px; height: 60px; background: #fffbeb; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: var(--accent-amber); box-shadow: var(--shadow-sm); }
        .stat-info h3 { font-size: 1.6rem; color: var(--primary-dark); font-weight: 800; }
        .stat-info p { font-size: 0.85rem; color: var(--text-muted); font-weight: 600; }
        
        .section-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .section-title h2 { color: var(--primary-dark); font-size: 1.4rem; font-weight: 800; letter-spacing: -0.01em; }
        
        .bookings-list { overflow: hidden; }
        .booking-item { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-light); }
        .booking-item:last-child { border-bottom: none; }
        .booking-info h4 { color: var(--primary-dark); margin-bottom: 0.4rem; font-size: 1.15rem; font-weight: 700; }
        .booking-info p { font-size: 0.85rem; color: var(--text-muted); display: flex; gap: 1rem; font-weight: 500; }
        .booking-info p i { color: var(--accent-amber); }
        .booking-status { display: inline-block; padding: 0.3rem 1rem; border-radius: 30px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-top: 0.5rem; }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-pending { background: #ffedd5; color: #9a3412; }
        .status-completed { background: #dbeafe; color: #1e40af; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .booking-price { font-weight: 800; color: var(--accent-amber); font-size: 1.25rem; }
        
        .info-card { padding: 2.5rem; }
        .info-row { display: flex; padding: 1.2rem 0; border-bottom: 1px solid var(--border-light); }
        .info-row:last-of-type { border-bottom: none; }
        .info-label { width: 150px; font-weight: 700; color: var(--primary-dark); }
        .info-value { color: var(--text-muted); flex: 1; font-weight: 500; }
        .edit-btn { background: var(--gradient-gold); color: var(--primary-dark); border: none; padding: 0.8rem 2.2rem; border-radius: 40px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25); transition: all 0.3s; }
        .edit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(245, 158, 11, 0.35); }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 2000; justify-content: center; align-items: center; }
        .modal-content { max-width: 500px; width: 90%; padding: 2.5rem; }
        .modal-content h3 { font-size: 1.5rem; font-weight: 800; margin-bottom: 1rem; border-bottom: 2px solid var(--border-light); padding-bottom: 0.5rem; }
        .modal-content label { display: block; font-size: 0.85rem; font-weight: 700; margin-top: 1rem; color: var(--primary-dark); }
        .modal-content input { width: 100%; padding: 0.85rem 1.2rem; margin: 0.5rem 0; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 30px; font-size: 0.95rem; background: rgba(255, 255, 255, 0.8); outline: none; transition: all 0.3s; }
        .modal-content input:focus { border-color: var(--accent-gold); box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.15); background: white; }
        .modal-buttons { display: flex; gap: 1rem; margin-top: 1.8rem; }
        .modal-buttons button { flex: 1; padding: 0.85rem; border-radius: 40px; border: none; cursor: pointer; font-weight: 700; transition: all 0.3s; }
        .modal-buttons button:hover { transform: translateY(-1px); }
        
        .toast { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: var(--primary-dark); color: white; padding: 0.85rem 2rem; border-radius: 50px; z-index: 2000; border-left: 5px solid var(--accent-gold); box-shadow: var(--shadow-lg); font-weight: 600; }

        .services-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 2rem; padding: 1rem 0; }
        .service-card { overflow: hidden; cursor: pointer; display: flex; flex-direction: column; }
        .service-card:hover { border-color: rgba(251, 191, 36, 0.3) !important; }
        .service-image { height: 170px; overflow: hidden; }
        .service-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
        .service-card:hover .service-image img { transform: scale(1.06); }
        .service-info { padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .service-info h4 { color: var(--primary-dark); font-size: 1.1rem; margin-bottom: 0.5rem; font-weight: 700; }
        .service-price { color: var(--accent-amber); font-weight: 800; font-size: 1.1rem; }

        @media (max-width: 900px) {
            .dashboard-grid { grid-template-columns: 1fr; gap: 2rem; }
            .profile-sidebar { width: 100%; }
        }
    </style>
</head>
<body>
    <!-- Header Template -->
    <?php include 'header.php'; ?>

    <main class="profile-container">
        <div class="welcome-banner">
            <div class="welcome-content">
                <div class="welcome-text">
                    <h1>Welcome back, <span style="color:var(--accent-gold);"><?php echo htmlspecialchars($user['name']); ?></span>!</h1>
                    <p>Your event journey starts here — manage bookings, explore services, and track everything.</p>
                </div>
                <div class="role-badge"><i class="fas fa-crown"></i> <span><?php echo ucfirst($user['role']); ?> Member</span></div>
            </div>
        </div>

        <div class="dashboard-grid">
            <aside class="profile-sidebar">
                <div class="avatar-container">
                    <img src="https://ui-avatars.com/api/?background=fbbf24&color=0f172a&bold=true&size=130&name=<?php echo urlencode($user['name']); ?>" alt="Avatar" class="profile-avatar">
                </div>
                <h3 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h3>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                
                <div class="profile-stats">
                    <div class="stat-item"><div class="stat-number"><?php echo count($bookings); ?></div><div class="stat-label">Bookings</div></div>
                    <div class="stat-item"><div class="stat-number"><?php echo count($wishlist); ?></div><div class="stat-label">Saved</div></div>
                </div>
                
                <div class="sidebar-menu">
                    <div class="menu-item active" data-panel="dashboard"><i class="fas fa-chart-line"></i> Dashboard</div>
                    <div class="menu-item" data-panel="bookings"><i class="fas fa-ticket-alt"></i> My Bookings</div>
                    <div class="menu-item" data-panel="profile"><i class="fas fa-id-card"></i> Profile Settings</div>
                    <div class="menu-item" data-panel="wishlist"><i class="fas fa-heart"></i> Wishlist</div>
                    <a href="#" onclick="logout(); return false;" class="menu-item logout-btn" style="color: #dc2626;"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </aside>

            <div class="main-content">
                <!-- Dashboard Panel -->
                <div class="panel active-panel" id="dashboardPanel">
                    <div class="dashboard-cards">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                            <div class="stat-info"><h3><?php echo count($bookings); ?></h3><p>Total Bookings</p></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                            <div class="stat-info"><h3>Rs <?php echo number_format($totalSpent, 0); ?></h3><p>Total Spent</p></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-heart"></i></div>
                            <div class="stat-info"><h3><?php echo count($wishlist); ?></h3><p>Saved Items</p></div>
                        </div>
                    </div>
                    
                    <div class="section-title">
                        <h2>📋 Recent Bookings</h2>
                        <a onclick="switchPanel('bookings')" style="color: var(--accent-amber); font-weight: 600;">View All →</a>
                    </div>
                    
                    <div class="bookings-list">
                        <?php if (count($bookings) > 0): ?>
                            <?php foreach (array_slice($bookings, 0, 3) as $b): ?>
                                <div class="booking-item">
                                    <div class="booking-info">
                                        <h4><?php echo htmlspecialchars($b['service_name']); ?></h4>
                                        <p>
                                            <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($b['event_date'])); ?></span>
                                            <span><i class="fas fa-users"></i> <?php echo $b['guests']; ?> guests</span>
                                        </p>
                                        <span class="booking-status status-<?php echo $b['status']; ?>"><?php echo $b['status']; ?></span>
                                    </div>
                                    <div class="booking-price">Rs <?php echo number_format($b['total_price'], 0); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-alt"></i>
                                <h3>No bookings yet</h3>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bookings Panel -->
                <div class="panel" id="bookingsPanel">
                    <div class="section-title">
                        <h2>📅 All Bookings</h2>
                    </div>
                    <div class="bookings-list">
                        <?php if (count($bookings) > 0): ?>
                            <?php foreach ($bookings as $b): ?>
                                <div class="booking-item">
                                    <div class="booking-info">
                                        <h4><?php echo htmlspecialchars($b['service_name']); ?></h4>
                                        <p>
                                            <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($b['event_date'])); ?></span>
                                            <span><i class="fas fa-users"></i> <?php echo $b['guests']; ?> guests</span>
                                        </p>
                                        <span class="booking-status status-<?php echo $b['status']; ?>"><?php echo $b['status']; ?></span>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                                        <div class="booking-price">Rs <?php echo number_format($b['total_price'], 0); ?></div>
                                        <?php if ($b['status'] === 'pending' || $b['status'] === 'confirmed'): ?>
                                            <button class="btn-remove" style="font-size: 0.8rem; font-weight: 600; padding: 0.2rem 0.5rem;" onclick="cancelBooking(<?php echo $b['id']; ?>)">Cancel</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-alt"></i>
                                <h3>No bookings yet</h3>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Profile Panel -->
                <div class="panel" id="profilePanel">
                    <div class="info-card">
                        <h3 style="margin-bottom:1.5rem; color: var(--primary-dark); font-weight: 700; font-size: 1.25rem;"><i class="fas fa-user-circle"></i> Personal Information</h3>
                        <div class="info-row"><div class="info-label">Full Name</div><div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div></div>
                        <div class="info-row"><div class="info-label">Email Address</div><div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div></div>
                        <div class="info-row"><div class="info-label">Phone Number</div><div class="info-value"><?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></div></div>
                        <div class="info-row"><div class="info-label">City / Location</div><div class="info-value"><?php echo htmlspecialchars($user['city'] ?: 'Not provided'); ?></div></div>
                        <div class="info-row"><div class="info-label">Member Since</div><div class="info-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div></div>
                        <button class="edit-btn" onclick="openProfileModal()"><i class="fas fa-pen"></i> Edit Profile</button>
                    </div>
                </div>

                <!-- Wishlist Panel -->
                <div class="panel" id="wishlistPanel">
                    <div class="section-title"><h2>❤️ Saved Services</h2></div>
                    <div class="services-grid">
                        <?php if (count($wishlist) > 0): ?>
                            <?php foreach ($wishlist as $item): ?>
                                <div class="service-card" onclick="window.location.href='service-details.php?id=<?php echo $item['service_id']; ?>'">
                                    <div class="service-image"><img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>"></div>
                                    <div class="service-info">
                                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <div class="service-price">Rs <?php echo number_format($item['price'], 0); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state" style="grid-column: 1 / -1;">
                                <i class="fas fa-heart-broken"></i>
                                <h3>No saved services yet</h3>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Profile Edit Modal -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-user-edit"></i> Edit Profile</h3>
            <label>Full Name</label>
            <input type="text" id="editName" value="<?php echo htmlspecialchars($user['name']); ?>">
            <label>Email</label>
            <input type="email" id="editEmail" value="<?php echo htmlspecialchars($user['email']); ?>">
            <label>Phone Number</label>
            <input type="tel" id="editPhone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            <label>Location / City</label>
            <input type="text" id="editLocation" value="<?php echo htmlspecialchars($user['city']); ?>">
            <div class="modal-buttons">
                <button onclick="saveProfile()" style="background:var(--accent-gold); font-weight:bold;">Save Changes</button>
                <button onclick="closeProfileModal()" style="background:#f1f5f9; font-weight: 600;">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Footer Template -->
    <?php include 'footer.php'; ?>

    <script src="../js/script.js"></script>
    <script>
        function showToast(msg, isErr = false) {
            let t = document.querySelector('.toast');
            if(t) t.remove();
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.background = isErr ? '#b91c1c' : '#1e293b';
            toast.textContent = msg;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2800);
        }

        function switchPanel(panel) {
            document.querySelectorAll('.panel').forEach(p => p.classList.remove('active-panel'));
            document.getElementById(`${panel}Panel`).classList.add('active-panel');
            
            document.querySelectorAll('.menu-item').forEach(m => {
                m.classList.remove('active');
                if(m.getAttribute('data-panel') === panel) m.classList.add('active');
            });
        }

        document.querySelectorAll('.menu-item[data-panel]').forEach(item => {
            item.addEventListener('click', () => switchPanel(item.getAttribute('data-panel')));
        });

        function openProfileModal() {
            document.getElementById('profileModal').style.display = 'flex';
        }

        function closeProfileModal() {
            document.getElementById('profileModal').style.display = 'none';
        }

        async function saveProfile() {
            const newName = document.getElementById('editName').value.trim();
            const newEmail = document.getElementById('editEmail').value.trim();
            const newPhone = document.getElementById('editPhone').value.trim();
            const newLocation = document.getElementById('editLocation').value.trim();
            
            if(!newName || !newEmail) { showToast('Name and email required', true); return; }
            
            const formData = new FormData();
            formData.append('action', 'update_profile');
            formData.append('name', newName);
            formData.append('email', newEmail);
            formData.append('phone', newPhone);
            formData.append('location', newLocation);

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    showToast('Profile updated successfully!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(result.message, true);
                }
            } catch (err) {
                showToast('Failed to update profile', true);
            }
        }

        async function cancelBooking(id) {
            if(confirm('Cancel this booking?')) {
                const formData = new FormData();
                formData.append('action', 'cancel_booking');
                formData.append('booking_id', id);
                
                try {
                    const response = await fetch('', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        showToast('Booking cancelled successfully');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(result.message, true);
                    }
                } catch (err) {
                    showToast('Failed to cancel booking', true);
                }
            }
        }
    </script>
</body>
</html>
