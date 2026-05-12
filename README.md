# Internal Management System API

A scalable and secure RESTful API built with Laravel using clean architecture principles, OAuth2 authentication, and role-based authorization.

This project follows the **Service Repository Pattern** to keep business logic organized and maintainable while providing a structured API for internal management operations such as staff management, cards, buildings, rooms, rosters, departments, and ISP management.

---

# 🚀 Features

## Authentication & Security

- OAuth2 Authentication using Laravel Passport
- Access Token authentication with Bearer Token
- Refresh Token stored securely in HTTP-only Cookies
- Refresh Token rotation flow
- Protected API routes using middleware
- Role-based authorization middleware
- Secure logout and token revocation

---

## Clean Architecture

- Service Repository Pattern
- Separation of Concerns
- Reusable business logic
- Modular code structure
- API Versioning (`/api/v1`)

---

## Validation & Error Handling

- Laravel Form Request Validation
- Standardized API Response Helper
- Consistent JSON responses
- Centralized validation logic

---

## Core Modules

- Authentication
- Staff Management
- Department Management
- Position Management
- Card Management
- Building & Room Management
- ISP Management
- Roster Management

---

# 🛠️ Tech Stack

- Laravel
- Laravel Passport
- PHP
- MySQL
- REST API
- Docker

---

# 📁 Project Structure

```bash
app/
├── Helper/
│   └── ResponseHelper.php
│
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
│
├── Repository/
│   ├── AuthRepository.php
│   ├── StaffRepository.php
│   └── ...
│
├── Services/
│   ├── AuthService.php
│   ├── StaffService.php
│   └── ...
│
├── Models/
│
routes/
└── api.php
```

---

# 🧱 Architecture Pattern

This project uses the **Service Repository Pattern**.

## Repository Layer

Handles database queries and data access.

Example:

```php
StaffRepository.php
```

Responsibilities:

- Database operations
- Query optimization
- Data retrieval
- Data persistence

---

## Service Layer

Handles business logic.

Example:

```php
StaffService.php
```

Responsibilities:

- Business rules
- Data processing
- Coordinating repositories
- Application logic

---

## Controller Layer

Handles HTTP requests and responses.

Example:

```php
StaffController.php
```

Responsibilities:

- Receive client requests
- Call services
- Return API responses

---

# 🔐 Authentication Flow

This project uses Laravel Passport for OAuth2 authentication.

## Login Flow

1. User logs in with credentials
2. API generates Access Token
3. Refresh Token is stored in HTTP-only Cookie
4. Client uses Access Token for protected requests
5. Refresh endpoint generates new Access Token when expired

---

## Access Token

Used for authenticated API requests.

Example:

```http
Authorization: Bearer your_access_token
```

---

## Refresh Token

Stored in secure HTTP-only Cookie.

Benefits:

- Better security against XSS attacks
- Hidden from JavaScript access
- Safer token management

---

# 🛡️ Middleware

## Authentication Middleware

Protects private routes.

```php
Route::middleware('auth:api')
```

---

## Role Middleware

Restricts routes based on user roles.

Middleware used:

```php
CheckUserRoleBase
```

Example:

```php
Route::middleware('CheckUserRoleBase')
```

---

# ✅ Request Validation

Validation is handled using Laravel Form Requests.

Example:

```php
public function rules(): array
{
    return [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];
}
```

Benefits:

- Cleaner controllers
- Reusable validation rules
- Centralized validation
- Better maintainability

---

# 📦 API Response Helper

The project uses a custom response helper for consistent API responses.

Example Response:

```json
{
    "success": true,
    "message": "Data fetched successfully",
    "data": {}
}
```

Benefits:

- Standardized responses
- Easier frontend integration
- Better error handling
- Cleaner controllers

---

# 🌐 API Versioning

All APIs are versioned under:

```bash
/api/v1
```

Benefits:

- Easier maintenance
- Backward compatibility
- Future scalability

---

# 📌 Main API Modules

## Authentication

```http
POST /api/v1/register
POST /api/v1/login
POST /api/v1/refresh-token
POST /api/v1/logout
```

---

## Staff

```http
GET    /api/v1/staff
POST   /api/v1/staff
PATCH  /api/v1/staff/{staff_id}
DELETE /api/v1/staff/{staff_id}
```

---

## Departments

```http
GET    /api/v1/department
POST   /api/v1/department
PUT    /api/v1/department/{department_id}
DELETE /api/v1/department/{department_id}
```

---

## Positions

```http
GET    /api/v1/positions
POST   /api/v1/positions
PUT    /api/v1/positions/{position_id}
DELETE /api/v1/positions/{position_id}/{department_id}
```

---

## Cards

```http
GET    /api/v1/cards
POST   /api/v1/create_card
POST   /api/v1/card/search
POST   /api/v1/card/cards_filter
DELETE /api/v1/card/delete/{type_card_id}/{card_type}
```

---

## Buildings & Rooms

```http
GET    /api/v1/blocks/all_buildings
POST   /api/v1/blocks/add_building
PUT    /api/v1/blocks/update_building/{building_id}
DELETE /api/v1/blocks/delete_building/{building_id}
```

---

## ISP

```http
GET    /api/v1/isp/all_isps
POST   /api/v1/isp/add_isp
PUT    /api/v1/isp/update_isp/{isp_id}
DELETE /api/v1/isp/delete_isp/{isp_id}
```

---

# ⚙️ Installation

## 1. Clone Repository

```bash
git clone https://github.com/CheaSeyha/internal_management_system_api
```

---

## 2. Install Dependencies

```bash
composer install
```

---

## 3. Setup Environment

```bash
cp .env.example .env
```

Update database configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=root
DB_PASSWORD=
```

---

## 4. Generate Application Key

```bash
php artisan key:generate
```

---

## 5. Run Database Migration

```bash
php artisan migrate
```

---

## 6. Install Passport

```bash
php artisan passport:install
```

---

## 7. Start Development Server

```bash
php artisan serve
```
## 7. Start Development OAuth Token

```bash
php artisan serve --port=8001
```
In ENV file Add This 
```bash
APP_DEV_URL=http://localhost:8001
```
---

# 🐳 Docker Support

This project includes:

- Dockerfile
- Apache configuration
- Docker entrypoint script
