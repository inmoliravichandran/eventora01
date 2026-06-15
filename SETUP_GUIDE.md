# 🔧 Eventora - Setup & Troubleshooting Guide

## ✅ Quick Setup Steps

### 1. Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Click **Start** next to both:
   - ✅ **Apache** (web server)
   - ✅ **MySQL** (database)

### 2. Create the Database

1. Once MySQL is running, open your browser
2. Navigate to: `http://localhost/eventora/backend/install_db.php`
3. Wait for the success message: `{"success":true,"message":"SQL imported successfully"}`

### 3. Create Admin Account

1. Go to: `http://localhost/eventora/backend/create_admin.php`
2. This creates the default admin user

### 4. Open the Application

1. Navigate to: `http://localhost/eventora/`
2. Login with admin credentials:
   - **Email**: admin@eventora.com
   - **Password**: admin123

---

## 🐛 Troubleshooting

### ❌ Error: "Database connection failed"

**Cause:** MySQL service is not running or database doesn't exist

**Solution:**

1. ✅ Check XAMPP Control Panel — MySQL should show **green "Running"** status
2. ✅ If MySQL is NOT running, click **Start** next to MySQL
3. ✅ Wait 2-3 seconds for it to fully start
4. ✅ Run the installation script: `http://localhost/eventora/backend/install_db.php`

### ❌ Error: "No database selected"

**Cause:** Database `eventora_db` doesn't exist

**Solution:**

1. ✅ Go to: `http://localhost/eventora/backend/install_db.php`
2. ✅ This script will create the database and all tables automatically

### ❌ Error: Still failing after trying above?

**Check PHP MySQL Extension:**

1. Open `http://localhost/` (XAMPP dashboard)
2. Click **phpMyAdmin** — if it loads, MySQL is working
3. If phpMyAdmin fails, the MySQL extension may not be enabled

**Re-enable MySQL Extension in php.ini:**

1. Open `xampp/php/php.ini`
2. Find: `;extension=pdo_mysql`
3. Remove the semicolon: `extension=pdo_mysql`
4. Save and restart Apache & MySQL in XAMPP

---

## 🔑 Default Database Credentials

```
Host:     127.0.0.1
Database: eventora_db
Username: root
Password: (empty)
Port:     3307
```

These are configured in: `backend/config.php`

---

## 📁 Project Structure

```
eventora/
│
├── backend/            ← API endpoints & database
│   ├── config.php      ← Database connection settings
│   ├── install_db.php  ← Run this to create database
│   ├── event.sql       ← Database schema
│   ├── login.php       ← Login API
│   ├── register.php    ← Registration API
│   └── ...             ← Other API endpoints
│
├── css/
│   └── style.css       ← Global styles
│
├── js/
│   └── script.js       ← Frontend logic
│
├── uploads/            ← Uploaded media files
│   └── .gitkeep
│
├── index.php           ← Home page
├── about.php           ← About page
├── services.php        ← Services listing
├── cart.php            ← Shopping cart
├── checkout.php        ← Checkout
├── login.php           ← Login page
├── register.php        ← Register page
├── profile.php         ← User profile
├── admin-dashboard.php ← Admin panel
├── contact.php         ← Contact page
├── privacy.php         ← Privacy policy
├── header.php          ← Shared header
└── footer.php          ← Shared footer
```

---

## 🚀 Testing the Application

### Register a New User:

1. Go to: `http://localhost/eventora/register.php`
2. Fill in name, email, and password
3. Click **Create Account**

### Login:

1. Go to: `http://localhost/eventora/login.php`
2. Enter your credentials and click **Login**

### Admin Panel:

1. Go to: `http://localhost/eventora/admin-dashboard.php`
2. Login with admin credentials (admin@eventora.com / admin123)

---

## 🌐 Key URLs

| Page | URL |
|------|-----|
| Home | `http://localhost/eventora/` |
| Services | `http://localhost/eventora/services.php` |
| Login | `http://localhost/eventora/login.php` |
| Register | `http://localhost/eventora/register.php` |
| Admin | `http://localhost/eventora/admin-dashboard.php` |
| Install DB | `http://localhost/eventora/backend/install_db.php` |
