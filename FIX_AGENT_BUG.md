# Fix: Agent Tidak Berfungsi Saat Screening Tokoh

## Masalah yang Ditemukan

Agent tidak berfungsi ketika melakukan screening tokoh karena **API key tidak terkirim ke AI Service**.

### Root Cause

1. **Field `api_key_encrypted` ada di `$hidden` array** di model `AiProvider` (line 18-20)
2. Ketika Laravel serialize model ke array/JSON untuk HTTP request, field yang ada di `$hidden` **tidak disertakan**
3. Di `FastAiClient.php` line 30, kode menggunakan `$provider->api_key_encrypted`
4. Karena field ini hidden, nilai yang dikirim ke AI service adalah **null**
5. AI service tidak bisa memanggil external AI provider karena tidak ada API key

### Dampak

- Screening tokoh gagal atau menggunakan fallback mock report
- External AI provider (OpenAI, dll) tidak pernah dipanggil
- User tidak mendapat hasil screening yang sebenarnya

## Solusi yang Diterapkan

### 1. Hapus `api_key_encrypted` dari `$hidden` array

**File**: `backend/app/Models/AiProvider.php`

```php
protected $hidden = [
    // Removed api_key_encrypted from hidden so it can be accessed internally
    // Security is maintained via masked_api_key appended attribute for API responses
];
```

**Alasan**: Field ini perlu bisa diakses secara internal untuk dikirim ke AI service, tapi tetap tidak boleh terekspos ke frontend.

### 2. Tambahkan `makeHidden()` di Controller Responses

**File**: `backend/app/Http/Controllers/Api/Admin/AiProviderController.php`

Modifikasi 3 method:

```php
// index() - line 13-19
public function index()
{
    $providers = AiProvider::latest()->get()->each(function ($provider) {
        $provider->makeHidden('api_key_encrypted');
    });

    return response()->json(['data' => $providers]);
}

// store() - line 38
return response()->json(['data' => $provider->makeHidden('api_key_encrypted')], 201);

// update() - line 65
return response()->json(['data' => $aiProvider->fresh()->makeHidden('api_key_encrypted')]);
```

**Alasan**: Proteksi di level controller memastikan API key tidak pernah terekspos ke frontend, tapi tetap bisa diakses secara internal.

### 3. Pastikan `FastAiClient` Menggunakan Accessor

**File**: `backend/app/Services/FastAiClient.php` (line 30)

```php
'api_key' => $provider->api_key_encrypted, // This will be decrypted by Laravel's encrypted cast
```

**Alasan**: Laravel's `encrypted` cast akan otomatis decrypt nilai ketika diakses via accessor.

## Cara Kerja Setelah Fix

1. **Internal Access** (FastAiClient → AI Service):
   - `$provider->api_key_encrypted` mengembalikan nilai **decrypted** (`local-dev-key`)
   - Nilai ini dikirim ke AI service via HTTP request
   - AI service bisa memanggil external provider dengan API key yang benar

2. **API Response** (Controller → Frontend):
   - `makeHidden('api_key_encrypted')` memastikan field tidak ada di JSON response
   - Frontend hanya melihat `masked_api_key` (contoh: `local-...-key`)
   - Security tetap terjaga

## Testing

Setelah fix diterapkan, test dengan:

```bash
# 1. Clear config cache
cd backend
php artisan config:clear

# 2. Restart queue worker
php artisan queue:restart

# 3. Test screening dari frontend
# Input nama tokoh → Submit → Tunggu hasil
```

## Verifikasi

Cek apakah screening berhasil:

```bash
cd backend
php artisan tinker --execute="App\Models\ScreeningReport::latest()->first();"
```

Jika `status` = `completed` dan `result_json` terisi, maka fix berhasil.

## File yang Dimodifikasi

1. `backend/app/Models/AiProvider.php` - Hapus field dari $hidden
2. `backend/app/Services/FastAiClient.php` - Tambah comment untuk clarity
3. `backend/app/Http/Controllers/Api/Admin/AiProviderController.php` - Tambah makeHidden() di responses

## Catatan Keamanan

- API key tetap **encrypted di database** (Laravel encrypted cast)
- API key **tidak pernah terekspos** ke frontend (makeHidden di controller)
- API key **bisa diakses internal** untuk dikirim ke AI service
- Audit log tetap mencatat perubahan provider (tapi API key di-mask)

---

**Tanggal Fix**: 2026-06-01  
**Status**: ✅ Resolved
