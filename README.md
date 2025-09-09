# Data Import & Export System (Laravel 10)

## 📌 Project Overview
This project is a **Laravel 10 application** that provides functionality to **import and export data** in multiple formats such as **CSV, JSON, and XML**.  
It also includes support for **batch import with queue jobs**, making large file imports scalable and efficient.

---

## 🚀 Features
- Export data in **CSV, JSON, XML** formats.
- Import data from **CSV and JSON** files.
- **Batch Import Service** with Laravel Queues.
- Upsert logic to **update existing records** instead of duplicates.
- `Importable` Trait for easy integration with models.
- Example implementation with the **User model**.
- Configurable handling for date/time fields (`email_verified_at`, `created_at`).

---

## 🛠️ Tech Stack
- **Laravel 10**
- **PHP 8+**
- **MySQL**
- **Queue System** (for batch import)

---

## 📂 Project Structure (Key Files)

app/
├── Services/
│ ├── Exporters/ # Export logic (CSV, JSON, XML)
│ ├── Importers/ # Import logic (CSV, JSON)
│ └── BatchImportService.php
├── Jobs/
│ └── DataImport.php
├── Models/
│ └── User.php (with Importable trait)
└── Http/Controllers/
└── DataController.php
