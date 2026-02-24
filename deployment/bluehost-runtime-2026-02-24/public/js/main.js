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
  const dropdowns = Array.from(document.querySelectorAll("[data-dropdown]"));

  const closeAllDropdowns = () => {
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
  };

  const getMenuItems = (dropdown) => {
    return Array.from(dropdown.querySelectorAll(".nav-menu-link"));
  };

  const openDropdown = (dropdown) => {
    const trigger = dropdown.querySelector(".nav-trigger");
    const menu = dropdown.querySelector("[data-menu]");
    if (!trigger || !menu) {
      return;
    }

    dropdown.classList.add("is-open");
    trigger.setAttribute("aria-expanded", "true");
    menu.removeAttribute("hidden");
  };

  const focusMenuItem = (dropdown, index) => {
    const items = getMenuItems(dropdown);
    if (items.length === 0) {
      return;
    }

    const safeIndex = Math.max(0, Math.min(index, items.length - 1));
    items[safeIndex].focus();
  };
  
  if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', () => {
      const isOpen = navLinks.hasAttribute('data-mobile-open');
      if (isOpen) {
        navLinks.removeAttribute('data-mobile-open');
        menuToggle.setAttribute('aria-expanded', 'false');
        closeAllDropdowns();
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
  dropdowns.forEach((dropdown) => {
    const trigger = dropdown.querySelector(".nav-trigger");

    if (!trigger) {
      return;
    }

    trigger.addEventListener("click", (event) => {
      event.preventDefault();
      const isOpen = dropdown.classList.contains("is-open");

      closeAllDropdowns();

      if (!isOpen) {
        openDropdown(dropdown);
      }
    });

    trigger.addEventListener("keydown", (event) => {
      const key = event.key;

      if (key !== "ArrowDown" && key !== "ArrowUp" && key !== "Escape") {
        return;
      }

      if (key === "Escape") {
        closeAllDropdowns();
        trigger.focus();
        return;
      }

      event.preventDefault();
      closeAllDropdowns();
      openDropdown(dropdown);

      if (key === "ArrowDown") {
        focusMenuItem(dropdown, 0);
      } else if (key === "ArrowUp") {
        const items = getMenuItems(dropdown);
        focusMenuItem(dropdown, items.length - 1);
      }
    });

    const menu = dropdown.querySelector("[data-menu]");
    if (menu) {
      menu.addEventListener("keydown", (event) => {
        const items = getMenuItems(dropdown);
        if (items.length === 0) {
          return;
        }

        const activeIndex = items.findIndex((item) => item === document.activeElement);

        switch (event.key) {
          case "ArrowDown": {
            event.preventDefault();
            const nextIndex = activeIndex >= 0 ? (activeIndex + 1) % items.length : 0;
            focusMenuItem(dropdown, nextIndex);
            break;
          }
          case "ArrowUp": {
            event.preventDefault();
            const prevIndex = activeIndex >= 0 ? (activeIndex - 1 + items.length) % items.length : items.length - 1;
            focusMenuItem(dropdown, prevIndex);
            break;
          }
          case "Home":
            event.preventDefault();
            focusMenuItem(dropdown, 0);
            break;
          case "End":
            event.preventDefault();
            focusMenuItem(dropdown, items.length - 1);
            break;
          case "Escape":
            event.preventDefault();
            closeAllDropdowns();
            trigger.focus();
            break;
          default:
            break;
        }
      });
    }
  });

  document.addEventListener("click", (event) => {
    if (dropdowns.length === 0) {
      return;
    }

    const target = event.target;
    const clickedDropdown = target instanceof Element ? target.closest("[data-dropdown]") : null;

    if (!clickedDropdown) {
      closeAllDropdowns();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeAllDropdowns();
    }
  });

  document.addEventListener('click', (event) => {
    if (!navLinks || !menuToggle) {
      return;
    }
    const target = event.target;
    if (!(target instanceof Element)) {
      return;
    }
    if (target.closest('.nav-menu-link') && window.matchMedia('(max-width: 767px)').matches) {
      navLinks.removeAttribute('data-mobile-open');
      menuToggle.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
    }
  });

  window.addEventListener('resize', () => {
    if (!navLinks || !menuToggle) {
      return;
    }
    if (window.innerWidth >= 768) {
      navLinks.removeAttribute('data-mobile-open');
      menuToggle.setAttribute('aria-expanded', 'false');
      closeAllDropdowns();
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
          if (field.hasAttribute("pattern")) {
            const pattern = new RegExp("^" + field.getAttribute("pattern") + "$");
            if (!pattern.test(value)) {
              errors.push("Please enter a valid format");
            }
          }
          if (field.hasAttribute("maxlength")) {
            const maxlength = parseInt(field.getAttribute("maxlength"));
            if (value.length > maxlength) {
              errors.push(`Maximum ${maxlength} characters allowed`);
            }
          }
          break;
      }
    }

    if (value && field.hasAttribute("minlength")) {
      const minlength = parseInt(field.getAttribute("minlength"));
      if (value.length < minlength) {
        errors.push(`Minimum ${minlength} characters required`);
      }
    }

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
      field.setAttribute("aria-invalid", "true");

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

      errorElement.textContent = errors[0];
      const errorId = `error-${field.id}`;
      errorElement.id = errorId;
      field.setAttribute("aria-describedby", errorId);
    } else {
      field.setAttribute("aria-invalid", "false");
      field.removeAttribute("aria-describedby");
      if (errorElement) {
        errorElement.remove();
      }
    }
  }

  const passwordFields = document.querySelectorAll('input[name="password"], input[name="confirm_password"]');
  if (passwordFields.length === 2) {
    const [passwordField, confirmField] = passwordFields;
    const confirmFieldContainer = confirmField.closest(".field") || confirmField.closest(".form-field");

    const validatePasswordMatch = () => {
      if (confirmField.value && passwordField.value !== confirmField.value) {
        confirmField.setAttribute("aria-invalid", "true");
        let errorElement = confirmFieldContainer ? confirmFieldContainer.querySelector(".form-error") : null;
        if (!errorElement) {
          errorElement = document.createElement("small");
          errorElement.className = "form-error";
          errorElement.setAttribute("role", "alert");
          if (confirmFieldContainer) {
            confirmFieldContainer.appendChild(errorElement);
          } else {
            confirmField.insertAdjacentElement("afterend", errorElement);
          }
        }
        errorElement.textContent = "Passwords do not match";
      } else if (confirmField.value) {
        confirmField.setAttribute("aria-invalid", "false");
        const errorElement = confirmFieldContainer ? confirmFieldContainer.querySelector(".form-error") : null;
        if (errorElement && errorElement.textContent === "Passwords do not match") {
          errorElement.remove();
        }
      }
    };

    confirmField.addEventListener("input", validatePasswordMatch);
    passwordField.addEventListener("input", validatePasswordMatch);
  }

  /**
   * STAR RATING MODULE
   * Provides immediate visual feedback when selecting review rating stars
   */
  const starRatingGroups = document.querySelectorAll("[data-rating-stars]");
  starRatingGroups.forEach((group) => {
    const ratingInputs = Array.from(group.querySelectorAll('input[type="radio"][name="rating"]'));
    const starElements = Array.from(group.querySelectorAll("[data-star-value]"));
    const ratingFeedback = group.parentElement?.querySelector("[data-rating-feedback]") || null;

    if (ratingInputs.length === 0 || starElements.length === 0) {
      return;
    }

    const updateRatingDisplay = (selectedRating) => {
      starElements.forEach((star) => {
        const starValue = Number(star.getAttribute("data-star-value") || 0);
        if (starValue <= selectedRating) {
          star.classList.remove("text-neutral-medium");
          star.classList.add("text-brand-accent");
        } else {
          star.classList.remove("text-brand-accent");
          star.classList.add("text-neutral-medium");
        }
      });

      if (ratingFeedback) {
        if (selectedRating > 0) {
          ratingFeedback.textContent = `${selectedRating} star${selectedRating === 1 ? "" : "s"} selected`;
        } else {
          ratingFeedback.textContent = "No rating selected";
        }
      }
    };

    ratingInputs.forEach((input) => {
      input.addEventListener("change", () => {
        updateRatingDisplay(Number(input.value || 0));
      });
    });

    const initiallySelected = ratingInputs.find((input) => input.checked);
    updateRatingDisplay(Number(initiallySelected?.value || 0));
  });

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
    const productsData = productCards.map((card) => ({
      element: card,
      name: card.querySelector(".product-title")?.textContent.toLowerCase() || "",
      category: card.getAttribute("data-category") || card.querySelector(".product-category-tag")?.textContent.toLowerCase() || "",
      vendor: card.getAttribute("data-vendor") || card.querySelector(".product-vendor-link")?.textContent.toLowerCase() || "",
      market: card.getAttribute("data-market") || "",
      createdAt: card.getAttribute("data-created") || 0,
    }));

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

    function filterProducts() {
      const searchTerm = searchInput ? searchInput.value.toLowerCase() : "";
      const selectedCategory = categoryFilter ? categoryFilter.value : "";
      const selectedVendor = vendorFilter ? vendorFilter.value : "";
      const selectedMarket = marketFilter ? marketFilter.value : "";
      const sortBy = sortSelect ? sortSelect.value : "name";

      let filtered = productsData.filter((product) => {
        const matchesSearch = product.name.includes(searchTerm);
        const matchesCategory = !selectedCategory || product.category.toLowerCase().includes(selectedCategory.toLowerCase()) || product.element.querySelector(".product-category-tag")?.textContent === selectedCategory;
        const matchesVendor = !selectedVendor || product.vendor.includes(selectedVendor) || product.element.querySelector(`[data-vendor="${selectedVendor}"]`);
        const matchesMarket = !selectedMarket || product.market.includes(selectedMarket) || product.element.getAttribute("data-market") === selectedMarket;

        return matchesSearch && matchesCategory && matchesVendor && matchesMarket;
      });

      if (sortBy === "newest") {
        filtered.sort((a, b) => b.createdAt - a.createdAt);
      } else {
        filtered.sort((a, b) => a.name.localeCompare(b.name));
      }

      productCards.forEach((card) => {
        card.style.display = "none";
      });

      filtered.forEach((product) => {
        product.element.style.display = "";
      });

      updateResultsInfo(filtered.length);

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
    const toggleBackToTopVisibility = () => {
      const shouldShow = window.scrollY > 300;
      backToTopButton.classList.toggle('show', shouldShow);
      backToTopButton.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
      if (shouldShow) {
        backToTopButton.removeAttribute('tabindex');
      } else {
        backToTopButton.setAttribute('tabindex', '-1');
      }
    };

    window.addEventListener('scroll', toggleBackToTopVisibility, { passive: true });
    toggleBackToTopVisibility();

    const scrollBehavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';

    backToTopButton.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: scrollBehavior
      });
    });

    backToTopButton.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        window.scrollTo({
          top: 0,
          behavior: scrollBehavior
        });
      }
    });
  }
});
