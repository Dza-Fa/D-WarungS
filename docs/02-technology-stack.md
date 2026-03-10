# Technology Stack Specification - D-WarungS

## 1. Core Technology Stack

### 1.1 Programming Language
| Component | Technology | Version | Notes |
|-----------|-------------|---------|-------|
| Backend | PHP | 8.1+ | Latest stable recommended |
| Frontend | HTML5, CSS3, JavaScript | - | Modern standards |

### 1.2 Database
| Component | Technology | Version | Notes |
|-----------|-------------|---------|-------|
| Database Server | MySQL | 8.0+ | Via XAMPP |
| Database Client | phpMyAdmin | Latest | Included with XAMPP |

### 1.3 Web Server
| Component | Technology | Version | Notes |
|-----------|-------------|---------|-------|
| HTTP Server | Apache | 2.4+ | Via XAMPP |
| Local Dev Environment | XAMPP | 8.2+ | PHP 8.1+ included |

---

## 2. Framework & Libraries

### 2.1 PHP Framework
| Library | Version | Purpose |
|---------|---------|---------|
| Laravel | 10.x or 11.x | PHP Framework (recommended) |
| **Alternative:** Plain PHP | - | Native PHP (if not using framework) |

> **Recommendation:** Using Laravel will significantly speed up development and provide built-in security, authentication, and database management.

### 2.2 Frontend Libraries
| Library | Version | Purpose |
|---------|---------|---------|
| Bootstrap | 5.3+ | CSS Framework for responsive design |
| jQuery | 3.7+ | DOM manipulation and AJAX |
| DataTables | 2.0+ | Data tables for admin panels |
| SweetAlert2 | 11+ | Beautiful alert dialogs |
| Font Awesome | 6.5+ | Icons |

### 2.3 Development Tools
| Tool | Purpose |
|------|---------|
| Composer | PHP dependency manager |
| npm | JavaScript package manager |
| Git | Version control |
| VS Code | Code editor |

---

## 3. Project Dependencies

### 3.1 Composer Dependencies (if using Laravel)
```json
{
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0",
        "laravel/breeze": "^1.0",
        "barryvdh/laravel-debugbar": "^3.8"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "fakerphp/faker": "^1.9.1"
    }
}
```

### 3.2 NPM Dependencies (Frontend)
```json
{
    "dependencies": {
        "bootstrap": "^5.3.0",
        "jquery": "^3.7.0",
        "datatables.net": "^2.0.0",
        "sweetalert2": "^11.0.0",
        "@fortawesome/fontawesome-free": "^6.5.0"
    }
}
```

---

## 4. Environment Requirements

### 4.1 Development Environment
| Requirement | Specification |
|-------------|---------------|
| Operating System | Windows 11 (XAMPP) |
| PHP Version | 8.1 or higher |
| MySQL Version | 8.0 or higher |
| Apache Version | 2.4 or higher |
| Memory Limit | 512MB minimum |
| Upload Max Size | 64MB |
| Post Max Size | 64MB |

### 4.2 XAMPP Configuration (php.ini)
```ini
; Memory
memory_limit = 512M

; Upload
upload_max_filesize = 64M
post_max_size = 64M

; Timezone
date.timezone = Asia/Jakarta

; Extensions
extension=mysqli
extension=mbstring
extension=curl
extension=json
extension=zip
```

---

## 5. Database Configuration

### 5.1 MySQL Settings (my.ini)
```ini
[mysqld]
# Character Set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# InnoDB Settings
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M

# Query Cache (MySQL 8.0+ removed this, but for reference)
# query_cache_type = 0
```

### 5.2 Database Connection Parameters
| Parameter | Default Value |
|-----------|---------------|
| Host | localhost |
| Port | 3306 |
| Database | d_warung_s |
| Username | root |
| Password | (empty - XAMPP default) |

---

## 6. Project Structure

### 6.1 Recommended Folder Structure
```
D-WarungS/
├── app/                    # Application source code
│   ├── Http/
│   │   ├── Controllers/    # Request handlers
│   │   ├── Middleware/     # Request filters
│   │   └── Requests/       # Form validation
│   ├── Models/             # Database models
│   ├── Providers/          # Service providers
│   └── Helpers/            # Utility functions
├── config/                 # Configuration files
├── database/
│   ├── migrations/         # Database migrations
│   ├── seeders/           # Test data
│   └── factories/         # Model factories
├── public/                 # Web root (htdocs)
│   ├── assets/            # CSS, JS, Images
│   └── index.php          # Entry point
├── resources/
│   ├── views/             # Blade templates
│   ├── lang/              # Localization
│   └── assets/            # Frontend source
├── routes/                # Route definitions
├── storage/                # Logs, caches, uploads
├── tests/                 # Unit tests
├── vendor/                # Composer packages
├── .env                   # Environment variables
├── artisan                # CLI commands
├── composer.json          # Dependencies
└── package.json           # NPM dependencies
```

---

## 7. External Services (Optional)

### 7.1 Payment Gateway (Future)
| Service | Purpose |
|---------|---------|
| Midtrans | Indonesian payment gateway |
| Xendit | Alternative payment API |
| Doku | E-payment solutions |

### 7.2 Email Service (Future)
| Service | Purpose |
|---------|---------|
| Mailgun | Transactional emails |
| SendGrid | Email delivery |
| SMTP (Gmail) | Simple email relay |

### 7.3 SMS Service (Future)
| Service | Purpose |
|---------|---------|
| Twilio | SMS notifications |
| Nexmo | SMS API |

---

## 8. Version Control

### 8.1 Git Configuration
| Setting | Value |
|---------|-------|
| Branch Strategy | GitFlow |
| Main Branch | main |
| Development Branch | develop |
| Feature Prefix | feature/ |
| Bugfix Prefix | bugfix/ |

### 8.2 .gitignore (Recommended)
```
/vendor/
/node_modules/
/.env
/.env.*
/storage/*.key
/storage/logs/*
/storage/framework/cache/*
/storage/framework/sessions/*
/storage/framework/views/*
.DS_Store
Thumbs.db
*.log
```

---

## 9. Security Considerations

### 9.1 Required PHP Extensions
- `mysqli` - MySQL database
- `mbstring` - Multibyte string
- `curl` - HTTP requests
- `json` - JSON handling
- `zip` - File compression
- `openssl` - Encryption
- `tokenizer` - Laravel

### 9.2 Security Best Practices
1. Keep PHP and all dependencies updated
2. Use environment variables for sensitive data
3. Implement CSRF protection
4. Use prepared statements for SQL queries
5. Hash passwords with bcrypt/argon2
6. Sanitize all user inputs
7. Use HTTPS in production

---

*Document Version: 1.0*
*Technology Stack Specification for D-WarungS*

