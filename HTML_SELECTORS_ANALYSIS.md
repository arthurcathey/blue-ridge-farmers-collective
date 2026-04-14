# HTML Selectors Analysis Report
**Generated:** April 14, 2026

This document provides a comprehensive analysis of all HTML element selectors (IDs, data attributes, and classes) used in the Views templates. This analysis helps identify potential mismatches between HTML markup and JavaScript code.

---

## 1. Navigation Elements

### Header Navigation Structure
**File:** `src/Views/partials/header.php`

#### Navigation Container & Brand
- **Element:** `<header class="site-header">`
- **Navigation:** `<nav class="site-nav" aria-label="Primary">`
- **Logo Image:** 
  - `data-scroll-logo="default"`
  - `data-logo-default="[url]"`
  - `data-logo-scroll="[url]"`

#### Navigation Links Container
- **Data Attribute:** `data-nav` (on `<div class="nav-links">`)

#### Dropdown Menu Toggles
**Explore Dropdown:**
- **Trigger Element:** `<div class="nav-item" data-dropdown="explore">`
- **Trigger Button:** `class="nav-trigger"` with `aria-controls="nav-menu-explore"`
- **Menu Container:** `<div id="nav-menu-explore" class="nav-menu" data-menu="explore" hidden>`

**Account Dropdown:**
- **Trigger Element:** `<div class="nav-item" data-dropdown="account">`
- **Trigger Button:** `class="nav-trigger"` with `aria-controls="nav-menu-account"`
- **Menu Container:** `<div id="nav-menu-account" class="nav-menu" data-menu="account" hidden>`

#### Mobile Menu Toggle
- **Button Selector:** `class="nav-toggle md:hidden"`
- **Data Attribute:** `data-menu-toggle`
- **ARIA Attributes:** `aria-label="Toggle menu"`, `aria-expanded="false"`

### Summary of Navigation Selectors
| Selector Type | Value | Purpose |
|---|---|---|
| `data-dropdown` | `"explore"` | Marks dropdown trigger container |
| `data-dropdown` | `"account"` | Marks dropdown trigger container |
| `data-menu` | `"explore"` | Marks menu container for explore |
| `data-menu` | `"account"` | Marks menu container for account |
| `data-menu-toggle` | Present | Mobile menu toggle button |
| `data-nav` | Present | Navigation links container |
| `data-scroll-logo` | `"default"` | Logo for scroll detection |
| Class | `nav-trigger` | Dropdown trigger buttons |
| Class | `nav-chevron` | Chevron icon in triggers |
| ID | `nav-menu-explore` | Explore menu container |
| ID | `nav-menu-account` | Account menu container |

---

## 2. Form Elements

### Form Structure
**File:** `src/Views/partials/form-field.php`

#### Form Field Container
- **Class:** `form-field`
- **Label:** `<label for="[name]" class="form-label">`
- **Input:** `<input class="form-input">`
- **Error Display:** `<small id="error-[name]" class="form-error" role="alert">`

#### Validation Attributes
- **Aria Attributes on Input:** 
  - `aria-describedby="error-[fieldname]"` (when error present)
  - `aria-invalid="true"` (when error present)

### Specific Form Fields Found

#### Vendor Application Form
**File:** `src/Views/vendors/apply.php`
- **Data Attribute:** `data-autosave="vendor-apply"` (on form element)
- **Fields:**
  - `id="farm_description"` - textarea
  - `id="address"` - text input
  - `id="city"` - text input
  - `id="phone"` - text input
  - `id="primary_categories"` - multi-select
  - `id="farm_photo"` - file input
  - Checkboxes: `name="production_methods[]"`

#### Review/Rating Form
**File:** `src/Views/vendors/show.php`
- `id="customer_name"` - text input
- `id="rating-label"` - label for rating inputs
- Radio buttons: `name="rating"` (values 1-5)
- `id="review_text"` - textarea

#### Search/Filter Forms
**File:** `src/Views/products/index.php`
- **Form Class:** `search-form`
- **Fields:**
  - `id="search"` - search input with `data-search-input`
  - `id="category"` - category select with `class="search-form-select"`
  - `id="vendor"` - vendor select with `class="search-form-select"`
  - `id="market"` - market select with `class="search-form-select"`
  - `id="sort"` - sort select with `class="search-form-select"`
- **Data Attributes:**
  - `data-search-input` - on search input
  - `data-search-loading` - loading indicator (hidden by default)
  - `data-search-results` - results container

#### Hidden Form Fields
- `<input type="hidden" id="csrfToken" value="[token]">` - CSRF token storage
- `<input type="hidden" name="csrf_token">` - CSRF token in forms
- `<input type="hidden" id="modalBoothId">` - Modal form fields
- `<input type="hidden" name="transfer_id" id="modalTransferId">`

### Form Error Messages
- **Pattern:** `id="error-[fieldname]"`
- **Classes:** `form-error`, `role="alert"`
- **Examples:**
  - `id="error-farm_description"`
  - `id="error-address"`
  - `id="error-city"`
  - `id="error-phone"`
  - `id="error-primary_categories"`
  - `id="error-photo"`
  - `id="error-customer_name"`
  - `id="error-rating"`
  - `id="error-review_text"`

---

## 3. Carousel Elements

### Vendor Carousel (Home Page)
**File:** `src/Views/home/index.php`

#### Carousel Container
- **Data Attribute:** `data-carousel="vendors"` (on container div)
- **Structure:** 
  ```html
  <div class="relative w-full" data-carousel="vendors">
    <div class="carousel-track">
      <div class="carousel-slide">...</div>
    </div>
  </div>
  ```

#### Carousel Controls
**Previous Button:**
- **Class:** `carousel-btn carousel-prev`
- **Data Attribute:** `data-direction="prev"`
- **ARIA:** `aria-label="Previous vendor"`

**Next Button:**
- **Class:** `carousel-btn carousel-next`
- **Data Attribute:** `data-direction="next"`
- **ARIA:** `aria-label="Next vendor"`

#### Carousel Dots/Indicators
- **Class:** `carousel-dot`
- **Data Attribute:** `data-slide="[index]"` (0-based index)
- **ARIA:** `aria-label="Go to vendor [n]"`

### Summary of Carousel Selectors
| Selector Type | Value | Purpose |
|---|---|---|
| `data-carousel` | `"vendors"` | Carousel container identifier |
| Class | `carousel-track` | Sliding container |
| Class | `carousel-slide` | Individual slide wrapper |
| Class | `carousel-btn` | Button base class |
| Class | `carousel-prev` | Previous button |
| Class | `carousel-next` | Next button |
| `data-direction` | `"prev"` or `"next"` | Direction indicator |
| Class | `carousel-dot` | Dot indicator |
| `data-slide` | `"[index]"` | Slide index (0-based) |

---

## 4. Scroll Elements

### Back-to-Top Button
**File:** `src/Views/layouts/main.php`

#### Back-to-Top Button
- **ID:** `back-to-top`
- **Classes:** `back-to-top`
- **Type:** `button`
- **ARIA:** 
  - `aria-label="Back to top"`
  - `title="Back to top"`
  - `aria-hidden="true"` (initially)
  - `tabindex="-1"` (initially)

#### Main Content Target
- **ID:** `main-content`
- **Classes:** `container pt-6`
- **Attributes:** `tabindex="-1"`

#### Logo Scroll Detection
**File:** `src/Views/partials/header.php`
- **Logo Image:**
  - `data-scroll-logo="default"`
  - `data-logo-default="[url]"` - logo when at top
  - `data-logo-scroll="[url]"` - logo when scrolled

---

## 5. Product Elements

### Product Search Results
**File:** `src/Views/products/index.php`

#### Search Form
- **Form Class:** `search-form`
- **Grid Class:** `search-form-grid`
- **Buttons Class:** `search-form-buttons`
- **Input Element:** `id="search"` with `data-search-input`
- **Loading Indicator:** `data-search-loading` (hidden by default)
- **Results Container:** `data-search-results`

#### Product Cards
- **Card Container:** `class="card card-grid-hover"`
- **Image Container:** `class="card-image-container"`
- **Image Element:** `class="card-image"` with:
  - `data-lightbox="[url]"`
  - `data-caption="[product-name]"`
- **Content Container:** `class="card-content"`
- **Placeholder:** `class="card-image-placeholder"`

#### Product Display Details
- **Card Title:** `class="card-title"`
- **Card Description:** `class="card-description"`
- **Badge/Category:** Dynamic class based on category
- **Link:** `class="card-link"` with `aria-label`

### Product Lightbox
- **Data Attribute:** `data-lightbox="[url]"`
- **Data Attribute:** `data-caption="[caption]"`
- **Used on:** Images in products, vendors, and markets sections

### Product Grid Layouts
| Location | Class | Purpose |
|---|---|---|
| Products Index | `grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))]` | Product grid layout |
| Vendor Show | `grid grid-cols-[repeat(auto-fill,minmax(200px,1fr))]` | Vendor products grid |
| Markets | `grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))]` | Market cards grid |

---

## 6. Admin & Modal Elements

### Modal Containers
All modals follow the same structure pattern:

#### Modal IDs Found
| Modal ID | Purpose | File |
|---|---|---|
| `createLayoutModal` | Create booth layout | `booth-management.php` |
| `boothEditorModal` | Edit booth details | `booth-layout-editor.php` |
| `assignmentModal` | Assign vendor to booth | `booth-assignment.php` |
| `editAdminModal` | Edit admin user | `market-administrators.php` |
| `rejectModal` | Reject transfer request | `vendor-transfer-requests.php` |
| `vendorActionModal` | Vendor attendance actions | `vendor-attendance.php` |

#### Modal Structure
```html
<div id="[modalId]" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
  <div class="modal-content">
    <h2>Modal Title</h2>
    <form ...>
      <input type="hidden" id="modal[FieldName]" value="">
    </form>
  </div>
</div>
```

#### Modal-Related Input Fields
- `id="modalBoothId"` - Booth ID in editor
- `id="modalBoothNumber"` - Booth number display/edit
- `id="modalBoothX"` - X position
- `id="modalBoothY"` - Y position
- `id="modalBoothWidth"` - Width
- `id="modalBoothHeight"` - Height
- `id="modalBoothZone"` - Zone selector
- `id="modalBoothDescription"` - Description
- `id="modalCsrfToken"` - CSRF token
- `id="modalTransferId"` - Transfer ID
- `id="csrfToken"` - CSRF token (non-modal)

#### Booth/Assignment Elements
- **Booth Card:** `class="booth-card"`
- **Booth Assignment:** Changes from `bg-white border-gray-300` to `bg-brand-primary border-brand-primary`

### Admin Metrics/Cards
**File:** `src/Views/dashboard/admin.php`

#### Metric Card Classes
- `metric-card` - Base class
- `metric-card-pending-vendor` - Pending vendors
- `metric-card-pending-market` - Pending markets
- `metric-card-pending-review` - Pending reviews
- `metric-card-active-vendor` - Active vendors
- `metric-card-active-product` - Active products
- `metric-card-active-market` - Active markets
- `metric-card-inactive-market` - Inactive markets
- `metric-card-secondary` - Secondary metrics
- `metric-card-success` - Success metrics

#### Metric Label
- `class="metric-label"` - Label text

### Admin Button Triggers
Many admin elements use inline `onclick` handlers:

| Element | Trigger Function | File |
|---|---|---|
| Booth Selection | `onclick="selectBooth(id)"` | `booth-layout-editor.php` |
| Booth Layout | `onclick="generateBoothsGrid()"` | `booth-layout-editor.php` |
| Booth Regenerate | `onclick="regenerateBooth()"` | `booth-layout-editor.php` |
| Close Modal | `onclick="closeBoothEditor()"` | `booth-layout-editor.php` |
| Delete Booth | `onclick="deleteBooth()"` | `booth-layout-editor.php` |
| Open Assignment | `onclick="openAssignmentModal(id)"` | `booth-assignment.php` |
| Open Edit Admin | `onclick="openEditAdminModal(id, username, role)"` | `market-administrators.php` |
| Remove Admin | `onclick="removeAdmin(id, username)"` | `market-administrators.php` |
| Filter Status | `onclick="filterByStatus('status')"` | `vendor-attendance.php` |
| Check-in Vendor | `onclick="checkInVendor(vendorId, farmName)"` | `vendor-attendance.php` |
| Undo No-Show | `onclick="undoNoShow(vendorId)"` | `vendor-attendance.php` |
| Mark as No-Show | `onclick="markAsNoShow()"` | `vendor-attendance.php` |
| Mark as Confirmed | `onclick="markAsConfirmed()"` | `vendor-attendance.php` |

---

## 7. Flash Message Elements

### All Flash Message Types
Flash messages use the pattern: `<div class="alert-[type]" data-flash>`

#### Flash Message Classes & Data Attributes
| Class | Data Attribute | Purpose | Typical Locations |
|---|---|---|---|
| `alert-success` | `data-flash` | Success notifications | Forms, applications |
| `alert-error` | `data-flash` | Error notifications | Forms, validations |
| `alert-warning` | `data-flash` | Warning messages | Unverified emails |
| `alert-info` | `data-flash` | Info messages | Application status |
| `alert-rate-limit` | (none) | Rate limit message | Product search |

#### Files Using Flash Messages
- `src/Views/vendors/apply.php` - success/error on application
- `src/Views/auth/login.php` - success/warning/info/error
- `src/Views/auth/reset-password.php` - success/error
- `src/Views/auth/forgot-password.php` - success/error
- `src/Views/auth/resend-verification.php` - success/error
- `src/Views/auth/register.php` - error
- `src/Views/admin/market-applications.php` - success/error
- `src/Views/admin/vendor-applications.php` - success/error
- `src/Views/admin/market-dates.php` - success/error
- `src/Views/admin/market-list.php` - success/error
- `src/Views/admin/market-administrators.php` - info messages
- `src/Views/vendor-dashboard/` - multiple pages
- `src/Views/dashboard/member.php` - warning messages

### Non-Flash Alert Messages
These appear without `data-flash` and are inline static alerts:

- `class="alert-error mb-4"` - Inline error (no auto-dismiss)
- `class="alert-success mb-4"` - Inline success (no auto-dismiss)
- `class="alert-warning"` - Inline warning (email verification)
- `class="alert-warning mb-6"` - Error summary display
- `class="alert-info"` - Info without auto-dismiss

---

## 8. Additional Selectors Found

### Market Calendar
- **Data Attribute:** `data-market-calendar`
- **Location:** Markets index and vendor dashboard
- **Files:** 
  - `src/Views/markets/index.php`
  - `src/Views/vendor-dashboard/index.php`

### Action Buttons Used with onclick
| Button Type | Example | Used For |
|---|---|---|
| Save Actions | `id="saveVendorBtn"` onclick="saveVendor(id)" | Vendor saves |
| Delete Actions | Various onclick="deleteVendorPhoto()" | Photo deletion |
| Approve/Reject | onclick="approveTransfer(...)" | Admin approvals |
| Check-in | onclick="checkInVendor(...)" | Attendance |

### Data Attributes by Category
| Category | Data Attributes |
|---|---|
| Navigation | `data-dropdown`, `data-menu`, `data-menu-toggle`, `data-nav` |
| Carousel | `data-carousel`, `data-direction`, `data-slide` |
| Forms | `data-autosave`, `data-search-input`, `data-search-loading`, `data-search-results` |
| Modals | `data-market-calendar` |
| Images | `data-lightbox`, `data-caption` |
| Scroll | `data-scroll-logo`, `data-logo-default`, `data-logo-scroll` |
| Flash | `data-flash` |

---

## 9. Selector Consistency Check

### Potential Issues & Recommendations

#### HTML vs JavaScript Mismatch Areas
1. **Navigation**: JavaScript likely looks for `data-dropdown` and `data-menu` selectors
   - ✓ Present in HTML with proper structure
   
2. **Carousel**: Uses `data-carousel`, `data-direction`, `data-slide` selectors
   - ✓ Present but verify button indices match slide count
   
3. **Flash Messages**: Uses `data-flash` attribute
   - ✓ Consistently applied across forms
   
4. **Search Forms**: Uses `data-search-input`, `data-search-loading`, `data-search-results`
   - ✓ All present in product search
   
5. **Modals**: Modal IDs should match JavaScript selectors
   - Verify each modal ID in JavaScript matches HTML IDs
   
6. **Admin Inline Handlers**: Heavy reliance on `onclick` handlers
   - These need corresponding JavaScript functions to exist

### Missing Selectors Found
No critical missing selectors identified. However, the following should be verified:

1. Modal functions: Ensure all `onclick="functionName(...)"` have corresponding handlers
2. Image lightbox: Verify lightbox library is properly initialized for `data-lightbox` attributes
3. Carousel indices: Verify carousel dots are using correct 0-based indexing

---

## 10. Summary Statistics

| Category | Count | Status |
|---|---|---|
| Navigation Data Attributes | 7 | ✓ Complete |
| Carousel Selectors | 8 | ✓ Complete |
| Form Fields | 30+ | ✓ Complete |
| Form Error IDs | 9+ | ✓ Complete |
| Modal IDs | 6 | ✓ Complete |
| Modal Input Fields | 8+ | ✓ Complete |
| Flash Message Classes | 5 | ✓ Complete |
| Admin Inline Functions | 20+ | ⚠ Verify handlers exist |
| Lightbox Images | 6+ locations | ✓ Present |
| Filter/Search Selectors | 5 | ✓ Complete |

---

## 11. Files Analyzed

- `src/Views/partials/header.php`
- `src/Views/partials/form-field.php`
- `src/Views/home/index.php`
- `src/Views/products/index.php`
- `src/Views/products/show.php`
- `src/Views/vendors/index.php`
- `src/Views/vendors/show.php`
- `src/Views/vendors/apply.php`
- `src/Views/markets/index.php`
- `src/Views/admin/booth-management.php`
- `src/Views/admin/booth-layout-editor.php`
- `src/Views/admin/booth-assignment.php`
- `src/Views/admin/market-administrators.php`
- `src/Views/admin/vendor-transfer-requests.php`
- `src/Views/admin/vendor-attendance.php`
- `src/Views/auth/login.php`
- `src/Views/auth/register.php`
- `src/Views/auth/reset-password.php`
- `src/Views/dashboard/admin.php`
- `src/Views/layouts/main.php`
- And 20+ additional view files

---

## References

This analysis can be used to:
1. Debug JavaScript selector mismatches
2. Verify form validation is working correctly
3. Ensure modal functionality is properly mapped
4. Validate carousel implementation
5. Check navigation dropdown functionality
6. Verify flash message display logic
