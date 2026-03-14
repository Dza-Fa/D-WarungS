# D-WarungS - O2O Food Court Platform

Modern Laravel 12 app for food court order management with roles: Customer, Vendor, Cashier, Admin.

## Features
- Multi-vendor cart/checkout (Midtrans payments)
- Role-based dashboards
- Order workflow (pending → confirmed → preparing → ready)
- Tailwind UI components
- reCAPTCHA, permissions

## Production Deployment

### 1. Server Setup (Ubuntu VPS e.g. DigitalOcean $6/mo)
```
apt update
apt install nginx mysql-server php8.2-fpm php8.2-mysql redis-server composer
ufw allow 'Nginx Full'; ufw allow ssh; ufw enable
```

### 2. Clone & Install
```
git clone <repo> /var/www/dwarungs
cd /var/www/dwarungs
composer install --optimize-autoloader --no-dev
npm ci && npm run build
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Environment
```
cp .env.example .env
# Edit: APP_DEBUG=false, DB_*, MIDTRANS_MERCHANT_ID, REDIS_URL, etc.
php artisan key:generate
php artisan migrate --seed
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Nginx Config (/etc/nginx/sites-available/dwarungs)
```
server {
    listen 80; server_name yourdomain.com;
    root /var/www/dwarungs/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }
}
ln -s /etc/nginx/sites-available/dwarungs /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```

### 5. Queues & SSL
```
# Supervisor for queues
apt install supervisor
# Config /etc/supervisor/conf.d/dwarungs.conf
[program:dwarungs]
command=php /var/www/dwarungs/artisan queue:work --sleep=3 --tries=3
numprocs=1
autostart=true
supervisorctl reread; supervisorctl update; supervisorctl start dwarungs

# SSL
certbot --nginx -d yourdomain.com
```

### 6. Test
```
php artisan test
curl https://yourdomain.com/up  # Health check
```

Ready for production! 🚀
