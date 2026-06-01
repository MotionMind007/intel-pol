# 🔧 TROUBLESHOOTING: Screening Stuck

## Masalah yang Terjadi

**Screening "roberth rouw" stuck selama 9+ menit**

### Root Cause Analysis

✅ **API Key**: Valid dan berfungsi (test berhasil 2-4 detik)
✅ **Xiaomi API**: Responding dengan baik
✅ **Backend**: Running normal
❌ **Queue Worker**: Kemungkinan hang atau tidak memproses job

### Diagnosis

1. **Queue worker log terakhir**: 11:05:27
2. **Screening dimulai**: 11:19:22
3. **Gap**: 14 menit tanpa log baru
4. **Kesimpulan**: Queue worker mungkin crash atau tidak running

---

## ✅ Solusi yang Sudah Dilakukan

1. ✅ Report #3 di-cancel (status: failed)
2. ✅ API key tested dan valid
3. ✅ Xiaomi API responding normal

---

## 🚀 Cara Restart & Test Ulang

### Step 1: Restart Queue Worker

**Jika queue worker running di terminal terpisah:**
1. Tekan `Ctrl+C` untuk stop
2. Jalankan ulang:
```bash
cd backend
php artisan queue:work database --queue=screening --timeout=0 --tries=1
```

**Jika tidak tahu dimana queue worker:**
```bash
# Kill semua PHP process queue worker
Get-Process | Where-Object {$_.ProcessName -eq "php" -and $_.CPU -gt 100} | Stop-Process -Force

# Start ulang
cd backend
php artisan queue:work database --queue=screening --timeout=0 --tries=1
```

### Step 2: Test dengan Tokoh Terkenal

**Kenapa "roberth rouw" lambat?**
- Tokoh kurang terkenal
- Data Wikipedia minimal
- AI butuh waktu lebih lama

**Rekomendasi: Test dengan tokoh terkenal**
1. Login ke frontend
2. Input: **"Joko Widodo"** atau **"Prabowo Subianto"**
3. Submit
4. Harusnya selesai dalam **30-90 detik**

### Step 3: Monitor Progress

```bash
# Terminal 1: Queue worker
cd backend
php artisan queue:work database --queue=screening --timeout=0 --tries=1

# Terminal 2: Watch status
cd backend
php artisan tinker
```

```php
// Cek status real-time
while(true) {
    $report = App\Models\ScreeningReport::latest()->first();
    echo date('H:i:s') . " - Status: " . $report->status . "\n";
    sleep(5);
}
```

---

## 🔍 Kemungkinan Penyebab Hang

### 1. Timeout Configuration Issue

**File**: `backend/.env`
```env
FASTAPI_TIMEOUT=7200  # 2 hours - terlalu lama!
```

**Rekomendasi**: Reduce ke 300 detik (5 menit)
```env
FASTAPI_TIMEOUT=300
```

Lalu:
```bash
cd backend
php artisan config:clear
```

### 2. AI Service Timeout

**File**: `ai-service/app/main.py` line 126

```python
timeout = float(os.getenv("AI_PROVIDER_TIMEOUT", "7200"))
```

**Rekomendasi**: Tambah timeout di `.env`:
```env
AI_PROVIDER_TIMEOUT=300
```

### 3. Xiaomi API Rate Limit

Xiaomi mungkin punya rate limit. Jika terlalu banyak request:
- Tunggu 5-10 menit
- Atau ganti ke provider lain (Groq/OpenAI)

---

## 🎯 Rekomendasi Final

### Opsi A: Retry dengan Tokoh Terkenal (RECOMMENDED)

1. Restart queue worker
2. Test dengan **"Joko Widodo"**
3. Monitor - harusnya selesai 30-90 detik

### Opsi B: Reduce Timeout

1. Edit `backend/.env`:
   ```env
   FASTAPI_TIMEOUT=300
   ```
2. Restart Laravel & queue worker
3. Test ulang

### Opsi C: Ganti Provider ke Groq (FASTEST)

**Groq = GRATIS & SANGAT CEPAT (30-60 detik)**

1. Daftar: https://console.groq.com/
2. Update via frontend Settings:
   - Base URL: `https://api.groq.com/openai/v1`
   - Model: `llama-3.3-70b-versatile`
   - API Key: (dari Groq)
3. Test screening

---

## 📊 Summary

| Item | Status |
|------|--------|
| Backend | ✅ Running |
| Frontend | ✅ Running |
| AI Service | ✅ Running |
| Xiaomi API | ✅ Valid & Fast (2-4s) |
| Queue Worker | ❌ Hung/Not Processing |
| Screening #3 | ❌ Cancelled (timeout) |

**Next Action**: Restart queue worker dan test dengan "Joko Widodo"

---

**Updated**: 2026-06-01 11:29 WIB
