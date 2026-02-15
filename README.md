# Blue Ridge Farmers Collective

A comprehensive web application for managing local farmers markets with vendor management, product catalog, real-time market tracking, and customer engagement features.

## 📋 Project Details

- **Course:** WEB-289
- **Author:** Arthur Cathey
- **Status:** Active Development
- **Database:** MySQL (blueridge_farmers_db)

## 🌾 Features

### Core Functionality
- **Multi-Market Management** - Support for multiple independent farmers markets
- **Vendor Management** - Vendor profiles, applications, and market assignments
- **Product Catalog** - Comprehensive product management with seasonality tracking
- **Market Scheduling** - Market dates with weather tracking
- **Booth Management** - Interactive booth assignment and market layout
- **Customer Engagement** - Vendor reviews, notifications, and profiles

### Authentication & Security
- Role-based access control (Public, Vendor, Admin, Super Admin)
- Secure password reset tokens
- Email verification
- Session management
- Audit logging for compliance

### Advanced Features
- Analytics and trending searches
- Real-time vendor attendance tracking
- Email notification system with Twilio SMS
- Weather caching integration
- Vendor profile view analytics
- Product search with full-text indexing

## 🗄️ Database

The database features 35+ tables with a well-structured relational design:
- ENUM fields for controlled data (status, types)
- DECIMAL coordinates for precise location mapping
- JSON fields for flexible data storage
- Full-text indexes for search optimization
- Comprehensive indexing for performance

### Database Files
- `src/Database/schema.sql` - Complete database schema with seed data
- `src/Database/blueridge_farmers_db_dump.sql` - SQL dump for database recreation

## 🚀 Getting Started

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer (for dependencies)
- Node.js 14+ (for Tailwind CSS)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/blue-ridge-farmers-collective.git
   cd blue_ridge_farmers_collective
   ```

2. **Create database**
   ```bash
   mysql -u your_username -p < src/Database/schema.sql
   ```

3. **Configure environment**
   - Copy `.env.example` to `.env` (if available)
   - Update database credentials in `config/database.php`
   - Database defaults:
     - Host: 127.0.0.1
     - Port: 3306
     - Database: blueridge_farmers_db

4. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

5. **Build Tailwind CSS**
   ```bash
   npm run build
   npm run watch  # for development
   ```

6. **Verify Database Connection**
   - Visit `http://localhost/blue_ridge_farmers_collective/public/database_proof.php`
   - Should display database content successfully

## 🔑 Default Test Accounts

The database seed data includes test accounts (Password: `Test123!`):

| Username | Email | Role | ID |
|----------|-------|------|-----|
| superadmin | superadmin@example.com | Super Admin | 4 |
| admin | admin@example.com | Admin | 3 |
| vendor | vendor@example.com | Vendor | 2 |
| member | member@example.com | Public | 1 |

## 📁 Project Structure

```
blue_ridge_farmers_collective/
├── config/              # Configuration files (db connection, routes)
├── public/              # Web root (index.php, images, CSS, JS)
│   ├── css/
│   ├── js/
│   ├── uploads/
│   └── database_proof.php  # Database verification page
├── src/
│   ├── Controllers/     # Application controllers
│   ├── Database/        # Schema and dump files
│   ├── Helpers/         # Helper functions and utilities
│   ├── Models/          # Data models
│   └── Views/           # Template files
├── storage/             # Cache, sessions, temp files
├── package.json         # NPM dependencies
└── tailwind.config.js   # Tailwind CSS configuration
```

## 🔐 Security

This application implements several security best practices:
- Prepared statements to prevent SQL injection
- Password hashing with bcrypt
- CSRF token validation
- Audit logging of all changes
- Email verification for new accounts
- Secure session management

## 🛠️ Configuration

### Database Connection
Database credentials are configured in `config/database.php`:
- Uses environment variables when available (via `getenv()`)
- Falls back to defaults for local development
- Supports MySQL error modes and fetch modes

### Available Routes
Routes are defined in `config/routes.php` and handle:
- Authentication (login, register, password reset)
- Vendor management and applications
- Market pages and vendor listings
- Admin dashboards
- Product browsing and search

## 📊 Database Features

### Status/Type Enums
Controlled status fields using ENUM constraints:
- `vendor_ven.application_status_ven` - pending, approved, rejected, suspended
- `vendor_attendance_vat.status_vat` - intended, confirmed, checked_in, no_show
- `market_date_mda.weather_status_mda` - clear, cloudy, rainy, stormy, snowy, cancelled_weather
- `vendor_market_venmkt.membership_status_venmkt` - pending, approved, suspended, inactive
- And 10+ more for comprehensive data control

### Coordinate System
DECIMAL(8,2) precision for booth/market locations:
- Accurate GPS coordinates: DECIMAL(10,8) for latitude, DECIMAL(11,8) for longitude
- SVG rendering coordinates in booth_location_blo table
- Enables precise interactive market maps

## 🧪 Testing

To test database connectivity:
1. Visit `/public/database_proof.php` in your browser
2. Should display:
   - Connection status (Connected/Failed)
   - List of all roles in system
   - Sample vendors from database
   - Sample products by category
   - Available markets
3. All data queries should execute without errors

## 📝 License

This project is part of the WEB-289 course curriculum.

## 👤 Author

**Arthur Cathey**
- Course: WEB-289
- Assignment: Database Schema Refactoring

## 📞 Support

For questions or issues:
1. Check the database logs in the audit_log_aud table
2. Review error messages in PHP error logs
3. Verify database connection in config/database.php

---

**Last Updated:** February 15, 2026
