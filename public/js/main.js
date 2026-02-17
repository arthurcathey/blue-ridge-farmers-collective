/**
 * Blue Ridge Farmers Collective - Main Application JavaScript
 * 
 * This file contains core functionality for:
 * - Accessibility features (skip links)
 * - Navigation interactions (mobile menu, dropdowns)
 * - Real-time form validation with comprehensive error handling
 * - Live product filtering and search on the products page
 * 
 * All code is wrapped in a DOMContentLoaded event to ensure DOM is ready
 * @file Main JavaScript application logic
 * @version 1.0.0
 */

"use strict";
document.addEventListener("DOMContentLoaded", () => {
  /**
   * Skip to main content link accessibility feature
   * Makes skip link visible on focus and hidden when blurred
   */
  const skipLink = document.querySelector('.skip-to-main-content');
  if (skipLink) {
    skipLink.style.opacity = '0';
    
    skipLink.addEventListener('focus', () => {
      skipLink.style.opacity = '1';
    });
    skipLink.addEventListener('blur', () => {
      skipLink.style.opacity = '0';
    });
  }

  /**
   * Flash message auto-dismissal
   * Removes flash messages after 5 seconds
   */
  const flash = document.querySelector("[data-flash]");
  if (flash) {
    setTimeout(() => {
      flash.remove();
    }, 5000);
  }

  /**
   * Mobile menu toggle functionality
   * Opens/closes navigation menu on mobile with accessibility support
   */
  const menuToggle = document.querySelector('[data-menu-toggle]');
  const navLinks = document.querySelector('[data-nav]');
  
  if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', () => {
      const isOpen = navLinks.hasAttribute('data-mobile-open');
      if (isOpen) {
        navLinks.removeAttribute('data-mobile-open');
        menuToggle.setAttribute('aria-expanded', 'false');
      } else {
        navLinks.setAttribute('data-mobile-open', '');
        menuToggle.setAttribute('aria-expanded', 'true');
      }
    });
  }

  /**
   * Dropdown menu management
   * Handles opening, closing, and toggling of dropdown menus
   * Closes all dropdowns when clicking outside
   */
  const dropdowns = Array.from(document.querySelectorAll("[data-dropdown]"));

  dropdowns.forEach((dropdown) => {
    const trigger = dropdown.querySelector(".nav-trigger");

    if (!trigger) {
      return;
    }

    trigger.addEventListener("click", (event) => {
      event.preventDefault();
      const isOpen = dropdown.classList.contains("is-open");
      const menu = dropdown.querySelector("[data-menu]");

      dropdowns.forEach((item) => {
        item.classList.remove("is-open");
        const btn = item.querySelector(".nav-trigger");
        const itemMenu = item.querySelector("[data-menu]");
        if (btn) {
          btn.setAttribute("aria-expanded", "false");
        }
        if (itemMenu) {
          itemMenu.setAttribute("hidden", "hidden");
        }
      });

      if (!isOpen) {
        dropdown.classList.add("is-open");
        trigger.setAttribute("aria-expanded", "true");
        if (menu) {
          menu.removeAttribute("hidden");
        }
      }
    });
  });

  document.addEventListener("click", (event) => {
    if (dropdowns.length === 0) {
      return;
    }

    const target = event.target;
    const clickedDropdown = target instanceof Element ? target.closest("[data-dropdown]") : null;

    if (!clickedDropdown) {
      dropdowns.forEach((item) => {
        item.classList.remove("is-open");
        const btn = item.querySelector(".nav-trigger");
        const itemMenu = item.querySelector("[data-menu]");
        if (btn) {
          btn.setAttribute("aria-expanded", "false");
        }
        if (itemMenu) {
          itemMenu.setAttribute("hidden", "hidden");
        }
      });
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      dropdowns.forEach((item) => {
        item.classList.remove("is-open");
        const btn = item.querySelector(".nav-trigger");
        const itemMenu = item.querySelector("[data-menu]");
        if (btn) {
          btn.setAttribute("aria-expanded", "false");
        }
        if (itemMenu) {
          itemMenu.setAttribute("hidden", "hidden");
        }
      });
    }
  });

  /**
   * FORM VALIDATION MODULE
   * Provides real time validation for all form fields
   * Supports email, URL, phone, password, text, and textarea inputs
   */
  const forms = document.querySelectorAll("form");

  forms.forEach((form) => {
    const inputs = form.querySelectorAll("input, textarea, select");

    inputs.forEach((input) => {
      input.addEventListener("blur", () => {
        validateField(input);
      });

      if (input.type === "email" || input.type === "url" || input.type === "password") {
        input.addEventListener("input", () => {
          validateField(input);
        });
      }
    });

    form.addEventListener("submit", (event) => {
      let isValid = true;
      inputs.forEach((input) => {
        if (!validateField(input)) {
          isValid = false;
        }
      });

      if (!isValid) {
        event.preventDefault();
        const firstInvalid = form.querySelector("[aria-invalid='true']");
        if (firstInvalid) {
          firstInvalid.focus();
        }
      }
    });
  });

  /**
   * Validates a form field based on its type and attributes
   * @param {HTMLElement} field - The form field element to validate
   * @returns {boolean} True if field is valid, false otherwise
   */
  function validateField(field) {
    const value = field.value.trim();
    const errors = [];

    if (field.type === "file") {
      return true;
    }

    if (field.hasAttribute("required") && !value) {
      errors.push("This field is required");
    }

    // Validate based on field type
    if (value) {
      switch (field.type) {
        case "email":
          if (!isValidEmail(value)) {
            errors.push("Please enter a valid email address");
          }
          break;
        case "url":
          if (!isValidUrl(value)) {
            errors.push("Please enter a valid URL");
          }
          break;
        case "tel":
          if (!isValidPhone(value)) {
            errors.push("Please enter a valid phone number");
          }
          break;
        case "password":
          const passwordErrors = validatePassword(value);
          errors.push(...passwordErrors);
          break;
        case "text":
        case "textarea":
          // Check pattern if it exists
          if (field.hasAttribute("pattern")) {
            const pattern = new RegExp("^" + field.getAttribute("pattern") + "$");
            if (!pattern.test(value)) {
              errors.push("Please enter a valid format");
            }
          }
          // Check maxlength
          if (field.hasAttribute("maxlength")) {
            const maxlength = parseInt(field.getAttribute("maxlength"));
            if (value.length > maxlength) {
              errors.push(`Maximum ${maxlength} characters allowed`);
            }
          }
          break;
      }
    }

    // Check minlength
    if (value && field.hasAttribute("minlength")) {
      const minlength = parseInt(field.getAttribute("minlength"));
      if (value.length < minlength) {
        errors.push(`Minimum ${minlength} characters required`);
      }
    }

    // Update field appearance and error message
    updateFieldError(field, errors);

    return errors.length === 0;
  }

  /**
   * Validates email address format using regex
   * @param {string} email - Email address to validate
   * @returns {boolean} True if email format is valid
   */
  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  /**
   * Validates URL format using URL constructor
   * @param {string} url - URL to validate
   * @returns {boolean} True if URL format is valid
   */
  function isValidUrl(url) {
    try {
      new URL(url);
      return true;
    } catch {
      return false;
    }
  }

  /**
   * Validates phone number format (flexible, requires at least 10 digits)
   * @param {string} phone - Phone number to validate
   * @returns {boolean} True if phone format is valid
   */
  function isValidPhone(phone) {
    const phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
    return phoneRegex.test(phone.replace(/\D/g, "").length >= 10 ? phone : "");
  }

  /**
   * Validates password strength requirements
   * @param {string} password - Password to validate
   * @returns {string[]} Array of error messages (empty if valid)
   */
  function validatePassword(password) {
    const errors = [];
    if (password.length < 8) {
      errors.push("Password must be at least 8 characters");
    }
    if (!/[A-Z]/.test(password)) {
      errors.push("Password must contain an uppercase letter");
    }
    if (!/[a-z]/.test(password)) {
      errors.push("Password must contain a lowercase letter");
    }
    if (!/[0-9]/.test(password)) {
      errors.push("Password must contain a number");
    }
    return errors;
  }

  /**
   * Updates form field error state and displays error messages
   * @param {HTMLElement} field - The form field element
   * @param {string[]} errors - Array of error messages to display
   */
  function updateFieldError(field, errors) {
    const fieldContainer = field.closest(".field") || field.closest(".form-field");
    let errorElement = fieldContainer ? fieldContainer.querySelector(".form-error") : null;

    if (errors.length > 0) {
      // Set aria attributes for accessibility
      field.setAttribute("aria-invalid", "true");

      // Create error element if it doesn't exist
      if (!errorElement) {
        errorElement = document.createElement("small");
        errorElement.className = "form-error";
        errorElement.setAttribute("role", "alert");
        if (fieldContainer) {
          fieldContainer.appendChild(errorElement);
        } else {
          field.insertAdjacentElement("afterend", errorElement);
        }
      }

      // Set error message (show first error)
      errorElement.textContent = errors[0];
      const errorId = `error-${field.id}`;
      errorElement.id = errorId;
      field.setAttribute("aria-describedby", errorId);
    } else {
      // Clear error state
      field.setAttribute("aria-invalid", "false");
      field.removeAttribute("aria-describedby");
      if (errorElement) {
        errorElement.remove();
      }
    }
  }

  // Validate password confirmation matches
  const passwordFields = document.querySelectorAll('input[name="password"], input[name="confirm_password"]');
  if (passwordFields.length === 2) {
    const [passwordField, confirmField] = passwordFields;

    const validatePasswordMatch = () => {
      if (confirmField.value && passwordField.value !== confirmField.value) {
        confirmField.setAttribute("aria-invalid", "true");
        let errorElement = confirmField.closest(".field").querySelector(".form-error");
        if (!errorElement) {
          errorElement = document.createElement("small");
          errorElement.className = "form-error";
          errorElement.setAttribute("role", "alert");
          confirmField.closest(".field").appendChild(errorElement);
        }
        errorElement.textContent = "Passwords do not match";
      } else if (confirmField.value) {
        confirmField.setAttribute("aria-invalid", "false");
        const errorElement = confirmField.closest(".field").querySelector(".form-error");
        if (errorElement && errorElement.textContent === "Passwords do not match") {
          errorElement.remove();
        }
      }
    };

    confirmField.addEventListener("input", validatePasswordMatch);
    passwordField.addEventListener("input", validatePasswordMatch);
  }

  /**
   * PRODUCT FILTERING & SEARCH MODULE
   * Provides real-time filtering and sorting of products
   * Supports search by name, category, vendor, and market filters
   */
  const searchInput = document.getElementById("search");
  const categoryFilter = document.getElementById("category");
  const vendorFilter = document.getElementById("vendor");
  const marketFilter = document.getElementById("market");
  const sortSelect = document.getElementById("sort");
  const productGrid = document.querySelector(".grid");
  const productCards = productGrid ? Array.from(productGrid.querySelectorAll(".product-card")) : [];

  if (productCards.length > 0 && (searchInput || categoryFilter || vendorFilter || marketFilter || sortSelect)) {
    // Store original product data for filtering
    const productsData = productCards.map((card) => ({
      element: card,
      name: card.querySelector(".product-title")?.textContent.toLowerCase() || "",
      category: card.getAttribute("data-category") || card.querySelector(".product-category-tag")?.textContent.toLowerCase() || "",
      vendor: card.getAttribute("data-vendor") || card.querySelector(".product-vendor-link")?.textContent.toLowerCase() || "",
      market: card.getAttribute("data-market") || "",
      createdAt: card.getAttribute("data-created") || 0,
    }));

    // Add data attributes to product cards for easier filtering
    productCards.forEach((card) => {
      const categoryTag = card.querySelector(".product-category-tag");
      const vendorLink = card.querySelector(".product-vendor-link");
      
      if (categoryTag) {
        card.setAttribute("data-category", categoryTag.textContent.toLowerCase());
      }
      if (vendorLink) {
        card.setAttribute("data-vendor", vendorLink.textContent.toLowerCase());
      }
    });

    // Filter function
    /**
     * Filters products based on search term and selected filters
     * Applies sorting and updates product visibility on the page
     * @returns {void}
     */
    function filterProducts() {
      const searchTerm = searchInput ? searchInput.value.toLowerCase() : "";
      const selectedCategory = categoryFilter ? categoryFilter.value : "";
      const selectedVendor = vendorFilter ? vendorFilter.value : "";
      const selectedMarket = marketFilter ? marketFilter.value : "";
      const sortBy = sortSelect ? sortSelect.value : "name";

      // Filter products
      let filtered = productsData.filter((product) => {
        const matchesSearch = product.name.includes(searchTerm);
        const matchesCategory = !selectedCategory || product.category.toLowerCase().includes(selectedCategory.toLowerCase()) || product.element.querySelector(".product-category-tag")?.textContent === selectedCategory;
        const matchesVendor = !selectedVendor || product.vendor.includes(selectedVendor) || product.element.querySelector(`[data-vendor="${selectedVendor}"]`);
        const matchesMarket = !selectedMarket || product.market.includes(selectedMarket) || product.element.getAttribute("data-market") === selectedMarket;

        return matchesSearch && matchesCategory && matchesVendor && matchesMarket;
      });

      // Sort products
      if (sortBy === "newest") {
        filtered.sort((a, b) => b.createdAt - a.createdAt);
      } else {
        filtered.sort((a, b) => a.name.localeCompare(b.name));
      }

      // Update visibility
      productCards.forEach((card) => {
        card.style.display = "none";
      });

      filtered.forEach((product) => {
        product.element.style.display = "";
      });

      // Update results info
      updateResultsInfo(filtered.length);

      // Show/hide no results message
      updateNoResultsMessage(filtered.length);
    }

    /**
     * Updates and displays the search results counter
     * @param {number} count - Number of products matching current filters
     * @returns {void}
     */
    function updateResultsInfo(count) {
      let resultsInfo = productGrid.parentElement.querySelector(".search-results-info");
      
      if (searchInput?.value || categoryFilter?.value || vendorFilter?.value || marketFilter?.value) {
        if (!resultsInfo) {
          resultsInfo = document.createElement("div");
          resultsInfo.className = "search-results-info";
          const form = document.querySelector(".search-form");
          if (form) {
            form.insertAdjacentElement("afterend", resultsInfo);
          }
        }
        resultsInfo.innerHTML = `Found <strong>${count}</strong> product${count === 1 ? "" : "s"}`;
        resultsInfo.style.display = "";
      } else if (resultsInfo) {
        resultsInfo.style.display = "none";
      }
    }

    // Update no products message
    /**
     * Displays or hides the "no products found" message based on filter results
     * @param {number} count - Number of products matching current filters
     * @returns {void}
     */
    function updateNoResultsMessage(count) {
      let noResultsMsg = productGrid.querySelector(".no-products-message");
      
      if (count === 0) {
        if (!noResultsMsg) {
          noResultsMsg = document.createElement("div");
          noResultsMsg.className = "no-products-message";
          productGrid.innerHTML = "";
          productGrid.appendChild(noResultsMsg);
        }
        noResultsMsg.innerHTML = "<p>No products found matching your search. Try adjusting your filters.</p>";
        noResultsMsg.style.display = "";
      } else if (noResultsMsg) {
        noResultsMsg.style.display = "none";
      }
    }

    // Event listeners for real-time filtering
    if (searchInput) {
      searchInput.addEventListener("input", filterProducts);
    }
    if (categoryFilter) {
      categoryFilter.addEventListener("change", filterProducts);
    }
    if (vendorFilter) {
      vendorFilter.addEventListener("change", filterProducts);
    }
    if (marketFilter) {
      marketFilter.addEventListener("change", filterProducts);
    }
    if (sortSelect) {
      sortSelect.addEventListener("change", filterProducts);
    }

    // Preserve filters when page loads (from URL parameters)
    // Filters are already selected by PHP, just needs display update on load
  }

  /**
   * Sticky header scroll effect
   * Changes header background color when user scrolls
   */
  const siteHeader = document.querySelector('.site-header');
  if (siteHeader) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 0) {
        siteHeader.classList.add('is-scrolled');
      } else {
        siteHeader.classList.remove('is-scrolled');
      }
    });
  }

  /**
   * Back to Top Button
   * Shows button when user scrolls down, hides when at top
   * Smoothly scrolls to top when clicked
   */
  const backToTopButton = document.getElementById('back-to-top');
  
  if (backToTopButton) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 300) {
        backToTopButton.classList.add('show');
      } else {
        backToTopButton.classList.remove('show');
      }
    });

    backToTopButton.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });

    backToTopButton.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      }
    });
  }
});
