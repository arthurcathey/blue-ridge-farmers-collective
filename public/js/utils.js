/**
 * Utilities Module
 * 
 * Common utility functions used across the application.
 * 
 * @module utils
 */

/**
 * Debounce function calls with delay
 *
 * @param {Function} func - Function to debounce
 * @param {number} delay - Delay in milliseconds
 * @returns {Function} Debounced function
 */
export const debounce = (func, delay) => {
  let timeoutId;
  return function (...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => func.apply(this, args), delay);
  };
};

/**
 * Initialize flash message auto-removal
 *
 * Removes flash message element after 5 second timeout
 *
 * @returns {void}
 */
export const initFlashMessages = () => {
  const flash = document.querySelector("[data-flash]");
  if (flash) {
    setTimeout(() => {
      flash.remove();
    }, 5000);
  }
};
