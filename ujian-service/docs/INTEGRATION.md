# Integrasi SIAKAD ↔ Ujian Service

Dokumentasi lengkap integrasi antara SIAKAD utama dan Ujian Service microservice.

## Arsitektur Integrasi

```
┌─────────────────────────────────────────────────────────────────┐
│                         SIAKAD UTAMA                            │
│  - Database: MySQL (siswa, kelas, mapel, guru, admin)          │
│  - Auth: Laravel Sanctum                                        │
│  - Port: 8000                                                   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ API Endpoints
                              │ JWT Token Handoff
                              │ Siswa Sync
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                       UJIAN SERVICE                             │
│  - Database: MySQL (siswa local, ujian, soal, sesi)            │
│  - Auth: JWT (admin/guru), Session (siswa)                     │
│  - WebSocket: Laravel Reverb (proktor monitoring)              │
│  - Port: 8001                                                   │
└─────────────────────────────────────────────────────────────────┘
```

## Mekanisme Autentikasi

### 1. Admin/Guru (JWT Token Handoff)

Admin dan guru login di SIAKAD utama, kemudian mendapat JWT token untuk akses ke Ujian Service.

**Flow:**
1. Admin/Guru login di SIAKAD (Sanctum)
2. Klik menu "Ujian Service" di SIAKAD
3. SIAKAD generate JWT token via `/api/jwt/generate`
4. Redirect ke Ujian Service dengan token: `/auth/handoff?token=xxx`
5. Ujian Service verify token ke SIAKAD via `/api/jwt/verify`
6. Token disimpan di session Ujian Service
7. Admin/Guru dapat akses semua fitur Ujian Service

**Endpoint SIAKAD:**
```
POST /api/jwt/generate
Authorization: Bearer {sanctum_token}

Response:
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "username": "admin",
      "nama": "Administrator",
      "role": "admin"
    }
  }
}
```

**Endpoint Ujian Service:**
```
GET /auth/handoff?token={jwt_token}

Redirect to: /dashboard
Session stored: jwt_token, user_id, user_role, user_nama
```

### 2. Siswa (Credential Verification)

Siswa login langsung di Ujian Service, credentials diverifikasi ke SIAKAD.

**Flow:**
1. Siswa buka Ujian Service `/siswa/login`
2. Input username & password
3. Ujian Service verify ke SIAKAD via `/api/ujian-service/verify-credentials`
4. SIAKAD cek credentials di database
5. Return data siswa jika valid
6. Ujian Service create/update local siswa record
7. Generate JWT token untuk siswa
8. Token disimpan di session

**Endpoint SIAKAD:**
```
POST /api/ujian-service/verify-credentials
Content-Type: application/json

{
  "username": "2024001",
  "password": "password123"
}

Response:
{
  "success": true,
  "data": {
    "siswa_id": 123,
    "nis": "2024001",
    "nisn": "0012345678",
    "nama_lengkap": "Ahmad Fauzi",
    "kelas_id": 5,
    "kelas_nama": "XII RPL 1",
    "tingkat": 12,
    "user_id": 456,
    "username": "2024001",
    "email": "ahmad@example.com"
  }
}
```

## Sinkronisasi Data Siswa

Ujian Service memiliki tabel `siswa` lokal yang disinkronkan dari SIAKAD.

### Manual Sync via Command

```bash
cd ujian-service
php artisan siakad:sync-siswa

# Sync specific kelas only
php artisan siakad:sync-siswa --kelas=1 --kelas=2

# Force full sync
php artisan siakad:sync-siswa --force
```

### Automatic Sync via Scheduler

Tambahkan di `ujian-service/app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Sync siswa every hour
    $schedule->command('siakad:sync-siswa')
        ->hourly()
        ->withoutOverlapping();
}
```

### Sync Endpoint

**SIAKAD Endpoint:**
```
POST /api/ujian-service/sync/siswa
Content-Type: application/json

{
  "last_sync": "2024-01-15T10:00:00Z",  // optional
  "kelas_ids": [1, 2, 3]                // optional
}

Response:
{
  "success": true,
  "data": [
    {
      "id": 123,
      "nis": "2024001",
      "nama_lengkap": "Ahmad Fauzi",
      "kelas_id": 5,
      "kelas_nama": "XII RPL 1",
      "tingkat": 12,
      "username": "2024001",
      "email": "ahmad@example.com",
      "status": "aktif",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ],
  "count": 150,
  "sync_time": "2024-01-15T11:00:00Z"
}
```

## Reference Data API

Ujian Service mengambil data referensi (kelas, mapel) dari SIAKAD saat membuat ujian.

### Get Kelas List

```
GET /api/ujian-service/kelas
Authorization: Bearer {jwt_token}

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nama": "X RPL 1",
      "jurusan": "Rekayasa Perangkat Lunak",
      "tingkat": 10,
      "nama_lengkap": "10 X RPL 1 (Rekayasa Perangkat Lunak)"
    }
  ]
}
```

### Get Mapel List

```
GET /api/ujian-service/mapel
Authorization: Bearer {jwt_token}

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nama": "Matematika",
      "kode": "MAT",
      "kategori": "umum"
    }
  ]
}
```

### Get Siswa by Kelas

```
GET /api/ujian-service/siswa/by-kelas?kelas_id=1
Authorization: Bearer {jwt_token}

Response:
{
  "success": true,
  "data": [
    {
      "id": 123,
      "nis": "2024001",
      "nisn": "0012345678",
      "nama": "Ahmad Fauzi",
      "username": "2024001",
      "email": "ahmad@example.com",
      "user_id": 456
    }
  ]
}
```

## Konfigurasi

### SIAKAD (.env)

```env
# JWT Configuration
JWT_SECRET=your-secret-key-here
JWT_TTL=60  # minutes

# Ujian Service URL
UJIAN_SERVICE_URL=http://localhost:8001
```

### Ujian Service (.env)

```env
# SIAKAD Integration
SIAKAD_BASE_URL=http://localhost:8000
SIAKAD_SERVICE_KEY=your-service-key-here
SIAKAD_SYNC_ENABLED=true
SIAKAD_SYNC_INTERVAL=3600  # seconds

# JWT Configuration
JWT_SECRET=your-secret-key-here  # MUST be same as SIAKAD
JWT_TTL=60  # minutes

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ujian_service
DB_USERNAME=root
DB_PASSWORD=

# Laravel Reverb (WebSocket)
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

## Setup & Installation

### 1. Install Dependencies

**SIAKAD:**
```bash
cd /path/to/siakad
composer require tymon/jwt-auth
php artisan jwt:secret
```

**Ujian Service:**
```bash
cd /path/to/ujian-service
composer install
npm install
php artisan key:generate
php artisan jwt:secret
```

### 2. Database Migration

**Ujian Service:**
```bash
php artisan migrate
```

### 3. Initial Sync

```bash
cd ujian-service
php artisan siakad:sync-siswa
```

### 4. Start Services

**SIAKAD:**
```bash
php artisan serve --port=8000
```

**Ujian Service:**
```bash
php artisan serve --port=8001
php artisan reverb:start  # WebSocket server
```

## Testing Integration

### 1. Test Health Check

```bash
curl http://localhost:8000/api/ujian-service/health
```

### 2. Test JWT Generation (Admin)

```bash
# Login to SIAKAD first, get Sanctum token
curl -X POST http://localhost:8000/api/jwt/generate \
  -H "Authorization: Bearer {sanctum_token}"
```

### 3. Test Siswa Verification

```bash
curl -X POST http://localhost:8000/api/ujian-service/verify-credentials \
  -H "Content-Type: application/json" \
  -d '{"username":"2024001","password":"password123"}'
```

### 4. Test Sync

```bash
curl -X POST http://localhost:8000/api/ujian-service/sync/siswa \
  -H "Content-Type: application/json" \
  -d '{}'
```

## Security Considerations

1. **JWT Secret**: Harus sama di SIAKAD dan Ujian Service
2. **HTTPS**: Gunakan HTTPS di production untuk enkripsi token
3. **Token Expiry**: Set TTL sesuai kebutuhan (default 60 menit)
4. **CORS**: Configure CORS jika domain berbeda
5. **Rate Limiting**: Implement rate limiting untuk API endpoints
6. **Service Key**: Gunakan service key untuk endpoint sync

## Troubleshooting

### Token Invalid

- Pastikan JWT_SECRET sama di kedua aplikasi
- Cek token expiry
- Verify token format (Bearer token)

### Sync Failed

- Cek koneksi network antara SIAKAD dan Ujian Service
- Verify SIAKAD_BASE_URL di config
- Cek log: `storage/logs/laravel.log`

### Siswa Cannot Login

- Verify credentials di SIAKAD database
- Cek status siswa (harus 'aktif')
- Test verify-credentials endpoint manually

### WebSocket Connection Failed

- Pastikan Reverb server running
- Cek REVERB_* config di .env
- Verify port tidak digunakan aplikasi lain

## Monitoring

### Log Files

**SIAKAD:**
- `storage/logs/laravel.log` - General logs
- JWT handoff logs dengan tag: `JWT handoff`

**Ujian Service:**
- `storage/logs/laravel.log` - General logs
- Sync logs dengan tag: `SIAKAD sync`
- Auth logs dengan tag: `Siswa auth`

### Health Check Endpoints

```bash
# SIAKAD
curl http://localhost:8000/api/health

# Ujian Service
curl http://localhost:8001/api/health

# SIAKAD Integration
curl http://localhost:8000/api/ujian-service/health
```

## API Reference Summary

### SIAKAD Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/ujian-service/health` | None | Health check |
| GET | `/api/ujian-service/kelas` | JWT | Get kelas list |
| GET | `/api/ujian-service/mapel` | JWT | Get mapel list |
| GET | `/api/ujian-service/siswa/by-kelas` | JWT | Get siswa by kelas |
| GET | `/api/ujian-service/siswa/{id}` | JWT | Get siswa detail |
| POST | `/api/ujian-service/sync/siswa` | Service Key | Sync siswa data |
| POST | `/api/ujian-service/verify-credentials` | None | Verify siswa login |
| POST | `/api/jwt/generate` | Sanctum | Generate JWT token |
| POST | `/api/jwt/verify` | JWT | Verify JWT token |
| POST | `/api/jwt/refresh` | JWT | Refresh JWT token |

### Ujian Service Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/auth/handoff` | None | Receive JWT from SIAKAD |
| GET | `/siswa/login` | None | Siswa login form |
| POST | `/siswa/login` | None | Siswa login process |
| POST | `/siswa/logout` | Session | Siswa logout |
| GET | `/dashboard` | JWT | Admin/Guru dashboard |
| GET | `/siswa/dashboard` | Session | Siswa dashboard |

## Support

Untuk pertanyaan atau issue terkait integrasi, silakan buka issue di repository atau hubungi tim development.
