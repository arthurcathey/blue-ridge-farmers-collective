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
   * Utility: Debounce function to prevent excessive function calls
   */
  const debounce = (func, delay) => {
    let timeoutId;
    return function (...args) {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
  };

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

  const closeMobileMenu = () => {
    if (navLinks) {
      navLinks.removeAttribute('data-mobile-open');
    }
    if (menuToggle) {
      menuToggle.setAttribute('aria-expanded', 'false');
    }
  };

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
        closeMobileMenu();
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
    const target = event.target;
    if (!(target instanceof Element)) return;

    // Close dropdowns when clicking outside
    if (dropdowns.length > 0) {
      const clickedDropdown = target.closest("[data-dropdown]");
      if (!clickedDropdown) {
        closeAllDropdowns();
      }
    }

    // Close mobile menu when clicking nav link
    if (navLinks && menuToggle && target.closest('.nav-menu-link') && window.matchMedia('(max-width: 767px)').matches) {
      closeMobileMenu();
      closeAllDropdowns();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeAllDropdowns();
    }
  });

  window.addEventListener('resize', () => {
    if (!navLinks || !menuToggle) {
      return;
    }
    if (window.innerWidth >= 768) {
      closeMobileMenu();
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
    const digitCount = phone.replace(/\D/g, "").length;
    return digitCount >= 10;
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

    const validatePasswordMatch = () => {
      const errors = confirmField.value && passwordField.value !== confirmField.value
        ? ["Passwords do not match"]
        : [];
      updateFieldError(confirmField, errors);
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
    const productsData = productCards.map((card) => {
      const categoryTag = card.querySelector(".product-category-tag");
      const vendorLink = card.querySelector(".product-vendor-link");
      
      return {
        element: card,
        name: card.querySelector(".product-title")?.textContent.toLowerCase() || "",
        category: (categoryTag?.textContent || card.getAttribute("data-category") || "").toLowerCase(),
        vendor: (vendorLink?.textContent || card.getAttribute("data-vendor") || "").toLowerCase(),
        market: card.getAttribute("data-market") || "",
        createdAt: parseInt(card.getAttribute("data-created") || "0"),
      };
    });

    function filterProducts() {
      const searchTerm = searchInput ? searchInput.value.toLowerCase() : "";
      const selectedCategory = categoryFilter ? categoryFilter.value : "";
      const selectedVendor = vendorFilter ? vendorFilter.value : "";
      const selectedMarket = marketFilter ? marketFilter.value : "";
      const sortBy = sortSelect ? sortSelect.value : "name";

      let filtered = productsData.filter((product) => {
        const matchesSearch = product.name.includes(searchTerm);
        const matchesCategory = !selectedCategory || product.category.includes(selectedCategory.toLowerCase());
        const matchesVendor = !selectedVendor || product.vendor.includes(selectedVendor.toLowerCase());
        const matchesMarket = !selectedMarket || product.market.includes(selectedMarket);

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
   * Sticky header and back-to-top button scroll effects
   * Updates header styling and shows/hides back-to-top button on scroll
   */
  const siteHeader = document.querySelector('.site-header');
  const backToTopButton = document.getElementById('back-to-top');
  const navLogo = document.querySelector('[data-scroll-logo]');
  
  const handleScroll = () => {
    const scrollY = window.scrollY;
    
    if (siteHeader) {
      siteHeader.classList.toggle('is-scrolled', scrollY > 0);
    }
    
    if (navLogo) {
      navLogo.src = scrollY > 0 ? navLogo.getAttribute('data-logo-scroll') : navLogo.getAttribute('data-logo-default');
    }
    
    if (backToTopButton) {
      const shouldShow = scrollY > 300;
      backToTopButton.classList.toggle('show', shouldShow);
      backToTopButton.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
      if (shouldShow) {
        backToTopButton.removeAttribute('tabindex');
      } else {
        backToTopButton.setAttribute('tabindex', '-1');
      }
    }
  };
  
  if (siteHeader || backToTopButton) {
    window.addEventListener('scroll', handleScroll, { passive: true });
    // Call immediately to set initial state
    setTimeout(handleScroll, 0);
  }
  
  if (backToTopButton) {
    const scrollBehavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';

    const scrollToTop = () => {
      window.scrollTo({
        top: 0,
        behavior: scrollBehavior
      });
    };

    backToTopButton.addEventListener('click', scrollToTop);

    backToTopButton.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        scrollToTop();
      }
    });
  }

  /**
   * Lightbox Image Gallery
   * Click images to view full-size with keyboard navigation
   */
  const galleryImages = document.querySelectorAll('[data-lightbox]');
  if (galleryImages.length > 0) {
    let currentIndex = 0;
    const images = Array.from(galleryImages);

    // Create lightbox overlay
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.innerHTML = `
      <div class="lightbox-overlay" data-close></div>
      <div class="lightbox-content">
        <button type="button" class="lightbox-close" data-close aria-label="Close image gallery">&times;</button>
        <button type="button" class="lightbox-prev" data-prev aria-label="Previous image">‹</button>
        <img class="lightbox-image" src="" alt="">
        <button type="button" class="lightbox-next" data-next aria-label="Next image">›</button>
        <div class="lightbox-caption"></div>
      </div>
    `;
    document.body.appendChild(lightbox);

    const lightboxImg = lightbox.querySelector('.lightbox-image');
    const lightboxCaption = lightbox.querySelector('.lightbox-caption');

    function showImage(index) {
      currentIndex = (index + images.length) % images.length;
      const img = images[currentIndex];
      lightboxImg.src = img.dataset.lightbox || img.src;
      lightboxImg.alt = img.alt;
      lightboxCaption.textContent = img.dataset.caption || img.alt;
      lightbox.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      lightbox.classList.remove('is-open');
      document.body.style.overflow = '';
    }

    // Attach click events
    images.forEach((img, index) => {
      img.addEventListener('click', () => showImage(index));
      img.style.cursor = 'pointer';
      img.setAttribute('role', 'button');
      img.setAttribute('tabindex', '0');
    });

    // Keyboard support for image click
    images.forEach((img, index) => {
      img.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          showImage(index);
        }
      });
    });

    // Navigation
    lightbox.querySelector('[data-prev]').addEventListener('click', () => showImage(currentIndex - 1));
    lightbox.querySelector('[data-next]').addEventListener('click', () => showImage(currentIndex + 1));
    lightbox.querySelectorAll('[data-close]').forEach(el => {
      el.addEventListener('click', closeLightbox);
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (!lightbox.classList.contains('is-open')) return;

      if (e.key === 'ArrowLeft') showImage(currentIndex - 1);
      if (e.key === 'ArrowRight') showImage(currentIndex + 1);
      if (e.key === 'Escape') closeLightbox();
    });
  }

  /**
   * Live Product Search with Debouncing
   * Updates results in real-time as user types, with AJAX fetch
   */
  const liveSearchInput = document.querySelector('[data-search-input]');
  const resultsContainer = document.querySelector('[data-search-results]');
  const loadingIndicator = document.querySelector('[data-search-loading]');

  if (searchInput && resultsContainer) {
    // Fetch and display search results
    async function searchProducts(query) {
      query = query.trim();

      if (query.length < 2) {
        resultsContainer.innerHTML = '';
        return;
      }

      // Show loading state
      if (loadingIndicator) {
        loadingIndicator.classList.remove('hidden');
      }

      try {
        const response = await fetch(
          `/api/products/search?q=${encodeURIComponent(query)}`
        );
        const data = await response.json();

        if (data.products.length === 0) {
          resultsContainer.innerHTML =
            '<p class="live-search-empty">No products found</p>';
        } else {
          resultsContainer.innerHTML = data.products
            .map(
              (product) => `
            <a href="/products?view=${encodeURIComponent(product.name)}" class="live-search-result" data-product-id="${product.id}">
              <img src="${product.photo}" alt="${product.name}" class="live-search-image" loading="lazy">
              <div class="live-search-content">
                <h3 class="live-search-name">${product.name}</h3>
                <p class="live-search-vendor">${product.vendor_name}</p>
              </div>
            </a>
          `
            )
            .join('');
        }
      } catch (error) {
        resultsContainer.innerHTML =
          '<p class="live-search-error">Error loading results. Please try again.</p>';
      } finally {
        if (loadingIndicator) {
          loadingIndicator.classList.add('hidden');
        }
      }
    }

    // Attach debounced search to input
    const debouncedSearch = debounce(searchProducts, 300);
    searchInput.addEventListener('input', (e) => {
      debouncedSearch(e.target.value.trim());
    });
  }

  /**
   * Auto-save form data to localStorage
   * Prevents data loss on accidental navigation
   */
  const autosaveForms = document.querySelectorAll('[data-autosave]');

  autosaveForms.forEach((form) => {
    const formId = form.dataset.autosave;
    const storageKey = `form_${formId}`;

    // Restore saved data on page load
    const savedData = localStorage.getItem(storageKey);
    if (savedData) {
      try {
        const data = JSON.parse(savedData);
        Object.keys(data).forEach((name) => {
          const field = form.querySelector(`[name="${name}"]`);
          if (field && field.type !== 'password') {
            if (field.type === 'checkbox' || field.type === 'radio') {
              field.checked = data[name] === 'true' || data[name] === true;
            } else {
              field.value = data[name];
            }
          }
        });

        // Show draft restoration notice
        const notice = document.createElement('div');
        notice.className = 'alert-info mb-4';
        notice.innerHTML =
          '✓ Draft restored. <button type="button" class="ml-2 text-sm underline" data-clear-draft>Clear draft</button>';
        form.insertAdjacentElement('afterbegin', notice);

        notice.querySelector('[data-clear-draft]')?.addEventListener('click', () => {
          localStorage.removeItem(storageKey);
          notice.remove();
          form.reset();
        });
      } catch (e) {
        console.error('Failed to restore form data:', e);
      }
    }

    // Save data on input (debounced)
    const saveData = debounce(() => {
      const formData = new FormData(form);
      const data = {};
      formData.forEach((value, key) => {
        // Don't save passwords or CSRF tokens
        if (!key.includes('password') && key !== 'csrf_token') {
          data[key] = value;
        }
      });
      localStorage.setItem(storageKey, JSON.stringify(data));
    }, 500);

    form.addEventListener('input', saveData);
    form.addEventListener('change', saveData);

    // Clear on successful submit
    form.addEventListener('submit', () => {
      localStorage.removeItem(storageKey);
    });
  });

  /**
   * Interactive Market Date Calendar
   * Shows market dates for current/selected month with click-to-view functionality
   */
  const calendarContainer = document.querySelector('[data-market-calendar]');
  if (calendarContainer) {
    let currentDate = new Date();

    function renderCalendar() {
      const year = currentDate.getFullYear();
      const month = currentDate.getMonth() + 1;

      // Fetch market dates for this month
      fetch(`/api/markets/calendar?year=${year}&month=${month}`)
        .then((res) => {
          if (!res.ok) {
            throw new Error(`API returned ${res.status}`);
          }
          return res.json().catch(err => {
            throw new Error('Invalid JSON response: ' + err.message);
          });
        })
        .then((data) => {
          if (!data || typeof data !== 'object' || !data.dates) {
            throw new Error('Invalid response format');
          }

          const firstDate = new Date(year, month - 1, 1);
          const lastDate = new Date(year, month, 0);
          const prevDate = new Date(year, month - 1, 0);

          let html = '<div class="calendar-header">';
          html +=
            '<button type="button" class="calendar-nav-btn" data-prev-month aria-label="Previous month">❮</button>';
          html += `<h3 class="calendar-title">${firstDate.toLocaleDateString('en-US', {
            month: 'long',
            year: 'numeric',
          })}</h3>`;
          html +=
            '<button type="button" class="calendar-nav-btn" data-next-month aria-label="Next month">❯</button>';
          html += '</div>';

          html += '<div class="calendar-weekdays">';
          ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach((day) => {
            html += `<div class="calendar-weekday">${day}</div>`;
          });
          html += '</div>';

          html += '<div class="calendar-days">';

          // Empty cells for days before month starts
          const startDay = firstDate.getDay();
          for (let i = 0; i < startDay; i++) {
            const day = prevDate.getDate() - (startDay - i - 1);
            html += '<div class="calendar-day calendar-day-other"></div>';
          }

          // Days of current month
          for (let day = 1; day <= lastDate.getDate(); day++) {
            const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(
              day
            ).padStart(2, '0')}`;
            const hasEvent = data.dates && data.dates[dateStr];
            const isToday =
              new Date().toDateString() === new Date(dateStr).toDateString();

            html += `<button type="button" class="calendar-day ${
              hasEvent ? 'calendar-day-has-event' : ''
            } ${isToday ? 'calendar-day-today' : ''}" data-date="${dateStr}" ${
              hasEvent
                ? `title="${hasEvent.event_count} market(s): ${hasEvent.market_names}"`
                : ''
            }>${day}`;

            if (hasEvent) {
              html += '<span class="calendar-day-indicator"></span>';
            }

            html += '</button>';
          }

          html += '</div>';

          calendarContainer.innerHTML = html;

          // Attach event listeners
          calendarContainer
            .querySelector('[data-prev-month]')
            ?.addEventListener('click', () => {
              currentDate.setMonth(currentDate.getMonth() - 1);
              renderCalendar();
            });

          calendarContainer
            .querySelector('[data-next-month]')
            ?.addEventListener('click', () => {
              currentDate.setMonth(currentDate.getMonth() + 1);
              renderCalendar();
            });

          // Date click handlers - safe without eval()
          calendarContainer.querySelectorAll('[data-date]').forEach((btn) => {
            btn.addEventListener('click', () => {
              const dateStr = btn.dataset.date;
              const eventData = data.dates[dateStr];
              if (eventData) {
                // Dispatch custom event instead of using eval
                const event = new CustomEvent('calendarDateSelected', {
                  detail: { date: dateStr, markets: eventData.market_names }
                });
                calendarContainer.dispatchEvent(event);
              }
            });
          });
        })
        .catch((err) => {
          console.error('Failed to load calendar:', err);
          calendarContainer.innerHTML =
            '<p class="calendar-error">Unable to load calendar. Please try refreshing the page.</p>';
        });
    }

    renderCalendar();
  }

  // Close modal when clicking outside of it
  const createLayoutModal = document.getElementById('createLayoutModal');
  if (createLayoutModal) {
    createLayoutModal.addEventListener('click', function(e) {
      if (e.target === this) {
        closeCreateLayoutModal();
      }
    });
  }
});

// ======================
// Vendor Attendance Functions
// ======================

let currentVendorId = null;
let currentDateId = null;
let csrfToken = document.querySelector('[name="csrf_token"]')?.value || '';

/**
 * Check in a vendor for the current market date
 */
window.checkInVendor = function(vendorId, farmName) {
  const dateInput = document.querySelector('[name="date_id"]');
  const dateId = dateInput?.value;

  if (!dateId) {
    alert('Please select a market date first');
    return;
  }

  const confirmMsg = `Check in ${farmName}?`;
  if (!confirm(confirmMsg)) return;

  const formData = new FormData();
  formData.append('vendor_id', vendorId);
  formData.append('date_id', dateId);
  formData.append('csrf_token', csrfToken);

  fetch('/admin/vendor-attendance/check-in', {
    method: 'POST',
    body: formData,
  })
    .then((res) => {
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    })
    .then((data) => {
      if (data.error) {
        alert('Error: ' + data.error);
        return;
      }
      // Reload to show updated list
      window.location.reload();
    })
    .catch((err) => {
      console.error('Failed to check in vendor:', err);
      alert('Failed to check in vendor. See console for details.');
    });
};

/**
 * Mark vendor as no-show
 */
window.markAsNoShow = function() {
  if (!currentVendorId || !currentDateId) {
    alert('No vendor selected');
    return;
  }

  const formData = new FormData();
  formData.append('vendor_id', currentVendorId);
  formData.append('date_id', currentDateId);
  formData.append('csrf_token', csrfToken);

  fetch('/admin/vendor-attendance/no-show', {
    method: 'POST',
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      closeVendorActionModal();
      window.location.reload();
    })
    .catch((err) => {
      console.error('Error marking no-show:', err);
      alert('Failed to update vendor status');
    });
};

/**
 * Mark vendor as confirmed
 */
window.markAsConfirmed = function() {
  if (!currentVendorId || !currentDateId) {
    alert('No vendor selected');
    return;
  }

  const formData = new FormData();
  formData.append('vendor_id', currentVendorId);
  formData.append('date_id', currentDateId);
  formData.append('csrf_token', csrfToken);

  fetch('/admin/vendor-attendance/confirm', {
    method: 'POST',
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      closeVendorActionModal();
      window.location.reload();
    })
    .catch((err) => {
      console.error('Error marking confirmed:', err);
      alert('Failed to update vendor status');
    });
};

/**
 * Undo no-show marking
 */
window.undoNoShow = function(vendorId) {
  const dateInput = document.querySelector('[name="date_id"]');
  const dateId = dateInput?.value;

  if (!dateId) {
    alert('Please select a market date first');
    return;
  }

  if (!confirm('Undo no-show for this vendor?')) return;

  const formData = new FormData();
  formData.append('vendor_id', vendorId);
  formData.append('date_id', dateId);
  formData.append('csrf_token', csrfToken);

  fetch('/admin/vendor-attendance/undo-no-show', {
    method: 'POST',
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      window.location.reload();
    })
    .catch((err) => {
      console.error('Error undoing no-show:', err);
      alert('Failed to undo no-show');
    });
};

/**
 * Open vendor action menu
 */
window.openVendorMenu = function(vendorId, status) {
  currentVendorId = vendorId;
  const dateInput = document.querySelector('[name="date_id"]');
  currentDateId = dateInput?.value;

  if (!currentDateId) {
    alert('Please select a market date first');
    return;
  }

  const modal = document.getElementById('vendorActionModal');
  if (modal) {
    modal.classList.remove('hidden');
  }
};

/**
 * Close vendor action modal
 */
window.closeVendorActionModal = function() {
  const modal = document.getElementById('vendorActionModal');
  if (modal) {
    modal.classList.add('hidden');
  }
  currentVendorId = null;
  currentDateId = null;
};

/**
 * Filter vendors by status
 */
window.filterByStatus = function(status) {
  const rows = document.querySelectorAll('.vendor-row');
  const buttons = document.querySelectorAll('[id^="filter"]');

  // Update button styling
  buttons.forEach((btn) => btn.classList.remove('btn-primary'));
  const activeBtn =
    status === 'all'
      ? document.getElementById('filterAll')
      : status === 'checked-in'
        ? document.getElementById('filterCheckedIn')
        : document.getElementById('filterPending');
  if (activeBtn) activeBtn.classList.add('btn-primary');

  // Filter rows
  rows.forEach((row) => {
    const rowStatus = row.dataset.status;
    let show = false;

    if (status === 'all') {
      show = true;
    } else if (status === 'checked-in') {
      show = rowStatus === 'checked_in';
    } else if (status === 'pending') {
      show = rowStatus !== 'checked_in' && rowStatus !== 'no_show';
    }

    row.style.display = show ? '' : 'none';
  });
};

/**
 * Search vendors by farm name
 */
const vendorSearchInput = document.getElementById('vendorSearch');
if (vendorSearchInput) {
  vendorSearchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    const results = document.getElementById('searchResults');

    if (!query) {
      results.classList.add('hidden');
      return;
    }

    const vendors = document.querySelectorAll('.vendor-row');
    const matches = [];

    vendors.forEach((vendor) => {
      const farmName = vendor.dataset.farmName;
      if (farmName.includes(query)) {
        matches.push(vendor);
      }
    });

    if (matches.length === 0) {
      results.innerHTML = '<p class="p-3 text-xs text-gray-500">No vendors found</p>';
    } else {
      results.innerHTML = matches
        .slice(0, 5)
        .map((v) => {
          const vendorId = v.dataset.vendorId;
          const vendorName = v.querySelector('h3').textContent;
          return `
        <button
          type="button"
          onclick="document.querySelector('[data-vendor-id=\\\"${vendorId}\\\"]').scrollIntoView({ behavior: 'smooth' }); document.getElementById('searchResults').classList.add('hidden'); document.getElementById('vendorSearch').value = '';"
          class="w-full border-b border-gray-100 p-3 text-left text-sm hover:bg-gray-50">
          ${vendorName}
        </button>
      `;
        })
        .join('');
    }

    results.classList.remove('hidden');
  });
}

// Close modal on background click
const modal = document.getElementById('vendorActionModal');
if (modal) {
  modal.addEventListener('click', function(e) {
    if (e.target === this) {
      closeVendorActionModal();
    }
  });
}

/**
 * Booth Management Modal Functions
 */
window.openCreateLayoutModal = function(marketId) {
  const modal = document.getElementById('createLayoutModal');
  if (modal) {
    document.getElementById('layoutMarketId').value = marketId;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }
};

window.closeCreateLayoutModal = function() {
  const modal = document.getElementById('createLayoutModal');
  if (modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }
};

// Close modal when clicking outside of it
document.addEventListener('DOMContentLoaded', function() {
  const createLayoutModal = document.getElementById('createLayoutModal');
  if (createLayoutModal) {
    createLayoutModal.addEventListener('click', function(e) {
      if (e.target === this) {
        closeCreateLayoutModal();
      }
    });
  }
});
