/**
 * Navigation Module
 * 
 * Handles mobile menu toggle, dropdown menus, and keyboard navigation.
 * Features:
 * - Mobile responsive menu with toggle button
 * - Dropdown menu management (open/close)
 * - Keyboard navigation (ArrowDown, ArrowUp, Home, End, Escape)
 * - Click outside to close dropdowns
 * - Automatic menu close on window resize
 * 
 * @module navigation
 */

export const Navigation = (() => {
  let isInitialized = false;

  const init = () => {
    if (isInitialized) return;

    const menuToggle = document.querySelector('[data-menu-toggle]');
    const navLinks = document.querySelector('[data-nav]');
    const dropdowns = Array.from(document.querySelectorAll("[data-dropdown]"));

    const closeMobileMenu = () => {
      if (navLinks) {
        navLinks.removeAttribute('data-mobile-open');
      }
      if (menuToggle) {
        menuToggle.setAttribute('aria-expanded', 'false');
      }
    };

    const closeAllDropdowns = () => {
      dropdowns.forEach((item) => {
        item.classList.remove("is-open");
        const btn = item.querySelector(".nav-trigger");
        const itemMenu = item.querySelector("[data-menu]");
        if (btn) btn.setAttribute("aria-expanded", "false");
        if (itemMenu) itemMenu.setAttribute("aria-hidden", "true");
      });
    };

    // Menu toggle click
    if (menuToggle) {
      menuToggle.addEventListener("click", () => {
        const isOpen = navLinks?.hasAttribute('data-mobile-open');
        if (isOpen) {
          closeMobileMenu();
        } else {
          if (navLinks) navLinks.setAttribute('data-mobile-open', '');
          menuToggle.setAttribute('aria-expanded', 'true');
        }
      });
    }

    // Dropdown menu interactions
    dropdowns.forEach((item) => {
      const btn = item.querySelector(".nav-trigger");
      const itemMenu = item.querySelector("[data-menu]");

      if (!btn) return;

      btn.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();

        const isOpen = item.classList.contains("is-open");
        closeAllDropdowns();

        if (!isOpen) {
          item.classList.add("is-open");
          btn.setAttribute("aria-expanded", "true");
          if (itemMenu) itemMenu.setAttribute("aria-hidden", "false");

          const items = Array.from(itemMenu?.querySelectorAll("a, button") || []);
          if (items.length > 0) {
            items[0].focus();
          }
        }
      });

      // Keyboard navigation
      btn.addEventListener("keydown", (e) => {
        const items = Array.from(itemMenu?.querySelectorAll("a, button") || []);
        if (items.length === 0) return;

        switch (e.key) {
          case "ArrowDown":
            e.preventDefault();
            if (!item.classList.contains("is-open")) {
              item.classList.add("is-open");
              btn.setAttribute("aria-expanded", "true");
              if (itemMenu) itemMenu.setAttribute("aria-hidden", "false");
            }
            items[0].focus();
            break;

          case "ArrowUp":
            e.preventDefault();
            if (!item.classList.contains("is-open")) {
              item.classList.add("is-open");
              btn.setAttribute("aria-expanded", "true");
              if (itemMenu) itemMenu.setAttribute("aria-hidden", "false");
            }
            items[items.length - 1].focus();
            break;

          case "Home":
            e.preventDefault();
            if (item.classList.contains("is-open")) {
              items[0].focus();
            }
            break;

          case "End":
            e.preventDefault();
            if (item.classList.contains("is-open")) {
              items[items.length - 1].focus();
            }
            break;

          case "Escape":
            e.preventDefault();
            closeAllDropdowns();
            btn.focus();
            break;
        }
      });

      // Item navigation within menu
      itemMenu?.addEventListener("keydown", (e) => {
        const items = Array.from(itemMenu.querySelectorAll("a, button"));
        const current = document.activeElement;
        const currentIndex = items.indexOf(current);

        if (e.key === "ArrowDown") {
          e.preventDefault();
          const nextItem = items[currentIndex + 1] || items[0];
          nextItem.focus();
        } else if (e.key === "ArrowUp") {
          e.preventDefault();
          const prevItem = items[currentIndex - 1] || items[items.length - 1];
          prevItem.focus();
        } else if (e.key === "Escape") {
          e.preventDefault();
          closeAllDropdowns();
          btn.focus();
        }
      });
    });

    // Close menu on click outside
    document.addEventListener("click", (e) => {
      if (!e.target.closest("[data-dropdown]") && !e.target.closest("[data-menu-toggle]")) {
        closeAllDropdowns();
      }

      if (!e.target.closest("[data-nav]") && !e.target.closest("[data-menu-toggle]")) {
        closeMobileMenu();
      }
    });

    // Close menus on resize
    window.addEventListener("resize", () => {
      if (window.innerWidth > 768) {
        closeMobileMenu();
        closeAllDropdowns();
      }
    });

    isInitialized = true;
  };

  return { init };
})();
