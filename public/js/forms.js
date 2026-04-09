/**
 * Forms Module
 * 
 * Handles form validation and interactive form elements.
 * Features:
 * - Real-time field validation (email, URL, phone, text length)
 * - Form submission validation
 * - Error message display with ARIA attributes
 * - Star rating widget with visual feedback
 * - Comprehensive error handling
 * 
 * @module forms
 */

export const Forms = (() => {
  let isInitialized = false;

  /**
   * Validate email address format
   *
   * @param {string} email - Email address to validate
   * @returns {boolean} True if email is valid
   */
  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  /**
   * Validate URL format
   *
   * @param {string} url - URL to validate
   * @returns {boolean} True if URL is valid
   */
  const isValidUrl = (url) => {
    try {
      new URL(url);
      return true;
    } catch (e) {
      return false;
    }
  };

  /**
   * Validate phone number format
   *
   * @param {string} phone - Phone number to validate
   * @returns {boolean} True if phone number has 10-15 digits
   */
  const isValidPhone = (phone) => {
    const digitsOnly = phone.replace(/[^\d]/g, "");
    return digitsOnly.length >= 10 && digitsOnly.length <= 15;
  };

  /**
   * Validate form field based on type and constraints
   *
   * @param {HTMLElement} field - Form field element
   * @returns {Object} Object with valid (boolean) and error (string|null) properties
   */
  const validateField = (field) => {
    const type = field.getAttribute("type") || field.tagName.toLowerCase();
    const value = field.value.trim();
    const pattern = field.getAttribute("pattern");

    if (field.hasAttribute("required") && value === "") {
      return { valid: false, error: "This field is required." };
    }

    if (value === "") {
      return { valid: true, error: null };
    }

    switch (type) {
      case "email":
        if (!isValidEmail(value)) {
          return { valid: false, error: "Invalid email address." };
        }
        break;

      case "url":
      case "website":
        if (value && !isValidUrl(value)) {
          return { valid: false, error: "Invalid URL format." };
        }
        break;

      case "tel":
      case "phone":
        if (!isValidPhone(value)) {
          return { valid: false, error: "Invalid phone number." };
        }
        break;

      case "number":
        const min = parseInt(field.getAttribute("min"), 10);
        const max = parseInt(field.getAttribute("max"), 10);
        const num = parseFloat(value);

        if (!isNaN(min) && num < min) {
          return { valid: false, error: `Must be at least ${min}.` };
        }
        if (!isNaN(max) && num > max) {
          return { valid: false, error: `Must be no more than ${max}.` };
        }
        break;

      case "text":
      case "textarea":
        const minLength = parseInt(field.getAttribute("minlength"), 10);
        const maxLength = parseInt(field.getAttribute("maxlength"), 10);

        if (!isNaN(minLength) && value.length < minLength) {
          return { valid: false, error: `Must be at least ${minLength} characters.` };
        }
        if (!isNaN(maxLength) && value.length > maxLength) {
          return { valid: false, error: `Must be no more than ${maxLength} characters.` };
        }
        break;

      case "password":
        if (value.length < 8) {
          return { valid: false, error: "Password must be at least 8 characters." };
        }
        break;
    }

    if (pattern && !new RegExp(pattern).test(value)) {
      return { valid: false, error: "Invalid format." };
    }

    return { valid: true, error: null };
  };

  /**
   * Display validation error message for field
   *
   * @param {HTMLElement} field - Form field element
   * @param {string} error - Error message text
   * @returns {void}
   */
  const showFieldError = (field, error) => {
    field.setAttribute("aria-invalid", "true");

    let errorElement = field.parentElement.querySelector("[role='alert']");
    if (!errorElement) {
      errorElement = document.createElement("div");
      errorElement.setAttribute("role", "alert");
      errorElement.className = "form-error";
      field.parentElement.appendChild(errorElement);
    }

    errorElement.textContent = error;
    field.setAttribute("aria-describedby", errorElement.id || field.name + "-error");
  };

  /**
   * Clear validation error for field
   *
   * @param {HTMLElement} field - Form field element
   * @returns {void}
   */
  const clearFieldError = (field) => {
    field.setAttribute("aria-invalid", "false");

    const errorElement = field.parentElement.querySelector("[role='alert']");
    if (errorElement) {
      errorElement.textContent = "";
    }
  };

  /**
   * Initialize form validation for all marked forms
   *
   * @returns {void}
   */
  const initFormValidation = () => {
    const forms = document.querySelectorAll("form[data-validate]");

    forms.forEach((form) => {
      const fields = form.querySelectorAll("input, textarea, select");

      fields.forEach((field) => {
        field.addEventListener("blur", () => {
          const result = validateField(field);
          if (!result.valid) {
            showFieldError(field, result.error);
          } else {
            clearFieldError(field);
          }
        });

        field.addEventListener("input", () => {
          if (field.getAttribute("aria-invalid") === "true") {
            const result = validateField(field);
            if (result.valid) {
              clearFieldError(field);
            }
          }
        });
      });

      form.addEventListener("submit", (e) => {
        let hasErrors = false;

        fields.forEach((field) => {
          const result = validateField(field);
          if (!result.valid) {
            showFieldError(field, result.error);
            hasErrors = true;
          } else {
            clearFieldError(field);
          }
        });

        if (hasErrors) {
          e.preventDefault();
        }
      });
    });
  };

  /**
   * Initialize star rating widgets
   *
   * @returns {void}
   */
  const initStarRating = () => {
    const starRatingGroups = document.querySelectorAll("[data-rating-stars]");

    starRatingGroups.forEach((group) => {
      const stars = Array.from(group.querySelectorAll("button[data-rating]"));
      const hiddenInput = group.querySelector("input[type='hidden']");
      const feedbackElement = group.nextElementSibling;

      const updateRatingDisplay = (selectedRating) => {
        stars.forEach((star) => {
          const rating = parseInt(star.getAttribute("data-rating"), 10);
          if (rating <= selectedRating) {
            star.classList.add("selected");
            star.setAttribute("aria-pressed", "true");
          } else {
            star.classList.remove("selected");
            star.setAttribute("aria-pressed", "false");
          }
        });

        if (hiddenInput) {
          hiddenInput.value = selectedRating;
        }

        if (feedbackElement) {
          feedbackElement.textContent = `You rated this ${selectedRating} star${selectedRating > 1 ? "s" : ""}`;
          feedbackElement.setAttribute("role", "status");
          feedbackElement.setAttribute("aria-live", "polite");
        }
      };

      stars.forEach((star) => {
        star.addEventListener("click", () => {
          const rating = parseInt(star.getAttribute("data-rating"), 10);
          updateRatingDisplay(rating);
        });

        star.addEventListener("keydown", (e) => {
          const rating = parseInt(star.getAttribute("data-rating"), 10);

          if (e.key === "ArrowRight" || e.key === "ArrowUp") {
            e.preventDefault();
            const nextStar = stars[rating] || stars[stars.length - 1];
            nextStar.focus();
          } else if (e.key === "ArrowLeft" || e.key === "ArrowDown") {
            e.preventDefault();
            const prevStar = stars[rating - 2] || stars[0];
            prevStar.focus();
          }
        });

        star.addEventListener("mouseenter", () => {
          const rating = parseInt(star.getAttribute("data-rating"), 10);
          stars.forEach((s) => {
            const r = parseInt(s.getAttribute("data-rating"), 10);
            if (r <= rating) {
              s.classList.add("hover");
            } else {
              s.classList.remove("hover");
            }
          });
        });
      });

      group.addEventListener("mouseleave", () => {
        stars.forEach((s) => s.classList.remove("hover"));
      });
    });
  };

  /**
   * Initialize Forms module
   *
   * Sets up form validation and star rating widgets
   *
   * @returns {void}
   */
  const init = () => {
    if (isInitialized) return;
    initFormValidation();
    initStarRating();
    isInitialized = true;
  };

  return { init };
})();
