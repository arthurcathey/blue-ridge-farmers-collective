/**
 * Carousel Module
 * 
 * Handles carousel functionality for featured vendors and other carousels.
 * Features:
 * - Next/previous button navigation
 * - Dot indicator navigation
 * - Smooth transitions
 * - Auto-cycling support
 * - Keyboard navigation (arrow keys)
 * 
 * @module carousel
 */

export const Carousel = (() => {
  let isInitialized = false;
  const carousels = new Map();

  /**
   * Initialize a single carousel
   *
   * @param {HTMLElement} carouselEl - The carousel container element
   * @returns {void}
   */
  const initCarousel = (carouselEl) => {
    const track = carouselEl.querySelector(".carousel-track");
    const slides = carouselEl.querySelectorAll(".carousel-slide");
    const prevBtn = carouselEl.querySelector("[data-direction='prev']");
    const nextBtn = carouselEl.querySelector("[data-direction='next']");
    const dots = carouselEl.parentElement?.querySelectorAll("[data-slide]") || [];

    if (!track || slides.length === 0) return;

    let currentIndex = 0;

    /**
     * Update carousel position and dots
     *
     * @param {number} index - Slide index to show
     * @returns {void}
     */
    const updateCarousel = (index) => {
      if (index < 0) {
        currentIndex = slides.length - 1;
      } else if (index >= slides.length) {
        currentIndex = 0;
      } else {
        currentIndex = index;
      }

      const translateX = -currentIndex * 100;
      track.style.transform = `translateX(${translateX}%)`;

      dots.forEach((dot, i) => {
        if (i === currentIndex) {
          dot.classList.remove("bg-gray-300");
          dot.classList.add("bg-brand-primary");
        } else {
          dot.classList.remove("bg-brand-primary");
          dot.classList.add("bg-gray-300");
        }
      });
    };

    /**
     * Go to next slide
     *
     * @returns {void}
     */
    const nextSlide = () => {
      updateCarousel(currentIndex + 1);
    };

    /**
     * Go to previous slide
     *
     * @returns {void}
     */
    const prevSlide = () => {
      updateCarousel(currentIndex - 1);
    };

    /**
     * Go to specific slide
     *
     * @param {number} index - Slide index
     * @returns {void}
     */
    const goToSlide = (index) => {
      updateCarousel(parseInt(index, 10));
    };

    if (prevBtn) {
      prevBtn.addEventListener("click", prevSlide);
    }

    if (nextBtn) {
      nextBtn.addEventListener("click", nextSlide);
    }

    dots.forEach((dot) => {
      dot.addEventListener("click", (e) => {
        goToSlide(e.target.getAttribute("data-slide"));
      });
    });

    document.addEventListener("keydown", (e) => {
      const isCarouselFocused =
        carouselEl.contains(document.activeElement) ||
        carouselEl.contains(e.target);

      if (isCarouselFocused) {
        if (e.key === "ArrowLeft") {
          e.preventDefault();
          prevSlide();
        } else if (e.key === "ArrowRight") {
          e.preventDefault();
          nextSlide();
        }
      }
    });

    carousels.set(carouselEl, { updateCarousel, nextSlide, prevSlide });
  };

  /**
   * Initialize all carousels on the page
   *
   * @returns {void}
   */
  const init = () => {
    if (isInitialized) return;

    const allCarousels = document.querySelectorAll("[data-carousel]");
    allCarousels.forEach(initCarousel);

    isInitialized = true;
  };

  return { init };
})();
