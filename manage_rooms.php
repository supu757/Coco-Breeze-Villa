<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$error = '';

// Add new room
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_room') {
    $roomName = trim($_POST['room_name'] ?? '');
    $roomType = trim($_POST['room_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $pricePerNight = (float)($_POST['price_per_night'] ?? 0);
    $maxGuests = (int)($_POST['max_guests'] ?? 0);
    $roomStatus = ($_POST['room_status'] ?? 'available') === 'inactive' ? 'inactive' : 'available';

    if ($roomName === '' || $roomType === '' || $description === '' || $pricePerNight <= 0 || $maxGuests < 1) {
        $error = "Please fill all room fields with valid values.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO rooms (room_name, room_type, description, price_per_night, max_guests, room_status)
            VALUES (:room_name, :room_type, :description, :price_per_night, :max_guests, :room_status)
        ");
        $stmt->execute([
            'room_name' => $roomName,
            'room_type' => $roomType,
            'description' => $description,
            'price_per_night' => $pricePerNight,
            'max_guests' => $maxGuests,
            'room_status' => $roomStatus
        ]);
        $message = "New room added successfully.";
    }
}

// Update room price or status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_room') {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $pricePerNight = (float)($_POST['price_per_night'] ?? 0);
    $maxGuests = (int)($_POST['max_guests'] ?? 0);
    $roomStatus = ($_POST['room_status'] ?? 'available') === 'inactive' ? 'inactive' : 'available';

    if ($roomId <= 0 || $pricePerNight <= 0 || $maxGuests < 1) {
        $error = "Invalid room update values.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE rooms
            SET price_per_night = :price_per_night, max_guests = :max_guests, room_status = :room_status
            WHERE room_id = :room_id
        ");
        $stmt->execute([
            'price_per_night' => $pricePerNight,
            'max_guests' => $maxGuests,
            'room_status' => $roomStatus,
            'room_id' => $roomId
        ]);
        $message = "Room #" . $roomId . " updated.";
    }
}

$rooms = $pdo->query("SELECT * FROM rooms ORDER BY room_id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - Serene Wave Villa</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-flex">
            <a href="../index.php" class="logo">Serene Wave Villa (Admin)</a>
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
        <h1>Manage Rooms</h1>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="search-box">
            <h3>Add New Room</h3>
            <form method="POST" action="manage_rooms.php">
                <input type="hidden" name="action" value="add_room">
                <div class="search-grid">
                    <div class="form-group">
                        <label for="room_name">Room Name</label>
                        <input type="text" id="room_name" name="room_name" required>
                    </div>
                    <div class="form-group">
                        <label for="room_type">Room Type</label>
                        <input type="text" id="room_type" name="room_type" placeholder="e.g. Deluxe Sea View" required>
                    </div>
                    <div class="form-group">
                        <label for="price_per_night">Price / Night (LKR)</label>
                        <input type="number" step="0.01" id="price_per_night" name="price_per_night" required>
                    </div>
                    <div class="form-group">
                        <label for="max_guests">Max Guests</label>
                        <input type="number" min="1" id="max_guests" name="max_guests" required>
                    </div>
                    <div class="form-group">
                        <label for="room_status">Status</label>
                        <select id="room_status" name="room_status">
                            <option value="available">available</option>
                            <option value="inactive">inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description & Facilities</label>
                    <textarea id="description" name="description" rows="2" required></textarea>
                </div>
                <button type="submit" class="btn-primary">Add Room</button>
            </form>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Room Name</th>
                        <th>Type</th>
                        <th>Price (LKR)</th>
                        <th>Max Guests</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $r): ?>
                        <tr>
                            <td>#<?= (int)$r['room_id'] ?></td>
                            <td><?= htmlspecialchars($r['room_name']) ?></td>
                            <td><?= htmlspecialchars($r['room_type']) ?></td>
                            <td>
                                <form method="POST" action="manage_rooms.php" class="inline-form">
                                    <input type="hidden" name="action" value="update_room">
                                    <input type="hidden" name="room_id" value="<?= (int)$r['room_id'] ?>">
                                    <input type="number" step="0.01" name="price_per_night" value="<?= htmlspecialchars($r['price_per_night']) ?>" class="small-input">
                            </td>
                            <td>
                                    <input type="number" min="1" name="max_guests" value="<?= (int)$r['max_guests'] ?>" class="tiny-input">
                            </td>
                            <td>
                                    <select name="room_status">
                                        <option value="available" <?= $r['room_status'] === 'available' ? 'selected' : '' ?>>available</option>
                                        <option value="inactive" <?= $r['room_status'] === 'inactive' ? 'selected' : '' ?>>inactive</option>
                                    </select>
                            </td>
                            <td>
                                    <button type="submit" class="btn-primary">Save</button>
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
            <p>&copy; 2026 Serene Wave Villa, Mirissa. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>