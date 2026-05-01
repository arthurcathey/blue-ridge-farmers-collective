# Blue Ridge Farmers Collective - Complete System Architecture

## **PART 1: BRIEF SYSTEM OVERVIEW**

Blue Ridge Farmers Collective is a **full-stack web application** designed to manage farmers' markets, vendors, products, and customer reviews. It uses a **custom PHP MVC (Model-View-Controller) framework** with a **vanilla JavaScript** frontend and **MySQL database** backend.

**Core Purpose:** Connect farmers' markets with vendors, manage booth assignments, track product listings, and handle customer reviews—all through role-based dashboards (Member/Vendor/Admin/Super Admin).

---

## **PART 2: TECHNOLOGY STACK**

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Backend Language** | PHP 7.4+ (OOP with namespaces) | Server-side logic, routing, data processing |
| **Frontend Language** | Vanilla JavaScript (ES6 modules) | DOM manipulation, interactivity, form validation |
| **Styling** | Tailwind CSS (compiled) | Responsive UI with utility-first classes |
| **Database** | MySQL 5.7+ | Persistent data storage (PDO with prepared statements) |
| **Server** | Apache (via Bluehost/AMPPS) | HTTP request handling |
| **Hosting** | Bluehost (production) / AMPPS (local) | App deployment |

---

## **PART 3: HOW THE REQUEST FLOWS THROUGH THE SYSTEM**

```
User Request (Browser)
    ↓
Apache Server Routes to /public/index.php
    ↓
Router (config/routes.php) Matches URL to Controller & Action
    ↓
Autoloader (PSR-4) Loads Controller Class
    ↓
Controller Executes Business Logic
    ├─ Validates Request (CSRF, Methods, Roles)
    ├─ Queries Database via Models/Services
    └─ Prepares Data Array
    ↓
View Rendered with Data
    ├─ PHP extracts() data into view scope
    ├─ Wraps content with Layout (header/footer)
    └─ Returns HTML to Browser
    ↓
HTML + JavaScript + Tailwind CSS Rendered in Browser
    ↓
JavaScript Modules Initialize and Handle Interactivity
```

---

## **PART 4: FOLDER STRUCTURE & PURPOSE**

### **Root Level**
```
blue_ridge_farmers_collective/
├─ index.php              ← Entry point (loads environment, delegates to public/index.php)
├─ package.json           ← NPM config (Tailwind build scripts)
├─ tailwind.config.js     ← Tailwind CSS configuration
├─ .env                   ← Environment variables (DB credentials, API keys)
└─ README.md              ← Documentation
```

**`index.php` Does:**
- Starts session
- Loads environment variables from `.env`
- Requires helper functions
- Delegates to `public/index.php` (actual request handler)

---

### **`/config` - Configuration Files**

| File | Purpose |
|------|---------|
| `config.php` | Global app settings (app base, asset paths) |
| `database.php` | Database connection config (reads from `.env`) |
| `database-connection.php` | Creates PDO instance with MySQL |
| `routes.php` | URL → Controller mappings |
| `env.php` | Environment variable loader (.env file parser) |

**Example Route Mapping:**
```php
'GET' => [
  '/' => ['App\\Controllers\\HomeController', 'index'],
  '/login' => ['App\\Controllers\\AuthController', 'showLogin'],
  '/admin' => ['App\\Controllers\\AdminController', 'index'],
  // ... 60+ routes
]
```

---

### **`/src - Application Source Code**

#### **`/Controllers` - Request Handlers (10 Controllers)**

**Request Flow for Controllers:**
1. `public/index.php` parses URL
2. Looks up route in `config/routes.php`
3. Instantiates controller class with `$basePath` and `$config`
4. Calls action method (e.g., `index()`, `create()`)
5. Controller calls Service → Model → Database
6. Returns rendered view with data

**Key Controllers:**

| Controller | Purpose | Key Methods |
|------------|---------|------------|
| `BaseController` | Abstract parent—all others extend | `render()`, `redirect()`, `requireRole()`, `requireMethod()`, `authUser()`, `flash()`, `db()` |
| `HomeController` | Public pages | `index()`, `about()`, `contact()`, `faq()` |
| `AuthController` | Login/Register/Verification | `showLogin()`, `register()`, `logout()`, `verifyEmail()` |
| `DashboardController` | Member dashboard | `index()` (redirects to role-specific) |
| `VendorController` | Vendor forms & operations | `apply()`, `marketApply()`, `vendorReviews()` |
| `AdminController` | Admin management & analytics | `index()`, `reviewManagement()`, `vendorManagement()`, `boothAssignment()` |
| `SuperAdminController` | System-level operations | `index()`, `manageAdmins()`, `listMarkets()` |

**Controller Example (Simplified):**
```php
class VendorController extends BaseController {
  public function apply() {
    $user = $this->authUser();
    if (!$user) $this->redirect('/login');
    
    $vendors = $this->db()->fetchAll("SELECT * FROM vendor_ven WHERE account_id_ven = ?", [$user['id']]);
    
    return $this->render('vendors/apply', ['vendors' => $vendors]);
  }
}
```

---

#### **`/Models - Data Access Layer (1 Base Model)**

```
Models/
└─ BaseModel.php
   ├─ Provides: static PDO connection
   └─ Usage: Inherit in custom models OR use $this->db() in controllers
```

**Philosophy:** The app uses a **service-oriented approach**. Models are minimal; business logic lives in Controllers & Services.

**Database Connection:**
```php
class BaseModel {
  protected static function defaultConnection(): PDO {
    require_once 'config/database-connection.php';
    return new PDO("mysql:host=...", $user, $pass);
  }
}
```

---

#### **`/Services - Business Logic (7 Specialized Services)**

| Service | Purpose |
|---------|---------|
| `ValidationService` | Form validation rules (required, email, unique checks) |
| `SpellCheckerService` | Detects typos in vendor/product fields (farming terminology aware) |
| `ImageProcessor` | Uploads, resizes, optimizes images (vendors, products, markets) |
| `MailService` | Sends emails (registration, password reset, notifications) |
| `NotificationService` | Creates/retrieves in-app notifications |
| `AuditService` | Logs admin actions (compliance & accountability) |
| `WeatherService` | Fetches weather forecast for market dates |

**Service Pattern:**
```php
$spellChecker = new SpellCheckerService();
$result = $spellChecker->checkFields([
  'farm_name' => 'Organc Farm',  // typo
  'description' => 'Fresh vegetables'
]);
// Returns: ['isValid' => false, 'misspellings' => [...], 'suggestions' => [...]]
```

---

#### **`/Views - Presentation Layer (40+ PHP Templates)**

```
Views/
├─ layouts/
│  └─ main.php              ← Master layout (header, nav, footer)
├─ home/
│  ├─ index.php             ← Landing page
│  ├─ about.php
│  └─ contact.php
├─ auth/
│  ├─ login.php
│  ├─ register.php
│  └─ reset-password.php
├─ dashboard/
│  ├─ member.php            ← Member role overview
│  ├─ admin.php             ← Admin role dashboard
│  └─ super-admin.php
├─ admin/                   ← Admin management pages
│  ├─ vendor-management.php
│  ├─ product-management.php
│  ├─ booth-management.php
│  ├─ review-management.php ← Vendor reviews (colored by rating)
│  └─ ... (20+ more)
├─ vendor-dashboard/
│  └─ ... (vendor-specific pages)
├─ products/
│  └─ ... (product listing & detail)
├─ markets/
│  └─ ... (market info & vendor applications)
├─ partials/
│  ├─ header.php
│  ├─ navigation.php
│  └─ ... (reusable components)
└─ errors/
   ├─ 404.php
   ├─ 403.php
   └─ 500.php
```

**View Rendering Flow:**
```php
// In Controller:
return $this->render('dashboard/admin', [
  'activeVendors' => [...],
  'activeProducts' => [...]
]);

// In BaseController::render():
extract($data);  // Creates $activeVendors, $activeProducts variables
ob_start();
require 'src/Views/dashboard/admin.php';  // Uses variables
$content = ob_get_clean();

// Wraps with layout
require 'src/Views/layouts/main.php';
// $content variable inserted in layout's body
```

---

#### **`/Database - Schema & Data**

```
Database/
├─ schema.sql     ← Table definitions (CREATE TABLE statements)
└─ seed.sql       ← Initial data (test users, markets, etc.)
```

**Key Tables:**

| Table | Prefix | Purpose |
|-------|--------|---------|
| `vendor_ven` | _ven | Vendor profiles (farm name, description, photos) |
| `product_prd` | _prd | Products (name, price, category, active status) |
| `vendor_review_vre` | _vre | Customer reviews (rating, text, verified purchase) |
| `market_mkt` | _mkt | Farmers' markets (name, location, schedule) |
| `vendor_market_venmkt` | _venmkt | Join table (vendor's markets & booth assignments) |
| `account_acc` | _acc | User accounts (email, password hash, role) |
| `market_date_mdt` | _mdt | Individual market event dates |
| `booth_bth` | _bth | Booth information (size, price, location) |

**Database Uses 3-Letter Abbreviations:**
- `farm_name_ven` → field `farm_name` in `vendor_ven` table
- `is_approved_prd` → field `is_approved` in `product_prd` table

**All Queries Use Prepared Statements:**
```php
$stmt = $this->db()->prepare("SELECT * FROM vendor_ven WHERE account_id_ven = ? AND status_ven = ?");
$stmt->execute([$userId, 'active']);
$vendors = $stmt->fetchAll();
```

---

#### **`/Helpers - Utility Functions**

| File | Purpose |
|------|---------|
| `functions.php` | Global helpers: `h()` (HTML escape), `csrf_token()`, `flash()`, `app_base()` |
| `cache.php` | Caching mechanism for performance |

**Critical Helper - `h()` (HTML Escape):**
```php
<?= h($user['name']) ?>  // Prevents XSS attacks
// Converts < > & " ' to HTML entities
```

---

### **`/public - Web-Accessible Files**

```
public/
├─ index.php              ← Actual router (handles requests)
├─ diagnostics.php        ← Health check / debug page
├─ css/
│  ├─ main.css            ← Custom CSS (header scroll effects)
│  └─ tailwind.css        ← Compiled Tailwind output
├─ js/
│  ├─ main.js             ← Module orchestrator (ES6 imports)
│  ├─ navigation.js       ← Mobile menu & dropdowns
│  ├─ forms.js            ← Form validation & star ratings
│  ├─ products.js         ← Product filtering & search
│  ├─ scroll.js           ← Scroll effects (logo swap, back-to-top)
│  ├─ carousel.js         ← Image carousels
│  ├─ calendar.js         ← Market date picker
│  ├─ admin.js            ← Admin-specific interactions
│  └─ utils.js            ← Shared utilities (toast messages)
├─ images/
│  ├─ backgrounds/        ← Hero images
│  ├─ banners/            ← Logo files (logo.png, logo2.png)
│  └─ icons/              ← SVGs and icons
└─ uploads/               ← User-uploaded images (secure, outside web root)
    ├─ vendors/           ← Vendor profile photos
    ├─ products/          ← Product photos
    └─ markets/           ← Market photos
```

**`public/index.php` - The Router:**
```php
// 1. Parse URL from $_GET or REQUEST_URI
// 2. Handle base path (/public/ stripping)
// 3. Lookup route in config/routes.php
// 4. Instantiate controller & call action
// 5. Echo output OR JSON response
// 6. Handle 404/500 errors
```

---

### **`/storage - Non-Web-Accessible Storage**

```
storage/
└─ cache/                  ← Server-side cache files
```

---

## **PART 5: REQUEST FLOW - DETAILED EXAMPLE**

**User clicks "Login":**

1. **Browser Request:**
   ```
   GET /login HTTP/1.1
   ```

2. **Apache Routes to `/public/index.php`**
   - Apache receives request for `/login`
   - Redirects to `/public/index.php?_route=/login`

3. **Router Parses:**
   ```php
   $path = '/' . ltrim($_GET['_route'], '/');  // '/login'
   ```

4. **Route Lookup:**
   ```php
   // config/routes.php
   '/login' => ['App\\Controllers\\AuthController', 'showLogin']
   ```

5. **Controller Instantiation & Execution:**
   ```php
   $controller = new AuthController($basePath, $config);
   $output = $controller->showLogin();
   echo $output;
   ```

6. **Controller Logic (Simplified):**
   ```php
   public function showLogin() {
     $csrfToken = csrf_token();  // Generate CSRF token
     return $this->render('auth/login', [
       'csrfToken' => $csrfToken
     ]);
   }
   ```

7. **View Rendering:**
   ```php
   // src/Views/auth/login.php
   extract(['csrfToken' => '...']);
   ob_start();
   require 'src/Views/auth/login.php';
   $content = ob_get_clean();  // HTML content
   
   // Wrap with layout
   require 'src/Views/layouts/main.php';
   // $content inserted into layout's body
   ```

8. **HTML Returned to Browser**
   - `main.js` imports and initializes modules
   - JavaScript modules handle form submission
   - Tailwind CSS styles the page

---

## **PART 6: JavaScript Frontend Architecture**

**Philosophy:** ES6 module pattern with **conditional initialization**.

### **Module System**

```javascript
// public/js/main.js (Entry point)
import { Navigation } from './navigation.js';
import { Forms } from './forms.js';
import { Products } from './products.js';
import { ScrollEffects } from './scroll.js';
import { Carousel } from './carousel.js';
import { initFlashMessages } from './utils.js';

document.addEventListener("DOMContentLoaded", () => {
  Navigation.init();      // Always initializes
  Forms.init();           // Always initializes
  Products.init();        // Checks for data-product-page
  ScrollEffects.init();   // Always initializes (logo swap)
  Carousel.init();        // Checks for carousel elements
});
```

### **Conditional Module Loading**

Modules check for data attributes before running:

```javascript
// public/js/products.js
export const Products = {
  init() {
    if (!document.querySelector('[data-product-page]')) return;
    // Product-specific logic only runs on product pages
  }
};

// public/js/admin.js
if (document.querySelector('[data-admin-page]')) {
  import('./admin.js').then(({ AdminModule }) => AdminModule.init());
}
```

### **Key JavaScript Modules**

| Module | Purpose |
|--------|---------|
| `navigation.js` | Mobile hamburger menu, dropdown menus |
| `forms.js` | Form validation, star rating widget, form submission |
| `products.js` | Filter by category, search, sort |
| `scroll.js` | **Logo swap** (white → green on scroll), back-to-top button |
| `carousel.js` | Image carousels (product photos, market galleries) |
| `calendar.js` | Market date picker (if present) |
| `admin.js` | Admin-specific interactions (charts, data tables) |
| `utils.js` | Toast notifications, shared utilities |

### **Logo Swap Example (scroll.js)**
```javascript
const logoElement = document.querySelector("[data-scroll-logo]");
window.addEventListener("scroll", () => {
  if (window.scrollY > 0) {
    header.classList.add("is-scrolled");  // Triggers CSS styling
    logoElement.src = logoElement.dataset.logoScroll;  // /images/banners/logo.png (green)
  } else {
    header.classList.remove("is-scrolled");
    logoElement.src = logoElement.dataset.logoDefault;  // /images/banners/logo2.png (white)
  }
});
```

---

## **PART 7: CSS Styling Architecture**

### **Tailwind CSS Workflow**

```
src/assets/tailwind.css (Custom utilities)
        ↓ (build process)
public/css/tailwind.css (Compiled output)
        ↓
Applied to HTML via class names
```

### **Custom Color Palette**
```javascript
// tailwind.config.js
theme: {
  colors: {
    'brand-primary': '#1f6b45',    // Green (main)
    'brand-accent': '#c9935f',     // Brown/Gold (accents)
    'brand-secondary': '#7b5b3e',  // Dark brown
    'neutral-dark': '#1e293b',     // Almost black
    // ... + Tailwind defaults
  }
}
```

### **Key CSS Classes**
- **Layout:** `grid-cols-[repeat(auto-fit,minmax(200px,1fr))]` (responsive grid)
- **Cards:** `.card`, `.dashboard-list`, `.dashboard-list-item`
- **Badges:** `.badge`, `.badge-success`, `.badge-warning`
- **Scroll Effect:** `.is-scrolled` (applied to header when scrollY > 0)

---

## **PART 8: Authentication & Authorization**

### **Session-Based RBAC (Role-Based Access Control)**

```php
// Session stores on login
$_SESSION['user'] = [
  'id' => 1,
  'email' => 'user@example.com',
  'role' => 'vendor',  // member | vendor | admin | super_admin
  'account_id' => 1
];

// Protected pages require role
public function index() {
  $this->requireRole('admin');  // In BaseController
  // Only admins can access this action
}
```

### **CSRF Protection**
```php
// Generate token on form render
<?= csrf_field() ?>  // Outputs: <input type="hidden" name="csrf_token" value="...">

// Verify on form submission
if (!csrf_verify($_POST['csrf_token'])) {
  $this->flash('error', 'Invalid token');
  $this->redirect('/');
}
```

---

## **PART 9: Data Security**

- **SQL Injection Prevention:** All queries use prepared statements with `?` placeholders
- **XSS Prevention:** All user input escaped with `h()` function before output
- **Password Hashing:** `password_hash()` with default algo (bcrypt)
- **File Upload Security:** ImageProcessor validates MIME types, sanitizes filenames
- **Session Security:** Secure cookies, session regeneration on login

---

## **PART 10: Key Integration Points**

### **Admin Creates Market Date**
```
Admin clicks "New Market Date"
  ↓ Routes to: POST /admin/market-dates/new
  ↓ Controller: AdminController::createMarketDate()
  ↓ Validates input (ValidationService)
  ↓ Inserts to database: INSERT INTO market_date_mdt
  ↓ Stores cache for vendor queries (cache.php)
  ↓ Flashes success message
  ↓ Redirects to market dates list
  ↓ View shows updated list
```

### **Vendor Uploads Product Photo**
```
Vendor selects image file
  ↓ JavaScript: FormData with CSRF token
  ↓ POST to: /admin/products/upload-photo
  ↓ Controller: AdminController::productPhotoUpload()
  ↓ ImageProcessor validates MIME type
  ↓ Resizes image (multiple sizes)
  ↓ Saves to: /public/uploads/products/{vendorId}/{filename}
  ↓ Stores path in database: product_prd.photo_path_prd
  ↓ Returns JSON: {path: '/images/products/...', success: true}
  ↓ JavaScript updates preview image
```

### **Vendor Applies to Market**
```
Form submission (vendors/apply.php)
  ↓ VendorController::marketApply()
  ↓ SpellCheckerService checks field typos
  ↓ ValidationService validates all fields
  ↓ If typos, store in $_SESSION['spell_warnings']
  ↓ AuditService logs application attempt
  ↓ Inserts to: vendor_market_venmkt (many-to-many relationship)
  ↓ MailService sends notification emails
  ↓ Redirects to confirmation view
```

---

## **PART 11: How Everything Works Together - Visual Map**

```
┌─────────────────────────────────────────────────────────────┐
│                     BROWSER (Client)                         │
│  HTML + JavaScript Modules + Tailwind CSS + User Input      │
└────────────────────────┬────────────────────────────────────┘
                         │ HTTP Request (URL + Data)
                         ↓
┌─────────────────────────────────────────────────────────────┐
│              APACHE SERVER + /public/index.php              │
│  ├─ Route lookup (config/routes.php)                        │
│  └─ PSR-4 Autoload (spl_autoload_register)                  │
└────────────────────────┬────────────────────────────────────┘
                         │ Instantiate Controller
                         ↓
┌─────────────────────────────────────────────────────────────┐
│            CONTROLLER LAYER (src/Controllers/)              │
│  ├─ Parse & validate request                               │
│  ├─ Check CSRF token & permissions                         │
│  └─ Call Services & Models                                 │
└────────────────────────┬────────────────────────────────────┘
                         │
        ┌────────────────┴────────────────┐
        ↓                                 ↓
┌──────────────────────┐      ┌──────────────────────┐
│ SERVICES LAYER       │      │ MODELS LAYER         │
│ ├─ Validation        │      │ ├─ Database queries  │
│ ├─ SpellChecker      │      │ └─ PDO statements    │
│ ├─ ImageProcessor    │      │                      │
│ ├─ Mail              │      │ BaseModel extends    │
│ ├─ Notifications     │      │ to controllers       │
│ ├─ Weather           │      │ via $this->db()      │
│ └─ Audit             │      │                      │
└──────────┬───────────┘      └──────────┬───────────┘
           │                              │
           └──────────────┬───────────────┘
                          ↓
        ┌─────────────────────────────────┐
        │   MYSQL DATABASE                │
        │   ├─ vendor_ven                 │
        │   ├─ product_prd                │
        │   ├─ vendor_review_vre          │
        │   ├─ account_acc                │
        │   ├─ market_mkt                 │
        │   ├─ vendor_market_venmkt       │
        │   ├─ market_date_mdt            │
        │   └─ ... (13+ tables)           │
        └─────────────────────────────────┘
                          │ Query results
                          ↓
        ┌──────────────────────────────────┐
        │  CONTROLLER Prepares Data        │
        │  ['users' => [...], ...]         │
        └──────────────────┬───────────────┘
                           │ render($view, $data)
                           ↓
        ┌──────────────────────────────────┐
        │  VIEW LAYER (src/Views/)         │
        │  ├─ Extract $data                │
        │  ├─ Generate HTML               │
        │  └─ Wrap with layouts/main.php   │
        └──────────────────┬───────────────┘
                           │ Rendered HTML
                           ↓
        ┌──────────────────────────────────┐
        │  Browser Receives HTML           │
        │  ├─ Parse & render DOM           │
        │  ├─ Load CSS (Tailwind)          │
        │  ├─ Load JS (main.js)            │
        │  └─ Initialize modules           │
        └──────────────────────────────────┘
```

---

## **SUMMARY**

**Blue Ridge Farmers Collective is a sophisticated web application where:**

1. **Users submit requests** via browser URLs or forms
2. **Apache routes** to `/public/index.php` which matches routes to controllers
3. **Controllers** orchestrate business logic using services, models, and database queries
4. **Services** handle specialized tasks (validation, spell-checking, image processing, emails)
5. **Models** provide database access via PDO prepared statements
6. **Views** render HTML templates with passed data
7. **JavaScript modules** add interactivity (navigation, forms, scroll effects)
8. **Tailwind CSS** provides responsive styling with custom brand colors
9. **MySQL database** persists all data with 3-letter field prefixes for clarity
10. **Session-based authentication** controls access via roles (member/vendor/admin/super_admin)

Every piece—routing, validation, database access, image handling, spell-checking, notifications, and UI effects—works together in a unified flow to create a complete farmers' market management platform.
