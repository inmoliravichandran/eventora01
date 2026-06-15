# 🔧 Eventora - Setup & Troubleshooting Guide

## ✅ Quick Setup Steps

### 1. Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Click **Start** next to both:
   - ✅ **Apache** (web server)
   - ✅ **MySQL** (database)

### 2. Create the Database

1. Once MySQL is running, open your browser
2. Navigate to: `http://localhost/Eventora-01/backend/install_db.php`
3. Wait for the success message: `{"success":true,"message":"SQL imported successfully"}`

### 3. Test Authentication

1. Go to: `http://localhost/Eventora-01/uploads/register.html`
2. Create a test account
3. Then log in with those credentials

---

## 🐛 Troubleshooting - "Connection Failed" Error

### ❌ Error: "Database connection failed"

**Cause:** MySQL service is not running or database doesn't exist

**Solution:**

1. ✅ Check XAMPP Control Panel - MySQL should show **green "Running"** status
2. ✅ If MySQL is NOT running, click **Start** next to MySQL
3. ✅ Wait 2-3 seconds for it to fully start
4. ✅ Run the installation script: `http://localhost/Eventora-01/backend/install_db.php`

### ❌ Error: "No database selected"

**Cause:** Database `eventora_db` doesn't exist

**Solution:**

1. ✅ Go to: `http://localhost/Eventora-01/backend/install_db.php`
2. ✅ Run this script - it will create the database and tables

### ❌ Error: Still failing after trying above?

**Check PHP MySQL Extension:**

1. Open `http://localhost/` (XAMPP dashboard)
2. Click **phpMyAdmin**
3. If it loads, MySQL is working
4. If you can't access phpMyAdmin, MySQL extension may not be enabled

**Re-enable MySQL Extension in php.ini:**

1. Open `xampp/php/php.ini`
2. Find: `;extension=pdo_mysql` (should have semicolon)
3. Remove the semicolon: `extension=pdo_mysql`
4. Save and restart Apache & MySQL in XAMPP

---

## 🔑 Default Database Credentials

```
Host:     localhost or 127.0.0.1
Database: eventora_db
Username: root
Password: (empty)
Port:     3306
```

These are set in: `backend/config.php`

---

## 📁 Project Structure

```
Eventora-01/
├── backend/
│   ├── config.php          ← Database connection settings
│   ├── install_db.php      ← Run this to create database
│   ├── event.sql           ← Database schema
│   ├── login.php           ← Login API endpoint
│   ├── register.php        ← Registration API endpoint
│   └── other backend files...
├── uploads/
│   ├── index.html          ← Home page
│   ├── login.html          ← Login page
│   ├── register.html       ← Registration page
│   └── other pages...
├── js/
│   └── script.js           ← Frontend logic
├── css/
│   └── style.css           ← Styling
└── README.md
```

---

## 🚀 Testing Login Flow

### Test Account Creation:

1. Go to: `http://localhost/Eventora-01/uploads/register.html`
2. Fill in:
   - Full Name: Test User
   - Email: test@example.com
   - Password: password123
3. Click **Create Account**

### Test Login:

1. Go to: `http://localhost/Eventora-01/uploads/login.html`
2. Enter:
   - Email: test@example.com
   - Password: password123
3. Click **Sign In**

---

## 🔍 How to Check MySQL Status

### Method 1: XAMPP Control Panel

- MySQL should show **Running** in green

### Method 2: phpMyAdmin

- Go to: `http://localhost/phpmyadmin`
- If it loads, MySQL is working

### Method 3: Check Port

- MySQL usually runs on port 3306
- Windows: `netstat -an | findstr 3306`

---

## 📝 Modified Files

The following files have been updated to fix the connection issue:

1. **backend/config.php** - Improved error handling and connection settings
2. **backend/install_db.php** - Better error messages

These changes make debugging easier and provide clearer error messages.

---

## ✅ Verification Checklist

- [ ] XAMPP Control Panel shows MySQL is Running
- [ ] No error messages when visiting `http://localhost/Eventora-01/backend/install_db.php`
- [ ] Can successfully register a new account
- [ ] Can successfully log in with registered credentials
- [ ] Browser console has no red errors (press F12 to open Developer Tools)

---

If you still have issues, please check:

1. XAMPP is fully updated
2. No other services are blocking port 3306
3. MySQL has proper read/write permissions

Good luck! 🎉
