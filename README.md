# 🎉 Eventora - Event Arrangement E-Commerce Platform

## 📌 Project Overview

**Eventora** is a full-stack Event Arrangement E-Commerce Platform developed to simplify the process of planning and organizing events such as:

💍 Weddings
🎂 Birthday Parties
🏢 Corporate Events
🎊 Celebrations
🎤 Conferences
🎵 Music Events

The platform allows customers to browse, compare, book, and manage event services online through a user-friendly and secure web application.

---

## 🎯 Project Objectives

✅ Provide a centralized marketplace for event services

✅ Simplify event planning and booking processes

✅ Offer transparent pricing and package comparisons

✅ Enable secure online bookings and order management

✅ Help event service providers showcase their services

---

## 🛠️ Technologies Used

### 🎨 Frontend

* HTML5
* CSS3 (Glassmorphism design system)
* JavaScript (Vanilla JS)

### ⚙️ Backend

* PHP (with PDO)

### 🗄️ Database

* MySQL

### 💻 Development Environment

* XAMPP Server
* phpMyAdmin

### 🔒 Security Features

* Password Hashing
* Session Management
* Form Validation
* SQL Injection Prevention (PDO Prepared Statements)

---

## 📂 Project Structure

```
eventora/
│
├── backend/                    ← API endpoints & database logic
│   ├── config.php              ← DB connection settings
│   ├── install_db.php          ← Run this once to create the database
│   ├── event.sql               ← Database schema
│   ├── login.php               ← Login API endpoint
│   ├── register.php            ← Registration API endpoint
│   ├── logout.php              ← Logout handler
│   ├── get_services.php        ← Fetch services API
│   ├── add_to_cart.php         ← Cart management
│   ├── get_cart.php            ← Cart fetch API
│   ├── remove_from_cart.php    ← Cart remove API
│   ├── update_cart_quantity.php← Cart update API
│   ├── create_booking.php      ← Booking creation API
│   ├── get_bookings.php        ← Fetch bookings API
│   ├── admin_data.php          ← Admin stats API
│   └── check_auth.php          ← Session auth check
│
├── css/
│   └── style.css               ← Global styling (glassmorphism theme)
│
├── js/
│   └── script.js               ← Frontend logic & API calls
│
├── uploads/                    ← User/service uploaded media files
│   └── .gitkeep
│
├── index.php                   ← Home page
├── about.php                   ← How it works page
├── services.php                ← Services listing page
├── service-details.php         ← Individual service page
├── cart.php                    ← Shopping cart page
├── checkout.php                ← Checkout page
├── login.php                   ← Login page
├── register.php                ← Registration page
├── profile.php                 ← User profile page
├── admin-dashboard.php         ← Admin panel
├── contact.php                 ← Contact page
├── privacy.php                 ← Privacy policy page
├── header.php                  ← Shared header component
├── footer.php                  ← Shared footer component
│
├── README.md
└── SETUP_GUIDE.md
```

---

## ✨ Main Features

### 👤 User Features

* **Home Page** — Featured services, category showcase, trust indicators
* **Service Browsing** — Search, filter by category, compare packages
* **User Registration & Login** — Secure auth with session management
* **Shopping Cart** — Add, update, remove services
* **Event Booking** — Book packages with date selection
* **User Profile** — Manage account & view booking history
* **Contact Us** — Submit inquiries

### 👨‍💼 Admin Features

* **Dashboard** — Statistics overview (users, bookings, services)
* **Service Management** — Full CRUD (Add, Edit, Delete services)
* **Booking Management** — View & update booking statuses
* **User Management** — View registered users

---

## 🗄️ Database Tables

| Table | Key Fields |
|-------|-----------|
| `users` | user_id, full_name, email, password, role |
| `services` | id, name, category, description, price, image_url, is_featured |
| `bookings` | id, user_id, service_id, event_date, status |
| `cart` | id, user_id, service_id, quantity |

---

## 🚀 Installation Guide

### Step 1️⃣ — Install XAMPP

Download and install XAMPP from: https://www.apachefriends.org

### Step 2️⃣ — Place Project

Copy the project folder into:

```
xampp/htdocs/eventora
```

### Step 3️⃣ — Start Services

Open XAMPP Control Panel and start:
- ✅ **Apache**
- ✅ **MySQL**

### Step 4️⃣ — Create the Database

Navigate to:

```
http://localhost/eventora/backend/install_db.php
```

Wait for: `{"success":true,"message":"SQL imported successfully"}`

### Step 5️⃣ — Run Application

Open browser:

```
http://localhost/eventora/
```

🎉 Eventora is ready to use!

---

## 🔑 Default Admin Credentials

```
Email:    admin@eventora.com
Password: admin123
```

> You can create the admin account via: `http://localhost/eventora/backend/create_admin.php`

---

## 🌟 Future Enhancements

💳 Online Payment Gateway

📱 Mobile Application

📧 Email Notifications

⭐ Customer Reviews & Ratings

🤖 AI-based Service Recommendations

📈 Advanced Analytics Dashboard

---

## 👩‍💻 Developed By

**R. Inmoly**

🎓 Higher Diploma in Information Technology

📚 Module: Web Application Development

🏫 Sri Lanka Institute of Information Technology (SLIIT)

---

## 📜 License

This project is developed for educational and academic purposes only.

© 2026 Eventora. All Rights Reserved.
