# 🧩 Task Management API

A robust RESTful backend API for task management built with **Laravel** and tested using **PestPHP**. It supports role-based access control (Admin, Manager, User), task dependencies, status control workflows, and dynamic permission logic.

---

## 📄 Table of Contents

- [🧩 Task Management API](#-task-management-api)
  - [📄 Table of Contents](#-table-of-contents)
  - [📚 Features](#-features)
  - [📊 ER Diagram](#-er-diagram)
  - [📂 Folder Structure](#-folder-structure)
  - [🛠️ Technologies Used](#️-technologies-used)
  - [⚙️ Installation \& Setup](#️-installation--setup)
    - [📦 Clone \& Install Dependencies](#-clone--install-dependencies)
    - [🔑 Environment Configuration](#-environment-configuration)
    - [🧪 Testing Environment](#-testing-environment)
    - [🗃️ Migrate \& Seed Database](#️-migrate--seed-database)
  - [✅ Testing](#-testing)
  - [📮 Postman Documentation](#-postman-documentation)
  - [📘 API Documentation (Scribe)](#-api-documentation-scribe)
    - [📍 URL](#-url)
    - [📁 Files](#-files)
    - [🔁 Regenerate](#-regenerate)
  - [🚦 API Summary](#-api-summary)
  - [👨‍👩‍👧‍👦 Roles \& Permissions](#-roles--permissions)
  - [🧪 Test Coverage](#-test-coverage)
  - [📦 Packages Installed (excluding Laravel defaults)](#-packages-installed-excluding-laravel-defaults)
    - [⚙️ Development](#️-development)
    - [🔐 Auth \& Roles](#-auth--roles)
    - [📄 Docs \& Utilities](#-docs--utilities)
  - [🗺️ ERD (Entity Relationship Diagram)](#️-erd-entity-relationship-diagram)
  - [🎯 Final Notes](#-final-notes)

---

## 📚 Features

* 🔐 Auth (Register, Login, Logout)
* 👤 User profile management
* 🔁 Role Switching (Admin ↔ Manager)
* 📋 Task CRUD with:
  * Assignees
  * Status lifecycle
  * Task dependencies (children)
* 🔄 Change task status with validation on role and dependency
* 👁️ Task visibility based on role & assignment
* 🧩 Add task dependencies (children) via dedicated endpoint
* 🧪 100% feature coverage in PestPHP

---

## 📊 ER Diagram

The system consists of users, tasks, and a pivot table for both task dependencies and assignees.

📄 **File:** [`ERD.drawio`](ERD.drawio) — Open it with [draw.io](https://app.diagrams.net).

---

## 📂 Folder Structure

```
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Policies/
├── Enums/
tests/
├── Feature/
│   ├── AuthTest.php
│   ├── UserTest.php
│   ├── ChangeTaskStatusTest.php
│   └── ...
```

---

## 🛠️ Technologies Used

* **Laravel 12.20.0**
* **PHP 8.2+**
* **Sanctum** for token-based auth
* **Spatie Laravel-Permission** for RBAC
* **PestPHP** for testing
* **Scribe** for API documentation
* **Postman** for API collection
* **Draw.io** for ER Diagram

---

## ⚙️ Installation & Setup

### 📦 Clone & Install Dependencies

```bash
git clone https://github.com/YoussefIbraheem/ideal_laravel_app.git
cd ideal_laravel_app
composer install
```

### 🔑 Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

> ✅ Update `.env` with your DB credentials.

### 🧪 Testing Environment

```bash
cp .env.testing.example .env.testing
```

> Uses `sqlite` in-memory for fast, isolated test runs.

---

### 🗃️ Migrate & Seed Database

```bash
php artisan migrate --seed
```

To reset:

```bash
php artisan migrate:fresh --seed
```

---

## ✅ Testing

```bash
php artisan test
# or
vendor/bin/pest
```

Run with coverage:

```bash
vendor/bin/pest --coverage
```

---

## 📮 Postman Documentation

🧪 Postman collection: [`ideal_project.postman_collection.json`](ideal_project.postman_collection.json)

You can import it into Postman to quickly test all API endpoints.

<details>
<summary>Endpoints Overview</summary>

**Auth**
- POST `/register`
- POST `/login`
- POST `/logout`

**User**
- GET `/user`
- POST `/user/update`
- POST `/change-role`
- GET `/users`
- GET `/users/{id}`

**Tasks**
- GET `/tasks`
- GET `/tasks/{id}`
- POST `/tasks/create`
- POST `/tasks/{id}/update`
- POST `/tasks/{id}/change-status`
- POST `/tasks/{id}/add-dependents`

</details>

---

## 📘 API Documentation (Scribe)

The full API documentation is generated using **Scribe** and is viewable in-browser.

### 📍 URL

```bash
http://localhost:8000/docs
```

### 📁 Files

- Documentation lives in: `public/docs/`
- Configurable in: `config/scribe.php`

### 🔁 Regenerate

```bash
php artisan scribe:generate
```

---

## 🚦 API Summary

| Action                    | Admin | Manager |       User      |
| ------------------------- | :---: | :-----: | :-------------: |
| Create task               |   ✅   |    ✅    |        ❌        |
| Edit task (except status) |   ✅   |    ✅    |        ❌        |
| Add dependents            |   ✅   |    ✅    |        ❌        |
| Change status (normal)    |   ✅   |    ✅    | ✅ (if assigned) |
| Cancel task               |   ✅   |    ✅    |        ❌        |
| View assigned tasks       |   ✅   |    ✅    |        ✅        |
| Delete task               |   ✅   |    ❌    |        ❌        |
| View all tasks            |   ✅   |    ✅    |        ❌        |

---

## 👨‍👩‍👧‍👦 Roles & Permissions

- **Admin**
  - Full access, including delete, cancel, and role changes.
- **Manager**
  - Full task control, no deletion or role change.
- **User**
  - Can only update the status of their assigned tasks (not cancel).

---

## 🧪 Test Coverage

Fully covered using PestPHP:

* Auth & login/logout
* Role checks
* Task creation, update, filtering
* Status rules
* Dependency enforcement
* User update
* Policy enforcement

---

## 📦 Packages Installed (excluding Laravel defaults)

### ⚙️ Development

- `pestphp/pest`
- `brianium/paratest`
- `larastan/larastan`
- `phpstan/phpstan`
- `filp/whoops`

### 🔐 Auth & Roles

- `laravel/sanctum`
- `spatie/laravel-permission`

### 📄 Docs & Utilities

- `knuckleswtf/scribe`
- `fakerphp/faker`
- `guzzlehttp/guzzle`
- `fruitcake/php-cors`

---

## 🗺️ ERD (Entity Relationship Diagram)

📁 File: [`ERD.drawio`](ERD.drawio) (view in draw.io)

<details>
<summary>Overview</summary>

- **Users**
  - One-to-many: tasks (created)
  - Many-to-many: assigned tasks

- **Tasks**
  - Belongs to: owner (creator)
  - Many-to-many: assignees (users)
  - Self-referencing: depends_on, dependents

</details>

---

## 🎯 Final Notes

✅ Ready for deployment or CI/CD integration.  
✅ API tested and documented.  
✅ Fully modular and extendable.
