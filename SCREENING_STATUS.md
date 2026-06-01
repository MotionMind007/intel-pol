# Status Screening - Roberth Rouw

## Current Status: STUCK IN PROCESSING

**Report ID**: 3
**Subject**: roberth rouw
**Status**: processing
**Started**: 2026-06-01 11:19:22 (UTC)
**Runtime**: ~4+ minutes
**Expected**: 30-90 seconds

---

## Diagnosis

### Kemungkinan Penyebab:

1. **Xiaomi API Timeout** ⚠️
   - Request ke `https://api.xiaomimimo.com/v1` tidak mendapat response
   - Timeout default: 7200 seconds (2 hours)
   - Kemungkinan API sedang lambat atau down

2. **Model Issue**
   - Model `mimo-v2.5-pro` mungkin membutuhkan waktu lama untuk complex prompt
   - Screening prompt sangat panjang (~15k tokens)

3. **Network Issue**
   - Koneksi ke Xiaomi API terputus
   - Firewall blocking

---

## Rekomendasi

### Opsi 1: Tunggu Lebih Lama (Recommended)

Xiaomi API mungkin sedang memproses request yang kompleks. Tunggu hingga:
- **5-10 menit** untuk screening pertama
- Jika lebih dari 10 menit, lanjut ke Opsi 2

### Opsi 2: Cancel & Retry

```bash
# 1. Stop queue worker (Ctrl+C di terminal queue worker)

# 2. Update report status ke failed
cd backend
php artisan tinker
```

```php
$report = App\Models\ScreeningReport::find(3);
$report->update([
    'status' => 'failed',
    'error_message' => 'Timeout - cancelled manually',
    'completed_at' => now()
]);
exit;
```

```bash
# 3. Restart queue worker
php artisan queue:work database --queue=screening --timeout=0
```

### Opsi 3: Test dengan Tokoh Lebih Terkenal

Coba screening dengan tokoh yang lebih terkenal (lebih banyak data Wikipedia):
- **Joko Widodo**
- **Prabowo Subianto**
- **Anies Baswedan**

Tokoh terkenal biasanya lebih cepat karena:
- Wikipedia data lebih lengkap
- AI sudah familiar dengan nama

### Opsi 4: Reduce Timeout

Edit `backend/.env`:
```env
FASTAPI_TIMEOUT=300  # 5 minutes instead of 7200
```

Lalu restart Laravel:
```bash
cd backend
php artisan config:clear
# Restart php artisan serve
```

---

## Monitoring

### Cek Status Real-time

```bash
# Terminal 1: Watch screening status
cd backend
watch -n 5 "php artisan tinker --execute='echo App\Models\ScreeningReport::find(3)->status;'"

# Terminal 2: Watch AI service logs
tail -f ai-service-8002.out.log

# Terminal 3: Watch queue worker
tail -f queue-screening.out.log
```

### Cek Jika Selesai

```bash
cd backend
php artisan tinker
```

```php
$report = App\Models\ScreeningReport::find(3);
echo "Status: " . $report->status . "\n";
echo "Score: " . $report->final_score . "\n";
echo "Completed: " . $report->completed_at . "\n";

if ($report->status === 'completed') {
    echo "SUCCESS! Check result in frontend.\n";
}

if ($report->error_message) {
    echo "Error: " . $report->error_message . "\n";
}
```

---

## Alternative: Gunakan Provider Lain

Jika Xiaomi API terlalu lambat, coba provider lain:

### Groq (GRATIS & CEPAT)
- Base URL: `https://api.groq.com/openai/v1`
- Model: `llama-3.3-70b-versatile`
- Speed: ~30-60 detik per screening
- API Key: Daftar di https://console.groq.com/

### OpenAI (STABIL)
- Base URL: `https://api.openai.com/v1`
- Model: `gpt-4o-mini` (murah) atau `gpt-4o` (bagus)
- Speed: ~45-90 detik per screening
- API Key: Daftar di https://platform.openai.com/

---

## Next Steps

1. **Tunggu 5-10 menit** untuk melihat apakah selesai
2. Jika masih stuck, **cancel dan retry** dengan tokoh lain
3. Jika masih bermasalah, **ganti provider** ke Groq atau OpenAI

---

**Update**: Screening dimulai 11:19:22, sekarang 11:23+
**Status**: Masih processing, tunggu atau cancel
