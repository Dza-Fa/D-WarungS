# URL Routing Structure - D-WarungS

## 1. Route Overview

This document defines the clean URL routes for all pages in the D-WarungS platform, following RESTful conventions where applicable.

---

## 2. Public Routes (Guest Users)

### 2.1 Home & Landing
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/` | HomeController@index | Landing page |
| GET | `/home` | HomeController@home | Home page (authenticated) |

### 2.2 Authentication
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/login` | AuthController@showLoginForm | Login page |
| POST | `/login` | AuthController@login | Process login |
| GET | `/register` | AuthController@showRegisterForm | Registration page |
| POST | `/register` | AuthController@register | Process registration |
| POST | `/logout` | AuthController@logout | Logout |
| GET | `/password/reset` | AuthController@showResetForm | Password reset request |
| POST | `/password/email` | AuthController@sendResetLink | Send reset link |
| GET | `/password/reset/{token}` | AuthController@showNewPassword | New password form |
| POST | `/password/reset` | AuthController@resetPassword | Reset password |

### 2.3 Vendor & Menu (Public)
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/vendors` | VendorController@index | List all vendors |
| GET | `/vendors/{slug}` | VendorController@show | Vendor details |
| GET | `/vendors/{slug}/menu` | VendorController@menu | Vendor menu |
| GET | `/menu/{productSlug}` | ProductController@show | Product details |

### 2.4 Search
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/search` | SearchController@index | Search results |
| GET | `/autocomplete` | SearchController@autocomplete | AJAX autocomplete |

---

## 3. Customer Routes (Authenticated)

### 3.1 Cart
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/cart` | CartController@index | View cart |
| POST | `/cart/add` | CartController@add | Add item to cart |
| POST | `/cart/update` | CartController@update | Update quantity |
| POST | `/cart/remove` | CartController@remove | Remove item |
| POST | `/cart/clear` | CartController@clear | Clear cart |

### 3.2 Checkout
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/checkout` | CheckoutController@index | Checkout page |
| POST | `/checkout` | CheckoutController@store | Place order |
| GET | `/checkout/success/{orderNumber}` | CheckoutController@success | Order success |

### 3.3 Orders
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/orders` | OrderController@index | Order history |
| GET | `/orders/{orderNumber}` | OrderController@show | Order details |
| POST | `/orders/{orderNumber}/cancel` | OrderController@cancel | Cancel order |

### 3.4 Profile
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/profile` | ProfileController@index | User profile |
| PUT | `/profile` | ProfileController@update | Update profile |
| GET | `/profile/edit` | ProfileController@edit | Edit profile form |
| PUT | `/profile/password` | ProfileController@updatePassword | Change password |
| GET | `/profile/addresses` | ProfileController@addresses | Saved addresses |
| POST | `/profile/addresses` | ProfileController@storeAddress | Save address |
| DELETE | `/profile/addresses/{id}` | ProfileController@destroyAddress | Delete address |

### 3.5 Reviews
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| POST | `/reviews` | ReviewController@store | Submit review |
| GET | `/reviews/{id}/edit` | ReviewController@edit | Edit review |
| PUT | `/reviews/{id}` | ReviewController@update | Update review |
| DELETE | `/reviews/{id}` | ReviewController@destroy | Delete review |

---

## 4. Vendor Routes (Authenticated - Vendor Role)

### 4.1 Dashboard
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/vendor/dashboard` | Vendor\DashboardController@index | Vendor dashboard |
| GET | `/vendor/orders` | Vendor\OrderController@index | Order list |
| GET | `/vendor/orders/{orderNumber}` | Vendor\OrderController@show | Order details |
| PUT | `/vendor/orders/{orderNumber}/status` | Vendor\OrderController@updateStatus | Update status |

### 4.2 Menu Management
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/vendor/menu` | Vendor\ProductController@index | Menu list |
| GET | `/vendor/menu/create` | Vendor\ProductController@create | Add product form |
| POST | `/vendor/menu` | Vendor\ProductController@store | Save product |
| GET | `/vendor/menu/{product}` | Vendor\ProductController@show | View product |
| GET | `/vendor/menu/{product}/edit` | Vendor\ProductController@edit | Edit product form |
| PUT | `/vendor/menu/{product}` | Vendor\ProductController@update | Update product |
| DELETE | `/vendor/menu/{product}` | Vendor\ProductController@destroy | Delete product |
| PUT | `/vendor/menu/{product}/toggle` | Vendor\ProductController@toggleStatus | Toggle availability |

### 4.3 Category Management
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/vendor/categories` | Vendor\CategoryController@index | Categories list |
| POST | `/vendor/categories` | Vendor\CategoryController@store | Create category |
| PUT | `/vendor/categories/{category}` | Vendor\CategoryController@update | Update category |
| DELETE | `/vendor/categories/{category}` | Vendor\CategoryController@destroy | Delete category |

### 4.4 Analytics
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/vendor/analytics` | Vendor\AnalyticsController@index | Sales analytics |
| GET | `/vendor/analytics/sales` | Vendor\AnalyticsController@sales | Sales data |
| GET | `/vendor/analytics/products` | Vendor\AnalyticsController@products | Product performance |

### 4.5 Profile
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/vendor/profile` | Vendor\ProfileController@index | Vendor profile |
| PUT | `/vendor/profile` | Vendor\ProfileController@update | Update profile |
| PUT | `/vendor/profile/hours` | Vendor\ProfileController@updateHours | Update operating hours |

---

## 5. Cashier Routes (Authenticated - Cashier Role)

### 5.1 Dashboard
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/cashier/dashboard` | Cashier\DashboardController@index | Cashier dashboard |
| GET | `/cashier/orders` | Cashier\OrderController@index | All orders |
| GET | `/cashier/orders/pending` | Cashier\OrderController@pending | Pending orders |

### 5.2 Order Processing
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/cashier/orders/{orderNumber}` | Cashier\OrderController@show | Order details |
| PUT | `/cashier/orders/{orderNumber}/confirm` | Cashier\OrderController@confirm | Confirm order |
| PUT | `/cashier/orders/{orderNumber}/payment` | Cashier\OrderController@updatePayment | Update payment |
| PUT | `/cashier/orders/{orderNumber}/complete` | Cashier\OrderController@complete | Complete order |

### 5.3 Reports
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/cashier/reports` | Cashier\ReportController@index | Daily reports |
| GET | `/cashier/reports/daily` | Cashier\ReportController@daily | Daily sales |

---

## 6. Admin Routes (Authenticated - Admin Role)

### 6.1 Dashboard
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/admin/dashboard` | Admin\DashboardController@index | Admin dashboard |
| GET | `/admin/stats` | Admin\DashboardController@stats | Statistics |

### 6.2 User Management
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/admin/users` | Admin\UserController@index | All users |
| GET | `/admin/users/create` | Admin\UserController@create | Create user form |
| POST | `/admin/users` | Admin\UserController@store | Save user |
| GET | `/admin/users/{user}` | Admin\UserController@show | User details |
| GET | `/admin/users/{user}/edit` | Admin\UserController@edit | Edit user form |
| PUT | `/admin/users/{user}` | Admin\UserController@update | Update user |
| DELETE | `/admin/users/{user}` | Admin\UserController@destroy | Delete user |
| PUT | `/admin/users/{user}/status` | Admin\UserController@toggleStatus | Toggle user status |

### 6.3 Vendor Management
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/admin/vendors` | Admin\VendorController@index | All vendors |
| GET | `/admin/vendors/pending` | Admin\VendorController@pending | Pending approvals |
| GET | `/admin/vendors/{vendor}` | Admin\VendorController@show | Vendor details |
| PUT | `/admin/vendors/{vendor}/approve` | Admin\VendorController@approve | Approve vendor |
| PUT | `/admin/vendors/{vendor}/reject` | Admin\VendorController@reject | Reject vendor |
| PUT | `/admin/vendors/{vendor}` | Admin\VendorController@update | Update vendor |
| DELETE | `/admin/vendors/{vendor}` | Admin\VendorController@destroy | Delete vendor |

### 6.4 Category Management
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/admin/categories` | Admin\CategoryController@index | All categories |
| POST | `/admin/categories` | Admin\CategoryController@store | Create category |
| PUT | `/admin/categories/{category}` | Admin\CategoryController@update | Update category |
| DELETE | `/admin/categories/{category}` | Admin\CategoryController@destroy | Delete category |

### 6.5 Product Management
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/admin/products` | Admin\ProductController@index | All products |
| DELETE | `/admin/products/{product}` | Admin\ProductController@destroy | Delete product |

### 6.6 Order Management
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/admin/orders` | Admin\OrderController@index | All orders |
| GET | `/admin/orders/{orderNumber}` | Admin\OrderController@show | Order details |
| PUT | `/admin/orders/{orderNumber}` | Admin\OrderController@update | Update order |

### 6.7 Reviews Management
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/admin/reviews` | Admin\ReviewController@index | All reviews |
| PUT | `/admin/reviews/{review}/approve` | Admin\ReviewController@approve | Approve review |
| DELETE | `/admin/reviews/{review}` | Admin\ReviewController@destroy | Delete review |

### 6.8 Reports & Analytics
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/admin/reports` | Admin\ReportController@index | Reports dashboard |
| GET | `/admin/reports/sales` | Admin\ReportController@sales | Sales reports |
| GET | `/admin/reports/users` | Admin\ReportController@users | User reports |
| GET | `/admin/reports/vendors` | Admin\ReportController@vendors | Vendor reports |

### 6.9 Settings
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/admin/settings` | Admin\SettingController@index | System settings |
| PUT | `/admin/settings` | Admin\SettingController@update | Update settings |

---

## 7. API Routes (RESTful API)

### 7.1 Authentication API
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| POST | `/api/auth/register` | API\AuthController@register | API registration |
| POST | `/api/auth/login` | API\AuthController@login | API login |
| POST | `/api/auth/logout` | API\AuthController@logout | API logout |
| GET | `/api/auth/me` | API\AuthController@me | Current user |

### 7.2 Vendor API
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/api/vendors` | API\VendorController@index | List vendors |
| GET | `/api/vendors/{id}` | API\VendorController@show | Vendor details |
| GET | `/api/vendors/{id}/products` | API\VendorController@products | Vendor products |

### 7.3 Product API
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/api/products` | API\ProductController@index | List products |
| GET | `/api/products/{id}` | API\ProductController@show | Product details |

### 7.4 Order API
| Method | Route | Controller | Description |
|--------|-------|------------|-------------|
| GET | `/api/orders` | API\OrderController@index | User orders |
| POST | `/api/orders` | API\OrderController@store | Create order |
| GET | `/api/orders/{id}` | API\OrderController@show | Order details |
| PUT | `/api/orders/{id}/status` | API\OrderController@updateStatus | Update status |

---

## 8. Route Naming Conventions

### 8.1 Naming Pattern
```
Resource: users
- index:   GET    /users           → users.index
- create:  GET    /users/create    → users.create
- store:   POST   /users           → users.store
- show:    GET    /users/{id}      → users.show
- edit:    GET    /users/{id}/edit → users.edit
- update:  PUT    /users/{id}      → users.update
- destroy: DELETE /users/{id}      → users.destroy
```

### 8.2 Middleware Groups
| Group | Middleware | Routes |
|-------|------------|--------|
| web | web (session, CSRF) | Public & authenticated web routes |
| api | api (throttle, auth:api) | API routes |
| vendor | auth, role:vendor | Vendor routes |
| cashier | auth, role:cashier | Cashier routes |
| admin | auth, role:admin | Admin routes |

---

## 9. Route Parameters

### 9.1 Common Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `{slug}` | string | URL-friendly identifier (vendors) |
| `{orderNumber}` | string | Unique order identifier |
| `{id}` | integer | Database ID |
| `{user}` | model | Route model binding |
| `{product}` | model | Route model binding |

### 9.2 Example URLs
```
/vendors/warung-nusantara
/vendors/warung-nusantara/menu
/orders/ORD-20240115-0001
/products/nasi-goreng-special
/vendor/menu/12/edit
/admin/vendors/5/approve
```

---

## 10. Redirect Rules

| Old Route | New Route | Notes |
|-----------|-----------|-------|
| `/login` | `/login` | No change |
| `/register` | `/register` | No change |
| `/home` | `/dashboard` | After login redirect |

---

*Document Version: 1.0*
*URL Routing Structure for D-WarungS*

