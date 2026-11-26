<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$totalRooms = (int)$pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$totalBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pendingBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Pending'")->fetchColumn();
$confirmedBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Confirmed'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Coco Breeze Mirissa</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-flex">
            <a href="../index.php" class="logo">Coco Breeze Mirissa (Admin)</a>
            <nav class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="manage_rooms.php">Manage Rooms</a>
                <a href="manage_bookings.php">Manage Bookings</a>
                <a href="../index.php">View Site</a>
                <span class="nav-user">Admin: <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="../logout.php" class="btn-outline">Logout</a>
            </nav>
        </div>
    </header>

    <main class="section container">
        <h1>Administrator Dashboard</h1>
        <p class="subtitle">Manage coastal villa rooms and guest reservation requests.</p>

        <div class="grid-3">
            <div class="card">
                <h3>Total Rooms</h3>
                <p class="price"><?= $totalRooms ?></p>
                <a href="manage_rooms.php" class="btn-primary">Manage Rooms</a>
            </div>

            <div class="card">
                <h3>Pending Bookings</h3>
                <p class="price"><?= $pendingBookings ?></p>
                <a href="manage_bookings.php" class="btn-primary">Review Bookings</a>
            </div>

            <div class="card">
                <h3>Confirmed / Total Bookings</h3>
                <p class="price"><?= $confirmedBookings ?> / <?= $totalBookings ?></p>
                <a href="manage_bookings.php" class="btn-secondary">All Bookings</a>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Coco Breeze Mirissa. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>