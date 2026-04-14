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
 * Removes flash message element after 7 second timeout.
 * Users can also close manually by clicking the message.
 *
 * @returns {void}
 */
export const initFlashMessages = () => {
  const flash = document.querySelector("[data-flash]");
  if (flash) {
    flash.style.cursor = 'pointer';
    flash.addEventListener('click', () => {
      flash.remove();
    });

    setTimeout(() => {
      if (flash.parentElement) {
        flash.remove();
      }
    }, 7000);
  }
};
