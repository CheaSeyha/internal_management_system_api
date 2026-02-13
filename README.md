# Internal Management System API

Laravel 12 REST API for Internal Management System

---

## 📌 Overview

This project is a **Laravel 12 REST API** built for an Internal Management System.

Main features include:

* ✅ JWT Authentication
* ✅ Card Management
* ✅ Building & Room Management
* ✅ Department & Position Management
* ⚠️ Staff Management (Partially Completed)
* ❌ Roster Management (Not Completed Yet)
* ⚠️ ISP Management (Basic CRUD Only)

Some modules are still under development and will be extended in future versions.

---

## 🛠 Tech Stack

* Laravel 12
* PHP 8.2+
* JWT Authentication (tymon/jwt-auth)
* SQLite (default) / MySQL supported
* RESTful API architecture
* Service + Repository pattern

---

## ⚙️ Installation (Local Setup)

### 1️⃣ Clone Project

```bash
git clone <your-repository-url>
cd internal_management_system_api
```

### 2️⃣ Install Dependencies

```bash
composer install
```

### 3️⃣ Create Environment File

```bash
cp .env.example .env
```

### 4️⃣ Generate App Key

```bash
php artisan key:generate
```

### 5️⃣ Configure Database (SQLite Example)

Create database file:

```bash
touch database/database.sqlite
```

Update `.env`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

### 6️⃣ Generate JWT Secret

```bash
php artisan jwt:secret
```

### 7️⃣ Run Migrations

```bash
php artisan migrate
```

### 8️⃣ Run Server

```bash
php artisan serve
```

API Base URL:

```
http://127.0.0.1:8000/api/v1
```

---

## 🐳 Docker Setup (Optional)

Build:

```bash
docker build -t internal-management-api .
```

Run:

```bash
docker run -p 8080:80 internal-management-api
```

API Base URL (Docker):

```
http://127.0.0.1:8080/api/v1
```

---

# 🔐 Authentication (JWT)

This project uses JWT for authentication.

After login, include token in header:

```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

---

## 🧾 Auth Routes

Public:

* POST `/api/v1/register`
* POST `/api/v1/login`
* POST `/api/v1/refresh-token`

Protected:

* GET `/api/v1/user`
* POST `/api/v1/logout`

---

# 📦 Modules

---

## 🟢 Card Module (Mostly Completed)

Features:

* Create card
* List cards
* Search cards
* Filter cards
* Delete card
* Edit card
* Card summary by date
* Card type management
* Get duplicate cards
* View card image

Main Routes:

* GET `/cards`
* POST `/create_card`
* POST `/card/search`
* DELETE `/card/delete/{id}/{type}`

---

## 🟢 Block Module (Buildings & Rooms)

Features:

* Add building
* Update building
* Delete building
* Add room
* Delete room
* List all buildings and rooms

Routes:

* GET `/blocks/all_buildings`
* POST `/blocks/add_building`
* GET `/blocks/all_rooms`

---

## 🟢 Department Module

Admin / Super Admin only.

Routes:

* GET `/department/all_departments`
* POST `/department/add_department`
* PUT `/department/update_department/{id}`
* DELETE `/department/delete_department/{id}`

---

## 🟢 Position Module

Admin / Super Admin only.

Routes:

* GET `/position/all_positions`
* POST `/position/add_position`
* PUT `/position/update_position/{id}`
* DELETE `/position/delete_position/{id}`

---

## 🟡 ISP Module (Basic CRUD Only)

Current Features:

* Add ISP
* Update ISP
* Delete ISP
* List all ISP

Routes:

* GET `/isp/all_isps`
* POST `/isp/add_isp`
* PUT `/isp/update_isp/{id}`
* DELETE `/isp/delete_isp/{id}`

Planned Improvements:

* ISP Packages
* Billing system
* Usage tracking
* Building-to-ISP mapping

---

## 🟡 Staff Module (Partially Completed)

Current Features:

* Add new staff
* Get all staff
* Get staff profile image

Routes:

* POST `/staff/add_new_staff`
* GET `/staff/get_all_staff`
* GET `/staff/image_profile/{staff_id}`

Planned Improvements:

* Update staff
* Delete staff
* Search/filter staff
* Staff status management
* File validation improvements

---

## 🔴 Roster Module (Not Completed Yet)

Database structure exists, but API endpoints are not implemented.

Planned Features:

* Create monthly roster
* Update shift schedule
* Filter roster by date
* Leave/day-off integration

---

# 👥 Role-Based Access Control

Some endpoints are restricted by role:

* role_id = 1 → Super Admin
* role_id = 2 → Admin

Unauthorized access will return 403 Forbidden.

---

# 📊 API Response Format

Standard success response:

```json
{
  "success": true,
  "message": "Request successful",
  "data": {}
}
```

Error response:

```json
{
  "success": false,
  "message": "Something went wrong",
  "error": "Error details"
}
```

---

# 🚀 Future Improvements

* Finish Roster Module
* Complete Staff Module
* Extend ISP Module
* Add API documentation (Swagger)
* Add automated tests
* Add deployment guide
* Add CI/CD pipeline

---
