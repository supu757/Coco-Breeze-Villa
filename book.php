<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$errors = [];
$success = '';

// Fetch logged-in user details
$userStmt = $pdo->prepare("SELECT name, email, contact_number FROM users WHERE user_id = :user_id LIMIT 1");
$userStmt->execute(['user_id' => $userId]);
$currentUser = $userStmt->fetch();

// Fetch available rooms
$roomsStmt = $pdo->query("SELECT * FROM rooms WHERE room_status = 'available' ORDER BY room_name ASC");
$availableRooms = $roomsStmt->fetchAll();

$selectedRoomId = (int)($_POST['room_id'] ?? $_GET['room_id'] ?? ($availableRooms[0]['room_id'] ?? 0));
$checkIn = trim($_POST['check_in'] ?? $_GET['check_in'] ?? '');
$checkOut = trim($_POST['check_out'] ?? $_GET['check_out'] ?? '');
$guestsCount = (int)($_POST['guests_count'] ?? $_GET['guests'] ?? 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($selectedRoomId <= 0) {
        $errors[] = "Please select a valid room.";
    }

    if ($checkIn === '' || $checkOut === '') {
        $errors[] = "Please provide both check-in and check-out dates.";
    } else {
        $inDate = DateTime::createFromFormat('Y-m-d', $checkIn);
        $outDate = DateTime::createFromFormat('Y-m-d', $checkOut);
        $today = new DateTime('today');

        if (!$inDate || !$outDate || $inDate->format('Y-m-d') !== $checkIn || $outDate->format('Y-m-d') !== $checkOut) {
            $errors[] = "Invalid date format.";
        } elseif ($inDate < $today) {
            $errors[] = "Check-in date cannot be in the past.";
        } elseif ($outDate <= $inDate) {
            $errors[] = "Check-out date must be after check-in date.";
        }
    }

    if ($guestsCount < 1) {
        $errors[] = "Number of guests must be at least 1.";
    }

    // Check selected room details
    $roomStmt = $pdo->prepare("SELECT * FROM rooms WHERE room_id = :room_id AND room_status = 'available' LIMIT 1");
    $roomStmt->execute(['room_id' => $selectedRoomId]);
    $room = $roomStmt->fetch();

    if (!$room) {
        $errors[] = "Selected room is not available.";
    } elseif ($guestsCount > (int)$room['max_guests']) {
        $errors[] = "This room allows a maximum of " . (int)$room['max_guests'] . " guests.";
    }

    // Check overlapping confirmed bookings
    if (empty($errors) && $room) {
        $overlapStmt = $pdo->prepare("
            SELECT booking_id
            FROM bookings
            WHERE room_id = :room_id
              AND booking_status = 'Confirmed'
              AND NOT (check_out <= :check_in OR check_in >= :check_out)
            LIMIT 1
        ");
        $overlapStmt->execute([
            'room_id' => $selectedRoomId,
            'check_in' => $checkIn,
            'check_out' => $checkOut
        ]);

        if ($overlapStmt->fetch()) {
            $errors[] = "This room is already confirmed for the selected dates. Please choose different dates or another room.";
        }
    }

    // Calculate nights and server-side total
    if (empty($errors) && $room) {
        $inDate = new DateTime($checkIn);
        $outDate = new DateTime($checkOut);
        $nights = (int)$inDate->diff($outDate)->days;
        $totalAmount = $nights * (float)$room['price_per_night'];

        $insertStmt = $pdo->prepare("
            INSERT INTO bookings (user_id, room_id, check_in, check_out, total_amount, booking_status)
            VALUES (:user_id, :room_id, :check_in, :check_out, :total_amount, 'Pending')
        ");
        $insertStmt->execute([
            'user_id' => $userId,
            'room_id' => $selectedRoomId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'total_amount' => $totalAmount
        ]);

        $success = "Booking request submitted successfully! Status: Pending (" . $nights . " night(s), Total: LKR " . number_format($totalAmount, 2) . ").";
        $checkIn = '';
        $checkOut = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room - Serene Wave Villa</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-flex">
            <a href="index.php" class="logo">Serene Wave Villa</a>
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
        <div class="form-box">
            <h2>Room Booking Request</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success !== ''): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?> <a href="my_bookings.php">View My Bookings</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="book.php" id="bookingForm" novalidate>
                <div class="form-group">
                    <label>Guest Name</label>
                    <input type="text" value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" value="<?= htmlspecialchars($currentUser['contact_number'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="room_id">Select Room</label>
                    <select id="room_id" name="room_id" required>
                        <?php foreach ($availableRooms as $r): ?>
                            <option
                                value="<?= (int)$r['room_id'] ?>"
                                data-price="<?= htmlspecialchars($r['price_per_night']) ?>"
                                data-max-guests="<?= (int)$r['max_guests'] ?>"
                                <?= ((int)$r['room_id'] === $selectedRoomId) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($r['room_name']) ?> (<?= htmlspecialchars($r['room_type']) ?> - LKR <?= number_format((float)$r['price_per_night'], 2) ?>/night)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="check_in">Check-In Date</label>
                    <input type="date" id="check_in" name="check_in" value="<?= htmlspecialchars($checkIn) ?>" required>
                </div>

                <div class="form-group">
                    <label for="check_out">Check-Out Date</label>
                    <input type="date" id="check_out" name="check_out" value="<?= htmlspecialchars($checkOut) ?>" required>
                </div>

                <div class="form-group">
                    <label for="guests_count">Number of Guests</label>
                    <input type="number" id="guests_count" name="guests_count" min="1" value="<?= (int)$guestsCount ?>" required>
                </div>

                <div id="priceSummary" class="summary-box">
                    Select dates to see nights and estimated total.
                </div>

                <div id="bookingJsMessage" class="js-msg"></div>

                <button type="submit" class="btn-primary full-width">Submit Booking Request</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Serene Wave Villa, Mirissa. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>