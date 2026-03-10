# Folder Structure Tree - D-WarungS

## 1. Project Root Structure

This document provides a scalable and maintainable folder structure for the D-WarungS O2O Food-Court platform using PHP and Laravel framework.

```
D-WarungS/
├── app/                          # Application source code
│   ├── Console/                  # Artisan commands
│   │   ├── Commands/            # Custom CLI commands
│   │   └── Kernel.php           # Console kernel
│   ├── Events/                  # Event classes
│   │   ├── OrderCreated.php
│   │   ├── OrderStatusChanged.php
│   │   └── PaymentReceived.php
│   ├── Exceptions/              # Exception handlers
│   │   └── Handler.php
│   ├── Http/
│   │   ├── Controllers/         # HTTP request handlers
│   │   │   ├── Auth/            # Authentication controllers
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── RegisterController.php
│   │   │   │   └── ResetPasswordController.php
│   │   │   ├── HomeController.php
│   │   │   ├── VendorController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CartController.php
│   │   │   ├── CheckoutController.php
│   │   │   ├── OrderController.php
│   │   │   ├── ReviewController.php
│   │   │   ├── SearchController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── Admin/           # Admin controllers
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── UserController.php
│   │   │   │   ├── VendorController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── ReviewController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   └── SettingController.php
│   │   │   ├── Vendor/          # Vendor panel controllers
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── AnalyticsController.php
│   │   │   │   └── ProfileController.php
│   │   │   ├── Cashier/         # Cashier controllers
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   └── ReportController.php
│   │   │   └── API/             # API controllers
│   │   │       ├── AuthController.php
│   │   │       ├── VendorController.php
│   │   │       ├── ProductController.php
│   │   │       └── OrderController.php
│   │   ├── Middleware/         # HTTP middleware
│   │   │   ├── Authenticate.php
│   │   │   ├── RedirectIfAuthenticated.php
│   │   │   ├── CheckRole.php
│   │   │   ├── VerifyCsrfToken.php
│   │   │   └── TrimStrings.php
│   │   ├── Requests/           # Form request validation
│   │   │   ├── StoreUserRequest.php
│   │   │   ├── UpdateUserRequest.php
│   │   │   ├── StoreProductRequest.php
│   │   │   ├── StoreOrderRequest.php
│   │   │   └── StoreReviewRequest.php
│   │   └── Kernel.php          # HTTP kernel
│   ├── Jobs/                   # Queue jobs
│   │   ├── SendOrderNotification.php
│   │   ├── ProcessPayment.php
│   │   └── GenerateReport.php
│   ├── Listeners/              # Event listeners
│   │   ├── OrderCreatedListener.php
│   │   └── SendEmailListener.php
│   ├── Mail/                   # Email classes
│   │   ├── OrderConfirmation.php
│   │   ├── OrderReady.php
│   │   └── WelcomeEmail.php
│   ├── Models/                 # Database models (Eloquent)
│   │   ├── User.php
│   │   ├── Vendor.php
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Review.php
│   │   ├── Payment.php
│   │   ├── Cart.php
│   │   └── Setting.php
│   ├── Notifications/         # Notification classes
│   │   ├── OrderStatusNotification.php
│   │   └── NewOrderNotification.php
│   ├── Observers/              # Model observers
│   │   ├── UserObserver.php
│   │   ├── VendorObserver.php
│   │   ├── OrderObserver.php
│   │   └── ProductObserver.php
│   ├── Policies/               # Authorization policies
│   │   ├── UserPolicy.php
│   │   ├── VendorPolicy.php
│   │   ├── OrderPolicy.php
│   │   └── ProductPolicy.php
│   ├── Providers/              # Service providers
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   ├── RouteServiceProvider.php
│   │   └── ViewServiceProvider.php
│   ├── Rules/                  # Custom validation rules
│   │   └── ValidOrderStatus.php
│   └── Helpers/                # Utility functions
│       ├── OrderNumberHelper.php
│       ├── PriceHelper.php
│       └── FormatHelper.php
│
├── bootstrap/                  # Application bootstrapping
│   ├── app.php
│   ├── cache/                  # Compiled routes, config
│   └── providers.php
│
├── config/                     # Configuration files
│   ├── app.php
│   ├── auth.php
│   ├── broadcasting.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── hashing.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php
│   ├── session.php
│   └── view.php
│
├── database/                   # Database files
│   ├── migrations/            # Database migrations
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   ├── 2024_01_01_000002_create_vendors_table.php
│   │   ├── 2024_01_01_000003_create_categories_table.php
│   │   ├── 2024_01_01_000004_create_products_table.php
│   │   ├── 2024_01_01_000005_create_orders_table.php
│   │   ├── 2024_01_01_000006_create_order_items_table.php
│   │   ├── 2024_01_01_000007_create_reviews_table.php
│   │   └── 2024_01_01_000008_create_payments_table.php
│   ├── seeders/               # Database seeders
│   │   ├── DatabaseSeeder.php
│   │   ├── UserSeeder.php
│   │   ├── VendorSeeder.php
│   │   ├── CategorySeeder.php
│   │   ├── ProductSeeder.php
│   │   └── RoleSeeder.php
│   └── factories/              # Model factories for testing
│       ├── UserFactory.php
│       ├── VendorFactory.php
│       ├── ProductFactory.php
│       └── OrderFactory.php
│
├── public/                      # Web root (htdocs)
│   ├── index.php              # Application entry point
│   ├── .htaccess              # Apache config
│   ├── robots.txt
│   ├── favicon.ico
│   └── assets/                # Public assets
│       ├── css/               # Compiled CSS
│       │   ├── app.css
│       │   ├── vendor.css
│       │   ├── admin.css
│       │   └── custom.css
│       ├── js/                # Compiled JavaScript
│       │   ├── app.js
│       │   ├── vendor.js
│       │   ├── admin.js
│       │   └── custom.js
│       ├── images/            # Public images
│       │   ├── logos/
│       │   ├── products/
│       │   ├── vendors/
│       │   └── banners/
│       ├── fonts/             # Font files
│       └── uploads/           # User uploads (temp)
│
├── resources/                  # Uncompiled resources
│   ├── css/                   # Source CSS (Sass/Less)
│   │   ├── _variables.scss
│   │   ├── _mixins.scss
│   │   ├── _buttons.scss
│   │   ├── _forms.scss
│   │   ├── _tables.scss
│   │   ├── _modals.scss
│   │   ├── app.scss
│   │   ├── frontend.scss
│   │   ├── backend.scss
│   │   └── auth.scss
│   ├── js/                    # Source JavaScript
│   │   ├── app.js
│   │   ├── components/
│   │   ├── bootstrap.js
│   │   └── utilities/
│   ├── lang/                  # Language files
│   │   ├── en/
│   │   │   ├── auth.php
│   │   │   ├── pagination.php
│   │   │   ├── passwords.php
│   │   │   └── validation.php
│   │   └── id/                # Indonesian translations
│   └── views/                 # Blade templates
│       ├── layouts/           # Master layouts
│       │   ├── app.blade.php
│       │   ├── frontend.blade.php
│       │   ├── backend.blade.php
│       │   └── auth.blade.php
│       ├── partials/          # Reusable partials
│       │   ├── header.blade.php
│       │   ├── footer.blade.php
│       │   ├── sidebar.blade.php
│       │   ├── navbar.blade.php
│       │   ├── flash-message.blade.php
│       │   └── pagination.blade.php
│       ├── home/              # Home pages
│       ├── auth/              # Authentication views
│       │   ├── login.blade.php
│       │   ├── register.blade.php
│       │   ├── password/
│       │   └── verify.blade.php
│       ├── vendors/           # Vendor public pages
│       │   ├── index.blade.php
│       │   ├── show.blade.php
│       │   └── menu.blade.php
│       ├── products/          # Product pages
│       │   └── show.blade.php
│       ├── cart/              # Cart views
│       │   └── index.blade.php
│       ├── checkout/          # Checkout views
│       │   ├── index.blade.php
│       │   └── success.blade.php
│       ├── orders/            # Order views
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── profile/           # Profile views
│       │   ├── edit.blade.php
│       │   └── show.blade.php
│       ├── reviews/           # Review views
│       ├── admin/             # Admin panel views
│       │   ├── dashboard/
│       │   ├── users/
│       │   ├── vendors/
│       │   ├── orders/
│       │   ├── products/
│       │   ├── categories/
│       │   ├── reviews/
│       │   ├── reports/
│       │   └── settings/
│       ├── vendor/            # Vendor panel views
│       │   ├── dashboard/
│       │   ├── orders/
│       │   ├── products/
│       │   ├── categories/
│       │   ├── analytics/
│       │   └── profile/
│       ├── cashier/           # Cashier views
│       │   ├── dashboard/
│       │   ├── orders/
│       │   └── reports/
│       ├── errors/            # Error pages
│       │   ├── 404.blade.php
│       │   ├── 500.blade.php
│       │   └── layout.blade.php
│       └── emails/            # Email templates
│           ├── order-confirmation.blade.php
│           ├── order-ready.blade.php
│           └── welcome.blade.php
│
├── routes/                      # Route definitions
│   ├── web.php                # Web routes
│   ├── api.php                # API routes
│   ├── console.php            # Console routes
│   └── channels.php           # Broadcast channels
│
├── storage/                     # Application storage
│   ├── app/                   # Application files
│   │   ├── public/            # User uploads
│   │   │   ├── products/
│   │   │   ├── vendors/
│   │   │   └── categories/
│   │   └── .gitkeep
│   ├── framework/             # Framework files
│   │   ├── cache/             # Application cache
│   │   │   └── data/
│   │   ├── sessions/          # Session files
│   │   ├── testing/           # Testing cache
│   │   └── views/             # Compiled Blade templates
│   └── logs/                  # Application logs
│       ├── laravel.log
│       └── daily/
│
├── tests/                      # Test files
│   ├── Feature/               # Feature tests
│   │   ├── ExampleTest.php
│   │   ├── OrderTest.php
│   │   └── PaymentTest.php
│   ├── Unit/                  # Unit tests
│   │   ├── ExampleTest.php
│   │   └── HelperTest.php
│   ├── TestCase.php
│   └── CreatesApplication.php
│
├── vendor/                     # Composer dependencies
│
├── .env                        # Environment configuration
├── .env.example                # Environment example
├── .gitignore                  # Git ignore rules
├── artisan                    # Laravel CLI
├── composer.json              # Composer dependencies
├── composer.lock              # Composer lock file
├── package.json               # NPM dependencies
├── package-lock.json          # NPM lock file
├── phpunit.xml                # PHPUnit configuration
├── readme.md                  # Project readme
└── webpack.mix.js             # Laravel Mix configuration
```

---

## 2. Alternative Structure (Plain PHP)

If not using Laravel, here's an alternative folder structure:

```
D-WarungS/
├── admin/                      # Admin panel
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   ├── includes/
│   │   ├── header.php
│   │   ├── sidebar.php
│   │   ├── footer.php
│   │   └── db.php
│   ├── pages/
│   │   ├── dashboard.php
│   │   ├── users/
│   │   ├── vendors/
│   │   ├── orders/
│   │   ├── products/
│   │   └── settings/
│   └── index.php
│
├── vendor/                     # Vendor panel
│   ├── assets/
│   ├── includes/
│   ├── pages/
│   │   ├── dashboard.php
│   │   ├── orders/
│   │   ├── menu/
│   │   └── profile/
│   └── index.php
│
├── public/                     # Public web root
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   ├── uploads/
│   │   ├── products/
│   │   └── vendors/
│   ├── index.php             # Main entry point
│   └── .htaccess
│
├── src/                        # Application source
│   ├── Config/
│   │   ├── database.php
│   │   └── app.php
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── AuthController.php
│   │   ├── VendorController.php
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   └── OrderController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Vendor.php
│   │   ├── Product.php
│   │   └── Order.php
│   ├── Views/
│   │   ├── layouts/
│   │   ├── home/
│   │   ├── auth/
│   │   ├── vendors/
│   │   ├── products/
│   │   ├── cart/
│   │   └── orders/
│   ├── Helpers/
│   │   ├── auth.php
│   │   ├── session.php
│   │   └── validator.php
│   └── Routes/
│       └── router.php
│
├── database/
│   ├── schema.sql
│   └── seed.sql
│
└── logs/
    └── app.log
```

---

## 3. Key Directories Explained

### 3.1 `app/` Directory
Contains all application logic, models, controllers, and business rules.

### 3.2 `public/` Directory
The web root - accessible via browser. Contains entry point and public assets.

### 3.3 `resources/` Directory
Contains uncompiled assets (SCSS, JS) and view templates (Blade).

### 3.4 `storage/` Directory
Application storage for logs, cached views, sessions, and file uploads.

### 3.5 `database/` Directory
Migrations, seeders, and factories for database setup and testing.

---

## 4. File Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Controllers | PascalCase + Controller | `UserController.php` |
| Models | PascalCase (Singular) | `User.php` |
| Middleware | PascalCase | `Authenticate.php` |
| Views | kebab-case | `user-profile.blade.php` |
| Migrations | timestamp_description | `2024_01_01_000001_create_users_table.php` |
| Controllers (Admin) | PascalCase | `Admin/UserController.php` |
| CSS Classes | kebab-case | `.btn-primary` |
| JavaScript Functions | camelCase | `calculateTotal()` |

---

## 5. Git Workflow Structure

```
feature/
├── feature/user-authentication
├── feature/vendor-dashboard
├── feature/shopping-cart
├── feature/order-tracking
├── feature/payment-integration
├── feature/admin-panel
└── feature/api-endpoints

bugfix/
├── bugfix/login-redirect
├── bugfix/cart-quantity
└── bugfix/order-status

hotfix/
└── hotfix/security-patch

release/
└── release/v1.0.0
```

---

*Document Version: 1.0*
*Folder Structure for D-WarungS*

