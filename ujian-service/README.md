# Ujian Online Microservice

Microservice terpisah untuk sistem ujian online yang terintegrasi dengan SIAKAD utama.

## Sprint 2 - Manajemen Periode & Fase ✅

### Fitur yang Sudah Diimplementasikan

#### 1. Database Schema
- ✅ Tabel `periode_ujian` - Manajemen periode ujian (UTS, UAS, dll)
- ✅ Tabel `fase_periode` - Fase-fase dalam periode (input_soal, try_out, pelaksanaan, remedial, rekapitulasi)
- ✅ Tabel `ujian`, `soal_ujian`, `sesi_ujian`, `jawaban_siswa` - Schema lengkap untuk ujian
- ✅ Tabel `bank_soal`, `media_soal` - Bank soal dan media
- ✅ Tabel `proktor_event` - Event monitoring proktor
- ✅ Tabel RBAC admin (admin_role, admin_permission, dll)

#### 2. Models
- ✅ `PeriodeUjian` - Model dengan relasi dan helper methods
- ✅ `FasePeriode` - Model dengan override management
- ✅ `Ujian`, `SoalUjian`, `SesiUjian` - Models untuk ujian
- ✅ `BankSoal`, `JawabanSiswa`, `ProktorEvent` - Supporting models

#### 3. Controllers
- ✅ `PeriodeUjianController` - CRUD periode + activate/deactivate
- ✅ `FasePeriodeController` - CRUD fase + override management

#### 4. Mesin Fase Otomatis
- ✅ `RefreshFaseAktifCommand` - Command untuk refresh fase aktif
- ✅ Scheduled job setiap menit via `routes/console.php`
- ✅ Auto-refresh berdasarkan tanggal dan override

#### 5. Override Manual Fase
- ✅ `paksaBuka()` - Paksa buka fase tertentu
- ✅ `paksaTutup()` - Paksa tutup fase tertentu
- ✅ `resetOverride()` - Reset ke otomatis
- ✅ Tracking siapa yang override dan kapan

#### 6. Middleware & Security
- ✅ `CheckFaseAktif` - Gating akses berdasarkan fase aktif
- ✅ `VerifyJwtToken` - Verifikasi JWT token dari SIAKAD
- ✅ `VerifyServiceKey` - Service-to-service authentication
- ✅ JWT Auth setup dengan shared secret

#### 7. Seeder
- ✅ `FasePresetSeeder` - Contoh periode dengan fase preset

## Sprint 3 - JWT Token Handoff & Integration ✅

### Fitur yang Sudah Diimplementasikan

#### 1. JWT Token Service
- ✅ `JwtTokenService` - Service class untuk JWT management
  - `generateToken()` - Generate token untuk user dari SIAKAD
  - `verifyToken()` - Verify dan decode token
  - `refreshToken()` - Refresh expired token
  - `invalidateToken()` - Logout/invalidate token
  - `getUserFromToken()` - Extract user data dari token
  - `generateServiceToken()` - Token untuk service-to-service
  - `verifyServiceToken()` - Verify service token

#### 2. SIAKAD Integration (di SIAKAD)
- ✅ `UjianServiceController` - Controller untuk komunikasi dengan ujian-service
  - `issueToken()` - Issue JWT token untuk user
  - `getReferenceData()` - Provide reference data (siswa, guru, kelas, mapel, jadwal)
  - `verifyUserAccess()` - Verify user access ke ujian tertentu

#### 3. SIAKAD Integration (di Ujian Service)
- ✅ `SiakadIntegrationController` - Controller untuk menerima request dari SIAKAD
  - `getDaftarUjian()` - Get daftar ujian untuk SIAKAD
  - `getDetailUjian()` - Get detail ujian
  - `getHasilUjian()` - Get hasil ujian siswa
  - `getStatistikUjian()` - Get statistik ujian
  - `getDaftarPeriode()` - Get daftar periode
  - `getPeriodeAktif()` - Get periode aktif
  - `syncData()` - Webhook untuk sync data dari SIAKAD
  - `exportHasil()` - Export hasil ujian untuk SIAKAD

## Sprint 4 - Laravel Reverb Realtime Proktor ✅

### Fitur yang Sudah Diimplementasikan

#### 1. Laravel Reverb Setup
- ✅ Reverb installed dan configured
- ✅ Broadcasting connection set to 'reverb'
- ✅ WebSocket server configuration

#### 2. Broadcasting Events
- ✅ `ProktorEvent` - Event class untuk broadcast proktor activities
  - Broadcast ke channel `proktor.sesi.{sesiUjianId}`
  - Broadcast ke channel `proktor.ujian.{ujianId}`
  - Include severity level (low, medium, high, critical)
  - Real-time notification ke guru/admin

#### 3. Channel Authorization
- ✅ `proktor.sesi.{sesiUjianId}` - Channel untuk monitoring sesi tertentu
- ✅ `proktor.ujian.{ujianId}` - Channel untuk monitoring semua sesi dalam ujian
- ✅ Role-based access control (hanya guru/admin yang bisa subscribe)

#### 4. Proktor Controller
- ✅ `ProktorController` - Handle proktor events dari frontend
  - `recordEvent()` - Record event dari siswa (tab_switch, copy, paste, dll)
  - `getEvents()` - Get history events untuk sesi tertentu
  - `getStatistik()` - Get statistik proktor untuk ujian
  - `getActiveSessions()` - Get active sessions untuk monitoring realtime

#### 5. Event Types
- ✅ `tab_switch` - Siswa pindah tab (severity: high)
- ✅ `blur` - Browser kehilangan fokus (severity: high)
- ✅ `focus` - Browser mendapat fokus kembali (severity: low)
- ✅ `copy` - Siswa melakukan copy (severity: medium)
- ✅ `paste` - Siswa melakukan paste (severity: medium)
- ✅ `right_click` - Siswa klik kanan (severity: medium)
- ✅ `fullscreen_exit` - Keluar dari fullscreen (severity: high)
- ✅ `reconnect` - Reconnect setelah disconnect (severity: low)
- ✅ `devtools_open` - Developer tools dibuka (severity: critical)

### API Endpoints

**Proktor Routes (JWT authenticated):**
```
POST   /api/proktor/event                    - Record event dari frontend siswa
GET    /api/proktor/sesi/{id}/events         - Get events untuk sesi (guru/admin)
GET    /api/proktor/statistik                - Get statistik proktor untuk ujian
GET    /api/proktor/active-sessions          - Get active sessions untuk monitoring
```

### Configuration

**Environment Variables (.env):**
```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=ujian-service
REVERB_APP_KEY=<generated-key>
REVERB_APP_SECRET=<generated-secret>
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
```

### Cara Menggunakan

#### 1. Generate Reverb Keys
```bash
cd ujian-service
php artisan reverb:install --no-interaction
```

#### 2. Start Reverb Server
```bash
# Development
php artisan reverb:start

# Production (dengan supervisor)
php artisan reverb:start --host=0.0.0.0 --port=8080
```

#### 3. Frontend Integration (Siswa)

**Install Laravel Echo & Pusher JS:**
```bash
npm install --save-dev laravel-echo pusher-js
```

**Setup Echo Client:**
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${jwtToken}`,
        },
    },
});
```

**Record Events:**
```javascript
// Tab switch detection
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        recordProktorEvent('tab_switch', {
            timestamp: new Date().toISOString()
        });
    }
});

// Copy detection
document.addEventListener('copy', (e) => {
    recordProktorEvent('copy', {
        text_length: window.getSelection().toString().length
    });
});

// Paste detection
document.addEventListener('paste', (e) => {
    recordProktorEvent('paste', {
        text_length: e.clipboardData.getData('text').length
    });
});

// Right click detection
document.addEventListener('contextmenu', (e) => {
    e.preventDefault();
    recordProktorEvent('right_click', {
        x: e.clientX,
        y: e.clientY
    });
});

// Function to record event
async function recordProktorEvent(eventType, eventData = {}) {
    await fetch('/api/proktor/event', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${jwtToken}`
        },
        body: JSON.stringify({
            sesi_ujian_id: currentSesiId,
            event_type: eventType,
            event_data: eventData
        })
    });
}
```

#### 4. Frontend Integration (Guru/Admin Dashboard)

**Subscribe to Proktor Channel:**
```javascript
// Subscribe to specific ujian
Echo.private(`proktor.ujian.${ujianId}`)
    .listen('.proktor.event', (event) => {
        console.log('Proktor event received:', event);
        
        // Update dashboard UI
        updateProktorDashboard({
            siswaId: event.siswa_id,
            siswaNama: event.siswa_nama,
            eventType: event.event_type,
            severity: event.severity,
            timestamp: event.timestamp,
            eventData: event.event_data
        });
        
        // Show notification for high/critical events
        if (['high', 'critical'].includes(event.severity)) {
            showNotification(
                `${event.siswa_nama} - ${event.event_type}`,
                event.severity
            );
        }
    });

// Subscribe to specific sesi
Echo.private(`proktor.sesi.${sesiId}`)
    .listen('.proktor.event', (event) => {
        // Handle sesi-specific events
    });
```

**Get Active Sessions:**
```javascript
async function loadActiveSessions() {
    const response = await fetch(`/api/proktor/active-sessions?ujian_id=${ujianId}`, {
        headers: {
            'Authorization': `Bearer ${jwtToken}`
        }
    });
    
    const data = await response.json();
    renderActiveSessions(data.data);
}
```

**Get Statistik:**
```javascript
async function loadStatistik() {
    const response = await fetch(`/api/proktor/statistik?ujian_id=${ujianId}`, {
        headers: {
            'Authorization': `Bearer ${jwtToken}`
        }
    });
    
    const data = await response.json();
    renderStatistik(data.data);
}
```

### Security Notes

1. **Channel Authorization**: Hanya guru/admin yang mengajar ujian tersebut yang bisa subscribe
2. **JWT Authentication**: Semua API calls require valid JWT token
3. **Event Validation**: Server validate event type dan sesi ownership
4. **Rate Limiting**: Prevent spam events dari client
5. **HTTPS**: Wajib di production untuk secure WebSocket (wss://)

### Production Deployment

#### 1. Supervisor Configuration
```ini
[program:reverb]
command=php /path/to/ujian-service/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/path/to/ujian-service
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/reverb.log
```

#### 2. Nginx Configuration
```nginx
location /reverb {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
}
```

### Monitoring & Debugging

**Check Reverb Status:**
```bash
php artisan reverb:status
```

**View Reverb Logs:**
```bash
tail -f storage/logs/laravel.log | grep -i reverb
```

**Test WebSocket Connection:**
```javascript
// Browser console
Echo.connector.pusher.connection.bind('connected', () => {
    console.log('Connected to Reverb!');
});

Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('Reverb connection error:', err);
});
```

## Sprint 5 - Migrasi Controller Ujian, Soal, Sesi ✅

### Fitur yang Sudah Diimplementasikan

#### 1. Ujian Controller
- ✅ `UjianController` - Full CRUD untuk manajemen ujian
  - `index()` - List ujian dengan filters (periode, kelas, mapel, guru, tanggal, status)
  - `store()` - Create ujian baru
  - `show()` - Get detail ujian
  - `update()` - Update ujian (tidak bisa jika sudah ada sesi)
  - `destroy()` - Delete ujian (tidak bisa jika sudah ada sesi)
  - `publish()` - Publish ujian (validasi jumlah soal)
  - `unpublish()` - Unpublish ujian
  - `statistik()` - Get statistik lengkap ujian

#### 2. Soal Controller
- ✅ `SoalController` - Full CRUD untuk manajemen soal
  - `index()` - List soal dalam ujian
  - `store()` - Create soal baru (validasi max soal)
  - `show()` - Get detail soal
  - `update()` - Update soal (tidak bisa jika ujian published)
  - `destroy()` - Delete soal dengan auto-reorder
  - `reorder()` - Reorder soal
  - `importFromBank()` - Import soal dari bank soal
  - `uploadMedia()` - Upload media untuk soal

#### 3. Sesi Controller
- ✅ `SesiController` - Full flow ujian untuk siswa
  - `index()` - List available ujian untuk siswa
  - `store()` - Start new exam session
  - `show()` - Get session dengan soals (auto-check timeout)
  - `submitAnswer()` - Submit jawaban per soal
  - `submit()` - Submit entire exam
  - `result()` - Get hasil ujian
  - Auto-grading untuk pilihan ganda & benar/salah
  - Manual grading untuk essay

### API Endpoints

**Ujian Management (JWT authenticated):**
```
GET    /api/ujian                           - List ujian dengan filters
POST   /api/ujian                           - Create ujian baru
GET    /api/ujian/{id}                      - Get detail ujian
PUT    /api/ujian/{id}                      - Update ujian
DELETE /api/ujian/{id}                      - Delete ujian
POST   /api/ujian/{id}/publish              - Publish ujian
POST   /api/ujian/{id}/unpublish            - Unpublish ujian
GET    /api/ujian/{id}/statistik            - Get statistik ujian
```

**Soal Management (JWT authenticated):**
```
GET    /api/ujian/{ujianId}/soal            - List soal dalam ujian
POST   /api/ujian/{ujianId}/soal            - Create soal baru
GET    /api/ujian/{ujianId}/soal/{id}       - Get detail soal
PUT    /api/ujian/{ujianId}/soal/{id}       - Update soal
DELETE /api/ujian/{ujianId}/soal/{id}       - Delete soal
POST   /api/ujian/{ujianId}/soal/reorder    - Reorder soal
POST   /api/ujian/{ujianId}/soal/import-bank - Import dari bank soal
POST   /api/ujian/{ujianId}/soal/{id}/media - Upload media
```

**Sesi Ujian (JWT authenticated - Siswa):**
```
GET    /api/sesi                            - List available ujian
POST   /api/ujian/{ujianId}/sesi            - Start new session
GET    /api/sesi/{id}                       - Get session dengan soals
POST   /api/sesi/{id}/answer                - Submit jawaban per soal
POST   /api/sesi/{id}/submit                - Submit entire exam
GET    /api/sesi/{id}/result                - Get hasil ujian
```

### Request/Response Examples

#### 1. Create Ujian
**Request:**
```json
POST /api/ujian
Authorization: Bearer {jwt_token}

{
  "periode_id": 1,
  "kelas_id": 10,
  "mapel_id": 5,
  "judul": "Ujian Tengah Semester Matematika",
  "deskripsi": "Ujian mencakup materi bab 1-5",
  "waktu_mulai": "2024-06-10 08:00:00",
  "waktu_selesai": "2024-06-10 10:00:00",
  "durasi_menit": 90,
  "passing_grade": 75,
  "jumlah_soal": 30,
  "acak_soal": true,
  "acak_opsi": true,
  "tampilkan_nilai": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Ujian berhasil dibuat",
  "data": {
    "id": 1,
    "periode_id": 1,
    "guru_id": 5,
    "guru_nama": "Pak Budi",
    "judul": "Ujian Tengah Semester Matematika",
    "is_published": false,
    "created_at": "2024-06-01T08:00:00Z"
  }
}
```

#### 2. Create Soal
**Request:**
```json
POST /api/ujian/1/soal
Authorization: Bearer {jwt_token}

{
  "tipe": "pilihan_ganda",
  "pertanyaan": "Berapakah hasil dari 2 + 2?",
  "opsi_a": "3",
  "opsi_b": "4",
  "opsi_c": "5",
  "opsi_d": "6",
  "kunci_jawaban": "B",
  "bobot": 1,
  "pembahasan": "2 + 2 = 4"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Soal berhasil ditambahkan",
  "data": {
    "id": 1,
    "ujian_id": 1,
    "nomor_urut": 1,
    "tipe": "pilihan_ganda",
    "pertanyaan": "Berapakah hasil dari 2 + 2?",
    "created_at": "2024-06-01T08:00:00Z"
  }
}
```

#### 3. Start Exam Session
**Request:**
```json
POST /api/ujian/1/sesi
Authorization: Bearer {jwt_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Sesi ujian berhasil dimulai",
  "data": {
    "sesi_id": 1,
    "waktu_mulai": "2024-06-10T08:00:00Z",
    "durasi_detik": 5400,
    "waktu_berakhir": "2024-06-10T09:30:00Z"
  }
}
```

#### 4. Get Session with Soals
**Request:**
```json
GET /api/sesi/1
Authorization: Bearer {jwt_token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "sesi": {
      "id": 1,
      "ujian_id": 1,
      "ujian_judul": "Ujian Tengah Semester Matematika",
      "status": "berlangsung",
      "waktu_mulai": "2024-06-10T08:00:00Z",
      "sisa_waktu_detik": 4500
    },
    "soals": [
      {
        "id": 1,
        "nomor_urut": 1,
        "tipe": "pilihan_ganda",
        "pertanyaan": "Berapakah hasil dari 2 + 2?",
        "opsi": {
          "A": "3",
          "B": "4",
          "C": "5",
          "D": "6"
        },
        "jawaban_id": 1,
        "jawaban": null,
        "is_ragu": false
      }
    ],
    "progress": {
      "terjawab": 0,
      "ragu": 0,
      "total": 30
    }
  }
}
```

#### 5. Submit Answer
**Request:**
```json
POST /api/sesi/1/answer
Authorization: Bearer {jwt_token}

{
  "jawaban_id": 1,
  "jawaban": "B",
  "is_ragu": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Jawaban berhasil disimpan"
}
```

#### 6. Submit Exam
**Request:**
```json
POST /api/sesi/1/submit
Authorization: Bearer {jwt_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Ujian berhasil diselesaikan",
  "data": {
    "nilai_akhir": 85.5,
    "tampilkan_nilai": true
  }
}
```

#### 7. Get Result
**Request:**
```json
GET /api/sesi/1/result
Authorization: Bearer {jwt_token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "sesi": {
      "id": 1,
      "ujian_judul": "Ujian Tengah Semester Matematika",
      "siswa_nama": "Ahmad",
      "status": "selesai",
      "nilai_akhir": 85.5,
      "passing_grade": 75,
      "lulus": true
    },
    "jawabans": [
      {
        "nomor_urut": 1,
        "pertanyaan": "Berapakah hasil dari 2 + 2?",
        "tipe": "pilihan_ganda",
        "jawaban": "B",
        "kunci_jawaban": "B",
        "is_benar": true,
        "nilai": 1,
        "pembahasan": "2 + 2 = 4"
      }
    ],
    "statistik": {
      "benar": 25,
      "salah": 5,
      "belum_dinilai": 0
    }
  }
}
```

### Business Rules

#### Ujian
- Tidak bisa edit/delete jika sudah ada sesi
- Tidak bisa publish jika jumlah soal belum mencukupi
- Guru hanya bisa manage ujian miliknya sendiri
- Admin bisa manage semua ujian

#### Soal
- Tidak bisa add/edit/delete jika ujian sudah published
- Auto-increment nomor_urut
- Auto-reorder saat delete
- Support import dari bank soal
- Support upload media (image, video, audio, pdf)

#### Sesi
- Siswa hanya bisa start sesi untuk ujian kelasnya
- Satu siswa hanya bisa punya satu sesi resmi per ujian
- Auto-submit saat waktu habis
- Auto-grading untuk pilihan ganda & benar/salah
- Manual grading untuk essay
- Soal bisa diacak (jika setting ujian aktif)
- Opsi bisa diacak (jika setting ujian aktif)

### Security & Authorization

**Role-based Access:**
- **Siswa**: Hanya bisa akses sesi ujian miliknya sendiri
- **Guru**: Bisa manage ujian & soal yang dia buat, lihat hasil siswa
- **Admin**: Full access ke semua ujian, soal, dan hasil

**JWT Token Required:**
- Semua endpoint require valid JWT token
- Token berisi: user_id, nama, role, kelas_id (untuk siswa)
- Token di-inject ke request sebagai `_auth_user`

**Validation:**
- Ownership check untuk semua operations
- Time-based validation untuk start/submit ujian
- Status validation untuk prevent invalid state transitions

## Next Steps (Sprint 6+)

- [ ] Migrasi view blade existing dan adaptasi layout
- [ ] Testing integrasi token handoff antara SIAKAD dan service ujian
- [ ] Testing end-to-end flow ujian lengkap
- [ ] Frontend integration lengkap dengan proktor monitoring
- [ ] Performance testing & optimization
- [ ] Security audit & penetration testing
- [ ] Load testing untuk concurrent users
- [ ] Backup & disaster recovery plan

## Teknologi
- Laravel 11.x
- MySQL 8.0
- JWT Auth (tymon/jwt-auth)
- Laravel Reverb (WebSocket server)
- Redis (optional, untuk scaling Reverb)

## License
Proprietary - SIAKAD PEHAPE NATIP
