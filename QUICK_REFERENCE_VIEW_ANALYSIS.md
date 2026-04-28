# QUICK REFERENCE: View File Analysis Summary
**Blue Ridge Farmers Collective PHP Views**  
**Format:** FILE | DOCUMENTED | VARIABLES | MISSING | RECOMMENDATION

---

## 🔴 CRITICAL ISSUES

FILE: src/Views/admin/booth-assignment.php
DOCUMENTED: no
VARIABLES: $title, $marketDate, $message, $error, $marketDates, $selectedDate, $layout, $booths, $assignments, $pendingVendors, $vendorOptions
MISSING: $marketDate (used on line 5, NOT passed from controller)
RECOMMENDATION: Add `'marketDate' => $selectedDate` to AdminController::boothAssignment() render call. Variable is referenced but undefined, causing potential "Unknown" placeholder display.

---

## 📋 ADMIN VIEWS (23 files)

FILE: src/Views/admin/analytics.php
DOCUMENTED: no
VARIABLES: $title, $stats, $topSearchedProducts
MISSING: $stats (array structure), $topSearchedProducts
RECOMMENDATION: Add @var documentation block specifying $stats keys: total_vendors, active_vendors, active_markets, total_market_dates, total_products, total_vendors_with_products, total_reviews, avg_rating

---

FILE: src/Views/admin/booth-assignment.php
DOCUMENTED: no
VARIABLES: $title, $marketDate, $message, $error, $marketDates, $selectedDate, $layout, $booths, $assignments, $pendingVendors, $vendorOptions
MISSING: $marketDate (CRITICAL - used but not passed)
RECOMMENDATION: **FIX IMMEDIATELY** - Pass $marketDate from controller

---

FILE: src/Views/admin/booth-layout-editor.php
DOCUMENTED: no
VARIABLES: $title, $message, $error, $layout, $market, $booths
MISSING: $layout (array with name_mla, booth_count_mla), $market (array with name_mkt, id_mkt), $booths (array with id_blo, x_position_blo, y_position_blo, width_blo, height_blo, number_blo)
RECOMMENDATION: Document complex data structures for layout editor API integration

---

FILE: src/Views/admin/booth-management.php
DOCUMENTED: no
VARIABLES: $title, $message, $error, $markets
MISSING: $markets (array with layouts nested structure)
RECOMMENDATION: Document $markets array structure including nested 'layouts' key with booth_count_mla, is_active_mla, id_mla, name_mla

---

FILE: src/Views/admin/manage-admins.php
DOCUMENTED: no
VARIABLES: $title, $admins, $message, $error, $pagination
MISSING: $admins, $pagination (array with pages, perPage keys)
RECOMMENDATION: Document admin array structure and pagination format

---

FILE: src/Views/admin/market-administrators.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file for complete variable list

---

FILE: src/Views/admin/market-applications.php
DOCUMENTED: no
VARIABLES: $title, $message, $error, $applications
MISSING: $applications (array with farm_name_ven, username_acc, name_mkt, city_mkt, state_mkt, applied_date_venmkt, id_venmkt)
RECOMMENDATION: Document $applications array with vendor and market data keys

---

FILE: src/Views/admin/market-create.php
DOCUMENTED: no
VARIABLES: $title, $old, $errors
MISSING: None identified
RECOMMENDATION: Good null coalescing pattern - follows best practices

---

FILE: src/Views/admin/market-date-create.php
DOCUMENTED: no
VARIABLES: $title, $markets, $errors, $old
MISSING: $markets (array)
RECOMMENDATION: Document $markets array structure

---

FILE: src/Views/admin/market-date-edit.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file for complete variable list

---

FILE: src/Views/admin/market-dates.php
DOCUMENTED: no
VARIABLES: $title, $message, $error, $dates, $isAdminPage
MISSING: $dates (complex array with date_mda, id_mda, name_mkt, city_mkt, state_mkt, id_mkt, start_time_mda, end_time_mda, location_mda)
RECOMMENDATION: Document $dates array structure with all expected fields from market date view

---

FILE: src/Views/admin/market-edit.php
DOCUMENTED: no
VARIABLES: $title, $errors, $market, $old
MISSING: $market (array with id_mkt, name_mkt, city_mkt, state_mkt, slug_mkt, and more)
RECOMMENDATION: Document complete $market array structure

---

FILE: src/Views/admin/market-list.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file for complete variable list

---

FILE: src/Views/admin/notification-settings.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file for complete variable list

---

FILE: src/Views/admin/product-management.php
DOCUMENTED: no
VARIABLES: $title, $message, $error, $products
MISSING: $products (array with name_prd, farm_name_ven, category_name, is_active_prd, id_prd)
RECOMMENDATION: Document $products array structure

---

FILE: src/Views/admin/product-photo-upload.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file for complete variable list

---

FILE: src/Views/admin/review-management.php
DOCUMENTED: no
VARIABLES: $title, $message, $error, $stats, $reviews
MISSING: $stats (array with pending, approved, total), $reviews (array with farm_name_ven, customer_name_vre, username_acc, rating_vre, is_approved_vre, is_featured_vre, is_verified_purchase_vre)
RECOMMENDATION: Document stats and reviews array structures

---

FILE: src/Views/admin/vendor-application.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file for complete variable list

---

FILE: src/Views/admin/vendor-applications.php
DOCUMENTED: no
VARIABLES: $title, $message, $error, $applications
MISSING: $applications (complex array with farm_name_ven, username_acc, email_acc, city_ven, state_ven, applied_date_ven, years_in_operation_ven, primary_categories_ven [JSON], production_methods_ven [JSON], food_safety_info_ven, photo_path_ven, id_ven)
RECOMMENDATION: Document $applications array with all keys, noting JSON-encoded fields (primary_categories_ven, production_methods_ven need json_decode())

---

FILE: src/Views/admin/vendor-attendance.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file for complete variable list

---

FILE: src/Views/admin/vendor-management.php
DOCUMENTED: no
VARIABLES: $title, $message, $error, $vendors
MISSING: $vendors (array with farm_name_ven, city_ven, state_ven, product_count, avg_rating, application_status_ven, is_featured_ven, id_ven)
RECOMMENDATION: Document $vendors array structure with expected keys

---

FILE: src/Views/admin/vendor-photo-upload.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file for complete variable list

---

FILE: src/Views/admin/vendor-transfer-requests.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file for complete variable list

---

## 🔐 AUTH VIEWS (5 files)

FILE: src/Views/auth/forgot-password.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/auth/login.php
DOCUMENTED: no
VARIABLES: $title, $message, $warning, $info, $errors, $old
MISSING: None identified
RECOMMENDATION: Good pattern - follows security best practices with null coalescing

---

FILE: src/Views/auth/register.php
DOCUMENTED: no
VARIABLES: $title, $errors, $old
MISSING: None identified
RECOMMENDATION: Good pattern - standard form variables

---

FILE: src/Views/auth/resend-verification.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/auth/reset-password.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

## 📊 DASHBOARD VIEWS (3 files)

FILE: src/Views/dashboard/admin.php
DOCUMENTED: no
VARIABLES: $title, $user, $dataRefreshedAt, $metrics, $vendorTrend
MISSING: $metrics (array with pending_vendors, pending_market_apps, pending_reviews, active_vendors, active_products, markets_count, market_issues), $vendorTrend (int)
RECOMMENDATION: Document $metrics array structure with all dashboard metric keys

---

FILE: src/Views/dashboard/member.php
DOCUMENTED: no
VARIABLES: $title, $user, $warning, $metrics, $savedVendors
MISSING: $metrics (array with saved_vendors, upcoming_markets), $savedVendors (array with slug, name, location)
RECOMMENDATION: Document $metrics and $savedVendors array structures

---

FILE: src/Views/dashboard/super-admin.php
DOCUMENTED: no
VARIABLES: $title, $user, $metrics
MISSING: $metrics (array with admin_count)
RECOMMENDATION: Document $metrics structure

---

## 🏠 HOME VIEWS (6 files)

FILE: src/Views/home/about.php
DOCUMENTED: no
VARIABLES: $title, $stats, $highlights
MISSING: $stats (array with markets, vendors, products), $highlights (array of strings)
RECOMMENDATION: Document $stats and $highlights array structures

---

FILE: src/Views/home/contact.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/home/faq.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/home/index.php
DOCUMENTED: no
VARIABLES: $title, $topVendors
MISSING: $topVendors (array with photo_path_ven, farm_name_ven, city_ven, state_ven, is_featured_ven, avg_rating, product_count)
RECOMMENDATION: Document $topVendors array structure

---

FILE: src/Views/home/privacy.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/home/terms.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

## 📦 PRODUCTS VIEWS (2 files)

FILE: src/Views/products/index.php
DOCUMENTED: no
VARIABLES: $title, $rate_limit_error, $categories, $vendors
MISSING: $rate_limit_error (bool), $categories (array with id_pct, name_pct), $vendors (array with id_ven, farm_name_ven)
RECOMMENDATION: Document $categories and $vendors array structures

---

FILE: src/Views/products/show.php
DOCUMENTED: no
VARIABLES: $title, $product
MISSING: $product (array with photo, name, category, vendor, vendor_slug, description)
RECOMMENDATION: Document $product array structure

---

## 🏪 MARKETS VIEWS (2 files)

FILE: src/Views/markets/index.php
DOCUMENTED: no
VARIABLES: $title, $markets, $pagination
MISSING: $markets (array with id_mkt, name_mkt, city_mkt, state_mkt, slug_mkt, hero_image_path_mkt, is_active_mkt), $pagination (array with page, pages, perPage)
RECOMMENDATION: Document $markets and $pagination array structures

---

FILE: src/Views/markets/show.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

## 👨‍🌾 VENDORS VIEWS (3 files)

FILE: src/Views/vendors/apply.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/vendors/index.php
DOCUMENTED: no
VARIABLES: $title, $vendors
MISSING: $vendors (array with slug, name, location, photo, featured)
RECOMMENDATION: Document $vendors array structure

---

FILE: src/Views/vendors/show.php
DOCUMENTED: YES ✓
VARIABLES: $title, $vendor, $products, $markets, $reviews, $authUser
MISSING: None - well documented
RECOMMENDATION: **FOLLOW THIS PATTERN** - Excellent example with complete @var documentation block

---

## 🎨 PARTIALS/LAYOUTS (8 files)

FILE: src/Views/layouts/main.php
DOCUMENTED: no
VARIABLES: $content, $title
MISSING: None identified (uses null coalescing)
RECOMMENDATION: Good null coalescing pattern

---

FILE: src/Views/partials/footer.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/partials/form-field.php
DOCUMENTED: YES ✓
VARIABLES: $name, $label, $type, $value, $errors, $required, $attributes, $pattern, $minlength, $maxlength, $min, $max, $spellcheck
MISSING: None - comprehensive documentation
RECOMMENDATION: **FOLLOW THIS PATTERN** - Best practice example with clear variable documentation

---

FILE: src/Views/partials/header.php
DOCUMENTED: no
VARIABLES: $user, $currentPath, $primaryLinks, $exploreLinks, $accountLinks, $displayName
MISSING: None identified - $user from SESSION
RECOMMENDATION: Document expected structure of header links

---

FILE: src/Views/partials/pagination.php
DOCUMENTED: YES ✓
VARIABLES: $pagination, $baseUrlBuilder, $ariaLabel
MISSING: None - well documented
RECOMMENDATION: **FOLLOW THIS PATTERN** - Good documentation with usage example

---

FILE: src/Views/partials/vendor-card.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/errors/403.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/errors/404.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/errors/500.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

## 👥 VENDOR DASHBOARD VIEWS (15 files)

FILE: src/Views/vendor-dashboard/analytics.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/vendor-dashboard/attendance-history.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/vendor-dashboard/booth-assignment.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/vendor-dashboard/index.php
DOCUMENTED: no
VARIABLES: $title, $user, $vendor, $checklist, $reviewStats, $reviews
MISSING: $vendor (array), $checklist (array with completion flags), $reviewStats (array with total, average_rating, pending), $reviews (array)
RECOMMENDATION: Document all complex variable structures

---

FILE: src/Views/vendor-dashboard/market-apply.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/vendor-dashboard/market-history.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/vendor-dashboard/notification-preferences.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/vendor-dashboard/product-create.php
DOCUMENTED: no
VARIABLES: $title, $errors, $spellWarnings, $old, $categories
MISSING: $spellWarnings (SESSION array), $categories (array with id_pct, name_pct)
RECOMMENDATION: Document $spellWarnings structure and $categories array

---

FILE: src/Views/vendor-dashboard/product-edit.php
DOCUMENTED: no
VARIABLES: $title, $errors, $spellWarnings, $product, $old, $categories
MISSING: $product (array with id_prd, name_prd, id_pct_prd), $categories (array)
RECOMMENDATION: Document $product and $categories array structures

---

FILE: src/Views/vendor-dashboard/product-show.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/vendor-dashboard/products-index.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/vendor-dashboard/reviews.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/vendor-dashboard/select-market-dates.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/vendor-dashboard/transfer-history.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

FILE: src/Views/vendor-dashboard/transfer-request.php
DOCUMENTED: no
VARIABLES: TBD
MISSING: TBD
RECOMMENDATION: Analyze file

---

## 📊 STATISTICS SUMMARY

| Category | Count | Documented | Missing Docs |
|----------|-------|-----------|--------------|
| Admin Views | 23 | 0 | 23 |
| Auth Views | 5 | 0 | 5 |
| Dashboard Views | 3 | 0 | 3 |
| Home Views | 6 | 0 | 6 |
| Products Views | 2 | 0 | 2 |
| Markets Views | 2 | 0 | 2 |
| Vendors Views | 3 | 1 | 2 |
| Partials/Layouts | 8 | 2 | 6 |
| Vendor Dashboard | 15 | 0 | 15 |
| **TOTAL** | **68** | **3** | **65** |

---

## 🎯 BEST PRACTICE EXAMPLES (COPY THIS PATTERN)

### Pattern 1: form-field.php
```php
<?php
/**
 * Reusable form field component
 * 
 * Usage:
 * <?php require __DIR__ . '/form-field.php'; ?>
 * 
 * Variables:
 * - $name (required): field name and id
 * - $label (required): field label text
 * - $type: input type (default: 'text')
 * - $value: current value
 * - $errors: array of validation errors
 * - $required: whether field is required
 */
```

### Pattern 2: pagination.php
```php
<?php
/**
 * Reusable pagination component
 * 
 * Variables:
 * - $pagination (required): array with 'page' and 'pages' keys
 * - $baseUrlBuilder (required): callable that takes page number
 * - $ariaLabel: aria-label for nav (default: 'Pagination')
 */
```

### Pattern 3: vendors/show.php
```php
<?php
/**
 * Vendor Detail View
 * 
 * @var string $title
 * @var array $vendor
 * @var array $products
 * @var array $markets
 * @var array $reviews
 * @var array|null $authUser
 */
```

---

## ✅ ACTION ITEMS (PRIORITY)

### 🔴 IMMEDIATE (This Week)
- [ ] Fix booth-assignment.php: Add missing `$marketDate` variable to controller
- [ ] Test that fix to ensure no undefined variable warnings

### 🟡 HIGH (This Sprint)
- [ ] Add documentation blocks to all 23 admin views
- [ ] Focus on complex data structures: $stats, $metrics, $applications, $dates, $vendors, $products
- [ ] Add documentation to 15 vendor-dashboard views

### 🟢 MEDIUM (Next Sprint)
- [ ] Add documentation to remaining 65 undocumented files
- [ ] Standardize documentation format across all views
- [ ] Use pagination.php and form-field.php as templates

### 💡 NICE TO HAVE (Future)
- [ ] Consider type-hinting for complex view data
- [ ] Implement view data validation layer
- [ ] Add automated checks to prevent undefined variables

---

## 📝 DOCUMENTATION TEMPLATE

Use this template for all new view documentation blocks:

```php
<?php
/**
 * View: brief-description
 * 
 * Description of what this view displays and its purpose.
 * 
 * Variables:
 * @var string $title - Page title
 * @var array $items - Array of item objects with keys: id, name, description
 * @var array $errors - Form validation errors (optional)
 * @var array $old - Previous form input values (optional)
 * @var bool $isAdmin - Whether current user is admin (optional)
 */
?>
```

---

## CONCLUSION

✅ **Functional Quality:** GOOD - Variables are passed correctly from controllers  
⚠️ **Documentation Quality:** POOR - 96% of views lack documentation  
📊 **IDE Support:** LIMITED - Missing @var blocks prevent autocomplete  
🔧 **Maintainability:** MEDIUM - New developers struggle without variable documentation  

**Recommended Action:** Implement Priority 1 and 2 items within this sprint to improve code quality and developer experience.
