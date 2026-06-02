#!/bin/bash

set -e

echo "=========================================="
echo "SIAKAD ↔ Ujian Service Integration Setup"
echo "=========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

SIAKAD_DIR="/home/roy/siakad-pehape-natip"
UJIAN_DIR="/home/roy/siakad-pehape-natip/ujian-service"

# Check if running from correct directory
if [ ! -d "$SIAKAD_DIR" ]; then
    echo -e "${RED}Error: SIAKAD directory not found at $SIAKAD_DIR${NC}"
    exit 1
fi

echo -e "${GREEN}Step 1: Configuring SIAKAD${NC}"
cd "$SIAKAD_DIR"

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}Creating .env from .env.example${NC}"
    cp .env.example .env
    php artisan key:generate
fi

# Generate JWT secret if not exists
if ! grep -q "^JWT_SECRET=" .env; then
    echo -e "${YELLOW}Generating JWT secret for SIAKAD${NC}"
    JWT_SECRET="base64:$(openssl rand -base64 32)"
    echo "" >> .env
    echo "# JWT Configuration" >> .env
    echo "JWT_SECRET=$JWT_SECRET" >> .env
    echo "JWT_TTL=60" >> .env
    echo "JWT_REFRESH_TTL=20160" >> .env
    echo "JWT_ALGO=HS256" >> .env
else
    echo -e "${GREEN}JWT secret already exists${NC}"
fi

# Update APP_URL
echo -e "${YELLOW}Updating SIAKAD APP_URL${NC}"
sed -i 's|^APP_URL=.*|APP_URL=http://0.0.0.0:8412|' .env

# Add Ujian Service config if not exists
if ! grep -q "^UJIAN_SERVICE_URL=" .env; then
    echo -e "${YELLOW}Adding Ujian Service configuration${NC}"
    SERVICE_KEY=$(openssl rand -hex 32)
    echo "" >> .env
    echo "# Ujian Service Integration" >> .env
    echo "UJIAN_SERVICE_URL=http://0.0.0.0:8413" >> .env
    echo "UJIAN_SERVICE_KEY=$SERVICE_KEY" >> .env
else
    echo -e "${GREEN}Ujian Service config already exists${NC}"
    sed -i 's|^UJIAN_SERVICE_URL=.*|UJIAN_SERVICE_URL=http://0.0.0.0:8413|' .env
fi

# Get JWT_SECRET and SERVICE_KEY for ujian-service
SIAKAD_JWT_SECRET=$(grep "^JWT_SECRET=" .env | cut -d '=' -f2-)
SIAKAD_SERVICE_KEY=$(grep "^UJIAN_SERVICE_KEY=" .env | cut -d '=' -f2-)

echo ""
echo -e "${GREEN}Step 2: Configuring Ujian Service${NC}"
cd "$UJIAN_DIR"

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}Creating .env from .env.example${NC}"
    cp .env.example .env
    php artisan key:generate
fi

# Update APP_URL
echo -e "${YELLOW}Updating Ujian Service APP_URL${NC}"
sed -i 's|^APP_URL=.*|APP_URL=http://0.0.0.0:8413|' .env

# Update APP_NAME
sed -i 's|^APP_NAME=.*|APP_NAME="Ujian Service"|' .env

# Update locale
sed -i 's|^APP_LOCALE=.*|APP_LOCALE=id|' .env
sed -i 's|^APP_FALLBACK_LOCALE=.*|APP_FALLBACK_LOCALE=id|' .env
sed -i 's|^APP_FAKER_LOCALE=.*|APP_FAKER_LOCALE=id_ID|' .env
sed -i 's|^APP_TIMEZONE=.*|APP_TIMEZONE=Asia/Jakarta|' .env

# Update database config
echo -e "${YELLOW}Updating database configuration${NC}"
sed -i 's|^DB_CONNECTION=.*|DB_CONNECTION=mysql|' .env
sed -i 's|^DB_DATABASE=.*|DB_DATABASE=ujian_service|' .env

# Set JWT_SECRET from SIAKAD
echo -e "${YELLOW}Copying JWT_SECRET from SIAKAD${NC}"
if grep -q "^JWT_SECRET=" .env; then
    sed -i "s|^JWT_SECRET=.*|JWT_SECRET=$SIAKAD_JWT_SECRET|" .env
else
    echo "" >> .env
    echo "# JWT Configuration (from SIAKAD)" >> .env
    echo "JWT_SECRET=$SIAKAD_JWT_SECRET" >> .env
    echo "JWT_TTL=60" >> .env
    echo "JWT_REFRESH_TTL=20160" >> .env
    echo "JWT_ALGO=HS256" >> .env
fi

# Add SIAKAD integration config
if ! grep -q "^SIAKAD_BASE_URL=" .env; then
    echo -e "${YELLOW}Adding SIAKAD integration configuration${NC}"
    echo "" >> .env
    echo "# SIAKAD Integration" >> .env
    echo "SIAKAD_BASE_URL=http://0.0.0.0:8412" >> .env
    echo "SIAKAD_SERVICE_KEY=$SIAKAD_SERVICE_KEY" >> .env
    echo "SIAKAD_SYNC_ENABLED=true" >> .env
    echo "SIAKAD_SYNC_INTERVAL=3600" >> .env
else
    echo -e "${GREEN}SIAKAD integration config already exists${NC}"
    sed -i "s|^SIAKAD_BASE_URL=.*|SIAKAD_BASE_URL=http://0.0.0.0:8412|" .env
    sed -i "s|^SIAKAD_SERVICE_KEY=.*|SIAKAD_SERVICE_KEY=$SIAKAD_SERVICE_KEY|" .env
fi

# Add Reverb config if not exists
if ! grep -q "^BROADCAST_CONNECTION=" .env; then
    echo -e "${YELLOW}Adding Laravel Reverb configuration${NC}"
    echo "" >> .env
    echo "# Laravel Reverb (WebSocket)" >> .env
    echo "BROADCAST_CONNECTION=reverb" >> .env
    echo "REVERB_APP_ID=ujian-app" >> .env
    echo "REVERB_APP_KEY=ujian-key" >> .env
    echo "REVERB_APP_SECRET=ujian-secret" >> .env
    echo "REVERB_HOST=0.0.0.0" >> .env
    echo "REVERB_PORT=8080" >> .env
    echo "REVERB_SCHEME=http" >> .env
    echo "" >> .env
    echo "VITE_REVERB_APP_KEY=\"\${REVERB_APP_KEY}\"" >> .env
    echo "VITE_REVERB_HOST=\"\${REVERB_HOST}\"" >> .env
    echo "VITE_REVERB_PORT=\"\${REVERB_PORT}\"" >> .env
    echo "VITE_REVERB_SCHEME=\"\${REVERB_SCHEME}\"" >> .env
else
    echo -e "${GREEN}Reverb config already exists${NC}"
fi

echo ""
echo -e "${GREEN}Step 3: Database Setup${NC}"

# Check if database exists
DB_EXISTS=$(mysql -u root -e "SHOW DATABASES LIKE 'ujian_service';" 2>/dev/null | grep ujian_service || true)

if [ -z "$DB_EXISTS" ]; then
    echo -e "${YELLOW}Creating ujian_service database${NC}"
    mysql -u root -e "CREATE DATABASE ujian_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || {
        echo -e "${RED}Failed to create database. Please create it manually:${NC}"
        echo "mysql -u root -p -e \"CREATE DATABASE ujian_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\""
    }
else
    echo -e "${GREEN}Database already exists${NC}"
fi

# Run migrations
echo -e "${YELLOW}Running migrations${NC}"
cd "$UJIAN_DIR"
php artisan migrate --force 2>/dev/null || {
    echo -e "${YELLOW}Migrations may have failed. Run manually if needed:${NC}"
    echo "cd $UJIAN_DIR && php artisan migrate"
}

echo ""
echo -e "${GREEN}Step 4: Initial Data Sync${NC}"
echo -e "${YELLOW}Syncing siswa data from SIAKAD${NC}"
php artisan siakad:sync-siswa 2>/dev/null || {
    echo -e "${YELLOW}Sync command may have failed. Run manually after servers are ready:${NC}"
    echo "cd $UJIAN_DIR && php artisan siakad:sync-siswa"
}

echo ""
echo "=========================================="
echo -e "${GREEN}✅ Setup Complete!${NC}"
echo "=========================================="
echo ""
echo "Configuration Summary:"
echo "  JWT_SECRET: $(echo $SIAKAD_JWT_SECRET | cut -c1-20)..."
echo "  SERVICE_KEY: $(echo $SIAKAD_SERVICE_KEY | cut -c1-20)..."
echo ""
echo "Servers are already running:"
echo "  - SIAKAD: http://0.0.0.0:8412"
echo "  - Ujian Service: http://0.0.0.0:8413"
echo ""
echo "Next steps:"
echo "  1. Verify servers are running:"
echo "     curl http://0.0.0.0:8412/api/health"
echo "     curl http://0.0.0.0:8413/api/health"
echo ""
echo "  2. Start Reverb WebSocket server (optional):"
echo "     cd $UJIAN_DIR"
echo "     php artisan reverb:start"
echo ""
echo "  3. Sync siswa data (if not done automatically):"
echo "     cd $UJIAN_DIR"
echo "     php artisan siakad:sync-siswa"
echo ""
echo "  4. Test integration:"
echo "     - Login to SIAKAD: http://0.0.0.0:8412/login"
echo "     - Access Ujian Service menu"
echo "     - Test siswa login: http://0.0.0.0:8413/siswa/login"
echo ""
echo "View detailed configuration guide:"
echo "  cat $SIAKAD_DIR/ENV_CONFIGURATION.md"
echo ""