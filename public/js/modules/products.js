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

  const init = () => {
    if (isInitialized) return;

    const searchInput = document.querySelector("[data-search-products]");
    const categoryFilter = document.querySelector("[data-filter-category]");
    const vendorFilter = document.querySelector("[data-filter-vendor]");
    const marketFilter = document.querySelector("[data-filter-market]");
    const sortSelect = document.querySelector("[data-sort-products]");
    const productCardsContainer = document.querySelector("[data-products-container]");
    const resultsCounter = document.querySelector("[data-results-count]");
    const noResultsMessage = document.querySelector("[data-no-results]");

    if (!productCardsContainer) return; // Not on products page

    /**
     * Filter and display products based on current filter values
     */
    const filterProducts = () => {
      const searchTerm = (searchInput?.value || "").toLowerCase();
      const categoryValue = categoryFilter?.value || "";
      const vendorValue = vendorFilter?.value || "";
      const marketValue = marketFilter?.value || "";
      const sortBy = sortSelect?.value || "newest";

      let productCards = Array.from(
        productCardsContainer.querySelectorAll("[data-product-id]")
      );

      // Apply search filter
      if (searchTerm) {
        productCards = productCards.filter((card) => {
          const name = card.querySelector("[data-product-name]")?.textContent || "";
          const description = card.querySelector("[data-product-description]")?.textContent || "";
          const vendor = card.querySelector("[data-product-vendor]")?.textContent || "";

          return (
            name.toLowerCase().includes(searchTerm) ||
            description.toLowerCase().includes(searchTerm) ||
            vendor.toLowerCase().includes(searchTerm)
          );
        });
      }

      // Apply category filter
      if (categoryValue) {
        productCards = productCards.filter((card) => {
          const category = card.getAttribute("data-product-category") || "";
          return category === categoryValue;
        });
      }

      // Apply vendor filter
      if (vendorValue) {
        productCards = productCards.filter((card) => {
          const vendor = card.getAttribute("data-product-vendor-id") || "";
          return vendor === vendorValue;
        });
      }

      // Apply market filter
      if (marketValue) {
        productCards = productCards.filter((card) => {
          const market = card.getAttribute("data-product-market-id") || "";
          return market === marketValue;
        });
      }

      // Apply sorting
      if (sortBy === "newest") {
        productCards.sort(
          (a, b) =>
            new Date(b.getAttribute("data-created-at")) -
            new Date(a.getAttribute("data-created-at"))
        );
      } else if (sortBy === "name-asc") {
        productCards.sort((a, b) => {
          const nameA = (a.querySelector("[data-product-name]")?.textContent || "").toLowerCase();
          const nameB = (b.querySelector("[data-product-name]")?.textContent || "").toLowerCase();
          return nameA.localeCompare(nameB);
        });
      }

      // Update visibility
      let visibleCount = 0;
      Array.from(productCardsContainer.querySelectorAll("[data-product-id]")).forEach(
        (card) => {
          if (productCards.includes(card)) {
            card.style.display = "";
            visibleCount++;
          } else {
            card.style.display = "none";
          }
        }
      );

      // Update results counter
      if (resultsCounter) {
        resultsCounter.textContent = visibleCount;
      }

      // Show/hide no-results message
      if (noResultsMessage) {
        if (visibleCount === 0) {
          noResultsMessage.style.display = "";
        } else {
          noResultsMessage.style.display = "none";
        }
      }
    };

    /**
     * Debounced filter function
     */
    const debounceFilter = (() => {
      let timeoutId;
      return () => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(filterProducts, 150);
      };
    })();

    // Attach event listeners
    if (searchInput) {
      searchInput.addEventListener("input", debounceFilter);
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

    isInitialized = true;
  };

  return { init };
})();
