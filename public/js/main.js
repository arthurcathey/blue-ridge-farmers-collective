/**
 * Blue Ridge Farmers Collective - Main Application JavaScript
 * 
 * This file orchestrates module initialization and acts as the entry point
 * for the application's JavaScript functionality.
 * 
 * Modules imported and initialized:
 * - Navigation: Mobile menus and dropdown management
 * - Forms: Form validation and star rating widgets
 * - Products: Product filtering and search
 * - ScrollEffects: Scroll-related UI effects
 * - Utils: Common utility functions
 * 
 * @file Application initialization and module orchestration
 * @version 2.0.0
 */

import { Navigation } from './navigation.js';
import { Forms } from './forms.js';
import { Products } from './products.js';
import { ScrollEffects } from './scroll.js';
import { Carousel } from './carousel.js';
import { initFlashMessages } from './utils.js';

"use strict";

document.addEventListener("DOMContentLoaded", () => {
  initFlashMessages();
  Navigation.init();
  Forms.init();
  Products.init();
  ScrollEffects.init();
  Carousel.init();

  window.saveVendor = function(vendorId, button) {
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
            button.onclick = () => window.unsaveVendor(vendorId, button);
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

  window.unsaveVendor = function(vendorId, button) {
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
            button.onclick = () => window.saveVendor(vendorId, button);
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

  const adminElements = document.querySelector('[data-admin-page]');
  // Fallback: if data-admin-page isn't set, check if admin functions like syncWeatherBtn exist
  const hasAdminContent = adminElements || document.querySelector('#syncWeatherBtn') || document.querySelector('.admin-section');
  
  console.log('Admin page detected:', !!adminElements);
  console.log('Has admin content (fallback):', !!hasAdminContent);
  
  if (hasAdminContent) {
    console.log('Loading admin.js...');
    import('./admin.js').then(({ Admin }) => {
      console.log('admin.js loaded, calling Admin.init()');
      Admin.init();
      console.log('Admin.init() completed, syncWeather type:', typeof window.syncWeather);
    }).catch(err => {
      console.error('Failed to load Admin module:', err);
    });
  }

  if (document.querySelector('[data-calendar]')) {
    import('./calendar.js').then(({ Calendar }) => {
      Calendar.init();
    });
  }

  // Create placeholder functions for admin features
  // These provide immediate access to admin functions even if module hasn't loaded yet
  if (!window.syncWeather) {
    window.syncWeather = function(...args) {
      console.warn('syncWeather called but admin module not ready yet');
      console.log('Current type:', typeof window.syncWeather);
    };
  }

  console.log('Blue Ridge Farmers Collective - JavaScript modules initialized');
  console.log('syncWeather available:', typeof window.syncWeather);
});
