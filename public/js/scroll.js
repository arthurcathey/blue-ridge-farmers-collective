/**
 * Scroll Effects Module
 * 
 * Handles scroll-related visual effects and animations.
 * Features:
 * - Sticky header on scroll
 * - Back-to-top button visibility toggle
 * - Logo swap on scroll
 * 
 * @module scroll
 */

export const ScrollEffects = (() => {
  let isInitialized = false;

  /**
   * Initialize scroll effects
   *
   * Sets up sticky header on scroll, back-to-top button visibility toggle, and logo swap
   *
   * @returns {void}
   */
  const init = () => {
    if (isInitialized) return;

    const header = document.querySelector("header");
    const logoElement = document.querySelector("[data-scroll-logo]");
    
    if (header) {
      window.addEventListener("scroll", () => {
        if (window.scrollY > 0) {
          header.classList.add("is-scrolled");
          if (logoElement && logoElement.dataset.logoScroll) {
            logoElement.src = logoElement.dataset.logoScroll;
          }
        } else {
          header.classList.remove("is-scrolled");
          if (logoElement && logoElement.dataset.logoDefault) {
            logoElement.src = logoElement.dataset.logoDefault;
          }
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
