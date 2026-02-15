# Deployment Guide

## Overview
This guide covers deploying the ERP system to production environments, including Docker setup, environment configuration, and operational considerations.

## Deployment Architecture

### Production Stack
- **Application**: Laravel 11 + PHP 8.2
- **Web Server**: Nginx
- **Database**: PostgreSQL 16
- **Cache/Queue**: Redis 7
- **Container**: Docker + Docker Compose
- **Reverse Proxy**: Nginx/Cloudflare (optional)

### Environment Types
- **Development**: Local development with volumes
- **Staging**: Production-like environment for testing
- **Production**: Live environment with optimizations

## Docker Deployment

### Production Docker Compose

#### docker-compose.prod.yml
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
      target: production
    container_name: erp_app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./storage/app:/var/www/html/storage/app
      - ./storage/logs:/var/www/html/storage/logs
    networks:
      - erp_network
    depends_on:
      - postgres
      - redis
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - REDIS_HOST=redis
      - REDIS_PORT=6379
      - CACHE_DRIVER=redis
      - SESSION_DRIVER=redis
      - QUEUE_CONNECTION=redis

  nginx:
    image: nginx:alpine
    container_name: erp_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/nginx/sites:/etc/nginx/sites-available
      - ./docker/nginx/ssl:/etc/nginx/ssl
      - ./storage/app/public:/var/www/html/storage/app/public
    networks:
      - erp_network
    depends_on:
      - app

  postgres:
    image: postgres:15-alpine
    container_name: erp_postgres
    restart: unless-stopped
    environment:
      - POSTGRES_DB=${DB_DATABASE}
      - POSTGRES_USER=${DB_USERNAME}
      - POSTGRES_PASSWORD=${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./docker/postgres/init.sql:/docker-entrypoint-initdb.d/init.sql
    networks:
      - erp_network
    ports:
      - "5432:5432"

  redis:
    image: redis:7-alpine
    container_name: erp_redis
    restart: unless-stopped
    command: redis-server --appendonly yes --requirepass ${REDIS_PASSWORD}
    volumes:
      - redis_data:/data
    networks:
      - erp_network
    ports:
      - "6379:6379"

  queue:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
      target: production
    container_name: erp_queue
    restart: unless-stopped
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    working_dir: /var/www/html
    volumes:
      - ./storage/app:/var/www/html/storage/app
      - ./storage/logs:/var/www/html/storage/logs
    networks:
      - erp_network
    depends_on:
      - postgres
      - redis
    environment:
      - APP_ENV=production
      - DB_HOST=postgres
      - REDIS_HOST=redis

  scheduler:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
      target: production
    container_name: erp_scheduler
    restart: unless-stopped
    command: sh -c "while true; do php artisan schedule:run; sleep 60; done"
    working_dir: /var/www/html
    volumes:
      - ./storage/app:/var/www/html/storage/app
      - ./storage/logs:/var/www/html/storage/logs
    networks:
      - erp_network
    depends_on:
      - postgres
      - redis
    environment:
      - APP_ENV=production
      - DB_HOST=postgres
      - REDIS_HOST=redis

volumes:
  postgres_data:
    driver: local
  redis_data:
    driver: local

networks:
  erp_network:
    driver: bridge
```

### Production Dockerfile

#### docker/php/Dockerfile
```dockerfile
# Multi-stage build for production
FROM php:8.2-fpm-alpine as base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    bcmath \
    xml \
    ctype \
    iconv \
    intl \
    opcache

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html/storage
RUN chmod -R 755 /var/www/html/bootstrap/cache

# Production stage
FROM base as production

# Copy optimized configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port
EXPOSE 9000

# Start services
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

## Environment Configuration

### Production Environment Variables

#### .env.production
```env
# Application
APP_NAME="ERP System"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=UTC

# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=erp_production
DB_USERNAME=erp_user
DB_PASSWORD=secure_database_password

# Cache
CACHE_DRIVER=redis
CACHE_PREFIX=erp_cache

# Session
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT_COOKIE=true
SESSION_PATH=/
SESSION_DOMAIN=.your-domain.com

# Queue
QUEUE_CONNECTION=redis
QUEUE_FAILED_DRIVER=database

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=secure_redis_password
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-mail-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# File Storage
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_USE_PATH_STYLE_ENDPOINT=false

# Logging
LOG_CHANNEL=stack
LOG_STACK=single,daily
LOG_LEVEL=warning

# Security
BCRYPT_ROUNDS=12
HASH_DRIVER=bcrypt

# Performance
OPCACHE_ENABLE=1
OPCACHE_MEMORY_CONSUMPTION=256
OPCACHE_MAX_ACCELERATED_FILES=10000

# Monitoring
TELESCOPE_ENABLED=false
SENTRY_LARAVEL_DSN=your_sentry_dsn

# SSL
FORCE_HTTPS=true
TRUSTED_PROxies=*
```

### Nginx Configuration

#### docker/nginx/nginx.conf
```nginx
user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log warn;
pid /var/run/nginx.pid;

events {
    worker_connections 1024;
    use epoll;
    multi_accept on;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    # Logging
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';
    access_log /var/log/nginx/access.log main;

    # Performance
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 100M;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml+rss
        application/atom+xml
        image/svg+xml;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Include site configurations
    include /etc/nginx/sites/*;
}
```

#### docker/nginx/sites/erp.conf
```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;

    # SSL configuration
    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    ssl_session_timeout 1d;
    ssl_session_cache shared:SSL:50m;
    ssl_session_tickets off;

    # Modern SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Root directory
    root /var/www/html/public;
    index index.php index.html;

    # Laravel specific
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Security
    location ~ /\.ht {
        deny all;
    }

    # Health check
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }
}
```

## Deployment Process

### 1. Server Preparation

#### System Requirements
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Create project directory
sudo mkdir -p /opt/erp
sudo chown $USER:$USER /opt/erp
```

### 2. Application Deployment

#### Deployment Script
```bash
#!/bin/bash
# deploy.sh

set -e

echo "🚀 Starting ERP deployment..."

# Variables
PROJECT_DIR="/opt/erp"
BACKUP_DIR="/opt/erp/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Create backup
echo "📦 Creating backup..."
mkdir -p $BACKUP_DIR
docker-compose -f docker-compose.prod.yml exec postgres pg_dump -U erp_user erp_production > $BACKUP_DIR/backup_$TIMESTAMP.sql

# Pull latest code
echo "📥 Pulling latest code..."
cd $PROJECT_DIR
git pull origin main

# Build and deploy
echo "🔨 Building and deploying..."
docker-compose -f docker-compose.prod.yml build --no-cache
docker-compose -f docker-compose.prod.yml up -d

# Run migrations
echo "🗄️ Running migrations..."
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
docker-compose -f docker-compose.prod.yml exec app php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec app php artisan config:clear
docker-compose -f docker-compose.prod.yml exec app php artisan route:clear
docker-compose -f docker-compose.prod.yml exec app php artisan view:clear

# Optimize for production
echo "⚡ Optimizing..."
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache

# Health check
echo "🏥 Health check..."
sleep 30
if curl -f http://localhost/health; then
    echo "✅ Deployment successful!"
else
    echo "❌ Health check failed. Rolling back..."
    # Implement rollback logic here
    exit 1
fi

echo "🎉 Deployment completed successfully!"
```

### 3. SSL Certificate Setup

#### Let's Encrypt with Certbot
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

#### Manual SSL Setup
```bash
# Create SSL directory
mkdir -p docker/nginx/ssl

# Generate self-signed certificate (for testing)
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout docker/nginx/ssl/key.pem \
    -out docker/nginx/ssl/cert.pem \
    -subj "/C=US/ST=State/L=City/O=Company/CN=your-domain.com"
```

## Monitoring and Maintenance

### 1. Health Monitoring

#### Health Check Endpoint
```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version'),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'cache' => Cache::ping() ? 'connected' : 'disconnected',
    ]);
});
```

#### Monitoring Script
```bash
#!/bin/bash
# monitor.sh

# Check container status
docker-compose -f docker-compose.prod.yml ps

# Check resource usage
docker stats --no-stream

# Check logs
docker-compose -f docker-compose.prod.yml logs --tail=100 app

# Database health
docker-compose -f docker-compose.prod.yml exec postgres pg_isready

# Redis health
docker-compose -f docker-compose.prod.yml exec redis redis-cli ping
```

### 2. Backup Strategy

#### Automated Backup Script
```bash
#!/bin/bash
# backup.sh

BACKUP_DIR="/opt/erp/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
echo "📦 Backing up database..."
docker-compose -f docker-compose.prod.yml exec postgres pg_dump -U erp_user erp_production > $BACKUP_DIR/db_backup_$TIMESTAMP.sql

# File backup
echo "📁 Backing up files..."
tar -czf $BACKUP_DIR/files_backup_$TIMESTAMP.tar.gz storage/app/

# Clean old backups
echo "🧹 Cleaning old backups..."
find $BACKUP_DIR -name "*.sql" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete

echo "✅ Backup completed: $TIMESTAMP"
```

#### Cron Job for Backups
```bash
# Add to crontab
0 2 * * * /opt/erp/scripts/backup.sh >> /opt/erp/logs/backup.log 2>&1
```

### 3. Log Management

#### Log Rotation
```bash
# /etc/logrotate.d/erp
/opt/erp/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        docker-compose -f /opt/erp/docker-compose.prod.yml restart app
    endscript
}
```

## Performance Optimization

### 1. PHP Optimization

#### OPcache Configuration
```ini
; docker/php/opcache.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.enable_cli=1
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.load_comments=1
```

### 2. Database Optimization

#### PostgreSQL Configuration
```sql
-- docker/postgres/postgresql.conf
shared_buffers = 256MB
effective_cache_size = 1GB
maintenance_work_mem = 64MB
checkpoint_completion_target = 0.9
wal_buffers = 16MB
default_statistics_target = 100
random_page_cost = 1.1
effective_io_concurrency = 200
```

### 3. Redis Optimization

#### Redis Configuration
```conf
# docker/redis/redis.conf
maxmemory 512mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

## Security Hardening

### 1. Container Security

#### Docker Security
```bash
# Run containers as non-root
# Use read-only filesystems where possible
# Limit container capabilities
# Use secrets for sensitive data
```

### 2. Network Security

#### Firewall Rules
```bash
# UFW configuration
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw deny 5432/tcp   # PostgreSQL (internal only)
sudo ufw deny 6379/tcp   # Redis (internal only)
sudo ufw enable
```

### 3. Application Security

#### Security Headers
```php
// app/Http/Middleware/SecurityHeaders.php
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    
    return $response;
}
```

## Troubleshooting

### Common Issues

#### 1. Container Won't Start
```bash
# Check logs
docker-compose -f docker-compose.prod.yml logs app

# Check configuration
docker-compose -f docker-compose.prod.yml config

# Check resources
docker system df
docker system prune
```

#### 2. Database Connection Issues
```bash
# Test database connection
docker-compose -f docker-compose.prod.yml exec app php artisan tinker
>>> DB::connection()->getPdo()

# Check database logs
docker-compose -f docker-compose.prod.yml logs postgres
```

#### 3. Performance Issues
```bash
# Check resource usage
docker stats

# Check slow queries
docker-compose -f docker-compose.prod.yml exec postgres psql -U erp_user -d erp_production -c "SELECT * FROM pg_stat_statements ORDER BY mean_time DESC LIMIT 10;"
```

This comprehensive deployment guide ensures your ERP system can be deployed reliably and maintained effectively in production environments.
