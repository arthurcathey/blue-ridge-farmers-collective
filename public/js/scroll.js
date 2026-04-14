/**
 * Scroll Effects Module
 * 
 * Handles scroll-related visual effects and animations.
 * Features:
 * - Sticky header on scroll
 * - Back-to-top button visibility toggle
 * 
 * @module scroll
 */

export const ScrollEffects = (() => {
  let isInitialized = false;

  /**
   * Initialize scroll effects
   *
   * Sets up sticky header on scroll and back-to-top button visibility toggle
   *
   * @returns {void}
   */
  const init = () => {
    if (isInitialized) return;

    const header = document.querySelector("header");
    if (header) {
      window.addEventListener("scroll", () => {
        if (window.scrollY > 0) {
          header.classList.add("is-scrolled");
        } else {
          header.classList.remove("is-scrolled");
        }
      });
    }

    const backToTopBtn = document.querySelector("#back-to-top");
    if (backToTopBtn) {
      window.addEventListener("scroll", () => {
        if (window.scrollY > 300) {
          backToTopBtn.classList.add("show");
        } else {
          backToTopBtn.classList.remove("show");
        }
      });

      backToTopBtn.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
      });
    }

    isInitialized = true;
  };

  return { init };
})();
