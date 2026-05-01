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
- Market hero images for featured listings
- Interactive market calendar with event details
- Admin dashboard with real-time metrics and quick actions
- Comprehensive CSS documentation with Tailwind organization

## 🗄️ Database

The database features 35+ tables with a well-structured relational design:
- ENUM fields for controlled data (status, types)
- DECIMAL coordinates for precise location mapping
- JSON fields for flexible data storage
- Full-text indexes for search optimization
- Comprehensive indexing for performance

### Database Diagram
**View the complete database schema:** [Blue Ridge Farmers Collective Database Schema](https://dbdiagram.io/d/Blue-Ridge-Farmers-Collective-DATABASE-SCHEMA-67daf14775d75cc844b65113)

This interactive diagram shows all 35+ tables, their relationships, and field definitions.

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
   - Visit `http://localhost/blue_ridge_farmers_collective/public/`
   - Home page should display live database statistics (markets, vendors, products)

## 🔑 Default Test Accounts

The database seed data includes test accounts (Password: `Test123!`):

| Username | Email | Role | ID |
|----------|-------|------|-----|
| superadmin | superadmin@example.com | Super Admin | 4 |
| admin | admin@example.com | Admin | 3 |
| vendor | vendor@example.com | Vendor | 2 |
| member | member@example.com | Public | 1 |

## � Documentation

For detailed system architecture and how all components work together:
- **[SYSTEM_ARCHITECTURE.md](SYSTEM_ARCHITECTURE.md)** - Complete technical documentation covering:
  - System overview and technology stack
  - Request flow through the application
  - Folder structure and component responsibilities
  - Controllers, Services, Models, and Views
  - JavaScript frontend architecture
  - CSS styling with Tailwind
  - Authentication and security
  - Integration examples

## 📁 Project Structure

```
blue_ridge_farmers_collective/
├── config/              # Configuration files (db connection, routes)
├── public/              # Web root (index.php, images, CSS, JS)
│   ├── css/
│   ├── js/
│   └── uploads/
├── src/
│   ├── Controllers/     # Application controllers (10 controllers)
│   ├── Database/        # Schema and dump files
│   ├── Helpers/         # Helper functions and utilities
│   ├── Models/          # Data models (BaseModel)
│   ├── Services/        # Business logic (7 specialized services)
│   └── Views/           # Template files (40+ PHP templates)
├── storage/             # Cache, sessions, temp files
├── package.json         # NPM dependencies
├── tailwind.config.js   # Tailwind CSS configuration
├── SYSTEM_ARCHITECTURE.md # Technical architecture documentation
└── README.md            # This file
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

### Tailwind CSS
The project uses Tailwind CSS with a comprehensive custom configuration:

- **Source file:** `src/assets/tailwind.css` - Well-documented with organized sections
- **Build output:** `public/css/tailwind.css` - Production minified CSS
- **Build command:** `npm run tailwind:build`
- **Watch mode:** `npm run tailwind:watch`
- **Color system:** Custom brand colors (primary green #1f6b45, accent tan #c9935f) with 13 category-specific colors
- **Typography:** Fluid typography system with responsive text scaling (text-fluid-xs through text-fluid-3xl)
- **Components:** Well-documented component classes for buttons, forms, cards, navigation, and dashboard elements

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

### Admin Dashboard
The admin dashboard provides real-time operational insights:
- **Metric Cards** - Display key metrics at a glance:
  - Pending vendors awaiting approval
  - Pending market applications
  - Pending reviews for moderation
  - Active vendor count with monthly trends
  - Active product inventory
- **Quick Actions** - Rapid access buttons for common admin tasks:
  - Review vendor applications
  - Review market applications
  - Manage market dates
  - Create new markets and dates
  - Manage admin users
- **Search Analytics** - Track trending searches across the platform
- **Data Dashboard** - Real-time statistics and system overview

### Status/Type Enums
Controlled status fields using ENUM constraints:
- `vendor_ven.application_status_ven` - pending, approved, rejected, suspended
- `vendor_attendance_vat.status_vat` - intended, confirmed, checked_in, no_show
- `market_date_mda.weather_status_mda` - clear, cloudy, rainy, stormy, snowy, cancelled_weather
- `vendor_market_venmkt.membership_status_venmkt` - pending, approved, suspended, inactive
- And 10+ more for comprehensive data control

### Market Features
Markets support rich media and event management:
- **Hero Images** - Upload custom images for market branding and featured listings
- **Market Calendar** - Interactive calendar showing market dates with event details
- **Weather Integration** - Real-time weather status tracking for each market date
- **Featured Markets** - Display selected markets on home page with images
- **Location Tracking** - DECIMAL coordinates for precise GPS mapping

### Coordinate System
DECIMAL(8,2) precision for booth/market locations:
- Accurate GPS coordinates: DECIMAL(10,8) for latitude, DECIMAL(11,8) for longitude
- SVG rendering coordinates in booth_location_blo table
- Enables precise interactive market maps

## 🧪 Testing

To test database connectivity:
1. Visit the home page at `/public/` in your browser
2. The page displays live database content:
   - Total active markets count
   - Total vendors count
   - Total products count
   - Featured markets list
3. If data displays correctly, your database connection is working

## 📝 License

This project is part of the WEB-289 course curriculum.

## 👤 Author

**Arthur Cathey**
- Course: WEB-289
- Assignment: Database Schema Refactoring

## � Deployment

### Pre-Deployment Checklist
1. **Build Tailwind CSS**
   ```bash
   npm run tailwind:build
   ```
   Ensure CSS is up-to-date before deployment.

2. **Test Locally**
   ```bash
   php -S localhost:8000 -t public/
   ```
   Verify all features work in local environment.

3. **Commit Changes**
   ```bash
   git status
   git add .
   git commit -m "Deployment ready"
   git push
   ```

### Deployment to Bluehost

#### One-Time Setup
1. In cPanel, set **Document Root** to `/public/`
2. Ensure **mod_rewrite** is enabled (contact support if needed)
3. Create database in cPanel
4. Update `config/env.php` with Bluehost credentials

#### Files to Upload
Upload from project root to Bluehost:

**Critical Files:**
- `public/.htaccess` - URL rewriting
- `public/index.php` - Front controller
- `config/routes.php` - Routes configuration
- `config/database.php` - Database connection
- `config/env.php` - Environment variables (with Bluehost credentials)

**Stylesheets & JavaScript:**
- `public/css/main.css`
- `public/css/tailwind.css`
- `public/js/main.js`

**Views & Controllers:**
- `src/Views/` - All PHP templates
- `src/Controllers/` - All controllers
- `src/Helpers/` - Helper functions
- `src/Models/` - Data models
- `src/Services/` - Service classes

**Images:**
- `public/images/` - All static images
- `public/uploads/` - Keep writable for uploads

**Database:**
- `src/Database/schema.sql` - For reference (import manually)

**Build Files:**
- `package.json` - Dependencies reference
- `tailwind.config.js` - Tailwind configuration

#### Database Setup
1. Access phpMyAdmin in cPanel
2. Create database (e.g., `hqkmwgmy_blueridge_farmers_db`)
3. Open "SQL" tab and paste contents of `src/Database/schema.sql`
4. Execute to create tables and seed data

#### After Upload
1. Hard refresh browser: `Ctrl+F5`
2. Test URLs:
   - https://blueridgefarmerscollective.com/ (home)
   - https://blueridgefarmerscollective.com/login (login)
   - https://blueridgefarmerscollective.com/about (about)
3. Check browser console for CSS/JS errors
4. Test on mobile device for responsive design

#### Troubleshooting Deployment

**Site goes to /public/ URL:**
- `.htaccess` not uploading correctly
- Check if mod_rewrite is enabled (cPanel → Apache Modules)
- Verify document root is set to `/public/`

**CSS/JS missing (404 errors):**
- Verify all files in `public/css/` and `public/js/` directories exist
- Check file permissions: should be readable (644)

**Database connection error:**
- Verify `config/env.php` credentials match cPanel database
- Test connection: Visit homepage, should show database statistics
- Check PHP error logs in cPanel

**Email not sending:**
- Update SMTP credentials in `config/env.php`
- Verify email account exists in cPanel
- Check `config/env.php` for correct MAIL_HOST and MAIL_PORT

---

## 📞 Support

For questions or issues:
1. Check the database logs in the audit_log_aud table
2. Review error messages in PHP error logs
3. Verify database connection in config/database.php
4. See DEPLOYMENT section for hosting-specific issues

---

**Last Updated:** May 1, 2026
