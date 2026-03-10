# Architecture Documentation - D-WarungS

## 1. Architecture Overview

### 1.1 Architecture Pattern
**3-Tier Architecture (Presentation, Business Logic, Data)**

```
┌─────────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                          │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │   Website   │  │ Mobile UI   │  │  Admin UI   │             │
│  └─────────────┘  └─────────────┘  └─────────────┘             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                   BUSINESS LOGIC LAYER                          │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │ Controllers │  │  Services   │  │   Middleware│             │
│  └─────────────┘  └─────────────┘  └─────────────┘             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      DATA LAYER                                │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │   Models    │  │  Database   │  │    Cache    │             │
│  └─────────────┘  └─────────────┘  └─────────────┘             │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. Layer Responsibilities

### 2.1 Presentation Layer
**Responsibilities:**
- Render user interfaces (HTML/CSS/JS)
- Handle user input and interactions
- Display data from business logic
- Manage client-side validation
- Handle responsive design

**Components:**
- Views (Blade templates)
- CSS/JavaScript assets
- Client-side form validation

### 2.2 Business Logic Layer
**Responsibilities:**
- Process application logic
- Handle request/response flow
- Implement business rules
- Manage authentication & authorization
- Coordinate between layers

**Components:**
- Controllers (HTTP requests)
- Services (business logic)
- Middleware (filters)
- Form Requests (validation)

### 2.3 Data Layer
**Responsibilities:**
- Database operations
- Data modeling and relationships
- Query optimization
- Data caching
- Migration management

**Components:**
- Models (Eloquent/ORM)
- Database Migrations
- Seeders
- Cache configuration

---

## 3. Component Diagram

### 3.1 System Components

```
┌────────────────────────────────────────────────────────────────────┐
│                         CLIENTS                                    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐           │
│  │   Customer   │  │   Vendor     │  │    Admin     │           │
│  │   Browser    │  │   Dashboard  │  │    Panel     │           │
│  └──────────────┘  └──────────────┘  └──────────────┘           │
└────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌────────────────────────────────────────────────────────────────────┐
│                       WEB SERVER (Apache)                         │
│  ┌──────────────────────────────────────────────────────────┐    │
│  │                    ENTRY POINT (index.php)                │    │
│  └──────────────────────────────────────────────────────────┘    │
│                              │                                    │
│  ┌──────────────────────────┼───────────────────────────────┐  │
│  │                    ROUTER                                   │  │
│  │  /                 /auth           /admin                  │  │
│  │  /home             /login          /vendor                 │  │
│  │  /vendors          /register       /cashier                │  │
│  │  /menu             /logout         /orders                 │  │
│  │  /cart                                               │  │
│  │  /checkout                                            │  │
│  └─────────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌────────────────────────────────────────────────────────────────────┐
│                     APPLICATION CORE                              │
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │
│  │  Auth       │  │  Order      │  │  Payment    │              │
│  │  Service    │  │  Service    │  │  Service    │              │
│  └─────────────┘  └─────────────┘  └─────────────┘              │
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │
│  │  Vendor     │  │  Product    │  │  Notification│              │
│  │  Service    │  │  Service    │  │  Service    │              │
│  └─────────────┘  └─────────────┘  └─────────────┘              │
│                                                                  │
└────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌────────────────────────────────────────────────────────────────────┐
│                        DATA LAYER                                 │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                    DATABASE (MySQL)                          │ │
│  │  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐    │ │
│  │  │users │ │vendors│ │orders│ │products│ │categories│ │reviews│ │    │
│  │  └──────┘ └──────┘ └──────┘ └──────┘ └──────┘ └──────┘    │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  ┌─────────────────────┐  ┌─────────────────────┐               │
│  │   CACHE (Session)   │  │   FILE STORAGE      │               │
│  │   /storage/cache    │  │   /storage/uploads │               │
│  └─────────────────────┘  └─────────────────────┘               │
└────────────────────────────────────────────────────────────────────┘
```

---

## 4. Database Schema

### 4.1 Entity Relationship Diagram

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│    users    │       │   vendors   │       │  categories │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id (PK)     │◄──────│ user_id(FK) │       │ id (PK)     │
│ name        │       │ id (PK)     │◄──────│ vendor_id(FK)│
│ email       │       │ name        │       │ id (PK)     │
│ phone       │       │ description │       │ name        │
│ password    │       │ logo        │       │ description │
│ role        │       │ address     │       │ order       │
│ created_at  │       │ status      │       │ created_at  │
│ updated_at  │       │ created_at  │       └─────────────┘
└─────────────┘       │ updated_at  │              │
       │              └─────────────┘              │
       │                     │                    │
       │                     ▼                    │
       │              ┌─────────────┐              │
       │              │  products   │              │
       │              ├─────────────┤              │
       └──────────────│ vendor_id(FK)│◄───────────┘
                      │ category_id(FK)
                      │ id (PK)
                      │ name
                      │ description
                      │ image
                      │ price
                      │ status
                      │ created_at
                      │ updated_at
                      └─────────────┘
                             │
                             ▼
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   orders    │       │ order_items│       │  reviews    │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id (PK)     │◄──────│ order_id(FK)│       │ id (PK)     │
│ user_id(FK) │       │ id (PK)     │       │ user_id(FK) │
│ vendor_id(FK)      │ product_id(FK)      │ vendor_id(FK)│
│ order_number│       │ quantity    │       │ order_id(FK)│
│ total_amount│       │ unit_price  │       │ rating      │
│ status      │       │ subtotal    │       │ comment     │
│ payment_method     │ notes       │       │ created_at  │
│ payment_status     └─────────────┘       └─────────────┘
│ notes        │
│ created_at   │
│ updated_at   │
└─────────────┘
```

### 4.2 Database Tables Detail

#### users
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'vendor', 'cashier', 'admin') DEFAULT 'customer',
    avatar VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### vendors
```sql
CREATE TABLE vendors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    logo VARCHAR(255),
    cover_image VARCHAR(255),
    address TEXT,
    phone VARCHAR(20),
    operating_hours JSON,
    status ENUM('pending', 'active', 'inactive', 'suspended') DEFAULT 'pending',
    rating DECIMAL(3,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### categories
```sql
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE
);
```

#### products
```sql
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

#### orders
```sql
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    vendor_id BIGINT UNSIGNED NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method ENUM('cash', 'digital_wallet', 'card') NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_proof VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE
);
```

#### order_items
```sql
CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

#### reviews
```sql
CREATE TABLE reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    vendor_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);
```

---

## 5. Component Interactions

### 5.1 Order Flow Sequence

```
Customer              System                 Vendor              Database
  │                     │                      │                    │
  │── Browse Vendors ──►│                      │                    │
  │◄─ Vendor List ──────│                      │                    │
  │                     │                      │                    │
  │── View Menu ───────►│                      │                    │
  │◄─ Products ────────│                      │                    │
  │                     │                      │                    │
  │── Add to Cart ────►│                      │                    │
  │◄─ Cart Updated ────│                      │                    │
  │                     │                      │                    │
  │── Place Order ────► │                      │                    │
  │                     │── Insert Order ────► │                    │
  │                     │◄─ Order Created ─────│                    │
  │                     │                      │                    │
  │                     │── Notify ──────────► │                    │
  │                     │                      │                    │
  │                     │                      │── Update Status ─►│
  │                     │◄─ Status Update ─────│                    │
  │◄─ Order Confirm ────│                      │                    │
  │                     │                      │                    │
  │                     │                      │── Ready ─────────►│
  │◄─ Ready Notify ────│                      │                    │
```

### 5.2 Authentication Flow

```
User                  System                 Database
  │                     │                      │
  │── Login ──────────►│                      │
  │                     │── Find User ───────►│
  │                     │◄─ User Data ────────│
  │                     │                      │
  │                     │── Verify Password ─►│
  │                     │◄─ Valid ────────────│
  │                     │                      │
  │                     │── Create Session ──►│
  │◄─ Login Success ────│                      │
  │                     │                      │
```

---

## 6. API Endpoints Structure

### 6.1 Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/auth/register | User registration |
| POST | /api/auth/login | User login |
| POST | /api/auth/logout | User logout |
| GET | /api/auth/me | Get current user |
| POST | /api/auth/forgot-password | Password reset request |

### 6.2 Vendors
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/vendors | List all vendors |
| GET | /api/vendors/{id} | Get vendor details |
| POST | /api/vendors | Create vendor (admin) |
| PUT | /api/vendors/{id} | Update vendor |
| DELETE | /api/vendors/{id} | Delete vendor |

### 6.3 Products
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/products | List products |
| GET | /api/products/{id} | Get product details |
| POST | /api/products | Create product |
| PUT | /api/products/{id} | Update product |
| DELETE | /api/products/{id} | Delete product |

### 6.4 Orders
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/orders | List orders |
| GET | /api/orders/{id} | Get order details |
| POST | /api/orders | Create order |
| PUT | /api/orders/{id}/status | Update order status |
| DELETE | /api/orders/{id} | Cancel order |

---

## 7. Security Architecture

### 7.1 Authentication & Authorization
```
┌─────────────────────────────────────┐
│         AUTHENTICATION              │
│  ┌─────────────────────────────┐   │
│  │  JWT / Session-based Auth   │   │
│  │  - Login/Register          │   │
│  │  - Password Hashing         │   │
│  │  - Token Management        │   │
│  └─────────────────────────────┘   │
└─────────────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────┐
│       AUTHORIZATION (RBAC)          │
│  ┌─────────────────────────────┐   │
│  │  Roles:                     │   │
│  │  - admin (all access)      │   │
│  │  - vendor (own data)        │   │
│  │  - cashier (orders)        │   │
│  │  - customer (own orders)   │   │
│  └─────────────────────────────┘   │
└─────────────────────────────────────┘
```

### 7.2 Security Layers
| Layer | Implementation |
|-------|---------------|
| Network | HTTPS, Firewall |
| Application | CSRF tokens, XSS sanitization |
| Database | Prepared statements, least privilege |
| Password | Bcrypt/Argon2 hashing |

---

*Document Version: 1.0*
*Architecture Documentation for D-WarungS*

