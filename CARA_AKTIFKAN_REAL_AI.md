# Cara Mengaktifkan Real AI Provider

## Masalah

Hasil screening menggunakan **template/mock report** karena:
- Model name = `"political-screening-mock"` 
- Provider base_url = `"http://ai-service:8000/mock-openai/v1"` (endpoint mock yang tidak ada)
- Fungsi `should_use_provider()` mendeteksi ini sebagai mock dan skip AI provider

## Solusi 1: Gunakan Real AI Provider (OpenAI/Anthropic/dll)

### Step 1: Dapatkan API Key

Pilih salah satu provider:
- **OpenAI**: https://platform.openai.com/api-keys
- **Anthropic**: https://console.anthropic.com/
- **Groq**: https://console.groq.com/
- **OpenRouter**: https://openrouter.ai/

### Step 2: Update Provider via Frontend

1. Login sebagai **Super Admin** (`superadmin@example.com` / `password`)
2. Klik icon **Settings** (⚙️) di sidebar
3. Klik tab **"AI Provider"**
4. Edit provider "OpenAI Compatible":
   - **Name**: OpenAI (atau nama provider lain)
   - **Base URL**: 
     - OpenAI: `https://api.openai.com/v1`
     - Anthropic: `https://api.anthropic.com/v1`
     - Groq: `https://api.groq.com/openai/v1`
   - **API Key**: Paste API key kamu
   - **Status**: Active
5. Klik **Save**

### Step 3: Update Model

1. Masih di Settings, klik tab **"AI Model"**
2. Edit model "Political Screening Mock":
   - **Model Name**: 
     - OpenAI: `gpt-4o` atau `gpt-4-turbo`
     - Anthropic: `claude-3-5-sonnet-20241022`
     - Groq: `llama-3.3-70b-versatile`
   - **Display Name**: GPT-4o (atau nama model lain)
   - **Context Window**: 128000 (untuk GPT-4o)
   - **Is Active**: ✓ Checked
3. Klik **Save**

### Step 4: Test Screening

1. Kembali ke **Screening Tokoh**
2. Input nama tokoh (contoh: "Joko Widodo")
3. Submit dan tunggu hasil
4. Hasil sekarang akan menggunakan **real AI** dengan analisis lengkap

---

## Solusi 2: Update via Database (Manual)

Jika tidak bisa akses frontend sebagai Super Admin:

```bash
cd backend
php artisan tinker
```

```php
// Update Provider
$provider = App\Models\AiProvider::first();
$provider->update([
    'name' => 'OpenAI',
    'base_url' => 'https://api.openai.com/v1',
    'api_key_encrypted' => 'sk-proj-xxxxxxxxxxxxxxxx', // API key kamu
    'status' => 'active'
]);

// Update Model
$model = App\Models\AiModel::first();
$model->update([
    'model_name' => 'gpt-4o',
    'display_name' => 'GPT-4o',
    'context_window' => 128000,
    'is_active' => true
]);

echo "Provider dan Model berhasil diupdate!";
```

---

## Solusi 3: Tetap Pakai Mock (Development)

Jika kamu belum punya API key dan ingin test sistem dulu, mock report sudah benar. Ini memang dirancang untuk development tanpa perlu API key berbayar.

**Catatan**: Mock report akan selalu return template yang sama dengan data minimal dari Wikipedia.

---

## Verifikasi Konfigurasi

Cek apakah provider sudah benar:

```bash
cd backend
php artisan tinker
```

```php
$agent = App\Models\Agent::with(['provider', 'model'])->first();

echo "Provider: " . $agent->provider->name . "\n";
echo "Base URL: " . $agent->provider->base_url . "\n";
echo "Model: " . $agent->model->model_name . "\n";

// Harus menunjukkan:
// Provider: OpenAI (atau provider lain)
// Base URL: https://api.openai.com/v1 (bukan mock URL)
// Model: gpt-4o (bukan political-screening-mock)
```

---

## Biaya Estimasi

Untuk screening 1 tokoh dengan GPT-4o:
- Input: ~15,000 tokens (system prompt + Wikipedia + skills)
- Output: ~3,000 tokens (laporan lengkap)
- **Biaya**: ~$0.15 - $0.20 per screening

Alternatif lebih murah:
- **GPT-4o-mini**: ~$0.01 per screening
- **Groq (Llama 3.3 70B)**: GRATIS (limited rate)
- **OpenRouter**: Berbagai model dengan harga bervariasi

---

## Troubleshooting

### Error: "Provider AI gagal dipanggil"

Cek di screening report, jika ada field `provider_error`, berarti:
1. API key invalid
2. Base URL salah
3. Model name tidak tersedia di provider
4. Rate limit exceeded

### Hasil Masih Mock

Pastikan:
1. ✅ Model name BUKAN `"political-screening-mock"`
2. ✅ Base URL BUKAN `"http://ai-service:8000/mock-openai/v1"`
3. ✅ API key sudah diisi dan valid
4. ✅ Restart queue worker: `php artisan queue:restart`

---

**Kesimpulan**: Sistem kamu sudah bekerja dengan benar! Mock report adalah behavior yang diharapkan ketika menggunakan model mock. Untuk hasil real, update provider dan model dengan credentials real AI provider.
