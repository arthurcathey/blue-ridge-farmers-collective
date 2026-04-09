/**
 * Utilities Module
 * 
 * Common utility functions used across the application.
 * 
 * @module utils
 */

/**
 * Debounce function to limit how often a function is called
 * 
 * @param {Function} func - The function to debounce
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
 * Flash message auto-dismissal
 * Removes flash messages after 5 seconds
 */
export const initFlashMessages = () => {
  const flash = document.querySelector("[data-flash]");
  if (flash) {
    setTimeout(() => {
      flash.remove();
    }, 5000);
  }
};
