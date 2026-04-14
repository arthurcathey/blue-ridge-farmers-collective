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
import { Admin } from './admin.js';
import { Carousel } from './carousel.js';
import { Calendar } from './calendar.js';
import { initFlashMessages } from './utils.js';

"use strict";

document.addEventListener("DOMContentLoaded", () => {
  initFlashMessages();
  Navigation.init();
  Forms.init();
  Products.init();
  ScrollEffects.init();
  Carousel.init();
  Calendar.init();
  Admin.init();

  console.log('Blue Ridge Farmers Collective - JavaScript modules initialized');
});
