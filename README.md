# Nha Trang Vinpearl Resort Website

A beautiful website for Vinpearl Resort in Nha Trang, featuring room bookings, vacation packages, tours, and an admin panel.

## Features

- **Multi-language Support**: English and Vietnamese interfaces
- **Room Booking**: Browse and reserve rooms with various options
- **Package Booking**: View and book vacation packages
- **Tour Booking**: Browse and book tours around Nha Trang
- **Nearby Attractions**: Information about places of interest near the resort
- **User Accounts**: Registration, login, and profile management
- **Admin Panel**: Comprehensive management interface for site administrators

## Tech Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Authentication**: Custom PHP authentication system

## Project Structure

```
vinpearl-website/
├── assets/                  # Images, fonts, CSS, JS
│   ├── css/                 # Style sheets
│   ├── js/                  # JavaScript files
│   └── images/              # Website images
├── includes/                # PHP components and functions
│   ├── config.php           # Database connection
│   ├── auth.php             # Authentication functions
│   └── functions.php        # Helper functions
├── admin/                   # Admin panel pages
├── database/                # Database scripts
│   ├── schema.sql           # Database schema
│   ├── mock_data.py         # Python script to generate mock data
│   └── init_database.py     # Python script to initialize database
└── Various feature pages    # Rooms, packages, tours, etc.
```

## Setup Instructions

### Prerequisites

- Web server with PHP 7.4+ support (Apache/Nginx)
- MySQL 5.7+ or MariaDB 10.3+
- Python 3.6+ (for database initialization only)

### Database Setup

1. Navigate to the `database` directory
2. Install required Python packages:
   ```
   pip install mysql-connector-python
   ```
3. Run the database initialization script:
   ```
   python init_database.py
   ```
4. Follow the prompts to enter your MySQL credentials

### Web Server Setup

1. Configure your web server to serve files from the project directory
2. Modify the database connection settings in `includes/config.php`
3. Access the website through your web browser

### Admin Access

- **URL**: `/admin`
- **Default Admin Credentials**:
  - Email: admin@vinpearl.com
  - Password: admin123

## Development Notes

- This project uses CSS custom properties for consistent styling
- The site is fully responsive for all device sizes
- PHP files follow PSR-12 coding standards 