# Bluehost Deployment Guide

**Domain:** https://blueridgefarmerscollective.com/  
**Date:** February 15, 2026  
**Project:** Blue Ridge Farmers Collective

---

## 📋 Pre-Deployment Checklist

- [ ] Bluehost account set up
- [ ] Domain registered and pointing to Bluehost
- [ ] cPanel access available
- [ ] FTP/SFTP credentials obtained
- [ ] MySQL database created on Bluehost
- [ ] Database user created with permissions
- [ ] Files ready to upload

---

## 🚀 Step 1: Access Bluehost cPanel

1. Go to **https://bluehost.com** → Login
2. Click **cPanel** (or go to your domain's cPanel directly)
3. Navigate to **MySQL Databases** or **Database Wizard**

### Create MySQL Database on Bluehost:
- **Database Name:** `blueridge_farmers_db` (or `yourusername_blueridge`)
- **Username:** `blueridge_user` (or `yourusername_user`)
- **Password:** Use a strong password (save it!)
- Add user to database with ALL privileges

---

## 📝 Step 2: Update database.php for Bluehost

**Edit:** `config/database.php`

Replace with your Bluehost database details:

```php
<?php

declare(strict_types=1);

return [
  'driver' => getenv('DB_DRIVER') ?: 'mysql',
  'host' => getenv('DB_HOST') ?: 'localhost',  // Bluehost usually uses 'localhost'
  'port' => getenv('DB_PORT') ?: '3306',
  'database' => getenv('DB_NAME') ?: 'yourusername_blueridge_farmers_db',  // CHANGE THIS
  'username' => getenv('DB_USER') ?: 'yourusername_blueridge_user',        // CHANGE THIS
  'password' => getenv('DB_PASS') ?: 'your_strong_password_here',          // CHANGE THIS
  'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
  'options' => [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
  ],
];
```

⚠️ **Important:** 
- Replace `yourusername` with your actual Bluehost account username
- Replace `your_strong_password_here` with the database password you created
- Bluehost typically prefixes database names with your username

---

## 🗄️ Step 3: Upload Database to Bluehost

### Option A: Using cPanel phpMyAdmin (Recommended)

1. In cPanel → **phpMyAdmin**
2. Create new database (if not created yet)
3. Click database name → **Import**
4. Choose file: `src/Database/blueridge_farmers_db_dump.sql`
5. Click **Go** and wait for completion

### Option B: Using SSH (If you prefer)

```bash
# SSH into Bluehost
ssh yourusername@blueridgefarmerscollective.com

# Navigate to project directory
cd public_html/blue_ridge_farmers_collective

# Import database
mysql -u yourusername_blueridge_user -p yourusername_blueridge_farmers_db < src/Database/blueridge_farmers_db_dump.sql

# Enter password when prompted
```

---

## 📂 Step 4: Upload Files via FTP

### Using FTP Client (FileZilla, WinSCP, etc.)

1. **Download FTP credentials from cPanel:**
   - cPanel → FTP Accounts → Your default FTP account
   - Get: Host, Username, Password, Port (usually 21)

2. **Connect via FTP:**
   - Host: `blueridgefarmerscollective.com`
   - Username: `yourusername` (or your FTP user)
   - Password: (from cPanel)
   - Port: 21

3. **Navigate to:** `/public_html`

4. **Create folder:** `blue_ridge_farmers_collective` (if needed)

5. **Upload these folders/files:**
   ```
   public/               (all files)
   src/                  (all files)
   config/               (all files EXCEPT database.php - update that first!)
   package.json          (optional, if using Node)
   tailwind.config.js    (if using Tailwind)
   .gitignore
   README.md
   ```

6. **DO NOT upload:**
   - `.env` (create new one on server)
   - `node_modules/` (run `npm install` on server instead)
   - `.git/` (optional, not needed on production)

---

## ⚙️ Step 5: Update config/database.php on Bluehost

**After uploading**, edit the config/database.php file on Bluehost with correct credentials.

**Using cPanel File Manager:**
1. cPanel → **File Manager**
2. Navigate to: `public_html/blue_ridge_farmers_collective/config/`
3. Right-click `database.php` → **Edit**
4. Update database credentials (host, database name, username, password)
5. Save

**Or using SSH:**
```bash
ssh yourusername@blueridgefarmerscollective.com
nano public_html/blue_ridge_farmers_collective/config/database.php
```

---

## ✅ Step 6: Verify Deployment

Test your deployment:

### 1. Test Database Proof Page
```
https://blueridgefarmerscollective.com/blue_ridge_farmers_collective/public/database_proof.php
```

Should show:
- ✓ Connection Status: Connected
- ✓ Database information
- ✓ Lists of roles, vendors, products, markets
- ✓ No error messages

### 2. Test Main Index Page
```
https://blueridgefarmerscollective.com/blue_ridge_farmers_collective/public/index.php
```

### 3. Check Permissions
Files should have:
- Directories: `755` (rwxr-xr-x)
- PHP files: `644` (rw-r--r--)

**Using cPanel File Manager:**
1. Right-click folder → **Change Permissions**
2. Set to 755 for directories, 644 for files

---

## 🔧 Troubleshooting

### Error: "Connection refused" or "Can't connect to database"
- [ ] Check database credentials in `config/database.php`
- [ ] Verify database exists in cPanel → MySQL Databases
- [ ] Confirm database user has privileges
- [ ] Use `localhost` as host (not IP address)

### Error: "database_proof.php not found" (404)
- [ ] Check folder path in URL
- [ ] Verify files uploaded to correct location
- [ ] Check permissions are 644 for files

### Error: "Permission denied" (403)
- [ ] Check file permissions (should be 644)
- [ ] Check directory permissions (should be 755)
- [ ] Use cPanel File Manager to fix

### PHP Errors/Blank Page
- [ ] Enable error logging in cPanel
- [ ] Check cPanel → Error Log
- [ ] Verify all required PHP extensions enabled (PDO, MySQL)

**Test PHP Info:**
```
https://blueridgefarmerscollective.com/blue_ridge_farmers_collective/public/phpinfo.php
```

(Create temp file with `<?php phpinfo(); ?>` to test)

---

## 📋 File Structure on Bluehost

```
public_html/
├── blue_ridge_farmers_collective/
│   ├── config/
│   │   ├── config.php
│   │   ├── database.php         (UPDATE WITH BLUEHOST CREDENTIALS)
│   │   ├── database-connection.php
│   │   └── routes.php
│   ├── public/
│   │   ├── index.php
│   │   ├── database_proof.php   (PROOF PAGE)
│   │   ├── css/
│   │   ├── js/
│   │   └── uploads/
│   ├── src/
│   │   ├── Controllers/
│   │   ├── Database/
│   │   ├── Models/
│   │   └── Views/
│   └── README.md
```

---

## 🔒 Security Notes

⚠️ **For Production:**
1. Create `.env` file on server with sensitive data:
   ```
   DB_DRIVER=mysql
   DB_HOST=localhost
   DB_NAME=yourusername_blueridge_farmers_db
   DB_USER=yourusername_blueridge_user
   DB_PASS=your_strong_password
   ```

2. Remove credentials from `config/database.php` (use getenv only)

3. Set file permissions:
   - Upload directories: 755
   - Config files: 600 (readable only by owner)
   - Public files: 644

4. Disable directory listing:
   - Create `.htaccess` with `Options -Indexes`

---

## 📞 Teacher Access

Once deployed, teacher can verify:
- Visit: `https://blueridgefarmerscollective.com/blue_ridge_farmers_collective/public/database_proof.php`
- See live database content
- No AMPPS or localhost needed
- Professional deployment proof

---

## 🎯 Final Checklist

- [ ] Database created on Bluehost
- [ ] Database dump imported successfully
- [ ] Files uploaded via FTP
- [ ] database.php updated with Bluehost credentials
- [ ] database_proof.php accessible and working
- [ ] No errors in browser console
- [ ] All pages load correctly
- [ ] Teacher can access the proof page
- [ ] GitHub repo updated with deployment info

---

## 📚 Additional Resources

- Bluehost Help: https://www.bluehost.com/help
- cPanel Tutorial: https://documentation.cpanel.net/
- MySQL on Bluehost: https://my.bluehost.com/cgi-bin/help/mysql

---

**Next Steps:**
1. Get Bluehost FTP credentials
2. Follow steps 1-6 in order
3. Test deployment
4. Update Moodle with live URL
5. Create PROOF.md on GitHub with new live URL

