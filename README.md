# EthioStay - Hotel Booking System

A hotel booking web application for Ethiopian hotels, built with PHP and MySQL.

## Features

- User registration and authentication
- Hotel browsing and search
- Room booking with date selection
- Additional services selection
- Payment simulation (Telebirr, CBE Birr, Bank Transfer)
- Admin dashboard for hotel management
- Responsive design with Ethiopian theme

## Requirements

- XAMPP (or any PHP/MySQL server)
- PHP 7.0 or higher
- MySQL 5.6 or higher

## Installation Instructions

### 1. Setup XAMPP

1. Download and install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL services from XAMPP Control Panel

### 2. Setup Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click on "Import" tab
3. Select the `database.sql` file from this project
4. Click "Go" to import the database
5. Alternatively, you can manually run the SQL commands in `database.sql`

The database will be named `ethiostay_db` and include:
- Sample users (admin and customer)
- Sample hotels with rooms
- Sample services

### 3. Configure Application

The database configuration is already set in `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ethiostay_db');
```

If your MySQL password is different, update `DB_PASS` in `includes/config.php`.

### 4. Access the Application

Open your browser and navigate to: http://localhost/ethiostay/

## Demo Credentials

### Admin Account
- Email: admin@ethiostay.com
- Password: Admin@123

### Customer Account
- Email: customer@test.com
- Password: Customer@123

## Google OAuth Setup (Optional)

To enable Google Sign-In:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable Google+ API
4. Go to Credentials → Create Credentials → OAuth client ID
5. Application type: Web application
6. Authorized redirect URI: `.http://localhost/ethiostay/google-callback.php`
7. Copy Client ID and Client Secret
8. Update `includes/config.php`:
   ```php
   define('GOOGLE_CLIENT_ID', 'your_client_id_here');
   define('GOOGLE_CLIENT_SECRET', 'your_client_secret_here');
   ```

Note: Google OAuth requires HTTPS in production. For local development, you can use HTTP.

## File Structure

```
ethiostay/
├── admin/
│   └── dashboard.php          # Admin dashboard for hotel management
├── assets/
│   └── css/
│       └── style.css           # Main stylesheet
├── includes/
│   ├── config.php              # Database configuration
│   ├── footer.php              # Footer template
│   └── header.php              # Header template
├── booking.php                 # Room booking page
├── payment.php                 # Payment processing page
├── hotel-detail.php            # Hotel details and rooms
├── index.php                   # Homepage with hotel listing
├── login.php                   # User login
├── register.php                # User registration
├── logout.php                  # User logout
├── database.sql                # Database schema and sample data
└── README.md                   # This file
```

## Usage

### For Customers

1. Register a new account or login with demo credentials
2. Browse hotels on the homepage
3. Search hotels by name or city
4. Click "View Rooms" on a hotel to see available rooms
5. Select a room and choose check-in/check-out dates
6. Add optional services
7. Proceed to payment
8. Complete payment (simulated)
9. Receive booking confirmation

### For Admins

1. Login with admin credentials
2. Access the dashboard
3. Add new hotels with details
4. View existing hotels and room counts
5. Manage hotel information

## Payment Integration

Currently, the payment system is simulated. In production, you would integrate with:
- Telebirr API
- CBE Birr API
- Other Ethiopian payment gateways

API placeholders are available in `includes/config.php` for integration.

## Security Notes

- Passwords are hashed using PHP's `password_hash()`
- SQL injection prevention using prepared statements
- Session-based authentication
- Input sanitization with `htmlspecialchars()`

## Troubleshooting

### Database Connection Error
If you see "Connection failed", check:
1. MySQL service is running in XAMPP
2. Database credentials in `includes/config.php` are correct
3. Database `ethiostay_db` exists in phpMyAdmin

### Blank Pages
1. Ensure PHP is enabled in XAMPP
2. Check PHP error logs in XAMPP
3. Verify file permissions

### CSS Not Loading
1. Check that `assets/css/style.css` exists
2. Verify the path in the `<link>` tag

## Support

For issues or questions, please check:
- XAMPP documentation
- PHP documentation
- MySQL documentation

## License

This project is for educational purposes.
