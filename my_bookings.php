<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$message = '';
$error = '';

// Cancel a pending booking belonging only to the logged-in user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $bookingId = (int)$_POST['cancel_booking_id'];

    $checkStmt = $pdo->prepare("
        SELECT booking_id, booking_status
        FROM bookings
        WHERE booking_id = :booking_id AND user_id = :user_id
        LIMIT 1
    ");
    $checkStmt->execute([
        'booking_id' => $bookingId,
        'user_id' => $userId
    ]);
    $booking = $checkStmt->fetch();

    if (!$booking) {
        $error = "Booking not found or access denied.";
    } elseif ($booking['booking_status'] !== 'Pending') {
        $error = "Only Pending bookings can be cancelled.";
    } else {
        $updateStmt = $pdo->prepare("
            UPDATE bookings
            SET booking_status = 'Cancelled'
            WHERE booking_id = :booking_id AND user_id = :user_id
        ");
        $updateStmt->execute([
            'booking_id' => $bookingId,
            'user_id' => $userId
        ]);
        $message = "Booking #" . $bookingId . " has been cancelled.";
    }
}

$stmt = $pdo->prepare("
    SELECT b.*, r.room_name, r.room_type
    FROM bookings b
    INNER JOIN rooms r ON b.room_id = r.room_id
    WHERE b.user_id = :user_id
    ORDER BY b.booking_date DESC
");
$stmt->execute(['user_id' => $userId]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Coco Breeze Mirissa</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-flex">
            <a href="index.php" class="logo">Coco Breeze Mirissa</a>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="rooms.php">Rooms</a>
                <a href="my_bookings.php">My Bookings</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php">Admin</a>
                <?php endif; ?>
                <span class="nav-user">Hi, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="logout.php" class="btn-outline">Logout</a>
            </nav>
        </div>
    </header>

    <main class="section container">
        <div class="section-header">
            <h1>My Bookings</h1>
            <a href="book.php" class="btn-primary">+ New Booking</a>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <div class="card">
                <p>You have not made any room bookings yet.</p>
                <a href="rooms.php" class="btn-primary">Explore Rooms</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Room</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Total (LKR)</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td>#<?= (int)$b['booking_id'] ?></td>
                                <td><?= htmlspecialchars($b['room_name']) ?> (<?= htmlspecialchars($b['room_type']) ?>)</td>
                                <td><?= htmlspecialchars($b['check_in']) ?></td>
                                <td><?= htmlspecialchars($b['check_out']) ?></td>
                                <td><?= number_format((float)$b['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower(htmlspecialchars($b['booking_status'])) ?>">
                                        <?= htmlspecialchars($b['booking_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($b['booking_status'] === 'Pending'): ?>
                                        <form method="POST" action="my_bookings.php" onsubmit="return confirm('Cancel this pending booking?');">
                                            <input type="hidden" name="cancel_booking_id" value="<?= (int)$b['booking_id'] ?>">
                                            <button type="submit" class="btn-danger">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; Coco Breeze Mirissa. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>