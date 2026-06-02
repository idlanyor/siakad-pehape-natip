# Testing Guide - Ujian Service

## Overview

Ujian Service memiliki comprehensive test suite yang mencakup:
- JWT Token Handoff & Authentication
- Ujian CRUD Operations
- Sesi Ujian Complete Flow
- Auto-grading System
- Role-based Access Control

## Setup Testing Environment

### 1. Create Test Database

```bash
sudo mysql -e "CREATE DATABASE IF NOT EXISTS ujian_db_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 2. Configure Environment

Test environment sudah dikonfigurasi di `phpunit.xml`:
- Database: `ujian_db_test`
- JWT Secret: `test-jwt-secret-key-for-testing-only`
- Service Key: `test-service-key-for-testing`

### 3. Run Migrations

```bash
php artisan migrate:fresh --env=testing --force
```

## Running Tests

### Run All Tests

```bash
php artisan test
```

**Note:** Full test suite memerlukan waktu ~2-3 menit karena RefreshDatabase trait melakukan migrate:fresh untuk setiap test class.

### Run Specific Test Suite

```bash
# JWT Token Handoff Tests
php artisan test --filter=JwtTokenHandoffTest

# Ujian CRUD Tests
php artisan test --filter=UjianCrudTest

# Sesi Ujian Flow Tests
php artisan test --filter=SesiUjianFlowTest
```

### Run Single Test

```bash
php artisan test --filter=JwtTokenHandoffTest::it_can_generate_jwt_token_for_user
```

### Stop on First Failure

```bash
php artisan test --stop-on-failure
```

## Test Coverage

### 1. JWT Token Handoff Tests (18 tests)

**Token Generation & Verification:**
- ✓ Generate JWT token for user
- ✓ Verify valid JWT token
- ✓ Reject invalid JWT token
- ✓ Reject expired JWT token
- ✓ Refresh JWT token
- ✓ Extract user from token

**Middleware Tests:**
- ✓ JWT middleware allows valid token
- ✓ JWT middleware rejects missing token
- ✓ JWT middleware rejects invalid token

**Service-to-Service Auth:**
- ✓ Generate service token
- ✓ Verify service token
- ✓ Reject invalid service token
- ✓ Service middleware allows valid service key
- ✓ Service middleware rejects missing service key
- ✓ Service middleware rejects invalid service key

**User Data Tests:**
- ✓ Token contains correct user data for siswa
- ✓ Token contains correct user data for guru
- ✓ Token contains correct user data for admin

### 2. Ujian CRUD Tests (14 tests)

**Create Operations:**
- ✓ Guru can create ujian
- ✓ Siswa cannot create ujian
- ✓ Validates required fields when creating ujian

**Read Operations:**
- ✓ Guru can view their ujian
- ✓ Guru cannot view other guru ujian
- ✓ Admin can view any ujian
- ✓ List ujian with filters

**Update Operations:**
- ✓ Guru can update their ujian
- ✓ Cannot update ujian that has sessions

**Delete Operations:**
- ✓ Guru can delete their ujian
- ✓ Cannot delete ujian that has sessions

**Publish/Unpublish:**
- ✓ Guru can publish ujian with sufficient soals
- ✓ Cannot publish ujian without sufficient soals
- ✓ Guru can unpublish ujian

### 3. Sesi Ujian Flow Tests (16 tests)

**Session Management:**
- ✓ Siswa can view available ujian
- ✓ Siswa can start exam session
- ✓ Siswa cannot start session for unpublished ujian
- ✓ Siswa cannot start session outside time window
- ✓ Siswa cannot start session for different kelas
- ✓ Siswa cannot start multiple sessions

**Exam Taking:**
- ✓ Siswa can get session with soals
- ✓ Siswa can submit answer
- ✓ Siswa can mark answer as ragu
- ✓ Siswa can submit entire exam
- ✓ Exam auto-submits on timeout

**Results & Access Control:**
- ✓ Siswa can view exam result
- ✓ Siswa cannot view result of ongoing exam
- ✓ Siswa cannot access other student session
- ✓ Guru can view student result

**Auto-grading:**
- ✓ Auto-grading works correctly
- ✓ Essay questions are not auto-graded

## Test Helpers

### TestCase Base Class

Located at `tests/TestCase.php`, provides helper methods:

```php
// Generate JWT token for testing
$token = $this->generateTestToken([
    'user_id' => 1,
    'nama' => 'Test User',
    'role' => 'siswa',
    'kelas_id' => 10,
]);

// Make authenticated request
$response = $this->authenticatedRequest('GET', '/api/ujian', [], [
    'user_id' => 1,
    'role' => 'guru',
]);

// Make service-to-service request
$response = $this->serviceRequest('GET', '/api/siakad/periode');
```

## Known Issues & Optimizations

### Performance

**Issue:** RefreshDatabase trait melakukan migrate:fresh untuk setiap test class, menyebabkan test suite lambat (~2-3 menit untuk 48 tests).

**Solutions:**
1. **Database Transactions** (Recommended for development):
   ```php
   use Illuminate\Foundation\Testing\DatabaseTransactions;
   
   class MyTest extends TestCase
   {
       use DatabaseTransactions;
   }
   ```

2. **Parallel Testing** (Requires Laravel 8+):
   ```bash
   php artisan test --parallel
   ```

3. **In-Memory SQLite** (Fastest, but may have compatibility issues):
   ```xml
   <!-- phpunit.xml -->
   <env name="DB_CONNECTION" value="sqlite"/>
   <env name="DB_DATABASE" value=":memory:"/>
   ```

### PHPUnit Warnings

**Warning:** "Metadata found in doc-comment... deprecated in PHPUnit 12"

**Solution:** Replace `/** @test */` with PHP 8 attributes:
```php
// Old way
/** @test */
public function it_can_generate_token() { }

// New way (PHP 8+)
#[Test]
public function it_can_generate_token() { }
```

## Manual Testing

### Quick Validation Script

Create `test-jwt.php` in project root:

```php
<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$jwtService = app(\App\Services\JwtTokenService::class);

// Test 1: Generate Token
echo "Test 1: Generate Token\n";
$token = $jwtService->generateToken([
    'user_id' => 1,
    'nama' => 'Test User',
    'role' => 'siswa',
    'kelas_id' => 10,
]);
echo "Token: " . substr($token, 0, 50) . "...\n\n";

// Test 2: Verify Token
echo "Test 2: Verify Token\n";
try {
    $decoded = $jwtService->verifyToken($token);
    echo "Decoded: " . json_encode($decoded, JSON_PRETTY_PRINT) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Service Token
echo "Test 3: Service Token\n";
$serviceToken = $jwtService->generateServiceToken();
echo "Service Token: " . $serviceToken . "\n";
echo "Valid: " . ($jwtService->verifyServiceToken($serviceToken) ? 'Yes' : 'No') . "\n";
```

Run:
```bash
php test-jwt.php
```

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: ujian_db_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, pdo, pdo_mysql
      
      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress
      
      - name: Run Tests
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: ujian_db_test
          DB_USERNAME: root
          DB_PASSWORD: root
        run: php artisan test
```

## Best Practices

1. **Isolate Tests**: Each test should be independent and not rely on other tests
2. **Use Factories**: Create model factories for consistent test data
3. **Mock External Services**: Don't make real API calls in tests
4. **Test Edge Cases**: Test both success and failure scenarios
5. **Keep Tests Fast**: Use database transactions when possible
6. **Clear Test Names**: Use descriptive test method names

## Troubleshooting

### Database Connection Error

```bash
# Check MySQL is running
sudo systemctl status mysql

# Verify test database exists
sudo mysql -e "SHOW DATABASES LIKE 'ujian_db_test';"

# Recreate test database
sudo mysql -e "DROP DATABASE IF EXISTS ujian_db_test;"
sudo mysql -e "CREATE DATABASE ujian_db_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Migration Errors

```bash
# Fresh migrations
php artisan migrate:fresh --env=testing --force

# Check migration status
php artisan migrate:status --env=testing
```

### Token Verification Fails

Check JWT secret configuration:
- `config/services.php` - JWT secret must match between SIAKAD and Ujian Service
- `phpunit.xml` - Test JWT secret for testing environment

## Next Steps

1. Add Unit Tests for Models
2. Add Integration Tests for SIAKAD <-> Ujian Service communication
3. Add Performance Tests for high-load scenarios
4. Add Security Tests for penetration testing
5. Implement test coverage reporting
