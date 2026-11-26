CREATE DATABASE IF NOT EXISTS coastal_villa_db;
USE coastal_villa_db;

-- 1. Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    contact_number VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('guest', 'admin') NOT NULL DEFAULT 'guest',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Rooms Table
CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(100) NOT NULL,
    room_type VARCHAR(60) NOT NULL,
    description TEXT NOT NULL,
    price_per_night DECIMAL(10, 2) NOT NULL,
    max_guests INT NOT NULL,
    room_image VARCHAR(255) DEFAULT 'room1.jpg',
    room_status ENUM('available', 'inactive') NOT NULL DEFAULT 'available'
);

-- 3. Bookings Table
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    booking_status ENUM('Pending', 'Confirmed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE
);

-- Sample Users (Password for both: Password@123)
INSERT INTO users (name, email, contact_number, password, role) VALUES
('Villa Admin', 'admin@coastalvilla.lk', '0771234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Nimal Perera', 'nimal@example.com', '0719876543', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guest');

-- Sample Rooms (Mirissa / Coastal Villa theme)
INSERT INTO rooms (room_name, room_type, description, price_per_night, max_guests, room_image, room_status) VALUES
('Ocean Breeze Suite', 'Deluxe Sea View', 'Private balcony overlooking the Indian Ocean, AC, king bed, free Wi-Fi.', 18500.00, 2, 'ocean_suite.jpg', 'available'),
('Palm Garden Villa Room', 'Garden Twin', 'Surrounded by tropical palms, air-conditioned, two single beds, en-suite bath.', 12500.00, 3, 'garden_room.jpg', 'available'),
('Sunset Family Loft', 'Family Suite', 'Spacious coastal loft ideal for families, sea breeze, sitting area and balcony.', 24000.00, 4, 'family_loft.jpg', 'available');

-- Sample Booking
INSERT INTO bookings (user_id, room_id, check_in, check_out, total_amount, booking_status) VALUES
(2, 1, '2026-12-10', '2026-12-12', 37000.00, 'Confirmed');