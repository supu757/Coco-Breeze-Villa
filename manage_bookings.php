<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['new_status'])) {
    $bookingId = (int)$_POST['booking_id'];
    $newStatus = $_POST['new_status'];

    if (!in_array($newStatus, ['Confirmed', 'Cancelled'], true)) {
        $error = "Invalid booking status.";
    } else {
        $bStmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = :booking_id LIMIT 1");
        $bStmt->execute(['booking_id' => $bookingId]);
        $booking = $bStmt->fetch();

        if (!$booking) {
            $error = "Booking not found.";
        } elseif ($newStatus === 'Confirmed') {
            // Check overlap against other Confirmed bookings for the same room
            $overlapStmt = $pdo->prepare("
                SELECT booking_id
                FROM bookings
                WHERE room_id = :room_id
                  AND booking_id != :booking_id
                  AND booking_status = 'Confirmed'
                  AND NOT (check_out <= :check_in OR check_in >= :check_out)
                LIMIT 1
            ");
            $overlapStmt->execute([
                'room_id' => $booking['room_id'],
                'booking_id' => $bookingId,
                'check_in' => $booking['check_in'],
                'check_out' => $booking['check_out']
            ]);

            if ($overlapStmt->fetch()) {
                $error = "Cannot confirm booking #" . $bookingId . ": another booking is already Confirmed for overlapping dates.";
            } else {
                $upd = $pdo->prepare("UPDATE bookings SET booking_status = 'Confirmed' WHERE booking_id = :booking_id");
                $upd->execute(['booking_id' => $bookingId]);
                $message = "Booking #" . $bookingId . " confirmed.";
            }
        } else {
            $upd = $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = :booking_id");
            $upd->execute(['booking_id' => $bookingId]);
            $message = "Booking #" . $bookingId . " cancelled.";
        }
    }
}

$sql = "
    SELECT b.*, u.name AS guest_name, u.email AS guest_email, u.contact_number, r.room_name
    FROM bookings b
    INNER JOIN users u ON b.user_id = u.user_id
    INNER JOIN rooms r ON b.room_id = r.room_id
    ORDER BY b.booking_date DESC
";
$bookings = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Coco Breeze Mirissa</title>
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
                <a href="../logout.php" class="btn-outline">Logout</a>
            </nav>
        </div>
    </header>

    <main class="section container">
        <h1>Manage Guest Bookings</h1>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Total (LKR)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>#<?= (int)$b['booking_id'] ?></td>
                            <td>
                                <?= htmlspecialchars($b['guest_name']) ?><br>
                                <small><?= htmlspecialchars($b['guest_email']) ?> | <?= htmlspecialchars($b['contact_number']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($b['room_name']) ?></td>
                            <td><?= htmlspecialchars($b['check_in']) ?></td>
                            <td><?= htmlspecialchars($b['check_out']) ?></td>
                            <td><?= number_format((float)$b['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower(htmlspecialchars($b['booking_status'])) ?>">
                                    <?= htmlspecialchars($b['booking_status']) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="manage_bookings.php" class="inline-form">
                                    <input type="hidden" name="booking_id" value="<?= (int)$b['booking_id'] ?>">
                                    <?php if ($b['booking_status'] !== 'Confirmed'): ?>
                                        <button type="submit" name="new_status" value="Confirmed" class="btn-primary">Confirm</button>
                                    <?php endif; ?>
                                    <?php if ($b['booking_status'] !== 'Cancelled'): ?>
                                        <button type="submit" name="new_status" value="Cancelled" class="btn-danger">Cancel</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Coco Breeze Mirissa. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>