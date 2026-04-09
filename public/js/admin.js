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

  const getCsrfToken = () => {
    return document.querySelector('[name="csrf_token"]')?.value || '';
  };

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

  const openVendorMenu = function(vendorId, status) {
    currentVendorId = vendorId;
    currentDateId = document.querySelector('[name="date_id"]')?.value;

    const modal = document.querySelector('[data-vendor-action-modal]');
    if (modal) {
      modal.removeAttribute('hidden');
    }
  };

  const closeVendorActionModal = function() {
    const modal = document.querySelector('[data-vendor-action-modal]');
    if (modal) {
      modal.setAttribute('hidden', '');
    }
    currentVendorId = null;
  };

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

  const approveTransfer = function(transferId, vendorName) {
    if (!confirm(`Approve transfer for ${vendorName}?`)) return;

    const formData = new FormData();
    formData.append('transfer_id', transferId);
    formData.append('csrf_token', getCsrfToken());

    fetch('/admin/vendor-transfer-requests/approve', {
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
        console.error('Failed to approve transfer:', err);
        alert('Failed to approve transfer');
      });
  };

  const showRejectModal = function(transferId, vendorName) {
    const modal = document.querySelector('[data-reject-modal]');
    if (modal) {
      modal.removeAttribute('hidden');
      const input = modal.querySelector('[name="transfer_id"]');
      if (input) input.value = transferId;
    }
  };

  const closeRejectModal = function() {
    const modal = document.querySelector('[data-reject-modal]');
    if (modal) {
      modal.setAttribute('hidden', '');
    }
  };

  const submittReject = function(event) {
    event?.preventDefault?.();

    const transferId = document.querySelector('[name="transfer_id"]')?.value;
    const reason = document.querySelector('[name="rejection_reason"]')?.value;

    if (!transferId || !reason) {
      alert('Please provide a rejection reason');
      return;
    }

    const formData = new FormData();
    formData.append('transfer_id', transferId);
    formData.append('rejection_reason', reason);
    formData.append('csrf_token', getCsrfToken());

    fetch('/admin/vendor-transfer-requests/reject', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.ok ? res.json() : Promise.reject(new Error(`HTTP ${res.status}`)))
      .then(() => {
        window.location.reload();
      })
      .catch((err) => {
        console.error('Failed to reject transfer:', err);
        alert('Failed to reject transfer');
      });
  };

  const saveVendor = function(vendorId) {
    const csrfField = document.getElementById('csrfToken');
    if (!csrfField) {
      alert('Security token missing. Please refresh the page.');
      return;
    }

    const csrfToken = csrfField.value;
    const formData = new FormData();
    formData.append('vendor_id', vendorId);
    formData.append('csrf_token', csrfToken);

    fetch('/save-vendor', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const button = event?.target;
          if (button) {
            button.textContent = 'Saved';
            button.disabled = true;
            button.onclick = () => unsaveVendor(vendorId);
          }
        } else {
          alert('Error: ' + (data.error || 'Could not save vendor'));
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
  };

  const unsaveVendor = function(vendorId) {
    const csrfField = document.getElementById('csrfToken');
    if (!csrfField) {
      alert('Security token missing. Please refresh the page.');
      return;
    }

    const csrfToken = csrfField.value;
    const formData = new FormData();
    formData.append('vendor_id', vendorId);
    formData.append('csrf_token', csrfToken);

    fetch('/unsave-vendor', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const button = event?.target;
          if (button) {
            button.textContent = 'Save Vendor';
            button.disabled = false;
            button.onclick = () => saveVendor(vendorId);
          }
        } else {
          alert('Error: ' + (data.error || 'Could not unsave vendor'));
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
  };

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

  const openCreateLayoutModal = function(marketId) {
    const modal = document.getElementById('createLayoutModal');
    if (modal) {
      const marketInput = document.getElementById('layoutMarketId');
      if (marketInput) marketInput.value = marketId;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }
  };

  const closeCreateLayoutModal = function() {
    const modal = document.getElementById('createLayoutModal');
    if (modal) {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }
  };

  const openAssignmentModal = function(boothId) {
    const modal = document.querySelector('[data-assignment-modal]');
    if (modal) {
      modal.removeAttribute('hidden');
      const input = modal.querySelector('[name="booth_id"]');
      if (input) input.value = boothId;
    }
  };

  const closeAssignmentModal = function() {
    const modal = document.querySelector('[data-assignment-modal]');
    if (modal) {
      modal.setAttribute('hidden', '');
    }
  };

  const highlightVendor = function(vendorId) {
    document.querySelectorAll('[data-vendor-option]').forEach((row) => {
      row.classList.toggle('highlight', row.getAttribute('data-vendor-option') === String(vendorId));
    });
  };

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

  const selectBooth = function(boothId) {
    const select = document.querySelector('[name="booth_id"]');
    if (select) select.value = boothId;
  };

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

  const openEditAdminModal = function(adminId, adminName, adminRole) {
    const modal = document.querySelector('[data-edit-admin-modal]');
    if (modal) {
      modal.removeAttribute('hidden');
      const idInput = modal.querySelector('[name="admin_id"]');
      const nameInput = modal.querySelector('[name="admin_name"]');
      const roleSelect = modal.querySelector('[name="admin_role"]');
      if (idInput) idInput.value = adminId;
      if (nameInput) nameInput.value = adminName;
      if (roleSelect) roleSelect.value = adminRole;
    }
  };

  const closeEditAdminModal = function() {
    const modal = document.querySelector('[data-edit-admin-modal]');
    if (modal) {
      modal.setAttribute('hidden', '');
    }
  };

  const closeBoothEditor = function() {
    const editor = document.querySelector('[data-booth-editor]');
    if (editor) {
      editor.setAttribute('hidden', '');
    }
  };

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
    window.saveVendor = saveVendor;
    window.unsaveVendor = unsaveVendor;
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

    isInitialized = true;
  };

  return { init };
})();
