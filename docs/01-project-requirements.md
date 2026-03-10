# Project Requirements Document - D-WarungS

## 1. Project Overview

### Project Name
**D-WarungS** - Website O2O (Online-to-Offline) Food-Court Platform

### Project Description
D-WarungS is a web-based platform that connects customers with food vendors in a food court setting, enabling online ordering with offline pickup/delivery. The platform facilitates seamless transactions between customers, cashiers, and restaurant vendors.

---

## 2. User Roles

### 2.1 Customer
**Description:** End-users who browse, order, and pay for food items online.

**Responsibilities:**
- Browse food court vendors and menus
- Add items to cart
- Place orders and make payments
- Track order status
- Rate and review vendors

**Access Level:** Public/Registered

### 2.2 Cashier
**Description:** Staff responsible for processing payments and managing order transactions at the food court.

**Responsibilities:**
- View incoming orders
- Process payments (cash, digital)
- Update order status
- Generate daily sales reports
- Manage refund requests

**Access Level:** Staff (Authenticated)

### 2.3 Vendor/Restaurant
**Description:** Food court merchants who manage their menu and fulfill orders.

**Responsibilities:**
- Manage menu items (add, edit, delete)
- View incoming orders
- Update order preparation status
- Manage inventory
- View sales analytics

**Access Level:** Vendor (Authenticated)

### 2.4 Admin
**Description:** System administrator who manages the entire platform.

**Responsibilities:**
- Manage all users (customers, cashiers, vendors)
- Manage vendors/restaurants
- Configure system settings
- View platform-wide analytics
- Handle disputes and issues

**Access Level:** Administrator (Authenticated)

---

## 3. Functional Requirements

### 3.1 User Management
| ID | Requirement | Priority |
|----|-------------|----------|
| FR-01 | User registration with email/phone | High |
| FR-02 | User login with authentication | High |
| FR-03 | Password reset functionality | High |
| FR-04 | User profile management | Medium |
| FR-05 | Role-based access control (RBAC) | High |

### 3.2 Vendor Management
| ID | Requirement | Priority |
|----|-------------|----------|
| FR-06 | Vendor registration and approval process | High |
| FR-07 | Vendor dashboard with analytics | High |
| FR-08 | Vendor profile management | High |
| FR-09 | Operating hours configuration | Medium |

### 3.3 Menu & Product Management
| ID | Requirement | Priority |
|----|-------------|----------|
| FR-10 | Create, read, update, delete menu items | High |
| FR-11 | Category management for menus | High |
| FR-12 | Product image upload | High |
| FR-13 | Price management with variants | Medium |
| FR-14 | Availability toggle (in-stock/out-of-stock) | High |
| FR-15 | Menu item customization (add-ons, modifications) | Medium |

### 3.4 Order Management
| ID | Requirement | Priority |
|----|-------------|----------|
| FR-16 | Shopping cart functionality | High |
| FR-17 | Order placement with confirmation | High |
| FR-18 | Order tracking (real-time status updates) | High |
| FR-19 | Order history for customers | High |
| FR-20 | Order management for vendors | High |
| FR-21 | Order status workflow (Pending → Preparing → Ready → Completed) | High |
| FR-22 | Order cancellation (with refund logic) | Medium |

### 3.5 Payment System
| ID | Requirement | Priority |
|----|-------------|----------|
| FR-23 | Multiple payment methods (Cash, Digital Wallet) | High |
| FR-24 | Payment processing | High |
| FR-25 | Receipt generation | Medium |
| FR-26 | Refund processing | Medium |

### 3.6 Search & Filtering
| ID | Requirement | Priority |
|----|-------------|----------|
| FR-27 | Search vendors by name | High |
| FR-28 | Search menu items | High |
| FR-29 | Filter by category | High |
| FR-30 | Sort by price, rating, popularity | Medium |

### 3.7 Reviews & Ratings
| ID | Requirement | Priority |
|----|-------------|----------|
| FR-31 | Rate vendors after order completion | Medium |
| FR-32 | Leave text reviews | Medium |
| FR-33 | View ratings and reviews | High |

### 3.8 Notifications
| ID | Requirement | Priority |
|----|-------------|----------|
| FR-34 | Email notifications (order confirmation, status updates) | Medium |
| FR-35 | SMS notifications (optional) | Low |
| FR-36 | In-app notifications | High |

### 3.9 Reporting & Analytics
| ID | Requirement | Priority |
|----|-------------|----------|
| FR-37 | Sales reports for vendors | High |
| FR-38 | Order statistics for admin | High |
| FR-39 | Revenue tracking | Medium |
| FR-40 | Popular items analysis | Medium |

---

## 4. Non-Functional Requirements

### 4.1 Performance
| ID | Requirement | Target |
|----|-------------|--------|
| NFR-01 | Page load time | < 3 seconds |
| NFR-02 | Order processing time | < 5 seconds |
| NFR-03 | Concurrent users support | 100+ users |

### 4.2 Security
| ID | Requirement | Priority |
|----|-------------|----------|
| NFR-04 | HTTPS encryption | High |
| NFR-05 | Password hashing (bcrypt/argon2) | High |
| NFR-06 | SQL injection prevention | High |
| NFR-07 | XSS prevention | High |
| NFR-08 | CSRF protection | High |
| NFR-09 | Session management | High |

### 4.3 Usability
| ID | Requirement | Priority |
|----|-------------|----------|
| NFR-10 | Responsive design (mobile-friendly) | High |
| NFR-11 | Intuitive navigation | High |
| NFR-12 | Accessibility compliance (WCAG) | Medium |

### 4.4 Reliability
| ID | Requirement | Priority |
|----|-------------|----------|
| NFR-13 | 99% system uptime | High |
| NFR-14 | Data backup and recovery | High |
| NFR-15 | Error logging and monitoring | High |

### 4.5 Scalability
| ID | Requirement | Priority |
|----|-------------|----------|
| NFR-16 | Modular architecture for easy scaling | High |
| NFR-17 | Database optimization for growth | High |

---

## 5. Core Features Summary

### Must-Have (MVP)
1. User registration and authentication
2. Vendor listing and profiles
3. Menu browsing and search
4. Shopping cart
5. Order placement and tracking
6. Basic payment processing
7. Order status management
8. Vendor dashboard

### Should-Have
1. Rating and review system
2. Advanced search and filters
3. Sales reports for vendors
4. Email notifications

### Could-Have
1. SMS notifications
2. Mobile app integration
3. Loyalty points system
4. Advanced analytics

### Won't-Have (Initial Phase)
1. Real-time chat
2. AI-based recommendations
3. Multi-language support
4. Restaurant reservation system

---

## 6. Data Entities

### Users
- id, name, email, phone, password_hash, role, created_at, updated_at

### Vendors
- id, user_id, name, description, logo, cover_image, address, operating_hours, status, created_at, updated_at

### Categories
- id, vendor_id, name, description, display_order, created_at

### Products
- id, vendor_id, category_id, name, description, image, price, status, created_at, updated_at

### Orders
- id, user_id, vendor_id, order_number, total_amount, status, payment_method, payment_status, notes, created_at, updated_at

### Order Items
- id, order_id, product_id, quantity, unit_price, subtotal, notes

### Reviews
- id, user_id, vendor_id, order_id, rating, comment, created_at

---

## 7. Acceptance Criteria

### AC-01: User Registration
- [ ] User can register with valid email and password
- [ ] Password must be at least 8 characters
- [ ] Email validation is performed
- [ ] Success message displayed after registration

### AC-02: Ordering Flow
- [ ] User can browse vendors and menus
- [ ] User can add items to cart
- [ ] User can place order with payment
- [ ] Order confirmation is displayed
- [ ] Vendor receives order notification

### AC-03: Order Tracking
- [ ] Customer can view order status
- [ ] Status updates reflect in real-time
- [ ] Status progression: Pending → Preparing → Ready → Completed

### AC-04: Vendor Management
- [ ] Vendor can add/edit/delete menu items
- [ ] Vendor can view and manage orders
- [ ] Vendor can update order status

---

*Document Version: 1.0*
*Created: Project Initiation Phase*

