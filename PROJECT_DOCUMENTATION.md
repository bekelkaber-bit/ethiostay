# EthioStay - Hotel Booking System
## Project Documentation

---

## Group Information

**Group Name:** Group 1

**Group Members:**
1. **Bekele Abera Sewunet**
2. **Efirem Masho**

**Project Title:** EthioStay - Ethiopian Hotel Booking System

**Submission Date:** August 2026

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Project Objectives](#project-objectives)
3. [Features](#features)
4. [Technology Stack](#technology-stack)
5. [System Architecture](#system-architecture)
6. [Database Design](#database-design)
7. [Installation Guide](#installation-guide)
8. [User Guide](#user-guide)
9. [Admin Guide](#admin-guide)
10. [Multi-Language Support](#multi-language-support)
11. [Payment System](#payment-system)
12. [Security Features](#security-features)
13. [Testing](#testing)
14. [Future Enhancements](#future-enhancements)
15. [Conclusion](#conclusion)

---

## Project Overview

EthioStay is a comprehensive hotel booking web application designed specifically for the Ethiopian market. The platform enables users to search, browse, and book hotels across various Ethiopian cities and regions. The system features multi-language support (English, Amharic, Oromic), Ethiopian Birr currency integration, and a robust payment verification system.

### Problem Statement
Ethiopia's tourism industry has been growing steadily, but there was a lack of a centralized, user-friendly platform for hotel bookings that caters to local needs, including multi-language support and local payment methods.

### Solution
EthioStay provides a modern, responsive web application that:
- Lists hotels across all Ethiopian regions
- Supports three major Ethiopian languages
- Integrates local payment methods (CBE, Telebirr)
- Provides an admin panel for hotel and booking management
- Ensures secure user authentication with Google OAuth support

---

## Project Objectives

### Primary Objectives
1. Develop a user-friendly hotel booking platform for Ethiopian hotels
2. Implement multi-language support (English, Amharic, Oromic)
3. Integrate Ethiopian payment systems (CBE Birr, Telebirr)
4. Create an admin panel for comprehensive management
5. Ensure responsive design for mobile and desktop users

### Secondary Objectives
1. Implement secure authentication with Google OAuth
2. Provide real-time booking availability
3. Enable payment verification by administrators
4. Support Ethiopian Birr currency throughout the system
5. Create an Ethiopian-themed visual design

---

## Features

### Customer Features
- **User Registration & Login**
  - Email/password registration with confirmation
  - Google OAuth sign-in/sign-up
  - Session-based authentication
  - Password hashing for security

- **Hotel Search & Browsing**
  - Search by city/location
  - Filter by availability
  - View hotel details (rating, amenities, location)
  - Room type selection
  - Price display in Ethiopian Birr

- **Booking System**
  - Room availability checking
  - Date selection (check-in/check-out)
  - Additional services selection
  - Real-time price calculation
  - Booking confirmation

- **Payment Processing**
  - Multiple payment methods (CBE Birr, Telebirr, Bank Transfer)
  - Transaction reference input
  - Payment status tracking
  - Receipt generation

- **Multi-Language Support**
  - English (EN)
  - Amharic (አማ)
  - Oromic (Afaan)
  - Language switcher on all pages
  - Complete translation of interface elements

### Admin Features
- **Dashboard**
  - Overview statistics (total payments, revenue, pending items)
  - Quick access to all admin functions
  - Ethiopian-themed visual design

- **Hotel Management**
  - Add new hotels
  - Edit existing hotel details
  - Delete hotels (with cascade delete)
  - View hotel statistics (room count)

- **Booking Management**
  - View all bookings
  - Customer details display
  - Booking status management (confirm/cancel)
  - Date and pricing information

- **Payment Verification**
  - View all payments
  - Transaction reference verification
  - Payment status updates (verify/reject)
  - Revenue tracking

- **User Management**
  - View registered users
  - Role-based access control
  - Admin and customer roles

---

## Technology Stack

### Frontend
- **HTML5** - Markup structure
- **CSS3** - Styling with Ethiopian theme colors
- **JavaScript** - Client-side interactivity
- **Responsive Design** - Mobile-first approach

### Backend
- **PHP 8.x** - Server-side logic
- **MySQL** - Database management
- **MySQLi** - Database connectivity with prepared statements

### Third-Party Integrations
- **Google OAuth 2.0** - Social authentication
- **Google Sign-In API** - User registration/login

### Development Environment
- **XAMPP** - Local server environment
- **Apache** - Web server
- **MySQL** - Database server
- **phpMyAdmin** - Database management tool

---

## System Architecture

### Directory Structure
```
ethiostay/
├── admin/
│   ├── dashboard.php          # Admin dashboard
│   ├── edit-hotel.php         # Hotel editing
│   ├── view-bookings.php      # Booking management
│   ├── verify-payments.php    # Payment verification
│   └── test-admin.php         # Admin testing tool
├── assets/
│   └── css/
│       └── style.css           # Main stylesheet
├── includes/
│   ├── config.php             # Configuration & database connection
│   ├── header.php             # Common header
│   ├── footer.php             # Common footer
│   └── languages.php          # Multi-language system
├── booking.php                # Booking page
├── database.sql               # Database schema & sample data
├── google-callback.php        # Google OAuth handler
├── hotel-detail.php          # Hotel details page
├── index.php                 # Homepage
├── login.php                 # Login page
├── logout.php                # Logout handler
├── payment.php               # Payment processing
├── register.php              # Registration page
└── README.md                 # Setup instructions
```

### Data Flow
1. **User Registration/Login** → Session creation → Role assignment
2. **Hotel Search** → Database query → Results display
3. **Booking Process** → Availability check → Price calculation → Booking creation
4. **Payment** → Transaction reference input → Payment record creation → Status update
5. **Admin Verification** → Payment review → Status confirmation → Booking finalization

---

## Database Design

### Database Schema

#### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255),
    phone VARCHAR(20),
    google_id VARCHAR(255),
    profile_picture VARCHAR(255),
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Hotels Table
```sql
CREATE TABLE hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    address VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    star_rating INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### Rooms Table
```sql
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    room_type VARCHAR(100) NOT NULL,
    price_per_night DECIMAL(10,2) NOT NULL,
    max_occupancy INT DEFAULT 2,
    room_size INT,
    bed_type VARCHAR(100),
    amenities TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
);
```

#### Services Table
```sql
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
);
```

#### Bookings Table
```sql
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hotel_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    num_guests INT DEFAULT 1,
    grand_total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);
```

#### Booking_Services Table
```sql
CREATE TABLE booking_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    service_id INT NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);
```

#### Payments Table
```sql
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('telebirr', 'cbe_birr', 'bank_transfer') NOT NULL,
    payment_status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Sample Data
The database includes sample data for:
- 1 Admin user (admin@ethiostay.com / Admin@123)
- 1 Customer user (customer@test.com / Customer@123)
- 14 Hotels across Ethiopian regions
- Multiple room types per hotel
- Additional services (breakfast, WiFi, airport pickup, etc.)

---

## Installation Guide

### Prerequisites
- XAMPP (or similar PHP/MySQL environment)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser (Chrome, Firefox, Safari, Edge)

### Step-by-Step Installation

#### 1. Download/Clone Project
Copy the `ethiostay` folder to:
```
C:\xampp\htdocs\ethiostay\
```

#### 2. Start XAMPP
- Open XAMPP Control Panel
- Start Apache server
- Start MySQL service

#### 3. Create Database
- Open phpMyAdmin: http://localhost/phpmyadmin
- Click "New" to create a new database
- Name it: `ethiostay_db`
- Click "Create"

#### 4. Import Database Schema
- Select the `ethiostay_db` database
- Click "Import" tab
- Browse to: `C:\xampp\htdocs\ethiostay\database.sql`
- Click "Go" at the bottom
- Wait for import to complete

#### 5. Configure Application
Open `includes/config.php` and verify:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ethiostay_db');
```

#### 6. Access Application
Open browser and navigate to:
```
http://localhost/ethiostay/
```

#### 7. Test Admin Access
- Go to: http://localhost/ethiostay/login.php
- Email: `admin@ethiostay.com`
- Password: `Admin@123`
- Should redirect to admin dashboard

#### 8. Test Customer Access
- Go to: http://localhost/ethiostay/login.php
- Email: `customer@test.com`
- Password: `Customer@123`
- Should redirect to homepage

### Google OAuth Setup (Optional)
To enable Google sign-in:
1. Go to Google Cloud Console: https://console.cloud.google.com/
2. Create a new project
3. Enable Google Sign-In API
4. Create OAuth 2.0 credentials
5. Update `includes/config.php`:
```php
define('GOOGLE_CLIENT_ID', 'your_client_id');
define('GOOGLE_CLIENT_SECRET', 'your_client_secret');
define('GOOGLE_REDIRECT_URI', 'http://localhost/ethiostay/google-callback.php');
```

---

## User Guide

### Customer Registration

#### Method 1: Email Registration
1. Navigate to: http://localhost/ethiostay/register.php
2. Fill in the form:
   - Full Name
   - Email Address
   - Phone Number (optional)
   - Password (minimum 6 characters)
   - Confirm Password
3. Click "Register"
4. Login with your credentials

#### Method 2: Google Sign-Up
1. Click "Sign up with Google" on registration page
2. Authorize with your Google account
3. Account created automatically

### Searching Hotels
1. On homepage, enter city name in search box
2. Click "Search Hotels"
3. Browse available hotels
4. View hotel details by clicking "View Rooms"

### Booking a Room
1. Select a hotel and view available rooms
2. Click "Book Now" on desired room
3. Select check-in and check-out dates
4. Choose number of guests
5. Select additional services (optional)
6. Click "Proceed to Payment"

### Making Payment
1. Choose payment method:
   - Telebirr (0910103423)
   - CBE Birr (1000477768828)
   - Bank Transfer
2. Make payment using selected method
3. Enter your transaction reference number
4. Click "Submit Payment"
5. Wait for admin verification
6. Receive booking confirmation

### Language Switching
- Click language buttons: EN | አማ | Afaan
- Interface updates immediately
- Language preference saved in session

---

## Admin Guide

### Accessing Admin Panel
1. Login as admin: admin@ethiostay.com / Admin@123
2. Automatically redirected to: admin/dashboard.php

### Dashboard Overview
The dashboard displays:
- Total payments received
- Verified payments count
- Pending verification count
- Total revenue in Ethiopian Birr
- Quick link to verify pending payments

### Adding a New Hotel
1. Go to Dashboard
2. Fill in "Add New Hotel" form:
   - Hotel Name
   - City
   - Address
   - Phone
   - Email
   - Star Rating (3-5 stars)
   - Description
3. Click "Add Hotel"
4. Hotel appears in "Your Hotels" section

### Editing a Hotel
1. Go to Dashboard
2. Find hotel in "Your Hotels" section
3. Click "Edit" button
4. Modify hotel details
5. Click "Update Hotel"
6. Or delete hotel from "Danger Zone"

### Viewing Bookings
1. Click "View Bookings" in navigation
2. See all bookings with:
   - Customer details
   - Hotel and room information
   - Booking dates
   - Total amount
   - Current status
3. For pending bookings:
   - Click "✓ Confirm" to approve
   - Click "✗ Cancel" to reject

### Verifying Payments
1. Click "Verify Payments" in navigation
2. Review payment details:
   - Customer information
   - Transaction reference
   - Payment method
   - Amount
3. Verify transaction reference with bank records
4. Click "✓ Verify" to approve payment
5. Or click "✗ Reject" to decline

### Admin Testing
Use the test tool to diagnose issues:
```
http://localhost/ethiostay/admin/test-admin.php
```

---

## Multi-Language Support

### Implementation
The multi-language system is implemented in `includes/languages.php` with:

#### Translation Structure
```php
$translations = [
    'en' => [/* English translations */],
    'am' => [/* Amharic translations */],
    'om' => [/* Oromic translations */]
];
```

#### Language Switching
- URL parameter: `?lang=en|am|om`
- Session-based language persistence
- Automatic translation function: `t('key')`

#### Supported Languages
1. **English (EN)**
   - Primary language
   - Full interface translation

2. **Amharic (አማ)**
   - Ethiopian national language
   - Ge'ez script support
   - RTL compatibility

3. **Oromic (Afaan)**
   - Widely spoken in Ethiopia
   - Latin script support
   - Cultural relevance

### Translation Keys
All interface elements use translation keys:
- Navigation: `site_title`, `home`, `login`, `register`
- Forms: `email`, `password`, `full_name`, `phone`
- Buttons: `login_button`, `register_button`, `submit_payment`
- Messages: `payment_successful`, `booking_confirmed`
- Hotel: `hotel`, `room`, `price`, `location`

---

## Payment System

### Payment Methods

#### 1. Telebirr
- **Account:** 0910103423
- **Process:**
  1. Select Telebirr as payment method
  2. Open Telebirr app
  3. Send payment to 0910103423
  4. Copy transaction reference
  5. Enter reference in payment form
  6. Submit for verification

#### 2. CBE Birr
- **Account:** 1000477768828
- **Process:**
  1. Select CBE Birr as payment method
  2. Use CBE mobile banking or ATM
  3. Transfer to account 1000477768828
  4. Note transaction reference
  5. Enter reference in payment form
  6. Submit for verification

#### 3. Bank Transfer
- **Process:**
  1. Select Bank Transfer
  2. Transfer to CBE account
  3. Enter transaction reference
  4. Submit for verification

### Payment Flow
1. User completes booking
2. Redirected to payment page
3. Selects payment method
4. Makes payment via chosen method
5. Enters transaction reference
6. Payment record created (status: pending)
7. Admin verifies transaction
8. Payment status updated (success/failed)
9. Booking status updated accordingly
10. User receives confirmation

### Admin Verification
- Admin reviews transaction references
- Cross-checks with bank records
- Approves or rejects payments
- Automatic booking status updates

---

## Security Features

### Authentication Security
- **Password Hashing:** Uses PHP's `password_hash()` with BCRYPT algorithm
- **Session Management:** Secure session handling with timeout
- **Role-Based Access:** Admin and customer role separation
- **SQL Injection Prevention:** Prepared statements for all database queries

### Data Protection
- **Input Validation:** All user inputs sanitized
- **XSS Prevention:** Output escaping with `htmlspecialchars()`
- **CSRF Protection:** Form validation and session checks
- **Secure Headers:** Proper HTTP security headers

### Payment Security
- **Transaction Verification:** Admin verification required
- **Reference Tracking:** Unique transaction IDs
- **Status Management:** Clear payment status tracking
- **Audit Trail:** All payment actions logged

### Google OAuth Security
- **State Parameter:** CSRF protection for OAuth flow
- **Token Validation:** Proper token verification
- **Secure Redirect URI:** Configured redirect URL
- **Scope Limitation:** Minimal requested permissions

---

## Testing

### Manual Testing Checklist

#### User Registration & Login
- [ ] Register new user with email/password
- [ ] Register with Google OAuth
- [ ] Login with correct credentials
- [ ] Login with incorrect credentials (should fail)
- [ ] Password confirmation validation
- [ ] Email format validation

#### Hotel Search & Booking
- [ ] Search by city
- [ ] View hotel details
- [ ] Check room availability
- [ ] Select dates
- [ ] Calculate total price
- [ ] Add services
- [ ] Complete booking

#### Payment Processing
- [ ] Select payment method
- [ ] Enter transaction reference
- [ ] Submit payment
- [ ] View payment success page
- [ ] Check booking status

#### Admin Functions
- [ ] Access admin dashboard
- [ ] Add new hotel
- [ ] Edit existing hotel
- [ ] Delete hotel
- [ ] View all bookings
- [ ] Confirm booking
- [ ] Cancel booking
- [ ] Verify payment
- [ ] Reject payment

#### Multi-Language
- [ ] Switch to English
- [ ] Switch to Amharic
- [ ] Switch to Oromic
- [ ] Verify translations display correctly
- [ ] Test language persistence

### Browser Compatibility
- [ ] Google Chrome
- [ ] Mozilla Firefox
- [ ] Microsoft Edge
- [ ] Safari
- [ ] Mobile browsers

### Responsive Design
- [ ] Desktop (1920x1080)
- [ ] Laptop (1366x768)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

---

## Future Enhancements

### Planned Features
1. **Room Management**
   - Add/edit/delete rooms via admin panel
   - Upload room images
   - Room availability calendar

2. **Advanced Search**
   - Price range filter
   - Star rating filter
   - Amenity filter
   - Date-based availability

3. **User Reviews**
   - Hotel rating system
   - Customer reviews
   - Photo uploads

4. **Email Notifications**
   - Booking confirmation emails
   - Payment status updates
   - Reminder emails

5. **Real-time Payment Integration**
   - Direct Telebirr API integration
   - CBE payment gateway
   - Instant payment confirmation

6. **Mobile App**
   - Android application
   - iOS application
   - Push notifications

7. **Analytics Dashboard**
   - Booking statistics
   - Revenue charts
   - Customer analytics
   - Popular destinations

8. **Loyalty Program**
   - Points system
   - Discount coupons
   - Membership tiers

---

## Conclusion

EthioStay is a comprehensive hotel booking system designed specifically for the Ethiopian market. The project successfully addresses the need for a localized, user-friendly platform that supports Ethiopian languages, payment methods, and cultural context.

### Key Achievements
- ✅ Multi-language support (English, Amharic, Oromic)
- ✅ Ethiopian payment integration (CBE, Telebirr)
- ✅ Admin panel with full management capabilities
- ✅ Ethiopian Birr currency throughout
- ✅ Ethiopian-themed visual design
- ✅ Secure authentication with Google OAuth
- ✅ Responsive design for all devices
- ✅ Comprehensive database with Ethiopian hotels

### Technical Highlights
- Clean, maintainable code structure
- Secure database operations with prepared statements
- Modular design for easy extension
- Session-based state management
- Role-based access control

### Project Significance
This project demonstrates practical application of web development skills in creating a real-world solution for the Ethiopian hospitality industry. The system can be deployed and used immediately, with room for future enhancements and scalability.

---

## Appendix

### File Reference
- **Main Application Files:**
  - index.php - Homepage
  - login.php - User login
  - register.php - User registration
  - booking.php - Booking process
  - payment.php - Payment processing
  - hotel-detail.php - Hotel details

- **Admin Files:**
  - admin/dashboard.php - Admin dashboard
  - admin/edit-hotel.php - Hotel management
  - admin/view-bookings.php - Booking management
  - admin/verify-payments.php - Payment verification
  - admin/test-admin.php - Admin testing

- **Configuration:**
  - includes/config.php - Database & app configuration
  - includes/languages.php - Multi-language system
  - includes/header.php - Common header
  - includes/footer.php - Common footer

- **Assets:**
  - assets/css/style.css - Main stylesheet
  - database.sql - Database schema & data

### Contact Information
For questions or support regarding this project:
- **Group Members:** Bekele Abera Sewunet, Efirem Masho
- **Project:** EthioStay Hotel Booking System
- **Submission:** August 2026

---

**End of Documentation**
