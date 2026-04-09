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

  const init = () => {
    if (isInitialized) return;

    // Sticky header effect
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

    // Back-to-top button
    const backToTopBtn = document.querySelector("[data-back-to-top]");
    if (backToTopBtn) {
      window.addEventListener("scroll", () => {
        if (window.scrollY > 300) {
          backToTopBtn.style.display = "block";
        } else {
          backToTopBtn.style.display = "none";
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
