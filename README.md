<p align="center">
  <img src="https://img.shields.io/badge/Version-1.0.0-blue.svg" alt="Version">
  <img src="https://img.shields.io/badge/PHP-8.0+-purple.svg" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0+-orange.svg" alt="MySQL">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

<h1 align="center">🛒 QuickMart IOMS</h1>

<p align="center">
  <strong>Inventory & Order Management System</strong><br>
  A modern, full-stack web application for managing inventory, orders, and staff operations
</p>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Screenshots](#-screenshots)
- [Technology Stack](#-technology-stack)
- [Project Structure](#-project-structure)
- [Installation](#-installation)
- [Database Schema](#-database-schema)
- [API Documentation](#-api-documentation)
- [Security Features](#-security-features)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🎯 Overview

**QuickMart IOMS** is a comprehensive Inventory and Order Management System designed for small to medium-sized retail businesses. The system provides a centralized platform for managing products, processing sales and purchase orders, tracking inventory levels, and generating business reports.

### Key Highlights

- 📊 **Real-time Dashboard** - Monitor sales, inventory, and business metrics at a glance
- 📦 **Smart Inventory Tracking** - Automatic low-stock alerts and status updates
- 🛍️ **Order Processing** - Handle both sales and purchase orders with transaction safety
- 👥 **Staff Management** - Role-based access control (Admin/Staff)
- 📈 **Business Reports** - Generate comprehensive sales and inventory reports
- 🔐 **Secure Authentication** - Session-based authentication with password hashing

---

## ✨ Features

### 🏠 Dashboard
- Real-time statistics overview (Total Products, Orders, Revenue, Low Stock Alerts)
- Recent orders tracking
- Quick action buttons for common tasks
- Welcome message with user personalization

### 📦 Product Management
- Add, edit, and delete products
- Category-based organization
- Price and quantity management
- Automatic stock status updates (Normal / Low Stock / Out of Stock)
- Configurable low-stock threshold per product

### 🛒 Order Management
- **Sell Orders**: Process customer sales with automatic stock deduction
- **Purchase Orders**: Restock inventory from suppliers
- Order details view with complete transaction history
- Party name tracking (Customer/Supplier)
- Real-time quantity validation to prevent overselling

### 👥 Staff Management *(Admin Only)*
- Create and manage staff accounts
- Role assignment (Admin/Staff)
- Profile management with contact details
- Secure password handling

### 📊 Reports
- Sales reports with date filtering
- Revenue analytics
- Order history and statistics
- Export-ready data presentation

### 👤 User Profile
- Personal information management
- Role and contact details display
- Account settings

---

## 💻 Technology Stack

### Frontend
| Technology | Purpose |
|------------|---------|
| HTML5 | Structure & Semantics |
| CSS3 | Styling & Animations |
| JavaScript (ES6+) | Interactive Functionality |
| Bootstrap 5 | UI Components & Grid System |
| Font Awesome | Icons |

### Backend
| Technology | Purpose |
|------------|---------|
| PHP 8.0+ | Server-side Logic |
| PDO | Database Abstraction Layer |
| Sessions | User Authentication |

### Database
| Technology | Purpose |
|------------|---------|
| MySQL 8.0+ | Data Storage |
| InnoDB | Transaction Support |

### Server
| Technology | Purpose |
|------------|---------|
| Apache (XAMPP) | Web Server |
| .htaccess | URL Rewriting & Security |

---

## 📁 Project Structure

```
QuickMart code/
├── 📁 assets/                      # Client-side files (CSS, JS, Images)
│   ├── 📁 login-signup/            # Authentication pages
│   ├── 📁 dashboard/               # Dashboard styles & scripts
│   ├── 📁 products/                # Product management
│   ├── 📁 orders/                  # Orders page
│   ├── 📁 create-order/            # Order creation
│   ├── 📁 order-details/           # Order viewing
│   ├── 📁 reports/                 # Business reports
│   ├── 📁 staff/                   # Staff management
│   ├── 📁 profile/                 # User profile
│   ├── 📄 common.css               # Shared styles
│   └── 📄 common.js                # Shared utilities
│
├── 📁 backend/                     # Server-side files
│   ├── 📁 api/                     # RESTful API endpoints
│   │   ├── 📁 auth/                # Authentication APIs
│   │   ├── 📁 products/            # Product CRUD APIs
│   │   ├── 📁 orders/              # Order processing APIs
│   │   ├── 📁 staff/               # Staff management APIs
│   │   ├── 📁 reports/             # Reporting APIs
│   │   └── 📄 bootstrap.php        # API bootstrapper
│   │
│   ├── 📁 views/                   # PHP view templates
│   │   ├── 📄 dashboard.php
│   │   ├── 📄 products.php
│   │   ├── 📄 orders.php
│   │   ├── 📄 create-order.php
│   │   ├── 📄 order-details.php
│   │   ├── 📄 reports.php
│   │   ├── 📄 staff.php
│   │   └── 📄 profile.php
│   │
│   ├── 📁 core/                    # Core system files
│   │   ├── 📄 config.php           # App configuration
│   │   ├── 📄 db.php               # Database connection
│   │   ├── 📄 auth_check.php       # Auth middleware
│   │   └── 📄 helpers.php          # Utility functions
│   │
│   └── 📄 .htaccess                # Security rules
│
├── 📁 database/                    # Database files
│   ├── 📄 schema.sql               # Table definitions
│   └── 📄 data.sql                 # Sample data
│
├── 📁 screenshots/                 # Project screenshots & evidence
├── 📄 index.php                    # Entry point
└── 📄 README.md                    # This file
```

---

## 🚀 Installation

### Prerequisites

- **XAMPP** (or any Apache + MySQL + PHP stack)
- **PHP 8.0** or higher
- **MySQL 8.0** or higher
- Modern web browser (Chrome, Firefox, Edge, Safari)

### Step-by-Step Setup

#### 1️⃣ Clone the Repository

```bash
git clone https://github.com/yourusername/QuickMart-IOMS.git
```

#### 2️⃣ Move to Web Server Directory

```bash
# For XAMPP on Windows
mv QuickMart-IOMS "C:\xampp\htdocs\QuickMart code"

# For XAMPP on Mac/Linux
mv QuickMart-IOMS /opt/lampp/htdocs/QuickMart\ code
```

#### 3️⃣ Configure Database Connection

Edit `backend/core/db.php` with your database credentials:

```php
$host = 'localhost';
$dbname = 'quickmart_db';
$username = 'root';
$password = '';  // Your MySQL password
```

#### 4️⃣ Create Database

Open phpMyAdmin or MySQL CLI and run:

```sql
-- Run schema.sql to create tables
SOURCE /path/to/QuickMart code/database/schema.sql;

-- (Optional) Run data.sql to add sample data
SOURCE /path/to/QuickMart code/database/data.sql;
```

Or import via phpMyAdmin:
1. Open `http://localhost/phpmyadmin`
2. Create new database: `quickmart_db`
3. Import `database/schema.sql`
4. Import `database/data.sql` (optional)

#### 5️⃣ Start the Application

1. Start Apache and MySQL from XAMPP Control Panel
2. Open browser and navigate to:
   ```
   http://localhost/QuickMart code/
   ```

#### 6️⃣ Default Admin Credentials

```
Email: admin@quickmart.com
Password: admin123
```

> ⚠️ **Important**: Change the default password after first login!

---

## 🗄️ Database Schema

### Entity Relationship

```
┌─────────────┐       ┌─────────────┐       ┌─────────────────┐
│   Staff     │       │   Orders    │       │  Order_Details  │
├─────────────┤       ├─────────────┤       ├─────────────────┤
│ Staff_ID PK │◄──────│ Staff_ID FK │       │ Detail_ID PK    │
│ Full_Name   │       │ Order_ID PK │◄──────│ Order_ID FK     │
│ Email       │       │ Order_Date  │       │ Product_ID FK   │───┐
│ Password    │       │ Order_Type  │       │ Ordered_Qty     │   │
│ Phone_Number│       │ Party_Name  │       │ Sold_Price      │   │
│ Role        │       └─────────────┘       └─────────────────┘   │
└─────────────┘                                                    │
                                                                   │
                       ┌─────────────┐                             │
                       │  Products   │                             │
                       ├─────────────┤                             │
                       │ Product_ID  │◄────────────────────────────┘
                       │ Name        │
                       │ Category    │
                       │ Quantity    │
                       │ Price       │
                       │ Status      │
                       │ Threshold   │
                       └─────────────┘
```

### Tables Overview

| Table | Description |
|-------|-------------|
| `Staff` | User accounts with roles (Admin/Staff) |
| `Products` | Product catalog with inventory tracking |
| `Orders` | Order headers (Sell/Purchase transactions) |
| `Order_Details` | Order line items with quantities and prices |

---

## 📡 API Documentation

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/auth/login.php` | User login |
| `POST` | `/api/auth/logout.php` | User logout |
| `GET` | `/api/auth/session.php` | Check session status |

### Products

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/products/list.php` | Get all products |
| `GET` | `/api/products/get.php?id=X` | Get single product |
| `POST` | `/api/products/create.php` | Create product |
| `PUT` | `/api/products/update.php` | Update product |
| `DELETE` | `/api/products/delete.php` | Delete product |

### Orders

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/orders/list.php` | Get all orders |
| `GET` | `/api/orders/get.php?id=X` | Get order details |
| `POST` | `/api/orders/create.php` | Create new order |

### Staff *(Admin Only)*

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/staff/list.php` | Get all staff |
| `GET` | `/api/staff/get.php?id=X` | Get staff details |
| `POST` | `/api/staff/create.php` | Create staff account |
| `PUT` | `/api/staff/update.php` | Update staff |
| `DELETE` | `/api/staff/delete.php` | Delete staff |

### Reports

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/reports/stats.php` | Get dashboard statistics |

### API Response Format

**Success Response:**
```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Error description"
}
```

---

## 🔐 Security Features

- ✅ **Password Hashing** - Bcrypt encryption for all passwords
- ✅ **SQL Injection Prevention** - PDO prepared statements throughout
- ✅ **XSS Protection** - Input sanitization and output escaping
- ✅ **CSRF Protection** - Session-based token validation
- ✅ **Session Security** - Secure session management
- ✅ **Database Transactions** - ACID compliance for order processing
- ✅ **Row-Level Locking** - Prevents race conditions in concurrent orders
- ✅ **Directory Protection** - `.htaccess` rules prevent direct file access
- ✅ **Role-Based Access** - Admin vs Staff permissions

---

## 🛠️ Development

### Running Locally

```bash
# Start XAMPP services (Apache + MySQL)
# Navigate to project directory
cd /xampp/htdocs/QuickMart\ code

# Access the application
open http://localhost/QuickMart%20code/
```

### Code Style

- **PHP**: PSR-12 coding standards
- **JavaScript**: ES6+ with JSDoc comments
- **CSS**: BEM methodology for class naming

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. **Fork** the repository
2. **Create** a feature branch
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. **Commit** your changes
   ```bash
   git commit -m "Add amazing feature"
   ```
4. **Push** to the branch
   ```bash
   git push origin feature/amazing-feature
   ```
5. **Open** a Pull Request

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Author

**Your Name**

- GitHub: [@mohammad-emad9](https://github.com/mohammad-emad9)
- LinkedIn: [‪Mohammad Emad‬‏](www.linkedin.com/in/‪mohammad-emad‬‏-61532b160)

---

## 🙏 Acknowledgments

- [Bootstrap](https://getbootstrap.com/) - UI Framework
- [Font Awesome](https://fontawesome.com/) - Icons
- [XAMPP](https://www.apachefriends.org/) - Development Server

---

<p align="center">
  Made with ❤️ for efficient inventory management
</p>
