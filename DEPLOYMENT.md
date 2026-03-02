# Bluehost Deployment Checklist

## Before Upload
1. Run `npm run tailwind:build` to ensure CSS is up-to-date
2. Test locally: `php -S localhost:8000 -t public/`
3. Verify all changes are committed to git

## Files to Upload to Bluehost
Upload these files from the root project to Bluehost (document root should point to `/public/`):

### Critical (Routing & Core)
- [ ] `public/.htaccess` - URL rewriting rules
- [ ] `public/index.php` - Front controller
- [ ] `config/routes.php` - Application routes

### Stylesheets & JavaScript
- [ ] `public/css/main.css` - Custom styles
- [ ] `public/css/tailwind.css` - Compiled Tailwind utilities
- [ ] `public/js/main.js` - JavaScript functionality

### Images
- [ ] `public/images/banners/logo.png` - Site logo
- [ ] `public/images/backgrounds/` - Background images
- [ ] `public/images/icons/` - Icon assets

### Views & Templates
- [ ] `src/Views/` - All PHP view templates (keep directory structure)
- [ ] `src/Controllers/` - All PHP controllers
- [ ] `src/Helpers/` - Helper functions
- [ ] `src/Database/` - Database schema & seeds (reference only)

### Configuration
- [ ] `config/database.php` - Database connection
- [ ] `config/env.php` - Environment variables (update with Bluehost credentials)

### Optional (Reference/Docs)
- [ ] `README.md` - Project documentation
- [ ] `package.json` - Node dependencies reference

## Bluehost Setup (One-Time)
1. In cPanel, set **Document Root** to `/public/`
2. Ensure **mod_rewrite** is enabled (contact Bluehost if not)
3. Create database and update `config/env.php` with credentials
4. Import `src/Database/schema.sql` via phpMyAdmin

## After Upload
1. Hard refresh browser: `Ctrl+F5`
2. Test URLs:
   - https://blueridgefarmerscollective.com/ (home)
   - https://blueridgefarmerscollective.com/login (login)
   - https://blueridgefarmerscollective.com/about (about)
3. Check browser console for CSS/JS errors
4. Test on mobile device for responsive design

## Troubleshooting
- **Site still goes to /public/ - `.htaccess` not uploading properly. Check if mod_rewrite is enabled.**
- **CSS/JS 404 - Check paths in `public/` folder are correct.**
- **Database connection error - Verify `config/env.php` credentials match Bluehost database.**
