/**
 * Vendor Module
 * 
 * Handles vendor save/unsave functionality available on public vendor pages
 * 
 * @module vendor
 */

export const Vendor = (() => {
  let isInitialized = false;

  /**
   * Save vendor to user's collection
   *
   * @param {number} vendorId - Vendor ID to save
   * @param {HTMLElement} button - The button element that triggered the action
   * @returns {void}
   */
  const saveVendor = function(vendorId, button) {
    if (!button) {
      button = document.getElementById('saveVendorBtn');
    }

    const csrfField = document.getElementById('csrfToken');
    if (!csrfField) {
      alert('Security token missing. Please refresh the page.');
      return;
    }

    const csrfToken = csrfField.value;
    const formData = new FormData();
    formData.append('vendor_id', vendorId);
    formData.append('csrf_token', csrfToken);

    fetch('/vendors/save', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          if (button) {
            button.textContent = 'Saved ✓';
            button.disabled = true;
            button.classList.remove('btn-action-green');
            button.classList.add('btn-disabled');
            button.onclick = () => unsaveVendor(vendorId, button);
          }
          alert('Vendor saved successfully!');
        } else {
          alert('Error: ' + (data.error || 'Could not save vendor'));
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
  };

  /**
   * Remove vendor from user's collection
   *
   * @param {number} vendorId - Vendor ID to unsave
   * @param {HTMLElement} button - The button element that triggered the action
   * @returns {void}
   */
  const unsaveVendor = function(vendorId, button) {
    if (!button) {
      button = document.getElementById('saveVendorBtn');
    }

    const csrfField = document.getElementById('csrfToken');
    if (!csrfField) {
      alert('Security token missing. Please refresh the page.');
      return;
    }

    const csrfToken = csrfField.value;
    const formData = new FormData();
    formData.append('vendor_id', vendorId);
    formData.append('csrf_token', csrfToken);

    fetch('/vendors/unsave', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          if (button) {
            button.textContent = 'Save Vendor';
            button.disabled = false;
            button.classList.remove('btn-disabled');
            button.classList.add('btn-action-green');
            button.onclick = () => saveVendor(vendorId, button);
          }
          alert('Vendor removed from saved list');
        } else {
          alert('Error: ' + (data.error || 'Could not unsave vendor'));
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
  };

  /**
   * Initialize Vendor module
   *
   * Exposes vendor functions to global scope for inline onclick handlers
   *
   * @returns {void}
   */
  const init = () => {
    if (isInitialized) return;

    window.saveVendor = saveVendor;
    window.unsaveVendor = unsaveVendor;

    isInitialized = true;
  };

  return { init };
})();
