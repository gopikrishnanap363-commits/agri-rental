# Agricultural Equipment Rental Management Portal 🌾🚜

A comprehensive, production-grade web application built to connect Indian farmers with agricultural equipment owners for affordable, transparent machinery rental with real-time availability checks, simulated digital payments, and verifiable tax invoice receipts.

---

## 📋 Table of Contents
1. [Project Overview](#-project-overview)
2. [Key System Features](#-key-system-features)
3. [Technology Stack](#-technology-stack)
4. [Folder Structure](#-folder-structure)
5. [Database Architecture](#-database-architecture)
6. [XAMPP Setup & Installation Guide](#-xampp-setup--installation-guide)
7. [Default Test Credentials](#-default-test-credentials)
8. [OTP Authentication Workflow](#-otp-authentication-workflow)
9. [Double-Booking Prevention System](#-double-booking-prevention-system)
10. [Payment Sandbox & Receipt System](#-payment-sandbox--receipt-system)
11. [User Journey Workflows](#-user-journey-workflows)
12. [Security Architecture](#-security-architecture)
13. [Deployment Instructions](#-deployment-instructions)

---

## 🌾 Project Overview

In traditional agriculture, smallholder farmers often lack the capital to purchase heavy machinery like tractors, combine harvesters, laser levellers, and rotavators. Simultaneously, equipment owners frequently have machinery sitting idle between their own cultivation cycles.

The **Agricultural Equipment Rental Management Portal** bridges this gap:
* **Farmers** can search, filter, and compare nearby farm machinery, check real-time availability, select rental dates, calculate dynamic charges (rental + refundable deposit + GST), pay digitally, and receive an instant printable invoice receipt with verification QR code.
* **Equipment Owners** can register their machinery fleet, upload photos with live preview, set hourly/daily prices, set security deposits, manage in-field availability, and review, accept, or decline booking requests.
* **Administrators** can moderate machinery listings, manage users, audit transactions, track system bookings, and view visual analytics with Chart.js.

---

## ✨ Key System Features

### 1. Mobile Number + OTP Login System
* **Screen 1 — Welcome Hero:** Agriculture-themed hero section, verified machinery statistics, category carousel, and quick search.
* **Screen 2 — Mobile Verification:** Country code (+91), Indian mobile number validation (`^[6-9]\d{9}$`), rate-limiting (max 5 requests per 15 min), and test credential presets.
* **Screen 3 — 6-Box OTP Verification:** Auto-focus auto-advance digit boxes, paste support, 2-minute countdown timer, Resend OTP, and **Configurable Test OTP Mode** with auto-fill button for grading and demo.

### 2. Farmer Module
* **Farmer Dashboard:** Available equipment count, active rentals, completed rentals, and total money spent.
* **Search & Filters:** Search by keyword, brand, category, city/district, price range, and sort by rating, price, or year.
* **Equipment Details:** Multi-image gallery, detailed technical specifications (HP, Year, Condition), owner contact details, and calendar of booked dates.
* **Smart Booking Workflow:** Real-time calculation of rental days $\times$ daily price + refundable deposit + 5% GST.
* **Availability Enforcement:** Strict server-side SQL date-overlap checks preventing double booking.
* **Demo Payment Gateway:** Multi-tab checkout with simulated UPI QR Code / VPA, Credit/Debit Cards, Net Banking (SBI, HDFC, ICICI), and Pay on Delivery.
* **Official Receipt:** Itemized tax invoice with QR code verification, GST breakdown, authorized stamp, and Print/Download PDF buttons.
* **Booking History & Cancellation:** Filter bookings by status (Confirmed, Active, Completed, Cancelled) and cancel pending reservations with reason tracking.

### 3. Equipment Owner Module
* **Owner Dashboard:** Live metrics for total machinery fleet, active rentals, pending farmer requests, and total net earnings (₹).
* **Add Equipment:** Multi-field form with secure image upload (MIME validation, size restriction, random naming, and live preview).
* **Fleet Management:** Fleet list with 1-click status dropdowns (*Available*, *Under Maintenance*, *Unavailable*), editing, and deletion.
* **Booking Requests Queue:** Review farmer details, requested dates, drop location, earnings, and **Approve**, **Reject**, or **Mark Return Completed**.

### 4. Admin Management & Analytics
* **Command Center:** Platform KPI cards, Chart.js Monthly Rental Revenue trend, and Machinery Category Distribution doughnut chart.
* **User Management:** Filter by Farmers/Owners/Admins, search, and 1-click Activate/Deactivate access toggle.
* **Equipment Moderation:** Moderation queue to approve or reject newly listed farm machinery before it appears in farmer search.
* **Booking Oversight:** Real-time monitor of all reservations with status badges and receipts.
* **Payment Audit Trail:** Complete transaction log with Txn IDs, payment methods, amounts, and references.
* **Business Reports & CSV Export:** Category utilization rates, leaderboard of most rented machinery, and 1-click CSV report export.

---

## 💻 Technology Stack

* **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3.2, FontAwesome 6 Icons, Chart.js 4.4, Google Fonts (Plus Jakarta Sans).
* **Backend:** PHP 8+ (Procedural + Functional, Beginner-friendly, PDO prepared statements).
* **Database:** MySQL 8.0 / MariaDB with full foreign keys, composite indexes, and timestamps.
* **Local Server Support:** XAMPP, Apache, MySQL, phpMyAdmin.

---

## 📁 Folder Structure

```
project/
├── index.php                 # Screen 1: Welcome & Landing Page
├── login.php                 # Screen 2: Mobile Number Verification
├── otp.php                   # Screen 3: 6-Box OTP Verification Screen
├── setup-role.php            # New user role selection & profile completion
├── logout.php                # Session destruction & sign out
├── config/
│   └── database.php          # Database PDO connection & dynamic URL detector
├── includes/
│   ├── header.php            # Global navigation bar & alert messages
│   ├── footer.php            # Global footer & CDN scripts
│   ├── auth.php              # RBAC middleware & session controllers
│   └── functions.php         # Core helpers, CSRF, price calc, upload, availability
├── farmer/
│   ├── dashboard.php         # Farmer KPI statistics & recent bookings
│   ├── equipment.php         # Equipment marketplace with multi-filters & sorting
│   ├── equipment-details.php # Technical specifications, owner card & gallery
│   ├── booking.php           # Date selection & price breakdown calculation
│   ├── payment.php           # Sandbox payment gateway (UPI, Card, NetBanking, COD)
│   ├── receipt.php           # Tax invoice receipt with QR code & print styling
│   └── bookings.php          # Booking history, status tracking & cancellation
├── owner/
│   ├── dashboard.php         # Owner earnings, fleet statistics & requests
│   ├── add-equipment.php     # Machinery registration form with photo upload
│   ├── edit-equipment.php    # Update machine specs, rates & photos
│   ├── manage-equipment.php  # Fleet table with availability dropdowns & delete
│   └── booking-requests.php  # Approve/Reject requests & return handover
├── admin/
│   ├── dashboard.php         # Analytics dashboard with Chart.js graphs
│   ├── users.php             # Manage farmers & owners (activate/deactivate)
│   ├── equipment.php         # Moderation queue (approve/reject machinery)
│   ├── bookings.php          # Platform-wide booking monitor
│   ├── payments.php          # Transaction logs & payment audits
│   └── reports.php           # Performance analytics & CSV export
├── api/
│   ├── otp.php               # REST endpoint for send, verify & resend OTP
│   ├── equipment.php         # REST endpoint for live search & date conflict check
│   ├── booking.php           # REST endpoint for dynamic cost breakdown
│   └── payment.php           # REST endpoint for payment execution
├── assets/
│   ├── css/
│   │   └── style.css         # Figma-inspired design system & agricultural theme
│   └── js/
│       └── main.js           # OTP auto-advance, price calculator & payment tabs
├── uploads/
│   └── equipment/            # Secure directory for uploaded machine images
│       └── .gitkeep
├── database/
│   └── agriculture_rental.sql# Normalized schema with 10 tables & seed data
├── .gitignore
└── README.md
```

---

## 🗄️ Database Architecture

The system utilizes a fully normalized relational database named `agriculture_rental`:

1. **`users`**: User records with role (`farmer`, `owner`, `admin`), hashed password, contact, and address.
2. **`otp_verifications`**: OTP audit logs, expiry timestamp, attempts counter, and verification state.
3. **`equipment_categories`**: 10 primary categories (Tractor, Harvester, Rotavator, Seed Drill, Cultivator, Water Pump, Plough, Thresher, Sprayer, Other).
4. **`equipment`**: Machinery fleet details, rates per day and per hour, security deposit, availability status, approval status, and ratings.
5. **`equipment_images`**: Multi-image storage linked to equipment records with primary photo flags.
6. **`bookings`**: Booking numbers, rental date ranges, daily rates, calculated rental subtotal, security deposits, GST tax, and statuses.
7. **`payments`**: Transaction IDs, payment methods (UPI, CARD, NETBANKING, CASH), amounts, and payment dates.
8. **`receipts`**: Unique receipt numbers, JSON payload, and timestamps.
9. **`notifications`**: Role-specific in-app notifications with read/unread statuses.
10. **`reviews`**: 1 to 5 star ratings and written reviews from farmers.

---

## 🚀 XAMPP Setup & Installation Guide

Follow these simple steps to run the application on your computer:

### Step 1: Copy Project to XAMPP
Copy or move this project folder into your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\project
```
*(Or rename it to `C:\xampp\htdocs\agriculture-rental`)*

### Step 2: Start Apache and MySQL
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

### Step 3: Import the Database
1. Open your web browser and go to:
   ```
   http://localhost/phpmyadmin
   ```
2. Click on the **Databases** tab, type `agriculture_rental` in the database name field, and click **Create**.
3. Select the newly created `agriculture_rental` database from the left sidebar.
4. Click the **Import** tab at the top.
5. Click **Choose File** and select:
   ```
   C:\xampp\htdocs\project\database\agriculture_rental.sql
   ```
6. Scroll down and click **Go** (or **Import**). All tables and seed data will be created instantly.

### Step 4: Open the Application
In your browser, visit:
```
http://localhost/project/index.php
```
*(The system automatically detects your URL whether hosted in `/project` or `/agriculture-rental`)*

---

## 🔑 Default Test Credentials

All test accounts are pre-seeded in the database. You can log in using either the **Mobile Number + OTP** method or the **Password** option:

| Role | Name | Mobile Number | Password | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **Admin** | Ramesh Kumar | `9876543210` | `admin123` | Full admin privileges & moderation |
| **Equipment Owner** | Murugan Palanisamy | `9845612345` | `admin123` | Located in Tiruchirappalli (Tractors & Cultivators) |
| **Equipment Owner** | Balwinder Singh | `9812345678` | `admin123` | Located in Punjab (Combine Harvesters & Seed Drills) |
| **Farmer** | Suresh Patel | `9823456789` | `admin123` | Active renter account |
| **Farmer** | Ananya Sharma | `9834567890` | `admin123` | Active renter account |

> **Tip:** On the `login.php` screen, click any of the **Quick Test Credentials** pill buttons to instantly populate the form!

---

## 📱 OTP Authentication Workflow

1. Enter any 10-digit Indian mobile number (e.g. `9823456789`) on `login.php`.
2. The system checks recent requests to enforce rate-limiting.
3. A 6-digit OTP code is generated and stored with a 10-minute expiry time.
4. On `otp.php`, in **Test OTP Mode**, the generated code (e.g. `123456`) is clearly displayed in a developer banner with a 1-click **Auto-Fill** button.
5. Entering the code advances user to their dashboard (or to `setup-role.php` if registering for the first time).

---

## 🛡️ Double-Booking Prevention System

To guarantee equipment is never double-booked, the backend uses server-side validation in `includes/functions.php`:

```php
function isEquipmentAvailable($pdo, $equipmentId, $startDate, $endDate, $excludeBookingId = null) {
    if ($startDate > $endDate) return false;

    $sql = "SELECT COUNT(*) as count FROM `bookings` 
            WHERE `equipment_id` = :equipment_id 
            AND `booking_status` IN ('approved', 'confirmed', 'active') 
            AND (`start_date` <= :end_date AND `end_date` >= :start_date)";
    // ...
}
```

* **Client-Side:** The booking form displays booked dates and restricts the date picker.
* **Server-Side:** Strict verification runs inside `farmer/booking.php` before database insertion. Conflicting dates are rejected with a clear notification.

---

## 💳 Payment Sandbox & Receipt System

* **Sandbox Payment:** Provides realistic checkout screens for UPI (dynamic QR code + VPA), Credit/Debit Card, Net Banking, and Pay on Delivery without requiring real banking credentials.
* **Receipt Generation:** Automatically assigns unique IDs (e.g., `RCP-2026-XXXX`), saves JSON audit payloads, and formats a printable GST tax invoice with a verification QR code and digital stamp.

---

## 🔄 User Journey Workflows

### Farmer Journey
```
Welcome (index.php) ➡️ Mobile Login (login.php) ➡️ Verify OTP (otp.php)
    ➡️ Farmer Dashboard ➡️ Browse Equipment ➡️ View Equipment Details
    ➡️ Check Available Dates ➡️ Select Rental Dates (booking.php)
    ➡️ Dynamic Price Breakdown ➡️ Sandbox Payment (payment.php)
    ➡️ Payment Confirmed ➡️ Official Tax Invoice (receipt.php) ➡️ Download / Print
```

### Equipment Owner Journey
```
Owner Login ➡️ Owner Dashboard ➡️ Add Equipment (add-equipment.php)
    ➡️ Upload Photo with Live Preview ➡️ Set Hourly & Daily Rates
    ➡️ Manage Fleet ➡️ Receive Farmer Booking Request
    ➡️ Approve / Reject Request ➡️ Equipment Handover ➡️ Mark Return Completed
```

### Administrator Journey
```
Admin Login ➡️ Command Center Dashboard ➡️ Revenue & Category Charts
    ➡️ Moderate New Equipment Listings (Approve/Reject)
    ➡️ Manage Farmers & Owners (Activate/Deactivate)
    ➡️ Monitor Platform Bookings ➡️ Audit Payment Transactions
    ➡️ Generate Business Intelligence Reports & Export to CSV
```

---

## 🔒 Security Architecture

1. **SQL Injection Protection:** 100% of database interactions utilize PDO prepared statements with parameter binding.
2. **Cross-Site Scripting (XSS):** All dynamic browser outputs pass through `htmlspecialchars()` via the `e()` helper function.
3. **Cross-Site Request Forgery (CSRF):** Synchronizer token pattern implemented across all POST forms with `generateCsrfToken()` and `verifyCsrfToken()`.
4. **Role-Based Access Control (RBAC):** Middleware in `includes/auth.php` enforces strict role boundaries (`farmer`, `owner`, `admin`).
5. **Secure File Uploads:** Uploaded images are checked for valid MIME types (`image/jpeg`, `image/png`, `image/webp`), size-restricted to 5MB, renamed with random cryptographic hashes, and stored outside executable code paths.
6. **Password Security:** All passwords stored using PHP's `password_hash()` with `PASSWORD_DEFAULT` (Bcrypt).

---

## 🌐 Deployment Instructions

### Local XAMPP
Place files in `C:\xampp\htdocs\project` and visit `http://localhost/project/`.

### Live Shared Hosting / cPanel
1. Upload the project files to your domain's `public_html/` folder.
2. Create a MySQL database and user in cPanel.
3. Import `database/agriculture_rental.sql` via phpMyAdmin.
4. Update `config/database.php` with your live DB credentials (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`).
5. Toggle `define('TEST_OTP_MODE', false);` to switch to production SMS gateway mode.

---

## 📄 License & Academic Attribution
Developed as an academic final-year project for the **Agricultural Equipment Rental Management Portal**. All rights reserved.
