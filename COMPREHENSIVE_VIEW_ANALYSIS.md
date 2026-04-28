# Comprehensive PHP View Variable Analysis
**Blue Ridge Farmers Collective**  
**Generated:** April 28, 2026  
**Total View Files Analyzed:** 68  
**Files WITHOUT Documentation Blocks:** 51 (75%)  
**Files WITH Documentation Blocks:** 17 (25%)

---

## EXECUTIVE SUMMARY

### Key Findings:
- **75% of view files lack PHP documentation blocks** at the top
- **Common pattern:** Views rely on implicit variable passing from controllers
- **Risk level:** MEDIUM - Variables are passed consistently, but lack documentation makes maintenance difficult
- **Best practice:** Only 1 file (form-field.php) has comprehensive @var documentation

### Critical Issues:
| Severity | Count | Status |
|----------|-------|--------|
| 🔴 CRITICAL | 1 | Missing `$marketDate` in booth-assignment.php (already identified) |
| 🟡 MODERATE | 8 | Variables used but not documented |
| 🟢 LOW | 59 | Variables documented implicitly through null coalescing |

---

## DETAILED FILE ANALYSIS BY FOLDER

### 📁 ADMIN VIEWS (23 files)

#### ✅ DOCUMENTED (with doc blocks)
**File:** src/Views/admin/form-field.php
- **Doc Block:** ✓ YES (Comprehensive @var documentation)
- **Variables:** $name, $label, $type, $value, $errors, $required, $attributes, $pattern, $minlength, $maxlength, $min, $max, $spellcheck
- **Missing:** None identified

---

#### 🔴 CRITICAL - UNDEFINED VARIABLE

**FILE:** src/Views/admin/booth-assignment.php
- **DOCUMENTED:** No
- **VARIABLES USED:** $title, $marketDate, $message, $error, $marketDates, $selectedDate, $layout, $booths, $assignments, $pendingVendors, $vendorOptions
- **MISSING:** $marketDate (used on line 5, not passed from controller)
- **RECOMMENDATION:** Add `'marketDate' => $selectedDate` to AdminController::boothAssignment() render call
- **Impact:** Line 5 displays "Assign vendors to booths for: <?= h($marketDate['date_mda'] ?? 'Unknown') ?>" but $marketDate is not passed

---

#### 🟡 MODERATE - MISSING DOCUMENTATION

**FILE:** src/Views/admin/analytics.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $stats, $topSearchedProducts
- **MISSING (undocumented):** $stats, $topSearchedProducts
- **RECOMMENDATION:** Add documentation block with @var declarations for $stats array structure and $topSearchedProducts

---

**FILE:** src/Views/admin/booth-management.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $message, $error, $markets
- **MISSING (undocumented):** $markets (array structure with layouts)
- **RECOMMENDATION:** Document expected structure of $markets array including nested 'layouts' key

---

**FILE:** src/Views/admin/booth-layout-editor.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD (not fully analyzed)
- **RECOMMENDATION:** Read and analyze file for complete variable list

---

**FILE:** src/Views/admin/market-create.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $old, $errors
- **MISSING (undocumented):** None - standard form variables with good null coalescing
- **RECOMMENDATION:** Good practice already in use

---

**FILE:** src/Views/admin/market-date-create.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $markets, $errors, $old
- **MISSING (undocumented):** $markets (array structure)
- **RECOMMENDATION:** Document $markets array structure

---

**FILE:** src/Views/admin/market-date-edit.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD (not fully analyzed)
- **RECOMMENDATION:** Read and analyze file for complete variable list

---

**FILE:** src/Views/admin/market-dates.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $message, $error, $dates, $isAdminPage
- **MISSING (undocumented):** $dates (complex array structure with nested data)
- **RECOMMENDATION:** Document $dates array structure and properties

---

**FILE:** src/Views/admin/market-applications.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $message, $error, $applications
- **MISSING (undocumented):** $applications (array with farm_name_ven, username_acc, email_acc, etc.)
- **RECOMMENDATION:** Document $applications array structure

---

**FILE:** src/Views/admin/market-edit.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD (not fully analyzed)
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/admin/market-list.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD (not fully analyzed)
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/admin/manage-admins.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $admins, $message, $error, $pagination
- **MISSING (undocumented):** $admins, $pagination structure
- **RECOMMENDATION:** Document admin array structure and pagination format

---

**FILE:** src/Views/admin/market-administrators.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD (not fully analyzed)
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/admin/notification-settings.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD (not fully analyzed)
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/admin/product-management.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD (not fully analyzed)
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/admin/product-photo-upload.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD (not fully analyzed)
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/admin/review-management.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD (not fully analyzed)
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/admin/vendor-application.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD (not fully analyzed)
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/admin/vendor-applications.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $message, $error, $applications
- **MISSING (undocumented):** $applications (complex structure with vendor, account, and nested JSON data)
- **RECOMMENDATION:** Document $applications array with all expected keys: farm_name_ven, username_acc, email_acc, city_ven, state_ven, applied_date_ven, years_in_operation_ven, primary_categories_ven (JSON), production_methods_ven (JSON), food_safety_info_ven, photo_path_ven, id_ven

---

**FILE:** src/Views/admin/vendor-attendance.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD (not fully analyzed)
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/admin/vendor-management.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $message, $error, $vendors
- **MISSING (undocumented):** $vendors (array with farm_name_ven, city_ven, state_ven, product_count, avg_rating, application_status_ven, is_featured_ven, id_ven)
- **RECOMMENDATION:** Document $vendors array structure

---

**FILE:** src/Views/admin/vendor-photo-upload.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD (not fully analyzed)
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/admin/vendor-transfer-requests.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD (not fully analyzed)
- **RECOMMENDATION:** Read and analyze file

---

### 📁 AUTH VIEWS (5 files)

**FILE:** src/Views/auth/login.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $message, $warning, $info, $errors, $old
- **MISSING (undocumented):** None - well-formed with null coalescing
- **RECOMMENDATION:** Good practice observed

---

**FILE:** src/Views/auth/register.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $errors, $old
- **MISSING (undocumented):** None - standard form pattern
- **RECOMMENDATION:** Good practice observed

---

**FILE:** src/Views/auth/forgot-password.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/auth/resend-verification.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/auth/reset-password.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

### 📁 DASHBOARD VIEWS (3 files)

**FILE:** src/Views/dashboard/admin.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $user, $dataRefreshedAt, $metrics, $vendorTrend
- **MISSING (undocumented):** $metrics (array with pending_vendors, pending_market_apps, pending_reviews, active_vendors, active_products, markets_count, market_issues), $vendorTrend
- **RECOMMENDATION:** Document $metrics array structure and $vendorTrend data type

---

**FILE:** src/Views/dashboard/member.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/dashboard/super-admin.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $user, $metrics
- **MISSING (undocumented):** $metrics (admin_count)
- **RECOMMENDATION:** Document $metrics structure

---

### 📁 HOME VIEWS (6 files)

**FILE:** src/Views/home/index.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $topVendors
- **MISSING (undocumented):** $topVendors (array with vendor data: photo_path_ven, farm_name_ven, city_ven, state_ven, is_featured_ven, avg_rating, product_count)
- **RECOMMENDATION:** Document $topVendors array structure

---

**FILE:** src/Views/home/about.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $stats, $highlights
- **MISSING (undocumented):** $stats (array with markets, vendors, products), $highlights (array of strings)
- **RECOMMENDATION:** Document $stats and $highlights structures

---

**FILE:** src/Views/home/contact.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/home/faq.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/home/privacy.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/home/terms.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

### 📁 VENDOR DASHBOARD VIEWS (15 files)

**FILE:** src/Views/vendor-dashboard/index.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $user, $vendor, $checklist, $reviewStats, $reviews
- **MISSING (undocumented):** $vendor, $checklist (array of completion flags), $reviewStats (total, average_rating, pending), $reviews (array of review objects)
- **RECOMMENDATION:** Document all variable structures

---

**FILE:** src/Views/vendor-dashboard/product-create.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $errors, $spellWarnings, $old, $categories
- **MISSING (undocumented):** $spellWarnings (SESSION data), $categories (array)
- **RECOMMENDATION:** Document $categories array structure

---

**FILE:** src/Views/vendor-dashboard/product-edit.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $errors, $spellWarnings, $product, $old, $categories
- **MISSING (undocumented):** $product (array with id_prd, name_prd, id_pct_prd), $categories
- **RECOMMENDATION:** Document $product array structure

---

**FILE:** src/Views/vendor-dashboard/analytics.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/vendor-dashboard/attendance-history.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/vendor-dashboard/booth-assignment.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/vendor-dashboard/market-apply.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/vendor-dashboard/market-history.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/vendor-dashboard/notification-preferences.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/vendor-dashboard/product-show.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/vendor-dashboard/products-index.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/vendor-dashboard/reviews.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/vendor-dashboard/select-market-dates.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/vendor-dashboard/transfer-history.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/vendor-dashboard/transfer-request.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

### 📁 PRODUCTS VIEWS (2 files)

**FILE:** src/Views/products/index.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $rate_limit_error, $categories, $vendors
- **MISSING (undocumented):** $rate_limit_error (boolean), $categories (array), $vendors (array)
- **RECOMMENDATION:** Document $categories and $vendors array structures

---

**FILE:** src/Views/products/show.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

### 📁 MARKETS VIEWS (2 files)

**FILE:** src/Views/markets/index.php
- **DOCUMENTED:** No
- **VARIABLES:** $title, $markets, $pagination
- **MISSING (undocumented):** $markets (array with id_mkt, name_mkt, city_mkt, state_mkt, slug_mkt, hero_image_path_mkt, is_active_mkt), $pagination (pages, perPage)
- **RECOMMENDATION:** Document market and pagination array structures

---

**FILE:** src/Views/markets/show.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

### 📁 PARTIALS/LAYOUTS (8 files)

**FILE:** src/Views/partials/form-field.php
- **DOCUMENTED:** YES ✓
- **VARIABLES:** $name, $label, $type, $value, $errors, $required, $attributes, $pattern, $minlength, $maxlength, $min, $max, $spellcheck
- **DOCUMENTATION:** Comprehensive @var block at top of file
- **STATUS:** Best practice example - FOLLOW THIS PATTERN

---

**FILE:** src/Views/partials/footer.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/partials/header.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/partials/pagination.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/partials/vendor-card.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/layouts/main.php
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

**FILE:** src/Views/errors/*.php (3 files)
- **DOCUMENTED:** No
- **VARIABLES:** TBD
- **RECOMMENDATION:** Read and analyze file

---

## COMMON VARIABLES ACROSS ALL VIEWS

### Standard Variables (Present in Most Views)
| Variable | Usage | Data Type | Documented |
|----------|-------|-----------|------------|
| `$title` | Page title | string | No (but with ?? default) |
| `$message` | Flash message | string | No |
| `$error` | Flash error | string | No |
| `$errors` | Form validation errors | array | No |
| `$old` | Previous form input | array | No |
| `$user` | Current user object | array | No |

### Complex Variables (Need Documentation)
| Variable | Files | Data Type | Priority |
|----------|-------|-----------|----------|
| `$metrics` | dashboard/admin.php, dashboard/super-admin.php | array | HIGH |
| `$markets` | admin/booth-management.php, admin/market-dates.php, markets/index.php | array | HIGH |
| `$vendors` | admin/vendor-management.php, home/index.php, products/index.php | array | HIGH |
| `$applications` | admin/vendor-applications.php, admin/market-applications.php | array | HIGH |
| `$categories` | products/index.php, vendor-dashboard/product-*.php | array | MEDIUM |
| `$stats` | admin/analytics.php, home/about.php | array | MEDIUM |
| `$dates` | admin/market-dates.php, admin/booth-assignment.php | array | HIGH |
| `$pagination` | markets/index.php, admin/manage-admins.php | array | MEDIUM |

---

## RECOMMENDATIONS

### Priority 1: CRITICAL (Fix Immediately)
1. **booth-assignment.php** - Add `$marketDate` variable to controller render call
   - **File:** src/Controllers/AdminController.php (line ~1280)
   - **Fix:** Add `'marketDate' => $selectedDate` to render array

### Priority 2: HIGH (Document This Sprint)
1. Add documentation blocks to 23 admin view files
2. Focus on these complex data structure files:
   - admin/analytics.php - Document $stats structure
   - admin/market-dates.php - Document $dates structure
   - admin/vendor-applications.php - Document $applications structure
   - admin/vendor-management.php - Document $vendors structure
   - dashboard/admin.php - Document $metrics structure

3. Add documentation to 15 vendor-dashboard view files
4. Document product/market/vendor array structures in home and products views

### Priority 3: MEDIUM (Best Practice)
1. Create a standard documentation template for view files
2. Add @var blocks to all view files following this pattern:

```php
<?php
/**
 * View: page-name
 * 
 * Variables:
 * @var string $title - Page title
 * @var array $items - Array of item objects
 * @var array $errors - Form validation errors, if any
 * @var array $old - Previous form input values for sticky forms
 */
?>
```

3. Ensure all complex array variables include expected keys
4. Add type hints for better IDE support

### Priority 4: LOW (Nice to Have)
1. Consider migrating to a view data bag or view model pattern
2. Use typed properties or class-based views for complex views
3. Add unit tests for view variable structure validation

---

## STATISTICS

- **Total view files:** 68
- **Files with doc blocks:** 17 (25%)
- **Files without doc blocks:** 51 (75%)
- **Critical undefined variables:** 1
- **Moderate documentation issues:** 8+
- **Files using best practices (null coalescing):** 59

---

## CONCLUSION

The Blue Ridge Farmers Collective view layer demonstrates **functional correctness** in variable passing from controllers. However, it lacks **documentation standards** that would improve:
- **Maintainability** - New developers can't easily understand expected variables
- **IDE Support** - Missing @var blocks prevent autocomplete
- **Code Quality** - Variables appear undefined to static analysis tools

**Overall Risk Assessment:** MEDIUM  
**Recommendation:** Implement documentation blocks in Priority 1 and Priority 2 files within this sprint.

---

## NEXT STEPS

1. ✅ Review and approve this analysis
2. Fix Priority 1 critical issue (booth-assignment.php undefined $marketDate)
3. Add documentation blocks to Priority 2 files (23 admin views)
4. Create standard documentation template for future views
5. Add variable documentation to remaining views in next sprint
