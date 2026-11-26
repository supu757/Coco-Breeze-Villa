# Coco Breeze Mirissa – Coastal Villa and Room Booking System

## 1. Project Overview
Coco Breeze Mirissa is a dynamic web-based coastal villa and room booking application developed for **IT1208 – Web Technologies (Continuous Assessment 2 – 2026)**. Designed around a coastal villa in Mirissa, Sri Lanka, the system enables tourists to browse accommodation, check room availability by dates and guest count, submit booking requests, and manage reservations, while allowing an administrator to manage rooms and booking statuses.

## 2. Implemented Features
- **Home Page (`index.php`):** Displays villa introduction, coastal highlights, navigation links, featured rooms, and booking entry points.
- **Rooms & Availability Search (`rooms.php`):** Lists available rooms with type, capacity, description, and nightly rate; supports filtering by check-in/check-out dates, guest count, and room type while excluding overlapping confirmed bookings.
- **Guest Registration & Authentication (`register.php`, `login.php`, `logout.php`):** Supports guest registration, PHP session-based login/logout, role-based access (`guest` and `admin`), and password hashing using `password_hash()` and `password_verify()`.
- **Room Booking & Live Cost Calculation (`book.php`):** Allows logged-in guests to submit booking requests with client-side JavaScript validation/estimation (`assets/js/script.js`) and server-side PHP validation, overlap checks, and total amount calculation.
- **My Bookings (`my_bookings.php`):** Lets guests view their reservation history and statuses (`Pending`, `Confirmed`, `Cancelled`) and cancel `Pending` bookings.
- **Administrator Panel (`admin/dashboard.php`, `admin/manage_rooms.php`, `admin/manage_bookings.php`):** Enables administrators to view booking summary counts, add or update rooms and availability status, and confirm or cancel guest bookings with overlap protection.
- **Responsive Design (`assets/css/style.css`):** Uses CSS grid/flexbox and media queries for desktop, tablet, and mobile viewports.

## 3. Software Requirements
- **Web & Database Server Stack:** XAMPP, WAMP, or an equivalent local environment with **PHP** (7.4+ / 8.x) and **MySQL / MariaDB**
- **Front-End:** HTML5, CSS3, JavaScript
- **Editor & Browser:** Visual Studio Code (recommended) and a modern web browser (Chrome, Edge, Firefox)

## 4. Installation and Configuration Instructions
1. Place the project folder inside your local server document root (e.g., `htdocs/coastal_villa` in XAMPP).
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`), open the **SQL** tab, paste the contents of `db_structure.sql`, and execute it to create `coastal_villa_db`, its tables (`users`, `rooms`, `bookings`), and sample records.
4. Verify database connection settings in `config/db.php` (`localhost`, `coastal_villa_db`, default user `root`, empty password unless configured otherwise).
5. Open `http://localhost/coastal_villa/` in your web browser.

## 5. Sample Login Credentials
> Update or verify password hashes in `db_structure.sql` if you generate custom credentials using `password_hash()`.

- **Administrator Account**
  - **Email:** `admin@coastalvilla.lk`
  - **Password:** `Password@123`
- **Guest Account**
  - **Email:** `nimal@example.com`
  - **Password:** `Password@123`

## 6. AI Assistance Declaration
Generative AI tools were used during development to assist with code structuring, debugging guidance, and documentation drafting; all submitted code and database logic were reviewed and tested locally.
