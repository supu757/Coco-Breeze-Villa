<?php
require_once 'config/db.php';

$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';
$guests = (int)($_GET['guests'] ?? 0);
$roomTypeFilter = trim($_GET['room_type'] ?? '');

$searchError = '';
$rooms = [];

// Fetch distinct room types for optional filter
$typeStmt = $pdo->query("SELECT DISTINCT room_type FROM rooms WHERE room_status = 'available' ORDER BY room_type");
$roomTypes = $typeStmt->fetchAll(PDO::FETCH_COLUMN);

$hasDateSearch = ($checkIn !== '' || $checkOut !== '');

if ($hasDateSearch) {
    if ($checkIn === '' || $checkOut === '') {
        $searchError = "Please select both check-in and check-out dates.";
    } elseif ($checkOut <= $checkIn) {
        $searchError = "Check-out date must be after check-in date.";
    }
}

if ($searchError === '') {
    $sql = "SELECT r.* FROM rooms r WHERE r.room_status = 'available'";
    $params = [];

    if ($guests > 0) {
        $sql .= " AND r.max_guests >= :guests";
        $params['guests'] = $guests;
    }

    if ($roomTypeFilter !== '') {
        $sql .= " AND r.room_type = :room_type";
        $params['room_type'] = $roomTypeFilter;
    }

    // Exclude rooms that have a Confirmed booking overlapping with requested dates
    if ($checkIn !== '' && $checkOut !== '') {
        $sql .= " AND r.room_id NOT IN (
            SELECT b.room_id
            FROM bookings b
            WHERE b.booking_status = 'Confirmed'
              AND NOT (b.check_out <= :check_in OR b.check_in >= :check_out)
        )";
        $params['check_in'] = $checkIn;
        $params['check_out'] = $checkOut;
    }

    $sql .= " ORDER BY r.price_per_night ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms & Availability - Coco Breeze Mirissa</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-flex">
            <a href="index.php" class="logo">Coco Breeze Mirissa</a>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="rooms.php">Rooms</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="my_bookings.php">My Bookings</a>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin/dashboard.php">Admin</a>
                    <?php endif; ?>
                    <span class="nav-user">Hi, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <a href="logout.php" class="btn-outline">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn-outline">Login</a>
                    <a href="register.php" class="btn-primary">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="section container">
        <h1>Rooms & Accommodation</h1>
        <p class="subtitle">Search available coastal rooms by dates, guest count, or room type.</p>

        <form method="GET" action="rooms.php" class="search-box" id="availabilityForm">
            <div class="search-grid">
                <div class="form-group">
                    <label for="search_check_in">Check-In Date</label>
                    <input type="date" id="search_check_in" name="check_in" value="<?= htmlspecialchars($checkIn) ?>">
                </div>

                <div class="form-group">
                    <label for="search_check_out">Check-Out Date</label>
                    <input type="date" id="search_check_out" name="check_out" value="<?= htmlspecialchars($checkOut) ?>">
                </div>

                <div class="form-group">
                    <label for="search_guests">Guests</label>
                    <select id="search_guests" name="guests">
                        <option value="">Any</option>
                        <option value="1" <?= $guests === 1 ? 'selected' : '' ?>>1 Guest</option>
                        <option value="2" <?= $guests === 2 ? 'selected' : '' ?>>2 Guests</option>
                        <option value="3" <?= $guests === 3 ? 'selected' : '' ?>>3 Guests</option>
                        <option value="4" <?= $guests === 4 ? 'selected' : '' ?>>4+ Guests</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="room_type">Room Type</label>
                    <select id="room_type" name="room_type">
                        <option value="">All Types</option>
                        <?php foreach ($roomTypes as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= $roomTypeFilter === $type ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="searchJsMessage" class="js-msg"></div>

            <div class="search-actions">
                <button type="submit" class="btn-primary">Check Availability</button>
                <a href="rooms.php" class="btn-secondary">Reset</a>
            </div>
        </form>

        <?php if ($searchError !== ''): ?>
            <div class="alert alert-error"><?= htmlspecialchars($searchError) ?></div>
        <?php endif; ?>

        <?php if ($checkIn !== '' && $checkOut !== '' && $searchError === ''): ?>
            <p class="info-banner">
                Showing available rooms from <strong><?= htmlspecialchars($checkIn) ?></strong> to <strong><?= htmlspecialchars($checkOut) ?></strong>
                <?php if ($guests > 0): ?> for at least <strong><?= $guests ?></strong> guest(s)<?php endif; ?>.
            </p>
        <?php endif; ?>

        <?php if (empty($rooms)): ?>
            <div class="alert alert-error">No matching rooms found for your selected criteria.</div>
        <?php else: ?>
            <div class="grid-3">
                <?php foreach ($rooms as $room): ?>
                    <?php
                    $bookUrl = "book.php?room_id=" . (int)$room['room_id'];
                    if ($checkIn !== '' && $checkOut !== '' && $searchError === '') {
                        $bookUrl .= "&check_in=" . urlencode($checkIn) . "&check_out=" . urlencode($checkOut);
                    }
                    if ($guests > 0) {
                        $bookUrl .= "&guests=" . $guests;
                    }
                    ?>
                    <div class="card">
                        <div>
                            <img src="assets/images/<?= htmlspecialchars($room['room_image'] ?: 'ocean_suite.jpg') ?>"
                                 alt="<?= htmlspecialchars($room['room_name']) ?>"
                                 class="room-img">
                            <h3><?= htmlspecialchars($room['room_name']) ?></h3>
                            <p class="room-type">
                                <?= htmlspecialchars($room['room_type']) ?> • Max <?= (int)$room['max_guests'] ?> Guests
                            </p>
                            <p><?= htmlspecialchars($room['description']) ?></p>
                        </div>
                        <div>
                            <p class="price">LKR <?= number_format((float)$room['price_per_night'], 2) ?> / night</p>
                            <a href="<?= htmlspecialchars($bookUrl) ?>" class="btn-primary">Book Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Coco Breeze Mirissa. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>