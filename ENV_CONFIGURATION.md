# Environment Configuration Guide

## SIAKAD Main Application (.env)

Add these lines to your `/home/roy/siakad-pehape-natip/.env` file:

```env
# Application URL
APP_URL=http://0.0.0.0:8412

# JWT Configuration (for ujian-service integration)
JWT_SECRET=base64:YOUR_GENERATED_SECRET_HERE
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_ALGO=HS256

# Ujian Service Integration
UJIAN_SERVICE_URL=http://0.0.0.0:8413
UJIAN_SERVICE_KEY=your-secure-service-key-here
```

### Generate JWT Secret

Run this command in SIAKAD directory:
```bash
cd /home/roy/siakad-pehape-natip
php artisan jwt:secret
```

This will automatically update your .env file with JWT_SECRET.

---

## Ujian Service (.env)

Create/update `/home/roy/siakad-pehape-natip/ujian-service/.env` file:

```env
APP_NAME="Ujian Service"
APP_ENV=local
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://0.0.0.0:8413

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=debug

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ujian_service
DB_USERNAME=root
DB_PASSWORD=

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database

# Queue
QUEUE_CONNECTION=database

# JWT Configuration (MUST be same as SIAKAD)
JWT_SECRET=base64:YOUR_GENERATED_SECRET_HERE
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_ALGO=HS256

# SIAKAD Integration
SIAKAD_BASE_URL=http://0.0.0.0:8412
SIAKAD_SERVICE_KEY=your-secure-service-key-here
SIAKAD_SYNC_ENABLED=true
SIAKAD_SYNC_INTERVAL=3600

# Laravel Reverb (WebSocket for Proktor Monitoring)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=ujian-app
REVERB_APP_KEY=ujian-key
REVERB_APP_SECRET=ujian-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http

# Vite
VITE_APP_NAME="${APP_NAME}"
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Setup Ujian Service

Run these commands:
```bash
cd /home/roy/siakad-pehape-natip/ujian-service

# Copy .env.example if .env doesn't exist
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate JWT secret (IMPORTANT: Copy JWT_SECRET from SIAKAD .env)
php artisan jwt:secret

# Create database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ujian_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Sync siswa data from SIAKAD
php artisan siakad:sync-siswa
```

---

## Quick Setup Script

Save this as `setup-env.sh` and run it:

```bash
#!/bin/bash

echo "=== Setting up SIAKAD ==="
cd /home/roy/siakad-pehape-natip

# Generate JWT secret if not exists
if ! grep -q "JWT_SECRET" .env; then
    php artisan jwt:secret
fi

# Update APP_URL
sed -i 's|APP_URL=.*|APP_URL=http://0.0.0.0:8412|' .env

# Add ujian service config if not exists
if ! grep -q "UJIAN_SERVICE_URL" .env; then
    echo "" >> .env
    echo "# Ujian Service Integration" >> .env
    echo "UJIAN_SERVICE_URL=http://0.0.0.0:8413" >> .env
    echo "UJIAN_SERVICE_KEY=your-secure-service-key-here" >> .env
fi

echo "=== Setting up Ujian Service ==="
cd /home/roy/siakad-pehape-natip/ujian-service

# Copy .env if not exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate keys
php artisan key:generate
php artisan jwt:secret

# Update APP_URL
sed -i 's|APP_URL=.*|APP_URL=http://0.0.0.0:8413|' .env

# Update SIAKAD_BASE_URL
sed -i 's|SIAKAD_BASE_URL=.*|SIAKAD_BASE_URL=http://0.0.0.0:8412|' .env

# Get JWT_SECRET from SIAKAD
SIAKAD_JWT=$(grep "JWT_SECRET=" /home/roy/siakad-pehape-natip/.env | cut -d '=' -f2)
sed -i "s|JWT_SECRET=.*|JWT_SECRET=$SIAKAD_JWT|" .env

echo "=== Setup Complete ==="
echo "Please verify the configuration and run:"
echo "cd /home/roy/siakad-pehape-natip/ujian-service"
echo "php artisan migrate"
echo "php artisan siakad:sync-siswa"
```

---

## Important Notes

1. **JWT_SECRET MUST BE IDENTICAL** in both SIAKAD and Ujian Service
2. **SIAKAD_SERVICE_KEY** should be a secure random string
3. Create `ujian_service` database before running migrations
4. Run `php artisan siakad:sync-siswa` to sync student data
5. Start Reverb server for WebSocket: `php artisan reverb:start`

## Verification

Test the configuration:

```bash
# Test SIAKAD health
curl http://0.0.0.0:8412/api/health

# Test Ujian Service health
curl http://0.0.0.0:8413/api/health

# Test SIAKAD integration endpoint
curl http://0.0.0.0:8412/api/ujian-service/health
```
