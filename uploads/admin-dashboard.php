<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Server-side admin role check
$role = $_SESSION['role'] ?? null;
$adminEmail = $_SESSION['user_email'] ?? '';
$isAdmin = false;

if ($role === 'admin') {
    $isAdmin = true;
} elseif (empty($role) && in_array($adminEmail, ['admin@eventora.com', 'admin@example.com'])) {
    $isAdmin = true;
}

if (!$isAdmin) {
    header("Location: login.php");
    exit;
}

require_once '../backend/config.php';

// Handle AJAX booking status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');
    $bookingId = intval($_POST['booking_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    
    $allowedStatuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'refunded'];
    if ($bookingId > 0 && in_array($newStatus, $allowedStatuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $bookingId]);
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    }
    exit;
}

try {
    $usersCount = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $bookingsCount = $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
    $servicesCount = $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
    $pendingBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
    $totalRevenue = $pdo->query('SELECT IFNULL(SUM(total_price), 0) FROM bookings')->fetchColumn();
    
    // Fetch recent bookings (limit to 6)
    $stmtRecent = $pdo->prepare(
        'SELECT b.id, b.booking_date, b.status, u.name AS user_name, s.name AS service_name, b.total_price FROM bookings b LEFT JOIN users u ON b.user_id = u.id LEFT JOIN services s ON b.service_id = s.id ORDER BY b.booking_date DESC LIMIT 6'
    );
    $stmtRecent->execute();
    $recentBookings = $stmtRecent->fetchAll();

    // Fetch all bookings for the main table
    $stmtAll = $pdo->prepare(
        'SELECT b.id, b.booking_date, b.event_date, b.status, u.name AS user_name, s.name AS service_name, b.total_price FROM bookings b LEFT JOIN users u ON b.user_id = u.id LEFT JOIN services s ON b.service_id = s.id ORDER BY b.booking_date DESC'
    );
    $stmtAll->execute();
    $allBookings = $stmtAll->fetchAll();

} catch (PDOException $e) {
    $usersCount = 0;
    $bookingsCount = 0;
    $servicesCount = 0;
    $pendingBookings = 0;
    $totalRevenue = 0;
    $recentBookings = [];
    $allBookings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Eventora | Event Management Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-dark: #0a0a0f;
            --bg-card: #111115;
            --bg-sidebar: #0c0c12;
            --gold-primary: #d4af37;
            --gold-dark: #b8960c;
            --gold-light: #f5e6a3;
            --text-white: #ffffff;
            --text-gray: #a0a0a8;
            --text-muted: #6b6b76;
            --border-dark: #1a1a22;
            --success: #00c853;
            --warning: #ffab00;
            --danger: #ff3b30;
            --info: #2196f3;
            --shadow-gold: 0 0 20px rgba(212, 175, 55, 0.15);
            --shadow-card: 0 8px 32px rgba(0, 0, 0, 0.4);
            --border-radius: 20px;
            --border-radius-sm: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg-dark);
            color: var(--text-white);
            overflow-x: hidden;
        }

        /* Dashboard Layout */
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar - Gold/Black Theme */
        .sidebar {
            width: 280px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-dark);
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 100;
            transition: var(--transition);
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 1.8rem 1.5rem;
            border-bottom: 1px solid var(--border-dark);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-icon i {
            font-size: 1.4rem;
            color: var(--bg-dark);
        }

        .logo-text {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-light));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.5px;
        }

        .logo-badge {
            font-size: 0.7rem;
            color: var(--gold-primary);
            margin-left: 4px;
        }

        .sidebar-nav {
            padding: 2rem 1.2rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0.9rem 1.2rem;
            margin-bottom: 8px;
            border-radius: var(--border-radius-sm);
            color: var(--text-gray);
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
        }

        .nav-item i {
            width: 24px;
            font-size: 1.2rem;
        }

        .nav-item span {
            font-weight: 500;
            font-size: 0.95rem;
        }

        .nav-item:hover {
            background: rgba(212, 175, 55, 0.1);
            color: var(--gold-primary);
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(184, 134, 11, 0.1));
            color: var(--gold-primary);
            border-left: 3px solid var(--gold-primary);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 1.5rem 2rem;
        }

        /* Top Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title h1 {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-light));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .page-title p {
            color: var(--text-gray);
            font-size: 0.85rem;
            margin-top: 4px;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: var(--bg-card);
            border: 1px solid var(--border-dark);
            border-radius: 40px;
            padding: 0.5rem 1rem;
            gap: 8px;
        }

        .search-bar i {
            color: var(--text-muted);
        }

        .search-bar input {
            background: none;
            border: none;
            color: var(--text-white);
            outline: none;
            width: 200px;
        }

        .profile-btn {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--bg-dark);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            border: 1px solid var(--border-dark);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark));
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-card);
            border-color: rgba(212, 175, 55, 0.3);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-header span {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .stat-header i {
            font-size: 1.8rem;
            color: var(--gold-primary);
            opacity: 0.7;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--text-white);
            margin-bottom: 0.5rem;
        }

        .stat-change {
            font-size: 0.75rem;
            color: var(--text-gray);
        }

        /* Dashboard Grid 2 columns */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Cards */
        .card {
            background: var(--bg-card);
            border-radius: var(--border-radius);
            border: 1px solid var(--border-dark);
            overflow: hidden;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--border-dark);
        }

        .card-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gold-primary);
        }

        .card-header a {
            color: var(--text-muted);
            font-size: 0.8rem;
            text-decoration: none;
            transition: var(--transition);
        }

        .card-header a:hover {
            color: var(--gold-primary);
        }

        /* Event Items */
        .event-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-dark);
            transition: var(--transition);
        }

        .event-item:hover {
            background: rgba(212, 175, 55, 0.05);
        }

        .event-date {
            min-width: 60px;
            text-align: center;
        }

        .event-day {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gold-primary);
        }

        .event-month {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .event-details {
            flex: 1;
        }

        .event-title {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .event-location {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .event-status {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            margin-top: 6px;
        }

        /* Calendar */
        .calendar-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border-dark);
        }

        .calendar-weekday {
            color: var(--gold-primary);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            padding: 1rem;
            gap: 6px;
        }

        .calendar-day {
            text-align: center;
            padding: 8px 4px;
            font-size: 0.85rem;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
        }

        .calendar-day:hover {
            background: rgba(212, 175, 55, 0.2);
        }

        .calendar-day.today {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark));
            color: var(--bg-dark);
            font-weight: 700;
        }

        /* Popular Events Tabs */
        .tabs {
            display: flex;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-dark);
        }

        .tab {
            padding: 0.4rem 1rem;
            border-radius: 30px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.85rem;
        }

        .tab.active {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark));
            color: var(--bg-dark);
            font-weight: 600;
        }

        .popular-event-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-dark);
        }

        .popular-event-img {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bg-dark);
        }

        .popular-event-info {
            flex: 1;
        }

        .popular-event-title {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .popular-event-stats {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .progress-bar {
            width: 100px;
            height: 4px;
            background: var(--border-dark);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--gold-primary), var(--gold-dark));
            border-radius: 4px;
        }

        /* All Events Table */
        .events-table-wrapper {
            overflow-x: auto;
        }

        .events-table {
            width: 100%;
            border-collapse: collapse;
        }

        .events-table th,
        .events-table td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--border-dark);
        }

        .events-table th {
            color: var(--gold-primary);
            font-weight: 600;
            font-size: 0.85rem;
        }

        .events-table td {
            font-size: 0.85rem;
            color: var(--text-gray);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Footer */
        .footer {
            margin-top: 2rem;
            padding: 1.5rem;
            text-align: center;
            border-top: 1px solid var(--border-dark);
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }
            .main-content {
                margin-left: 0;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .top-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-dark);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--gold-primary);
            border-radius: 10px;
        }

        /* Dark Glassmorphism Overrides */
        .sidebar, .stat-card, .card, .search-bar {
            background: rgba(17, 17, 21, 0.7) !important;
            backdrop-filter: blur(16px) saturate(120%) !important;
            -webkit-backdrop-filter: blur(16px) saturate(120%) !important;
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            border-radius: var(--border-radius) !important;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3) !important;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1) !important;
        }

        .stat-card:hover, .card:hover {
            transform: translateY(-4px) !important;
            border-color: rgba(212, 175, 55, 0.3) !important;
            box-shadow: 0 12px 40px rgba(212, 175, 55, 0.08) !important;
        }

        .logo-icon {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark)) !important;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2) !important;
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(184, 134, 11, 0.05)) !important;
            border-left: 3px solid var(--gold-primary) !important;
        }

        .events-table th {
            color: var(--gold-primary) !important;
            font-weight: 700 !important;
            border-bottom: 2px solid rgba(212, 175, 55, 0.2) !important;
        }

        /* Dashboard Action Buttons */
        .action-btn-dashboard {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-right: 4px;
            color: var(--text-white);
            transition: var(--transition);
        }
        .action-btn-dashboard:hover {
            transform: translateY(-2px);
        }
        .approve-btn:hover {
            background: rgba(0, 200, 83, 0.2);
            border-color: var(--success);
            color: var(--success);
        }
        .cancel-btn:hover {
            background: rgba(255, 59, 48, 0.2);
            border-color: var(--danger);
            color: var(--danger);
        }
        .complete-btn:hover {
            background: rgba(33, 150, 243, 0.2);
            border-color: var(--info);
            color: var(--info);
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="logo-text">Eventora<span class="logo-badge">admin</span></div>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="admin-dashboard.php" class="nav-item active">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="services.php" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Services Directory</span>
                </a>
                <a href="index.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Front Page</span>
                </a>
                <a href="../backend/logout.php" class="nav-item" style="margin-top: 4rem; color: var(--danger);">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sign Out</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <div class="top-header">
                <div class="page-title">
                    <h1>Dashboard</h1>
                    <p>Welcome back, Admin! Here's what's happening with your events today.</p>
                </div>
                <div class="header-actions">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search bookings...">
                    </div>
                    <div class="profile-btn" onclick="location.href='profile.php'">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span>Total Bookings</span>
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-value"><?php echo $bookingsCount; ?></div>
                    <div class="stat-change">Total orders received</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span>Total Revenue</span>
                        <i class="fas fa-chart-simple"></i>
                    </div>
                    <div class="stat-value">Rs <?php echo number_format($totalRevenue, 0); ?></div>
                    <div class="stat-change">All bookings combined</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span>Total Users</span>
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $usersCount; ?></div>
                    <div class="stat-change">Registered platform users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <span>Pending Bookings</span>
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-value"><?php echo $pendingBookings; ?></div>
                    <div class="stat-change">Bookings awaiting approval</div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Upcoming Events / Recent Bookings -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-week"></i> Recent Bookings</h3>
                        <a href="#all-bookings">View All Below</a>
                    </div>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($recentBookings as $b): ?>
                            <?php 
                            $bDate = strtotime($b['booking_date'] ?? 'now');
                            $day = date('d', $bDate);
                            $month = strtoupper(date('M', $bDate));
                            ?>
                            <div class="event-item">
                                <div class="event-date">
                                    <div class="event-day"><?php echo $day; ?></div>
                                    <div class="event-month"><?php echo $month; ?></div>
                                </div>
                                <div class="event-details">
                                    <div class="event-title"><?php echo htmlspecialchars($b['service_name'] ?? 'Unnamed Service'); ?></div>
                                    <div class="event-location">Client: <?php echo htmlspecialchars($b['user_name'] ?? 'Guest'); ?> · Rs <?php echo number_format($b['total_price'], 0); ?></div>
                                    <div class="event-status" style="background: <?php echo $b['status'] === 'confirmed' ? 'rgba(0, 200, 83, 0.2)' : ($b['status'] === 'pending' ? 'rgba(255, 171, 0, 0.2)' : 'rgba(255, 59, 48, 0.2)'); ?>; color: <?php echo $b['status'] === 'confirmed' ? '#00c853' : ($b['status'] === 'pending' ? '#ffab00' : '#ff3b30'); ?>;">
                                        <?php echo ucfirst(htmlspecialchars($b['status'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($recentBookings) === 0): ?>
                            <div style="padding: 2rem; text-align: center; color: var(--text-muted);">
                                <i class="fas fa-folder-open fa-2x" style="margin-bottom: 0.5rem; display: block;"></i>
                                No recent bookings.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-alt"></i> June 2026</h3>
                        <a href="#"><i class="fas fa-chevron-right"></i></a>
                    </div>
                    <div class="calendar-header">
                        <div class="calendar-weekday">Su</div>
                        <div class="calendar-weekday">Mo</div>
                        <div class="calendar-weekday">Tu</div>
                        <div class="calendar-weekday">We</div>
                        <div class="calendar-weekday">Th</div>
                        <div class="calendar-weekday">Fr</div>
                        <div class="calendar-weekday">Sa</div>
                    </div>
                    <div class="calendar-days">
                        <div class="calendar-day">1</div><div class="calendar-day">2</div><div class="calendar-day">3</div><div class="calendar-day">4</div><div class="calendar-day">5</div><div class="calendar-day">6</div><div class="calendar-day">7</div>
                        <div class="calendar-day">8</div><div class="calendar-day">9</div><div class="calendar-day">10</div><div class="calendar-day">11</div><div class="calendar-day">12</div><div class="calendar-day">13</div><div class="calendar-day">14</div>
                        <div class="calendar-day today">15</div><div class="calendar-day">16</div><div class="calendar-day">17</div><div class="calendar-day">18</div><div class="calendar-day">19</div><div class="calendar-day">20</div><div class="calendar-day">21</div>
                        <div class="calendar-day">22</div><div class="calendar-day">23</div><div class="calendar-day">24</div><div class="calendar-day">25</div><div class="calendar-day">26</div><div class="calendar-day">27</div><div class="calendar-day">28</div>
                        <div class="calendar-day">29</div><div class="calendar-day">30</div>
                    </div>
                </div>
            </div>

            <!-- Second Row: Business Category Distribution & Spotlight -->
            <div class="dashboard-grid">
                <!-- Platform Services Spotlight -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-fire"></i> Core Categories</h3>
                    </div>
                    <div class="tabs">
                        <div class="tab active">Overview</div>
                    </div>
                    <div class="popular-event-item">
                        <div class="popular-event-img">
                            <i class="fas fa-ring fa-2x"></i>
                        </div>
                        <div class="popular-event-info">
                            <div class="popular-event-title">Weddings & Receptions</div>
                            <div class="popular-event-stats">Most booked category this month</div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 85%"></div>
                        </div>
                    </div>
                    <div class="popular-event-item">
                        <div class="popular-event-img">
                            <i class="fas fa-utensils fa-2x"></i>
                        </div>
                        <div class="popular-event-info">
                            <div class="popular-event-title">Catering Services</div>
                            <div class="popular-event-stats">Consistent high rating and feedback</div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 92%"></div>
                        </div>
                    </div>
                    <div class="popular-event-item">
                        <div class="popular-event-img">
                            <i class="fas fa-camera fa-2x"></i>
                        </div>
                        <div class="popular-event-info">
                            <div class="popular-event-title">Photography & Video</div>
                            <div class="popular-event-stats">High value premium package orders</div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 78%"></div>
                        </div>
                    </div>
                </div>

                <!-- Featured Admin Quick Action -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-star"></i> Featured Vendor Partner</h3>
                        <a href="services.php">View Services</a>
                    </div>
                    <div style="padding: 1.5rem; text-align: center;">
                        <div class="popular-event-img" style="width: 80px; height: 80px; margin: 0 auto 1rem;">
                            <i class="fas fa-gem fa-3x"></i>
                        </div>
                        <h3 style="color: var(--gold-primary); margin-bottom: 0.5rem;">Royal Venues & Gardens</h3>
                        <h2 style="margin-bottom: 0.5rem;">Eventora Spotlight</h2>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1rem;">Premium vetted venue vendor partnership</p>
                        <div class="badge" style="background: rgba(0, 200, 83, 0.2); color: #00c853;">Verified Gold Status</div>
                    </div>
                </div>
            </div>

            <!-- All Events Table -->
            <div class="card" id="all-bookings" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h3><i class="fas fa-table-list"></i> All Bookings Log</h3>
                </div>
                <div class="events-table-wrapper">
                    <table class="events-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Client Name</th>
                                <th>Service Booked</th>
                                <th>Event Date</th>
                                <th>Revenue</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allBookings as $b): ?>
                                <tr>
                                    <td>#EVT-<?php echo $b['id']; ?></td>
                                    <td><?php echo htmlspecialchars($b['user_name'] ?? 'Guest'); ?></td>
                                    <td><?php echo htmlspecialchars($b['service_name'] ?? 'Unnamed Service'); ?></td>
                                    <td><?php echo htmlspecialchars($b['event_date'] ?? 'N/A'); ?></td>
                                    <td>Rs <?php echo number_format($b['total_price'], 0); ?></td>
                                    <td>
                                        <span class="badge" style="background: <?php echo $b['status'] === 'confirmed' ? 'rgba(0, 200, 83, 0.2)' : ($b['status'] === 'pending' ? 'rgba(255, 171, 0, 0.2)' : 'rgba(255, 59, 48, 0.2)'); ?>; color: <?php echo $b['status'] === 'confirmed' ? '#00c853' : ($b['status'] === 'pending' ? '#ffab00' : '#ff3b30'); ?>;">
                                            <?php echo ucfirst(htmlspecialchars($b['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($b['status'] === 'pending'): ?>
                                            <button class="action-btn-dashboard approve-btn" onclick="updateStatus(<?php echo $b['id']; ?>, 'confirmed')" title="Confirm Booking">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="action-btn-dashboard cancel-btn" onclick="updateStatus(<?php echo $b['id']; ?>, 'cancelled')" title="Cancel Booking">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php elseif ($b['status'] === 'confirmed'): ?>
                                            <button class="action-btn-dashboard complete-btn" onclick="updateStatus(<?php echo $b['id']; ?>, 'completed')" title="Mark Completed">
                                                <i class="fas fa-calendar-check"></i>
                                            </button>
                                            <button class="action-btn-dashboard cancel-btn" onclick="updateStatus(<?php echo $b['id']; ?>, 'cancelled')" title="Cancel Booking">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php elseif ($b['status'] === 'completed'): ?>
                                            <span style="color: var(--success); font-size: 0.8rem; font-weight: 600;"><i class="fas fa-check-double"></i> Done</span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 0.8rem;"><i class="fas fa-ban"></i> Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($allBookings) === 0): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No bookings found in database.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>© 2026 Eventora · Event Management Admin Panel · Designed for excellence</p>
            </div>
        </main>
    </div>

    <!-- Simple Interaction Script -->
    <script>
        // Sidebar navigation highlight simulation
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Toast notification
        function showMessage(msg) {
            let toast = document.createElement('div');
            toast.innerText = msg;
            toast.style.position = 'fixed';
            toast.style.bottom = '20px';
            toast.style.left = '50%';
            toast.style.transform = 'translateX(-50%)';
            toast.style.background = 'linear-gradient(135deg, #d4af37, #b8960c)';
            toast.style.color = '#0a0a0f';
            toast.style.padding = '12px 24px';
            toast.style.borderRadius = '40px';
            toast.style.fontWeight = '600';
            toast.style.zIndex = '9999';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2500);
        }

        async function updateStatus(bookingId, newStatus) {
            if (!confirm(`Are you sure you want to change this booking status to ${newStatus}?`)) return;
            
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('booking_id', bookingId);
            formData.append('status', newStatus);
            
            try {
                const response = await fetch('admin-dashboard.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    showMessage(`✅ ${result.message}`);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showMessage(`❌ ${result.message}`);
                }
            } catch (err) {
                showMessage('❌ Connection error. Please try again.');
            }
        }

        document.querySelector('.profile-btn')?.addEventListener('click', () => showMessage('✨ Welcome back, Admin!'));
    </script>
</body>
</html>
