/**
 * Admin Module
 * 
 * Handles interactive admin features including vendor attendance,
 * marketplace management, and booth assignments.
 * Functions are attached to window object for onclick handler compatibility.
 * 
 * @module admin
 */

export const Admin = (() => {
  let isInitialized = false;
  let currentVendorId = null;
  let currentDateId = null;

  /**
   * Retrieve CSRF token from form
   * Looks for any input field with name="csrf_token"
   *
   * @returns {string} CSRF token value or empty string
   */
  const getCsrfToken = () => {
    
    const token = document.querySelector('[name="csrf_token"]')?.value || '';
    if (!token) {
      console.warn('CSRF token not found on page');
    }
    return token;
  };

  /**
   * Check in vendor to market date
   *
   * @param {number} vendorId - Vendor ID
   * @param {string} farmName - Farm name for confirmation
   * @returns {void}
   */
  const checkInVendor = function(vendorId, farmName) {
    const dateInput = document.querySelector('[name="date_id"]');
    const dateId = dateInput?.value;

    if (!dateId) {
      alert('Please select a market date first');
      return;
    }

    if (!confirm(`Check in ${farmName}?`)) return;

    const formData = new FormData();
    formData.append('vendor_id', vendorId);
    formData.append('date_id', dateId);
    formData.append('csrf_token', getCsrfToken());

    fetch('/admin/vendor-attendance/check-in', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.ok ? res.json() : Promise.reject(new Error(`HTTP ${res.status}`)))
      .then((data) => {
        if (data.error) {
          alert('Error: ' + data.error);
          return;
        }
        window.location.reload();
      })
      .catch((err) => {
        console.error('Failed to check in vendor:', err);
        alert('Failed to check in vendor. See console for details.');
      });
  };

  /**
   * Mark vendor as no-show for selected date
   *
   * @returns {void}
   */
  const markAsNoShow = function() {
    if (!currentVendorId || !currentDateId) {
      alert('No vendor selected');
      return;
    }

    const formData = new FormData();
    formData.append('vendor_id', currentVendorId);
    formData.append('date_id', currentDateId);
    formData.append('csrf_token', getCsrfToken());

    fetch('/admin/vendor-attendance/no-show', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.json())
      .then(() => {
        closeVendorActionModal();
        window.location.reload();
      })
      .catch((err) => {
        console.error('Error marking no-show:', err);
        alert('Failed to update vendor status');
      });
  };

  /**
   * Mark vendor as confirmed for selected date
   *
   * @returns {void}
   */
  const markAsConfirmed = function() {
    if (!currentVendorId || !currentDateId) {
      alert('No vendor selected');
      return;
    }

    const formData = new FormData();
    formData.append('vendor_id', currentVendorId);
    formData.append('date_id', currentDateId);
    formData.append('csrf_token', getCsrfToken());

    fetch('/admin/vendor-attendance/confirm', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.json())
      .then(() => {
        closeVendorActionModal();
        window.location.reload();
      })
      .catch((err) => {
        console.error('Error marking confirmed:', err);
        alert('Failed to update vendor status');
      });
  };

  /**
   * Remove no-show status from vendor
   *
   * @param {number} vendorId - Vendor ID to update
   * @returns {void}
   */
  const undoNoShow = function(vendorId) {
    const dateInput = document.querySelector('[name="date_id"]');
    const dateId = dateInput?.value;

    if (!dateId) {
      alert('Please select a market date first');
      return;
    }

    const formData = new FormData();
    formData.append('vendor_id', vendorId);
    formData.append('date_id', dateId);
    formData.append('csrf_token', getCsrfToken());

    fetch('/admin/vendor-attendance/undo-no-show', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.json())
      .then(() => window.location.reload())
      .catch((err) => {
        console.error('Error undoing no-show:', err);
        alert('Failed to update vendor status');
      });
  };

  /**
   * Open vendor action menu modal
   *
   * @param {number} vendorId - Vendor ID
   * @param {string} status - Current vendor status
   * @returns {void}
   */
  const openVendorMenu = function(vendorId, status) {
    currentVendorId = vendorId;
    currentDateId = document.querySelector('[name="date_id"]')?.value;

    const modal = document.getElementById('vendorActionModal');
    if (modal) {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }
  };

  /**
   * Close vendor action modal
   *
   * @returns {void}
   */
  const closeVendorActionModal = function() {
    const modal = document.getElementById('vendorActionModal');
    if (modal) {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }
    currentVendorId = null;
  };

  /**
   * Filter vendor rows by status
   *
   * @param {string} status - Status to filter by (or 'all')
   * @returns {void}
   */
  const filterByStatus = function(status) {
    const rows = document.querySelectorAll('[data-vendor-status]');
    rows.forEach((row) => {
      const rowStatus = row.getAttribute('data-vendor-status');
      row.style.display = status === 'all' || rowStatus === status ? '' : 'none';
    });

    document.querySelectorAll('[data-status-filter]').forEach((btn) => {
      btn.classList.toggle('active', btn.getAttribute('data-status-filter') === status);
    });
  };

  /**
   * Approve vendor transfer request
   *
   * @param {number} transferId - Transfer request ID
   * @param {string} vendorName - Vendor name for confirmation
   * @returns {void}
   */
  const approveTransfer = function(transferId, vendorName) {
    if (!confirm(`Approve transfer for ${vendorName}?`)) return;

    const csrfToken = getCsrfToken();
    if (!csrfToken) {
      alert('Security token missing. Please refresh the page.');
      return;
    }

    const formData = new FormData();
    formData.append('transfer_id', transferId);
    formData.append('csrf_token', csrfToken);

    fetch('/admin/vendor-transfer-requests/approve', {
      method: 'POST',
      body: formData,
    })
      .then((res) => {
        return res.text().then(text => {
          try {
            const data = JSON.parse(text);
            return { ok: res.ok, status: res.status, data: data };
          } catch (e) {
            console.error('Server returned non-JSON response:', text);
            return { ok: res.ok, status: res.status, data: { error: 'Server error: ' + text.substring(0, 100) } };
          }
        });
      })
      .then(({ ok, status, data }) => {
        if (data.error) {
          alert('Error: ' + data.error);
          console.error('Transfer error:', data.error);
          return;
        }
        if (data.success) {
          alert('Transfer approved successfully!');
          window.location.reload();
        } else {
          alert('Unexpected response from server');
          console.error('Unexpected response:', data);
        }
      })
      .catch((err) => {
        console.error('Failed to approve transfer:', err);
        alert('Failed to approve transfer. See console for details.');
      });
  };

  /**
   * Show rejection modal for transfer request
   *
   * @param {number} transferId - Transfer request ID
   * @param {string} vendorName - Vendor name
   * @returns {void}
   */
  const showRejectModal = function(transferId, vendorName) {
    const modal = document.getElementById('rejectModal');
    if (modal) {
      modal.classList.remove('hidden');
      const input = document.getElementById('modalTransferId');
      if (input) input.value = transferId;
    }
  };

  /**
   * Close rejection modal
   *
   * @returns {void}
   */
  const closeRejectModal = function() {
    const modal = document.getElementById('rejectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  /**
   * Submit transfer rejection with reason
   *
   * @param {Event} event - Form submission event
   * @returns {void}
   */
  const submittReject = function(event) {
    event?.preventDefault?.();

    const transferId = document.querySelector('[name="transfer_id"]')?.value;
    const reason = document.querySelector('[name="admin_notes"]')?.value;

    if (!transferId) {
      alert('Transfer ID not found');
      return;
    }

    const formData = new FormData();
    formData.append('transfer_id', transferId);
    formData.append('admin_notes', reason);
    formData.append('csrf_token', getCsrfToken());

    fetch('/admin/vendor-transfer-requests/reject', {
      method: 'POST',
      body: formData,
    })
      .then((res) => {
        // Always try to parse as JSON
        return res.text().then(text => {
          try {
            const data = JSON.parse(text);
            return { ok: res.ok, status: res.status, data: data };
          } catch (e) {
            console.error('Server returned non-JSON response:', text);
            return { ok: res.ok, status: res.status, data: { error: 'Server error: ' + text.substring(0, 100) } };
          }
        });
      })
      .then(({ ok, status, data }) => {
        if (data.error) {
          alert('Error: ' + data.error);
          console.error('Rejection error:', data.error);
          return;
        }
        if (data.success) {
          alert('Transfer rejected successfully!');
          closeRejectModal();
          window.location.reload();
        } else {
          alert('Unexpected response from server');
          console.error('Unexpected response:', data);
        }
      })
      .catch((err) => {
        console.error('Failed to reject transfer:', err);
        alert('Failed to reject transfer. See console for details.');
      });
  };


  /**
   * Save vendor to user's collection
   *
   * @param {number} vendorId - Vendor ID to save
   * @param {HTMLElement} button - The button element that triggered the action
   * @returns {void}
   */
  const saveVendor = function(vendorId, button) {    if (!button) {
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
   * Delete vendor profile photo
   *
   * @returns {void}
   */
  const deleteVendorPhoto = function() {
    const performerId = document.querySelector('[name="performer_id"]')?.value;
    if (!performerId) {
      alert('Performer ID not found');
      return;
    }

    if (!confirm('Are you sure you want to delete this photo?')) return;

    const csrfToken = document.querySelector('[name="csrf_token"]')?.value || '';
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);

    fetch('/vendor/delete-photo', {
      method: 'POST',
      body: formData,
    })
      .then((response) => {
        if (response.ok) {
          window.location.reload();
        } else {
          alert('Failed to delete photo. Please try again.');
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
  };

  /**
   * Delete market image
   *
   * @param {number} marketId - Market ID
   * @returns {void}
   */
  const deleteMarketImage = function(marketId) {
    if (!confirm('Are you sure you want to delete this image?')) return;

    const formData = new FormData();
    formData.append('csrf_token', getCsrfToken());

    fetch(`/admin/markets/delete-image?market_id=${marketId}`, {
      method: 'POST',
      body: formData,
    })
      .then((response) => {
        if (response.ok) {
          window.location.reload();
        } else {
          alert('Failed to delete image. Please try again.');
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
  };

  /**
   * Open booth layout creation modal
   *
   * @param {number} marketId - Market ID
   * @returns {void}
   */
  const openCreateLayoutModal = function(marketId) {
    const modal = document.getElementById('createLayoutModal');
    if (modal) {
      const marketInput = document.getElementById('layoutMarketId');
      if (marketInput) marketInput.value = marketId;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }
  };

  /**
   * Close booth layout modal
   *
   * @returns {void}
   */
  const closeCreateLayoutModal = function() {
    const modal = document.getElementById('createLayoutModal');
    if (modal) {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }
  };

  /**
   * Open booth assignment modal
   *
   * @param {number} boothId - Booth ID
   * @returns {void}
   */
  const openAssignmentModal = function(boothId) {
    const modal = document.getElementById('assignmentModal');
    if (modal) {
      modal.classList.remove('hidden');
      const input = document.getElementById('modalBoothId');
      if (input) input.value = boothId;
    }
  };

  /**
   * Close booth assignment modal
   *
   * @returns {void}
   */
  const closeAssignmentModal = function() {
    const modal = document.getElementById('assignmentModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  /**
   * Highlight vendor row by ID
   *
   * @param {number} vendorId - Vendor ID to highlight
   * @returns {void}
   */
  const highlightVendor = function(vendorId) {
    document.querySelectorAll('[data-vendor-id]').forEach((row) => {
      row.classList.toggle('highlight', row.getAttribute('data-vendor-id') === String(vendorId));
    });
  };

  /**
   * Unassign vendor from booth
   *
   * @returns {void}
   */
  const unassignBooth = function() {
    if (!confirm('Are you sure you want to unassign this vendor?')) return;

    const boothSelect = document.querySelector('[name="booth_id"]');
    const boothId = boothSelect?.value;
    if (!boothId) return;

    const formData = new FormData();
    formData.append('booth_id', boothId);
    formData.append('csrf_token', getCsrfToken());

    fetch('/admin/booths/unassign', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.ok ? res.json() : Promise.reject(new Error(`HTTP ${res.status}`)))
      .then(() => {
        window.location.reload();
      })
      .catch((err) => {
        console.error('Error unassigning booth:', err);
        alert('Failed to unassign booth');
      });
  };

  /**
   * Clear all booths from layout
   *
   * @returns {void}
   */
  const clearLayout = function() {
    if (!confirm('Are you sure you want to clear this layout?')) return;

    const layoutSelect = document.querySelector('[name="layout_id"]');
    const layoutId = layoutSelect?.value;
    if (!layoutId) return;

    const formData = new FormData();
    formData.append('layout_id', layoutId);
    formData.append('csrf_token', getCsrfToken());

    fetch('/admin/layouts/clear', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.ok ? res.json() : Promise.reject(new Error(`HTTP ${res.status}`)))
      .then(() => {
        window.location.reload();
      })
      .catch((err) => {
        console.error('Error clearing layout:', err);
        alert('Failed to clear layout');
      });
  };

  /**
   * Generate grid of booths for layout
   *
   * @returns {void}
   */
  const generateBoothsGrid = function() {
    const layoutId = document.querySelector('[name="layout_id"]')?.value;
    if (!layoutId) {
      alert('Please select a layout first');
      return;
    }

    const formData = new FormData();
    formData.append('layout_id', layoutId);
    formData.append('csrf_token', getCsrfToken());

    fetch('/admin/layouts/generate-grid', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.ok ? res.json() : Promise.reject(new Error(`HTTP ${res.status}`)))
      .then(() => {
        window.location.reload();
      })
      .catch((err) => {
        console.error('Error generating booths:', err);
        alert('Failed to generate booths');
      });
  };

  /**
   * Delete booth from layout
   *
   * @returns {void}
   */
  const deleteBooth = function() {
    const boothId = document.querySelector('[name="booth_id"]')?.value;
    if (!boothId) {
      alert('No booth selected');
      return;
    }

    if (!confirm('Delete this booth?')) return;

    const formData = new FormData();
    formData.append('booth_id', boothId);
    formData.append('csrf_token', getCsrfToken());

    fetch('/admin/booths/delete', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.ok ? res.json() : Promise.reject(new Error(`HTTP ${res.status}`)))
      .then(() => {
        window.location.reload();
      })
      .catch((err) => {
        console.error('Error deleting booth:', err);
        alert('Failed to delete booth');
      });
  };

  /**
   * Select booth by ID in dropdown
   *
   * @param {number} boothId - Booth ID to select
   * @returns {void}
   */
  const selectBooth = function(boothId) {
    const select = document.querySelector('[name="booth_id"]');
    if (select) select.value = boothId;
  };

  /**
   * Cancel vendor transfer request
   *
   * @param {number} transferId - Transfer request ID
   * @returns {void}
   */
  const cancelTransfer = function(transferId) {
    if (!confirm('Cancel this transfer request?')) return;

    const formData = new FormData();
    formData.append('transfer_id', transferId);
    formData.append('csrf_token', getCsrfToken());

    fetch('/vendor/cancel-transfer', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.ok ? res.json() : Promise.reject(new Error(`HTTP ${res.status}`)))
      .then(() => {
        window.location.reload();
      })
      .catch((err) => {
        console.error('Error canceling transfer:', err);
        alert('Failed to cancel transfer');
      });
  };

  /**
   * Regenerate booth ID
   *
   * @returns {void}
   */
  const regenerateBooth = function() {
    const boothId = document.querySelector('[name="booth_id"]')?.value;
    if (!boothId) {
      alert('No booth selected');
      return;
    }

    const formData = new FormData();
    formData.append('booth_id', boothId);
    formData.append('csrf_token', getCsrfToken());

    fetch('/admin/booths/regenerate', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.ok ? res.json() : Promise.reject(new Error(`HTTP ${res.status}`)))
      .then(() => {
        window.location.reload();
      })
      .catch((err) => {
        console.error('Error regenerating booth:', err);
        alert('Failed to regenerate booth');
      });
  };

  /**
   * Remove admin user
   *
   * @param {number} adminId - Admin user ID
   * @param {string} adminName - Admin user name for confirmation
   * @returns {void}
   */
  const removeAdmin = function(adminId, adminName) {
    if (!confirm(`Remove ${adminName} as admin?`)) return;

    const formData = new FormData();
    formData.append('admin_id', adminId);
    formData.append('csrf_token', getCsrfToken());

    fetch('/admin/remove-admin', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.ok ? res.json() : Promise.reject(new Error(`HTTP ${res.status}`)))
      .then(() => {
        window.location.reload();
      })
      .catch((err) => {
        console.error('Error removing admin:', err);
        alert('Failed to remove admin');
      });
  };

  /**
   * Open admin edit modal
   *
   * @param {number} adminId - Admin user ID
   * @param {string} adminName - Admin user name
   * @param {string} adminRole - Admin user role
   * @returns {void}
   */
  const openEditAdminModal = function(adminId, adminName, adminRole) {
    const modal = document.getElementById('editAdminModal');
    if (modal) {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      const idInput = modal.querySelector('[name="admin_id"]');
      const nameInput = modal.querySelector('[name="admin_name"]');
      const roleSelect = modal.querySelector('[name="admin_role"]');
      if (idInput) idInput.value = adminId;
      if (nameInput) nameInput.value = adminName;
      if (roleSelect) roleSelect.value = adminRole;
    }
  };

  /**
   * Close admin edit modal
   *
   * @returns {void}
   */
  const closeEditAdminModal = function() {
    const modal = document.getElementById('editAdminModal');
    if (modal) {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }
  };

  /**
   * Close booth editor modal
   *
   * @returns {void}
   */
  const closeBoothEditor = function() {
    const editor = document.getElementById('boothEditorModal');
    if (editor) {
      editor.classList.add('hidden');
    }
  };

  /**
   * Sync weather data for upcoming market dates
   *
   * @returns {void}
   */
  const syncWeather = function() {
    if (!confirm('Sync weather data for all upcoming market dates? This may take a moment.')) return;

    const button = document.getElementById('syncWeatherBtn');
    if (button) {
      button.disabled = true;
      button.textContent = 'Syncing...';
    }

    const formData = new FormData();
    formData.append('csrf_token', getCsrfToken());

    fetch('/api/admin/weather/sync-market-dates', {
      method: 'POST',
      body: formData,
    })
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
      })
      .then((data) => {
        if (data.error) {
          alert('Error: ' + data.error);
        } else {
          alert(data.message || 'Weather data synced successfully!');
          window.location.reload();
        }
      })
      .catch((err) => {
        console.error('Error syncing weather:', err);
        alert('Failed to sync weather data. See console for details.');
      })
      .finally(() => {
        if (button) {
          button.disabled = false;
          button.textContent = 'Sync Weather';
        }
      });
  };

  /**
   * Initialize Admin module and expose functions to window object
   *
   * Sets up all window event handlers for onclick attributes
   *
   * @returns {void}
   */
  const init = () => {
    if (isInitialized) return;

    window.checkInVendor = checkInVendor;
    window.markAsNoShow = markAsNoShow;
    window.markAsConfirmed = markAsConfirmed;
    window.undoNoShow = undoNoShow;
    window.openVendorMenu = openVendorMenu;
    window.closeVendorActionModal = closeVendorActionModal;
    window.filterByStatus = filterByStatus;
    window.approveTransfer = approveTransfer;
    window.showRejectModal = showRejectModal;
    window.closeRejectModal = closeRejectModal;
    window.submitReject = submittReject;
    window.deleteVendorPhoto = deleteVendorPhoto;
    window.deleteMarketImage = deleteMarketImage;
    window.openCreateLayoutModal = openCreateLayoutModal;
    window.closeCreateLayoutModal = closeCreateLayoutModal;
    window.openAssignmentModal = openAssignmentModal;
    window.closeAssignmentModal = closeAssignmentModal;
    window.highlightVendor = highlightVendor;
    window.unassignBooth = unassignBooth;
    window.clearLayout = clearLayout;
    window.generateBoothsGrid = generateBoothsGrid;
    window.deleteBooth = deleteBooth;
    window.selectBooth = selectBooth;
    window.cancelTransfer = cancelTransfer;
    window.regenerateBooth = regenerateBooth;
    window.removeAdmin = removeAdmin;
    window.openEditAdminModal = openEditAdminModal;
    window.closeEditAdminModal = closeEditAdminModal;
    window.closeBoothEditor = closeBoothEditor;
    window.syncWeather = syncWeather;

    isInitialized = true;
  };

  return { init };
})();
