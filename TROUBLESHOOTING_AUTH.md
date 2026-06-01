# Troubleshooting: Unauthenticated Error

## Status Sistem

✅ **Backend Laravel**: Running di `http://127.0.0.1:8001`
✅ **Frontend Vite**: Running di `http://localhost:5173`
✅ **Database**: SQLite dengan 2 users aktif
✅ **Proxy Vite**: Configured untuk forward `/api` ke Laravel

## Masalah: "Unauthenticated"

Error ini muncul ketika:
1. User belum login
2. Token tidak ada di localStorage
3. Token expired atau invalid
4. Header Authorization tidak terkirim dengan benar

## Solusi Step-by-Step

### 1. Buka Browser DevTools

Tekan `F12` atau `Ctrl+Shift+I` di browser

### 2. Clear Storage & Refresh

```
1. Buka tab "Application" (Chrome) atau "Storage" (Firefox)
2. Klik "Local Storage" → http://localhost:5173
3. Hapus semua items (pi_token, pi_user)
4. Refresh halaman (F5)
```

### 3. Login Ulang

Gunakan salah satu akun:

**Super Admin:**
- Email: `superadmin@example.com`
- Password: `password`

**Analyst:**
- Email: `analyst@example.com`
- Password: `password`

### 4. Cek Network Tab

Setelah login, buka tab "Network" di DevTools:

**Request yang berhasil:**
```
POST /api/auth/login
Status: 200 OK
Response: { "token": "...", "user": {...} }
```

**Request berikutnya harus include header:**
```
Authorization: Bearer 1|xxxxxxxxxxxxx
```

### 5. Verifikasi Token Tersimpan

Di Console DevTools, ketik:
```javascript
localStorage.getItem('pi_token')
localStorage.getItem('pi_user')
```

Harus mengembalikan nilai, bukan `null`.

## Debug di Backend

### Cek Token di Database

```bash
cd backend
php artisan tinker
```

```php
// Lihat semua token aktif
Laravel\Sanctum\PersonalAccessToken::all();

// Lihat token untuk user tertentu
$user = App\Models\User::where('email', 'analyst@example.com')->first();
$user->tokens;
```

### Test Login Manual

```bash
curl -X POST http://127.0.0.1:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"analyst@example.com","password":"password"}'
```

Response harus:
```json
{
  "token": "1|xxxxxxxxxx",
  "user": {
    "id": 2,
    "name": "Political Analyst",
    "email": "analyst@example.com",
    "role": "analyst"
  }
}
```

### Test Authenticated Request

```bash
# Ganti TOKEN dengan token dari response login
curl -X GET http://127.0.0.1:8001/api/auth/me \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json"
```

Response harus:
```json
{
  "user": {
    "id": 2,
    "name": "Political Analyst",
    "email": "analyst@example.com",
    "role": "analyst"
  }
}
```

## Kemungkinan Penyebab Lain

### 1. CORS Issue

Cek di Console DevTools apakah ada error CORS. Jika ada, tambahkan middleware CORS di Laravel.

**File**: `backend/bootstrap/app.php`

Pastikan ada:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
```

### 2. Sanctum Configuration

**File**: `backend/config/sanctum.php`

Pastikan `stateful` domains include localhost:
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 
    'localhost,localhost:5173,127.0.0.1,127.0.0.1:5173,::1'
)),
```

### 3. Session Driver

Untuk API token-based auth, pastikan menggunakan Sanctum token, bukan session.

Frontend sudah benar menggunakan:
```javascript
Authorization: `Bearer ${token}`
```

## Quick Fix

Jika masih error, coba:

```bash
# 1. Clear Laravel cache
cd backend
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# 2. Restart Laravel server
# Stop dengan Ctrl+C, lalu:
php artisan serve --port=8001

# 3. Restart frontend
cd ../frontend
# Stop dengan Ctrl+C, lalu:
npm run dev
```

## Verifikasi Final

1. Buka `http://localhost:5173`
2. Clear localStorage (F12 → Application → Local Storage → Clear All)
3. Refresh halaman
4. Login dengan `analyst@example.com` / `password`
5. Setelah login, cek Network tab - semua request harus include `Authorization: Bearer ...`
6. Coba generate screening tokoh

Jika masih error, screenshot error di Console dan Network tab, lalu share untuk analisis lebih lanjut.

---

**Catatan**: Error "Unauthenticated" adalah response standard Laravel Sanctum ketika token tidak valid atau tidak ada. Ini bukan bug, tapi indikasi bahwa authentication belum berhasil.
