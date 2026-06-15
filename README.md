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
* CSS3
* JavaScript (Vanilla JS)

### ⚙️ Backend

* PHP

### 🗄️ Database

* MySQL

### 💻 Development Environment

* XAMPP Server
* phpMyAdmin

### 🔒 Security Features

* Password Hashing
* Session Management
* Form Validation
* SQL Injection Prevention

---

# 📂 Project Structure

```bash
Eventora/
│
├── frontend/
│   ├── index.html
│   ├── about.html
│   ├── services.html
│   ├── login.html
│   ├── register.html
│   ├── contact.html
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
│
├── backend/
│   ├── config/
│   │   └── database.php
│   ├── controllers/
│   ├── models/
│   ├── views/
│   ├── auth/
│   ├── admin/
│   └── api/
│
├── database/
│   └── eventora_db.sql
│
└── README.md
```

---

# ✨ Main Features

## 👤 User Features

### 🏠 Home Page

* Featured event services
* Popular packages
* Promotional banners

### 🔍 Service Browsing

* Search services
* Filter by category
* Compare packages

### 📝 User Registration

* Create new account
* Input validation
* Secure password storage

### 🔐 Login System

* Secure authentication
* Session management

### 🛒 Shopping Cart

* Add services to cart
* Update quantity
* Remove services

### 📅 Event Booking

* Book event packages
* Select event dates
* Booking confirmation

### 📦 Order Tracking

* View booking status
* Track service progress

### 👤 User Profile

* Update personal information
* Manage account settings
* View booking history

### 📞 Contact Us

* Submit inquiries
* Customer support requests

---

# 👨‍💼 Admin Features

### 📊 Dashboard

* System overview
* Statistics and reports

### 👥 User Management

* View users
* Edit user information
* Manage accounts

### 🎉 Service Management

* Add services
* Update services
* Delete services

### 📅 Booking Management

* View bookings
* Update booking status
* Confirm reservations

### 💰 Order Management

* Track payments
* View transactions

### 📩 Contact Management

* View customer inquiries
* Respond to messages

---

# 🗄️ Database Tables

### 👤 Users

* user_id
* full_name
* email
* password
* phone
* role

### 🎉 Services

* service_id
* service_name
* category
* description
* price
* image

### 📅 Bookings

* booking_id
* user_id
* service_id
* booking_date
* event_date
* status

### 🛒 Cart

* cart_id
* user_id
* service_id
* quantity

### 💰 Orders

* order_id
* user_id
* total_amount
* payment_status
* order_date

### 📩 Contact Messages

* message_id
* name
* email
* subject
* message

---

# 🚀 Installation Guide

## Step 1️⃣ Install XAMPP

Download and install XAMPP:

https://www.apachefriends.org

---

## Step 2️⃣ Move Project Folder

Copy the project folder to:

```bash
xampp/htdocs/Eventora
```

---

## Step 3️⃣ Start Services

Open XAMPP Control Panel and start:

✅ Apache

✅ MySQL

---

## Step 4️⃣ Create Database

Open:

```bash
http://localhost/phpmyadmin
```

Create database:

```sql
eventora_db
```

Import:

```bash
database/eventora_db.sql
```

---

## Step 5️⃣ Configure Database Connection

Update:

```php
backend/config/database.php
```

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "eventora_db";
```

---

## Step 6️⃣ Run Application

Open browser:

```bash
http://localhost/Eventora
```

🎉 Eventora is now ready to use!

---

# 🌟 Future Enhancements

💳 Online Payment Gateway

📱 Mobile Application

📧 Email Notifications

⭐ Customer Reviews & Ratings

🤖 AI-based Service Recommendations

📈 Advanced Analytics Dashboard

---

# 👩‍💻 Developed By

**R. Inmoly**

🎓 Higher Diploma in Information Technology

📚 Module: Web Application Development

🏫 Sri Lanka Institute of Information Technology (SLIIT)

---

# 📜 License

This project is developed for educational and academic purposes only.

© 2026 Eventora. All Rights Reserved.
  
