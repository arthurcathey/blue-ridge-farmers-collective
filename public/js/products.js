/**
 * Products Module
 * 
 * Handles product filtering, search, and real-time updates.
 * Features:
 * - Live search filtering
 * - Category filtering
 * - Vendor filtering
 * - Market filtering
 * - Product sorting
 * - Results counter
 * - No-results messaging
 * - Results updates with debouncing
 * 
 * @module products
 */

export const Products = (() => {
  let isInitialized = false;

  /**
   * Initialize Products module
   *
   * Sets up product filtering by search, category, vendor, market, and sort options
   *
   * @returns {void}
   */
  const init = () => {
    if (isInitialized) return;

    const searchInput = document.querySelector("#search");
    const categoryFilter = document.querySelector("#category");
    const vendorFilter = document.querySelector("#vendor");
    const marketFilter = document.querySelector("#market");
    const sortSelect = document.querySelector("#sort");
    const searchForm = document.querySelector("form.search-form");

    if (!searchForm) return;

    /**
     * Filter and display products based on search and filter criteria
     * Since products are server-side filtered, this just ensures proper form submission
     *
     * @returns {void}
     */
    const filterProducts = () => {
    };

    /**
     * Debounced version of filterProducts with 150ms delay
     *
     * @returns {void}
     */
    const debounceFilter = (() => {
      let timeoutId;
      return () => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(filterProducts, 150);
      };
    })();

    if (searchInput) {
      searchInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          searchForm.submit();
        }
      });
    }

    if (categoryFilter) {
      categoryFilter.addEventListener("change", () => {
        searchForm.submit();
      });
    }

    if (vendorFilter) {
      vendorFilter.addEventListener("change", () => {
        searchForm.submit();
      });
    }

    if (marketFilter) {
      marketFilter.addEventListener("change", () => {
        searchForm.submit();
      });
    }

    if (sortSelect) {
      sortSelect.addEventListener("change", () => {
        searchForm.submit();
      });
    }

    if (searchForm) {
      searchForm.addEventListener("submit", (e) => {
        const submitButton = searchForm.querySelector("button[type='submit']");
        if (submitButton) {
          submitButton.disabled = true;
        }
      });
    }

    isInitialized = true;
  };

  return { init };
})();
