<?php
require_once 'config/db.php';

// Fetch available rooms preview
$stmt = $pdo->query("SELECT * FROM rooms WHERE room_status = 'available' LIMIT 3");
$featuredRooms = $stmt->fetchAll();

// Gallery images per room matching files in assets/images/
$roomGalleries = [
    'Ocean Breeze Suite' => [
        'ocean_suite.jpg',
        'ocean_suite_2.jpg',
        'ocean_suite_3.jpg',
    ],
    'Palm Garden Villa Room' => [
        'garden_room.jpeg',
        'garden_room_2.jpg',
        'garden_room_3.jpg',
    ],
    'Sunset Family Loft' => [
        'family_loft.jpg',
        'family_loft_2.jpg',
        'family_loft_3.jpg',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coco Breeze Mirissa - Coastal Villa & Room Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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

    <section class="hero hero-video-section">
        <video id="heroPlaylistVideo" class="hero-bg-video" src="assets/videos/villa_hero.mp4" autoplay muted playsinline poster="assets/images/ocean_suite.jpg"></video>
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1>Escape to Sri Lanka's Golden Coast</h1>
            <p>Relax in comfortable coastal rooms in Mirissa with ocean breezes, tropical gardens, and warm hospitality.</p>
            <a href="rooms.php" class="btn-primary">Book Now</a>
        </div>
    </section>

    <section class="section container">
        <h2>Why Stay With Us</h2>
        <div class="grid-3">
            <div class="card">
                <h3>Prime Coastal Location</h3>
                <p>Steps away from golden beaches, surfing spots, and scenic sunset views.</p>
            </div>
            <div class="card">
                <h3>Modern Room Comfort</h3>
                <p>Air-conditioned rooms, high-speed Wi-Fi, clean en-suite baths, and private balconies.</p>
            </div>
            <div class="card">
                <h3>Easy Online Reservations</h3>
                <p>Check room availability, calculate stay costs instantly, and manage your bookings easily.</p>
            </div>
        </div>
    </section>

    <section class="section container">
        <div class="section-header">
            <h2>Featured Rooms</h2>
            <a href="rooms.php">View All Rooms →</a>
        </div>
        <div class="grid-3">
            <?php foreach ($featuredRooms as $room): ?>
                <?php
                $roomName = $room['room_name'];
                $preset = $roomGalleries[$roomName] ?? [];
                $dbImgs = !empty($room['room_image']) ? explode(',', $room['room_image']) : [];
                $merged = array_merge($preset, $dbImgs);
                $roomImages = array_values(array_unique(array_filter(array_map('trim', $merged))));
                if (empty($roomImages)) {
                    $roomImages = ['ocean_suite.jpg'];
                }
                ?>
                <div class="card">
                    <div class="room-gallery" data-index="0">
                        <div class="room-gallery-main">
                            <img src="assets/images/<?= htmlspecialchars($roomImages[0]) ?>"
                                 alt="<?= htmlspecialchars($room['room_name']) ?>"
                                 class="room-img room-gallery-active"
                                 onerror="this.src='assets/images/ocean_suite.jpg'">
                            <?php if (count($roomImages) > 1): ?>
                                <button type="button" class="room-nav prev" aria-label="Previous image">&#10094;</button>
                                <button type="button" class="room-nav next" aria-label="Next image">&#10095;</button>
                            <?php endif; ?>
                        </div>

                        <?php if (count($roomImages) > 1): ?>
                            <div class="room-thumbs">
                                <?php foreach ($roomImages as $idx => $imgFile): ?>
                                    <img src="assets/images/<?= htmlspecialchars($imgFile) ?>"
                                         alt="<?= htmlspecialchars($room['room_name']) ?> image <?= $idx + 1 ?>"
                                         class="room-thumb <?= $idx === 0 ? 'active' : '' ?>"
                                         data-src="assets/images/<?= htmlspecialchars($imgFile) ?>"
                                         onerror="this.style.display='none'">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h3><?= htmlspecialchars($room['room_name']) ?></h3>
                    <p class="room-type"><?= htmlspecialchars($room['room_type']) ?> • Max <?= (int)$room['max_guests'] ?> Guests</p>
                    <p><?= htmlspecialchars($room['description']) ?></p>
                    <p class="price">LKR <?= number_format((float)$room['price_per_night'], 2) ?> / night</p>
                    <a href="book.php?room_id=<?= (int)$room['room_id'] ?>" class="btn-primary">Book Now</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section container">
        <div class="section-header">
            <h2>Coastal Villa Tour — Mirissa</h2>
        </div>
        <div class="video-card">
            <div class="video-wrapper">
                <iframe
                    src="https://www.youtube.com/embed/AOm-m5uZhTE?mute=1&rel=0"
                    title="Coco Breeze Mirissa — Villa Tour"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>
            <p class="video-caption">
                Experience beachfront serenity, open tropical spaces, and coastal views inspired by Mirissa.
            </p>
        </div>
    </section>

    <section class="section container">
        <div class="section-header">
            <h2>Location — Mirissa, Sri Lanka</h2>
        </div>
        <div class="map-card">
            <div class="map-wrapper">
                <iframe
                    src="https://www.google.com/maps?q=Mirissa,+Sri+Lanka&output=embed"
                    title="Coco Breeze Mirissa Location Map"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen>
                </iframe>
            </div>
            <div class="map-info">
                <p><strong>Address:</strong> Mirissa Beach Road, Mirissa, Southern Province, Sri Lanka</p>
                <a href="https://www.google.com/maps/search/?api=1&query=Mirissa,+Sri+Lanka"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn-outline">
                    Open in Google Maps →
                </a>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Coco Breeze Mirissa, All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/script.js?v=5"></script>
</body>
</html>