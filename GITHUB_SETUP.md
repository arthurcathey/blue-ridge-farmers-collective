# GitHub Setup Instructions

## ✅ What I've Done

I've prepared your project for GitHub with:
- ✓ Initialized git repository
- ✓ Created `.gitignore` to protect sensitive files
- ✓ Created comprehensive `README.md`
- ✓ Made initial commit with all project files

## 📋 Current Git Status

```
Repository: Initialized
Branch: master
Commits: 1 (Initial commit: Blue Ridge Farmers Collective database and application)
Files tracked: 72
```

## 🚀 Next Steps to Push to GitHub

Follow these steps to push your code to GitHub:

### Step 1: Create a GitHub Repository

1. Go to [GitHub.com](https://github.com) and log in
2. Click the **+** icon in the top right → **New repository**
3. Repository name: `blue-ridge-farmers-collective`
4. Description: "A comprehensive web application for managing local farmers markets"
5. **Do NOT** initialize with:
   - README (we already have one)
   - .gitignore (we already have one)
   - License
6. Click **Create repository**

### Step 2: Add Remote and Push

In your terminal, navigate to the project directory and run these commands:

```bash
# Navigate to your project
cd "c:\Program Files\Ampps\www\blue_ridge_farmers_collective"

# Add GitHub as remote (replace USERNAME with your GitHub username)
git remote add origin https://github.com/USERNAME/blue-ridge-farmers-collective.git

# Verify the remote was added
git remote -v

# Push to GitHub
git branch -M main
git push -u origin main
```

### Step 3: For SSH (Alternative to HTTPS)

If you prefer SSH authentication:

```bash
git remote add origin git@github.com:USERNAME/blue-ridge-farmers-collective.git
git branch -M main
git push -u origin main
```

## 🔑 Authentication Methods

### HTTPS (Recommended for beginners)
- When prompted, use your GitHub username and a Personal Access Token (PAT)
- Get PAT: GitHub Settings → Developer settings → Personal access tokens
- Permissions needed: `repo`, `write:packages`

### SSH (More secure)
1. Generate SSH key: `ssh-keygen -t ed25519 -C "your_email@example.com"`
2. Add key to GitHub: Settings → SSH and GPG keys → New SSH key
3. Paste your public key from `~/.ssh/id_ed25519.pub`

## 📝 Deliverables Completed

### ✅ Generated Database
- File: `src/Database/schema.sql`
- Status: **Created and Running**
- Database: `blueridge_farmers_db`
- Tables: 35 with proper ENUM and DECIMAL types
- Seed data: 4 test accounts, 1 market, 1 vendor, 13 product categories, 3 sample products

### ✅ SQL Dump File
- File: `src/Database/blueridge_farmers_db_dump.sql`
- Status: **Created**
- Use: `mysql -u arthur -p < src/Database/blueridge_farmers_db_dump.sql`

### ✅ Database Connection PHP Include
- File: `config/database-connection.php`
- Status: **Ready to Use**
- Features: PDO connection, environment variable support, fallback defaults

### ✅ Proof PHP Works with Database
- File: `public/database_proof.php`
- Status: **Ready to Test**
- Access: `http://localhost/blue_ridge_farmers_collective/public/database_proof.php`
- Features: Displays roles, vendors, products, and markets from database

### ✅ GitHub Repository
- Status: **Ready to Push** (waiting for you to create GitHub account)
- Initial commit: 72 files tracked
- Files in git: All project files (sensitive files in .gitignore)

## 🧪 How to Test

### Test Database Connection
1. Open your browser
2. Go to: `http://localhost/blue_ridge_farmers_collective/public/database_proof.php`
3. You should see:
   - ✓ Connection status: "Connected"
   - ✓ Lists of roles, vendors, products, and markets
   - ✓ No error messages

### Create Backup
```bash
# Backup database
mysqldump -u arthur -p'$Chopper1984' blueridge_farmers_db > backup.sql

# Restore from backup
mysql -u arthur -p'$Chopper1984' < backup.sql
```

### Test Git History
```bash
cd "c:\Program Files\Ampps\www\blue_ridge_farmers_collective"
git log --oneline
git status
```

## 📚 Important Files

| File | Purpose | Status |
|------|---------|--------|
| `src/Database/schema.sql` | Database structure + seed data | ✓ Active |
| `src/Database/blueridge_farmers_db_dump.sql` | Database backup/export | ✓ Created |
| `config/database-connection.php` | PDO connection handler | ✓ Ready |
| `public/database_proof.php` | Database verification page | ✓ Ready |
| `.gitignore` | Files to exclude from git | ✓ Created |
| `README.md` | Project documentation | ✓ Created |
| `.git/` | Git repository | ✓ Initialized |

## ⚠️ Important Notes

1. **Database Credentials** are in `.gitignore` - they won't be pushed to GitHub
2. **node_modules** is in `.gitignore` - install with `npm install` after cloning
3. **uploads/ and cache/** are in `.gitignore` - create on deployment
4. Database defaults are configured in `config/database.php` for local development
5. For production, use environment variables via `.env` file

## 🎯 What Gets Pushed to GitHub

✓ All source code (Controllers, Models, Views)
✓ Database schema files
✓ Configuration templates
✓ Frontend assets (CSS, JS)
✓ README and documentation
✓ .gitignore (prevents secrets from being pushed)

What **DOES NOT** get pushed:
✗ .env file (environment variables)
✗ node_modules/ (use npm install)
✗ storage/cache/* (generated at runtime)
✗ Database connection credentials

## 🔗 GitHub Repository URL Format

After you push, your repository will be at:
```
https://github.com/YOUR-USERNAME/blue-ridge-farmers-collective
```

## 📌 For Moodle Submission

Post this as your GitHub URL in Moodle:
```
https://github.com/YOUR-USERNAME/blue-ridge-farmers-collective
```

The instructor will be able to:
1. View your code
2. See git commit history
3. Access the README
4. Download the schema and dump files
5. Verify the project structure

## ✨ Summary

You now have:
- ✅ Working database with 35 optimized tables
- ✅ SQL dump for easy recreation
- ✅ Database connection PHP file
- ✅ Proof page showing database + PHP working together
- ✅ Git repository initialized and ready to push
- ✅ Professional README documentation

Just follow the "Next Steps to Push to GitHub" section above to complete the submission!

---

**Need Help?**
1. Check that MySQL is running: `mysql -u arthur -p'$Chopper1984' -e "SELECT 'Connected!'"`
2. Verify PHP works: Visit `public/database_proof.php`
3. Test git: Run `git status` in project directory
4. For GitHub issues: Consult GitHub's documentation on creating repositories

Generated: February 15, 2026
