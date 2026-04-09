# JavaScript Modules Documentation

This directory contains modular JavaScript code organized by feature/responsibility.

## Structure

```
public/js/
├── main.js           # Entry point - Orchestrates module initialization
├── modules/
│   ├── navigation.js  # Mobile menu and dropdown management
│   ├── forms.js       # Form validation and star rating widgets
│   ├── products.js    # Product filtering and search functionality
│   ├── scroll.js      # Scroll effects and animations
│   ├── utils.js       # Common utility functions
│   └── README.md      # This file
```

## Modules

### `main.js`
**Entry point** - Imports all modules and initializes them on `DOMContentLoaded`.

- Size: ~37 lines (previously 1739 lines!)
- Reduces global scope pollution
- Clean module orchestration pattern

### `modules/navigation.js`
**Navigation Module** - Handles menu interactions and keyboard navigation.

**Features:**
- Mobile menu toggle with accessibility
- Dropdown menu management
- Keyboard navigation (Arrow keys, Home, End, Escape)
- Click-outside to close
- Auto-close on window resize

**Usage:**
```javascript
Navigation.init();
```

### `modules/forms.js`
**Forms Module** - Real-time form validation and interactive form elements.

**Features:**
- Field validation (email, URL, phone, text, number, password)
- Real-time validation on blur
- Error display with ARIA attributes
- Star rating widget with keyboard nav
- Comprehensive error handling

**Usage:**
```javascript
Forms.init();
```

**Data Attributes:**
- `data-validate` on forms - Enables validation
- `data-rating-stars` on containers - Star rating widget

### `modules/products.js`
**Products Module** - Product filtering, search, and sorting.

**Features:**
- Live search with debouncing
- Category filtering
- Vendor filtering
- Market filtering
- Product sorting (newest, name A-Z)
- Results counter
- No-results messaging

**Usage:**
```javascript
Products.init();
```

**Data Attributes:**
- `data-search-products` on input - Search field
- `data-filter-category/vendor/market` on selects - Filter controls
- `data-sort-products` on select - Sort control
- `data-products-container` on container - Product list
- `data-product-id` on product cards - Product identifier
- `data-product-name/description/vendor` on elements - Search targets

### `modules/scroll.js`
**Scroll Effects Module** - Scroll-related visual effects.

**Features:**
- Sticky header on scroll
- Back-to-top button visibility toggle
- Smooth scroll animations

**Usage:**
```javascript
ScrollEffects.init();
```

**CSS Classes:**
- `is-scrolled` - Applied to header when scrolled

### `modules/utils.js`
**Utilities Module** - Common functions and utilities.

**Exports:**
- `debounce(func, delay)` - Rate-limit function calls
- `initFlashMessages()` - Auto-dismiss flash notifications

**Usage:**
```javascript
import { debounce, initFlashMessages } from './modules/utils.js';
```

## Benefits

✅ **Separation of Concerns** - Each module has single responsibility  
✅ **Improved Maintainability** - Easier to find and update features  
✅ **Reduced Global Scope** - No global function pollution  
✅ **Better Code Organization** - Clear module boundaries  
✅ **Easier Testing** - Modules can be tested independently  
✅ **Code Reusability** - Utility functions easily importable  
✅ **Performance** - Can lazy-load modules as needed  
✅ **Reduced Complexity** - main.js went from 1739 → 37 lines

## Module Pattern

Each module uses the **Module Pattern** with an initialization function:

```javascript
export const ModuleName = (() => {
  let isInitialized = false;

  const init = () => {
    if (isInitialized) return; // Prevent duplicate initialization
    // ... module code ...
    isInitialized = true;
  };

  return { init };
})();
```

This ensures:
- Modules can be initialized multiple times safely
- Private helper functions don't pollute global scope
- Only `init()` method is exposed publicly

## Migration Notes

This refactoring converted a monolithic 1739-line `main.js` into:
- **37-line main.js** (orchestrator)
- **5 focused modules** (each under 300 lines)

Original code organization was preserved - Module split by feature, not line-by-line change.

## Browser Support

Uses ES6 modules - Requires:
- Modern browsers (Chrome 61+, Firefox 67+, Safari 10.1+, Edge 79+)
- For older browser support, use module bundler (webpack, esbuild, etc.)

## Future Improvements

- [ ] Lazy-load modules on demand
- [ ] Add module bundler (webpack/esbuild)
- [ ] Add unit tests for module functions
- [ ] Add TypeScript support
- [ ] Create additional modules (charts, analytics, etc.)

## Development

When adding new features:

1. **Is it 200+ lines?** → Create a new module
2. **Is it reusable?** → Add to utils.js or create new module
3. **Does it affect single feature?** → Add to existing module
4. **Import in main.js** and call `.init()`

---

**Last Updated:** April 9, 2026  
**Author:** Code Quality Audit  
**Status:** Production Ready
