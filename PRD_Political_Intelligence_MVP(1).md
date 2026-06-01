# PRD — Political Intelligence Platform

## MVP Modul: Screening Tokoh Politik

**Versi:** 1.0  
**Tanggal:** 01 Juni 2026  
**Status:** MVP Planning  
**Tech Stack Utama:** Laravel 11, FastAPI, React

---

## 1. Ringkasan Produk

**Political Intelligence Platform** adalah web app berbasis AI untuk membantu tim politik, konsultan, analis, dan internal organisasi dalam melakukan analisis tokoh, isu, elektabilitas, sentimen publik, serta rekomendasi strategi politik.

Untuk MVP awal, sistem hanya memiliki **1 modul utama**, yaitu:

```txt
Screening Tokoh Politik
```

Modul ini digunakan untuk mencari, membaca, menganalisis, dan menyusun laporan lengkap tentang seorang tokoh politik berdasarkan data publik, data internal, dan analisis AI.

---

## 2. Tujuan MVP

Tujuan MVP adalah membuat sistem awal yang bisa:

```txt
1. User login ke platform.
2. User masuk ke landing page modul.
3. User memilih modul Screening Tokoh.
4. User memasukkan nama tokoh.
5. AI melakukan screening lengkap.
6. Sistem menampilkan hasil dalam 1 halaman/kolom rapi.
7. Super Admin dapat mengatur agent, model, API provider, API key, dan skill.
```

MVP ini belum perlu banyak modul. Fokus utama adalah membuat **1 modul bekerja dengan baik**.

---

## 3. Tech Stack & Architecture

### 3.1 Tech Stack Utama

```txt
Frontend:
- React
- TypeScript
- Tailwind CSS
- Poppins font
- Lucide React untuk outline icons
- Dark mode support

Backend Core:
- Laravel 11
- Laravel Sanctum untuk authentication API
- Laravel Policy / Gate / Middleware untuk role access
- Laravel Queue untuk proses background ringan
- Laravel Encryption untuk API key

AI Service:
- FastAPI Python
- Pydantic schema validation
- LLM provider adapter
- Tool / skill orchestration
- Web search integration
- Screening report generator

Database:
- PostgreSQL

Cache & Queue:
- Redis

Deployment:
- Docker
- Docker Compose
- Nginx reverse proxy
- HTTPS/SSL
- VPS / Cloud server
```

---

### 3.2 Pembagian Tanggung Jawab Sistem

#### React Frontend

React bertugas untuk:

```txt
- Menampilkan login page.
- Menampilkan landing page modul.
- Menampilkan modul Screening Tokoh.
- Menampilkan hasil screening dalam format laporan satu kolom.
- Menampilkan Agent Settings khusus Super Admin.
- Menyediakan dark mode switch.
- Menampilkan loading state, error state, empty state, dan history.
```

Frontend tidak boleh menyimpan atau membaca API key asli.

---

#### Laravel 11 Backend Core

Laravel bertugas sebagai backend utama aplikasi:

```txt
- Authentication dan session/token login.
- User management.
- Role dan permission.
- Module access control.
- Agent settings management.
- API provider management.
- API key encryption.
- Skill registry.
- Menyimpan hasil screening ke database.
- Menyediakan REST API untuk frontend.
- Menjadi gateway aman antara frontend dan FastAPI.
```

Laravel adalah pusat kontrol keamanan. Semua request dari frontend harus melewati Laravel.

---

#### FastAPI AI Service

FastAPI bertugas sebagai AI engine:

```txt
- Menerima request screening dari Laravel.
- Membaca konfigurasi agent yang dikirim Laravel.
- Menjalankan prompt screening.
- Memanggil LLM provider sesuai konfigurasi.
- Menjalankan skill/tool yang aktif.
- Menghasilkan output JSON terstruktur.
- Mengembalikan hasil ke Laravel.
```

FastAPI tidak langsung diakses oleh user biasa dari frontend. Akses utama tetap melalui Laravel.

---

### 3.3 Arsitektur High Level

```txt
User Browser
   ↓
React Frontend
   ↓
Laravel 11 API Backend
   ↓
FastAPI AI Service
   ↓
LLM Provider / Web Search / Skill Tools
   ↓
FastAPI hasilkan JSON
   ↓
Laravel simpan ke PostgreSQL
   ↓
React tampilkan laporan screening
```

---

### 3.4 Service Architecture

```txt
political-intelligence-platform/
├── frontend/                 # React app
├── backend/                  # Laravel 11 app
├── ai-service/               # FastAPI app
├── docker-compose.yml
├── nginx/
└── README.md
```

---

### 3.5 Komunikasi Antar Service

#### Frontend → Laravel

```txt
POST /api/login
GET  /api/modules
POST /api/screening-reports
GET  /api/screening-reports
GET  /api/screening-reports/{id}
GET  /api/agent-settings
PUT  /api/agent-settings/{id}
```

#### Laravel → FastAPI

```txt
POST /internal/ai/screening/generate
POST /internal/ai/provider/test-connection
```

FastAPI endpoint harus dilindungi menggunakan internal API token agar tidak bisa dipanggil publik sembarangan.

---

### 3.6 LLM Provider Support

Sistem harus mendukung provider fleksibel:

```txt
- OpenRouter
- DeepSeek
- Qwen
- OpenAI compatible API
- Custom base URL
- Local LLM compatible endpoint
```

Format provider harus mendukung pola OpenAI-compatible API:

```txt
base_url + api_key + model
```

Contoh:

```txt
Provider: OpenRouter
Base URL: https://openrouter.ai/api/v1
Model: anthropic/claude-sonnet
```

```txt
Provider: Local LLM
Base URL: http://localhost:11434/v1
Model: qwen2.5-coder
```

---

### 3.7 Prinsip Keamanan Tech Stack

```txt
- API key tidak boleh disimpan di frontend.
- API key tidak boleh dikirim balik ke frontend.
- API key harus dienkripsi oleh Laravel sebelum disimpan ke database.
- Hanya Super Admin yang bisa mengakses Agent Settings.
- FastAPI hanya menerima request internal dari Laravel.
- Semua skill usage harus dicatat di agent_logs.
- Semua perubahan provider/model/skill harus dicatat di audit_logs.
```

---

## 4. Flow Utama Aplikasi

```txt
Login
↓
Landing Page Modul
↓
Pilih Modul Screening Tokoh
↓
Input Nama Tokoh
↓
Klik Generate Screening
↓
Laravel validasi request
↓
Laravel kirim request ke FastAPI
↓
FastAPI menjalankan AI screening
↓
Laravel menyimpan hasil ke PostgreSQL
↓
Hasil Screening tampil dalam 1 halaman/kolom rapi
↓
User bisa simpan / export / lihat sumber data
```

---

## 5. Struktur Halaman

### 5.1 Login Page

Fungsi:

```txt
- User memasukkan email dan password.
- Sistem melakukan validasi login.
- Sistem melakukan redirect berdasarkan role.
```

Field:

```txt
- Email
- Password
- Remember me
- Login button
```

Style:

```txt
- Background putih.
- Aksen merah dan hitam.
- Font Poppins.
- Ukuran font agak kecil.
- Clean, modern, tidak terlalu ramai.
```

---

### 5.2 Landing Page Modul

Setelah login, user masuk ke halaman utama berisi daftar modul.

Untuk MVP, modul yang aktif hanya:

```txt
Screening Tokoh
```

Modul lain boleh tampil sebagai placeholder, tetapi statusnya:

```txt
Coming Soon
```

Contoh modul:

```txt
- Screening Tokoh
- Monitoring Isu
- Analisis Sentimen
- Peta Elektoral
- Media Monitoring
- Strategi Kampanye
- Quick Count Internal
```

Untuk role **Super Admin**, landing page juga menampilkan menu:

```txt
- Agent Settings
- User Management
- Role Management
- API Provider Settings
- Usage Logs
```

Untuk role selain Super Admin, menu setting agent **tidak boleh terlihat**.

---

## 6. Modul MVP: Screening Tokoh

### 6.1 Tujuan Modul

Modul ini digunakan untuk membuat laporan lengkap tentang seorang tokoh politik.

Input utama hanya:

```txt
Nama Tokoh
```

Tidak perlu input tambahan pada MVP awal.

Field yang **tidak masuk MVP awal**:

```txt
- Wilayah
- Partai
- Jabatan
- Tahun analisis
- Catatan khusus
```

Jika data wilayah, partai, jabatan, atau informasi lain ditemukan dari sumber publik, AI boleh mengisinya otomatis. Jika tidak ditemukan, AI wajib menulis bahwa data belum ditemukan dan tidak boleh mengarang.

Contoh input:

```txt
Nama Tokoh: Jhony Banua Rouw
```

---

### 6.2 Flow Modul Screening Tokoh

```txt
User buka modul Screening Tokoh
↓
User isi Nama Tokoh
↓
Klik Generate Screening
↓
Laravel validasi input
↓
Laravel cek permission user
↓
Laravel ambil konfigurasi agent aktif
↓
Laravel kirim request ke FastAPI
↓
FastAPI menjalankan skill aktif:
- Web Search
- Candidate Screening
- Sentiment Analysis
- Political Risk Analysis
- Electoral Data Analysis
- Regional Base Analysis
- SWOT Generator
- Strategic Recommendation
- Source Collector
- Report Generator
↓
FastAPI mengembalikan JSON terstruktur
↓
Laravel menyimpan hasil screening
↓
React menampilkan hasil dalam format satu halaman rapi
```

---

### 6.3 Validasi Input

Field **Nama Tokoh** wajib diisi.

Jika kosong, tampilkan pesan:

```txt
Nama tokoh wajib diisi.
```

Placeholder input:

```txt
Contoh: Jhony Banua Rouw
```

Contoh tampilan form:

```txt
Screening Tokoh Politik

Masukkan nama tokoh politik yang ingin dianalisis.

[Nama Tokoh........................]

[Generate Screening]
```

---

## 7. Output Screening Tokoh

Hasil screening **tidak menggunakan banyak card terpisah**.

Format hasil harus seperti **laporan satu halaman/kolom panjang**, rapi, mudah dibaca, dan terstruktur berdasarkan heading.

Struktur output:

```txt
1. Profil Tokoh
2. Karier Politik
3. Jejak Digital
4. Kontroversi
5. Analisis Sentimen
6. Data Elektabilitas
7. Analisis Basis Daerah
8. SWOT Analysis
9. Skor Akhir
10. Insight Menaikkan Elektabilitas
11. Rekomendasi Strategis
12. Sumber Data
```

---

### 7.1 Profil Tokoh

Berisi informasi dasar:

```txt
- Nama lengkap
- Nama populer
- Tempat/tanggal lahir jika tersedia
- Partai politik
- Jabatan saat ini / terakhir
- Wilayah basis politik
- Latar belakang pendidikan
- Organisasi atau jaringan penting
```

---

### 7.2 Karier Politik

Berisi rekam jejak politik:

```txt
- Riwayat jabatan
- Pengalaman legislatif / eksekutif
- Riwayat pencalonan
- Partai yang pernah menaungi
- Pencapaian politik
- Posisi dalam struktur partai
```

---

### 7.3 Jejak Digital

Berisi ringkasan eksposur digital:

```txt
- Pemberitaan media online
- Aktivitas media sosial jika tersedia
- Isu yang sering dikaitkan
- Narasi publik yang dominan
- Gaya komunikasi digital
- Intensitas kemunculan di media
```

---

### 7.4 Kontroversi

Berisi isu negatif atau kontroversi yang pernah muncul.

Wajib dibedakan antara:

```txt
- Fakta terverifikasi
- Tuduhan
- Klarifikasi
- Isu yang belum terbukti
- Putusan resmi jika ada
```

Sistem tidak boleh menulis tuduhan sebagai fakta.

Format:

```txt
Isu:
Status:
Sumber:
Risiko Politik:
```

---

### 7.5 Analisis Sentimen

Berisi analisis persepsi publik:

```txt
- Sentimen positif
- Sentimen negatif
- Sentimen netral
- Isu yang mendorong sentimen positif
- Isu yang mendorong sentimen negatif
- Kesimpulan sentimen umum
```

Contoh output:

```txt
Sentimen Umum: Cenderung Netral-Positif
Faktor Positif: pengalaman politik, jaringan partai, popularitas lokal
Faktor Negatif: isu lama, framing elite lama, kompetisi internal
```

---

### 7.6 Data Elektabilitas

Berisi data elektoral jika tersedia:

```txt
- Hasil pemilu/pilkada sebelumnya
- Jumlah suara
- Selisih suara
- Tren dukungan
- Kekuatan suara berdasarkan wilayah
- Data survei jika tersedia
```

Jika data tidak tersedia, sistem harus menulis:

```txt
Data elektabilitas publik belum ditemukan atau belum cukup kuat untuk disimpulkan.
```

Sistem tidak boleh mengarang angka.

---

### 7.7 Analisis Basis Daerah

Berisi analisis wilayah kekuatan politik:

```txt
- Daerah basis utama
- Daerah lemah
- Wilayah swing
- Segmentasi pemilih
- Pengaruh jaringan adat/agama/komunitas
- Pengaruh partai dan relawan
```

---

### 7.8 SWOT Analysis

Format:

```txt
Strengths:
- ...

Weaknesses:
- ...

Opportunities:
- ...

Threats:
- ...
```

Analisis SWOT harus spesifik terhadap tokoh, bukan template umum.

---

### 7.9 Skor Akhir

Skor akhir menggunakan beberapa indikator.

Contoh indikator:

```txt
- Popularitas
- Elektabilitas
- Pengalaman politik
- Kekuatan jaringan
- Sentimen publik
- Risiko kontroversi
- Kekuatan basis daerah
- Potensi kemenangan
```

Format output:

```txt
Skor Akhir: 78/100

Kategori:
- Sangat Kuat: 85–100
- Kuat: 70–84
- Sedang: 55–69
- Lemah: <55
```

AI wajib menjelaskan alasan skor.

---

### 7.10 Insight Menaikkan Elektabilitas

Berisi saran peningkatan elektabilitas:

```txt
- Isu yang perlu diangkat
- Segmen pemilih yang harus didekati
- Narasi publik yang cocok
- Kanal komunikasi yang efektif
- Strategi memperbaiki sentimen negatif
```

Contoh:

```txt
Tokoh perlu memperkuat narasi pelayanan publik, kedekatan dengan anak muda, dan solusi konkret terhadap masalah lokal.
```

---

### 7.11 Rekomendasi Strategis

Berisi rekomendasi aksi:

```txt
- Strategi komunikasi
- Strategi wilayah
- Strategi media sosial
- Strategi koalisi
- Strategi mitigasi isu negatif
- Strategi kampanye darat
```

Format:

```txt
Prioritas Tinggi:
- ...

Prioritas Sedang:
- ...

Prioritas Rendah:
- ...
```

---

### 7.12 Sumber Data

Berisi daftar sumber yang digunakan.

Format:

```txt
- Nama sumber
- Link sumber
- Tanggal akses
- Jenis data
```

Contoh jenis data:

```txt
- Media online
- Website resmi
- KPU
- DPR/DPRD
- Media sosial
- Database internal
```

---

## 8. Agent Settings

### 8.1 Prinsip Utama

```txt
Semua Agent Settings hanya bisa diakses oleh role Super Admin.
```

Role lain tidak boleh melihat menu, tab, API key, model, base URL, skill, atau konfigurasi teknis AI.

---

### 8.2 Menu Agent Settings

Menu untuk Super Admin:

```txt
Agent Settings
├── Agent Profile
├── Model Settings
├── API Provider
├── Skills
├── Browser Automation
├── Prompt Settings
├── Usage & Logs
└── Security
```

---

### 8.3 Agent Profile

Field:

```txt
- Agent Name
- Agent Role
- Description
- Status active/inactive
```

Contoh:

```txt
Agent Name: Political Screening Agent
Role: Candidate Screening Analyst
Description: Agent untuk melakukan screening tokoh politik berbasis data publik dan internal.
```

---

### 8.4 Model Settings

Hanya Super Admin yang bisa mengatur:

```txt
- Provider
- Base URL
- Model name
- Temperature
- Max token
- Timeout
- Fallback model
```

Contoh:

```txt
Provider: OpenRouter
Base URL: https://openrouter.ai/api/v1
Model: anthropic/claude-sonnet
Temperature: 0.4
Max Token: 8000
```

---

### 8.5 API Provider Settings

Field:

```txt
- Provider name
- Base URL
- API key
- Status
- Test connection
```

API key harus:

```txt
- Input type password.
- Ditampilkan masked.
- Tidak pernah dikirim balik ke frontend.
- Disimpan encrypted di database.
```

Contoh tampilan:

```txt
API Key: sk-or-v1-••••••••••••••••9xA2
```

Super Admin bisa update API key, tetapi tidak perlu bisa melihat full API key lama.

---

## 9. Skill Settings

### 9.1 Prinsip

Skill adalah kemampuan agent yang bisa diaktifkan atau dimatikan oleh Super Admin.

Untuk MVP Screening Tokoh, skill yang dibutuhkan:

```txt
1. Web Search
2. Candidate Screening
3. Sentiment Analysis
4. Political Risk Analysis
5. Electoral Data Analysis
6. Regional Base Analysis
7. SWOT Generator
8. Strategic Recommendation
9. Source Collector
10. Report Generator
```

---

### 9.2 Mapping Skill ke Teknologi

```txt
Web Search
- Technology: Search API / web search provider
- Service: FastAPI
- Risk: Medium

Candidate Screening
- Technology: LLM + structured prompt + source collector
- Service: FastAPI
- Risk: Low

Sentiment Analysis
- Technology: LLM / NLP model
- Service: FastAPI
- Risk: Low

Political Risk Analysis
- Technology: LLM reasoning + rule-based risk categories
- Service: FastAPI
- Risk: Low

Electoral Data Analysis
- Technology: LLM + structured extraction from public sources/database
- Service: FastAPI
- Risk: Medium

Regional Base Analysis
- Technology: LLM + public electoral/geographic data
- Service: FastAPI
- Risk: Medium

SWOT Generator
- Technology: LLM structured output
- Service: FastAPI
- Risk: Low

Strategic Recommendation
- Technology: LLM strategy generation
- Service: FastAPI
- Risk: Low

Source Collector
- Technology: Search API + metadata parser
- Service: FastAPI
- Risk: Medium

Report Generator
- Technology: JSON to markdown/html renderer
- Service: Laravel + React
- Risk: Low
```

---

### 9.3 Skill Tambahan Opsional

Untuk tahap berikutnya:

```txt
- Browser Automation
- Screenshot Capture
- Social Media Scanner
- PDF Export
- Telegram Alert
- CRM Update
- Document Reader
```

---

### 9.4 Format Skill di Dashboard

Contoh:

```txt
[ON] Web Search
Mencari data publik dari internet.

[ON] Candidate Screening
Menyusun profil dan analisis tokoh politik.

[ON] Sentiment Analysis
Menganalisis sentimen dari berita dan data teks.

[OFF] Browser Automation
Membuka browser, klik, scroll, dan screenshot halaman.
```

---

### 9.5 Risk Level Skill

Setiap skill punya level risiko:

```txt
Low:
- Sentiment Analysis
- SWOT Generator
- Report Generator

Medium:
- Web Search
- Source Collector
- Electoral Analysis

High:
- Browser Automation
- Auto Post Social Media
- Send Message
- Delete Data
```

Skill high risk harus punya approval manual jika nanti diaktifkan.

---

## 10. Login System & Role

### 10.1 Role Sistem

Minimal role:

```txt
1. Super Admin
2. Admin
3. Analyst
4. Viewer
```

---

### 10.2 Hak Akses

#### Super Admin

```txt
✅ Akses semua modul
✅ Akses Agent Settings
✅ Atur API provider
✅ Atur base URL
✅ Atur model
✅ Atur API key
✅ Atur skill agent
✅ Atur user dan role
✅ Lihat usage log
✅ Lihat semua hasil screening
```

#### Admin

```txt
✅ Akses modul yang diizinkan
✅ Generate screening
✅ Lihat hasil tim
✅ Export laporan jika diizinkan
❌ Tidak bisa akses Agent Settings
❌ Tidak bisa lihat API key
❌ Tidak bisa ubah model
❌ Tidak bisa ubah skill
```

#### Analyst

```txt
✅ Generate screening
✅ Lihat hasil sendiri
✅ Simpan laporan
❌ Tidak bisa akses setting
```

#### Viewer

```txt
✅ Lihat laporan yang dibagikan
❌ Tidak bisa generate jika tidak diberi izin
❌ Tidak bisa akses setting
```

---

## 11. UI/UX Requirement

### 11.1 Style Utama

```txt
Theme: Clean intelligence dashboard
Base color: White
Accent color: Red
Text color: Black / dark gray
Dark mode: Available
Font: Poppins
Icon: Outline style
Font size: Agak kecil, compact, profesional
```

---

### 11.2 Warna

Light mode:

```txt
Background: #FFFFFF
Surface: #F8F8F8
Text Primary: #111111
Text Secondary: #666666
Accent Red: #D71920
Border: #E5E5E5
```

Dark mode:

```txt
Background: #0B0B0B
Surface: #151515
Text Primary: #FFFFFF
Text Secondary: #A3A3A3
Accent Red: #EF233C
Border: #2A2A2A
```

---

### 11.3 Font

```txt
Font Family: Poppins
Base size: 13px - 14px
Heading size: 18px - 24px
Report body: 13px - 14px
Line height: 1.6
```

---

### 11.4 Icon

Gunakan icon outline, contoh library:

```txt
Lucide React
```

Contoh icon:

```txt
- User
- Search
- FileText
- Settings
- Shield
- BarChart
- TrendingUp
- AlertTriangle
- Database
- Globe
```

---

## 12. Layout Hasil Screening

Hasil screening jangan pakai banyak card terpisah.

Gunakan layout seperti laporan:

```txt
Header laporan
↓
Ringkasan singkat
↓
Daftar isi kecil / anchor navigation
↓
Section 1: Profil Tokoh
↓
Section 2: Karier Politik
↓
Section 3: Jejak Digital
↓
...
↓
Section 12: Sumber Data
```

Layout:

```txt
- Satu kolom utama
- Width maksimal 900–1100px
- Ada heading setiap bagian
- Ada divider tipis antar section
- Ada highlight kecil untuk skor akhir
- Tidak terlalu banyak box/card
```

Contoh struktur visual:

```txt
Jhony Banua Rouw
Screening Tokoh Politik
Generated at: 01 Juni 2026

Ringkasan Eksekutif
Paragraf ringkas 3–5 kalimat.

1. Profil Tokoh
Isi laporan...

2. Karier Politik
Isi laporan...

3. Jejak Digital
Isi laporan...
```

---

## 13. Database Design

Database utama menggunakan **PostgreSQL**.

### 13.1 Tabel Users

```txt
users
- id
- name
- email
- password
- role
- status
- created_at
- updated_at
```

---

### 13.2 Tabel Agents

```txt
agents
- id
- name
- role_description
- system_prompt
- provider_id
- model_id
- temperature
- max_tokens
- status
- created_at
- updated_at
```

---

### 13.3 Tabel AI Providers

```txt
ai_providers
- id
- name
- base_url
- api_key_encrypted
- status
- created_by
- updated_by
- created_at
- updated_at
```

---

### 13.4 Tabel AI Models

```txt
ai_models
- id
- provider_id
- model_name
- display_name
- context_window
- input_price_per_million_tokens
- output_price_per_million_tokens
- is_active
- created_at
- updated_at
```

---

### 13.5 Tabel Skills

```txt
skills
- id
- name
- slug
- description
- category
- risk_level
- technology_type
- endpoint
- status
- created_at
- updated_at
```

---

### 13.6 Tabel Agent Skills

```txt
agent_skills
- id
- agent_id
- skill_id
- enabled
- requires_approval
- daily_limit
- created_at
- updated_at
```

---

### 13.7 Tabel Screening Reports

```txt
screening_reports
- id
- user_id
- agent_id
- subject_name
- status
- final_score
- result_json
- result_markdown
- sources_json
- created_at
- updated_at
```

---

### 13.8 Tabel Agent Logs

```txt
agent_logs
- id
- user_id
- agent_id
- skill_id
- action
- input_json
- output_json
- status
- error_message
- created_at
```

---

### 13.9 Tabel Audit Logs

```txt
audit_logs
- id
- user_id
- action
- entity_type
- entity_id
- old_values_json
- new_values_json
- ip_address
- user_agent
- created_at
```

---

## 14. API Design

### 14.1 Laravel Public/Auth API

```txt
POST /api/auth/login
POST /api/auth/logout
GET  /api/auth/me
```

---

### 14.2 Laravel Module API

```txt
GET /api/modules
```

Response berisi daftar modul sesuai role user.

---

### 14.3 Laravel Screening API

```txt
POST /api/screening-reports
GET  /api/screening-reports
GET  /api/screening-reports/{id}
DELETE /api/screening-reports/{id}
```

Request generate screening:

```json
{
  "subject_name": "Jhony Banua Rouw"
}
```

---

### 14.4 Laravel Agent Settings API

Semua endpoint berikut hanya untuk **Super Admin**:

```txt
GET  /api/admin/agents
POST /api/admin/agents
GET  /api/admin/agents/{id}
PUT  /api/admin/agents/{id}
DELETE /api/admin/agents/{id}

GET  /api/admin/ai-providers
POST /api/admin/ai-providers
PUT  /api/admin/ai-providers/{id}
POST /api/admin/ai-providers/{id}/test-connection

GET  /api/admin/skills
PUT  /api/admin/agents/{id}/skills
```

---

### 14.5 FastAPI Internal API

FastAPI hanya boleh diakses oleh Laravel.

```txt
POST /internal/ai/screening/generate
POST /internal/ai/provider/test-connection
```

Header wajib:

```txt
X-Internal-Token: <internal_service_token>
```

---

## 15. AI Output Format

Backend sebaiknya meminta AI mengembalikan hasil dalam JSON agar mudah disimpan dan ditampilkan.

Contoh struktur:

```json
{
  "subject_name": "Jhony Banua Rouw",
  "executive_summary": "...",
  "profile": "...",
  "political_career": "...",
  "digital_footprint": "...",
  "controversies": "...",
  "sentiment_analysis": "...",
  "electability_data": "...",
  "regional_base_analysis": "...",
  "swot": {
    "strengths": [],
    "weaknesses": [],
    "opportunities": [],
    "threats": []
  },
  "final_score": {
    "score": 78,
    "category": "Kuat",
    "reason": "..."
  },
  "electability_improvement_insights": "...",
  "strategic_recommendations": "...",
  "sources": []
}
```

Frontend bisa render JSON ini menjadi halaman laporan satu kolom.

---

## 16. Prompt Dasar Agent

```txt
You are a Political Intelligence Analyst.

Your task is to create a complete political figure screening report based on available public data, internal data, and structured reasoning.

Rules:
- Do not fabricate facts.
- Separate verified facts, allegations, clarifications, and opinions.
- Mention when data is unavailable.
- Always provide source references when available.
- Analyze political risk carefully.
- Return structured JSON only.
- Use neutral, professional, intelligence-report style.

The only user input is the political figure name.
The report must include exactly these 12 sections:
1. Profil Tokoh
2. Karier Politik
3. Jejak Digital
4. Kontroversi
5. Analisis Sentimen
6. Data Elektabilitas
7. Analisis Basis Daerah
8. SWOT Analysis
9. Skor Akhir
10. Insight Menaikkan Elektabilitas
11. Rekomendasi Strategis
12. Sumber Data
```

---

## 17. MVP Scope

### 17.1 Masuk MVP

```txt
✅ Login system
✅ Role system
✅ Landing page modul
✅ Modul Screening Tokoh
✅ Input hanya Nama Tokoh
✅ Generate hasil screening AI
✅ Simpan hasil screening
✅ Lihat history screening
✅ Agent Settings khusus Super Admin
✅ API provider setting khusus Super Admin
✅ Skill setting khusus Super Admin
✅ Light/dark mode
✅ UI putih, hitam, merah
✅ Laravel 11 backend
✅ FastAPI AI service
✅ React frontend
✅ PostgreSQL database
✅ Redis cache/queue
✅ Docker deployment setup
```

---

### 17.2 Tidak Masuk MVP Awal

```txt
❌ Multi modul penuh
❌ Auto posting social media
❌ Browser automation penuh
❌ Telegram alert
❌ Mobile app
❌ Payment/billing
❌ Quick count
❌ CRM politik
❌ Real-time social media monitoring
```

---

## 18. Acceptance Criteria

MVP dianggap berhasil jika:

```txt
1. User bisa login.
2. User masuk ke landing page modul.
3. User bisa membuka modul Screening Tokoh.
4. User hanya perlu input Nama Tokoh.
5. Field Nama Tokoh wajib diisi.
6. Jika nama kosong, tampil validasi: "Nama tokoh wajib diisi."
7. Sistem bisa generate laporan screening.
8. Hasil tampil dalam satu halaman/kolom rapi.
9. Hasil memiliki 12 bagian screening.
10. Sumber data tampil di bagian bawah.
11. Hasil bisa disimpan ke database.
12. Super Admin bisa melihat Agent Settings.
13. Role selain Super Admin tidak bisa melihat Agent Settings.
14. API key masked dan encrypted.
15. Dark mode bisa diaktifkan.
16. UI memakai style putih, hitam, merah, font Poppins, icon outline.
17. React frontend terhubung ke Laravel API.
18. Laravel bisa memanggil FastAPI internal endpoint.
19. FastAPI bisa menghasilkan output JSON screening.
20. PostgreSQL menyimpan user, agent config, skill, dan screening report.
21. Redis tersedia untuk cache/queue jika dibutuhkan.
```

---

## 19. Prioritas Development

### Phase 1 — Foundation

```txt
- Setup repository structure.
- Setup React frontend.
- Setup Laravel 11 backend.
- Setup FastAPI service.
- Setup PostgreSQL.
- Setup Redis.
- Setup Docker Compose.
- Setup auth/login.
- Setup role Super Admin, Admin, Analyst, Viewer.
- Setup layout dashboard.
- Setup landing page modul.
- Setup dark mode.
```

---

### Phase 2 — Screening Tokoh

```txt
- Form input Nama Tokoh di React.
- Laravel endpoint POST /api/screening-reports.
- Laravel service client untuk panggil FastAPI.
- FastAPI endpoint /internal/ai/screening/generate.
- Prompt screening tokoh.
- JSON schema output.
- Simpan hasil ke PostgreSQL.
- Render hasil screening satu halaman di React.
```

---

### Phase 3 — Agent Settings

```txt
- Agent settings page khusus Super Admin.
- Provider settings.
- Model settings.
- API key encryption di Laravel.
- API key masked di frontend.
- Skill toggle.
- Test connection provider.
- Role protection Super Admin only.
```

---

### Phase 4 — Polish

```txt
- Loading state saat generate screening.
- Error handling.
- Empty state.
- History screening.
- Export report.
- Agent logs.
- Audit logs.
```

---

## 20. Deployment MVP

### 20.1 Docker Services

```txt
services:
- frontend-react
- backend-laravel
- ai-service-fastapi
- postgres
- redis
- nginx
```

---

### 20.2 Environment Variables

#### Laravel

```txt
APP_NAME=Political Intelligence Platform
APP_ENV=production
APP_KEY=
APP_URL=
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=political_intelligence
DB_USERNAME=
DB_PASSWORD=
REDIS_HOST=redis
FASTAPI_BASE_URL=http://ai-service:8000
FASTAPI_INTERNAL_TOKEN=
```

#### FastAPI

```txt
APP_ENV=production
INTERNAL_SERVICE_TOKEN=
DEFAULT_LLM_PROVIDER=
DEFAULT_MODEL=
```

#### React

```txt
VITE_API_BASE_URL=https://domain.com/api
```

---

## 21. Brief Pendek untuk Developer / Codex

```txt
Build an MVP Political Intelligence Platform using React, Laravel 11, FastAPI, PostgreSQL, Redis, and Docker.

Flow:
Login → Module Landing Page → Select Screening Tokoh module → Input political figure name → Generate AI screening report → Show result in one clean single-column report page.

The MVP only needs one active module: Screening Tokoh.

The only input for Screening Tokoh is:
- Nama Tokoh

The screening result must contain:
1. Profil Tokoh
2. Karier Politik
3. Jejak Digital
4. Kontroversi
5. Analisis Sentimen
6. Data Elektabilitas
7. Analisis Basis Daerah
8. SWOT Analysis
9. Skor Akhir
10. Insight Menaikkan Elektabilitas
11. Rekomendasi Strategis
12. Sumber Data

Frontend:
React + TypeScript + Tailwind CSS + Poppins + Lucide React outline icons + dark mode.

Backend:
Laravel 11 as the core backend for auth, roles, modules, agent settings, encrypted API keys, skill registry, and report storage.

AI Service:
FastAPI as internal AI service for LLM orchestration, skill execution, web search, and JSON screening generation.

Database:
PostgreSQL.

Cache/Queue:
Redis.

Deployment:
Docker Compose + Nginx reverse proxy + HTTPS.

UI:
White base, black text, red accent, compact Poppins font, outline icons, dark mode switch. Screening result must not be displayed as many cards. It should be rendered like a clean intelligence report in one main column.

Roles:
Only Super Admin can access Agent Settings. Other roles must not see or access agent settings, API provider settings, base URL, model, API key, or skill configuration.

Agent Settings:
Super Admin can manage provider, base URL, model, masked encrypted API key, skills, prompt, usage logs, and test connection.
```

---

## 22. Catatan Keamanan

```txt
- API key tidak boleh pernah dikirim balik ke frontend.
- API key harus disimpan encrypted.
- Role selain Super Admin tidak boleh melihat Agent Settings.
- Backend tetap wajib melakukan authorization check, tidak cukup hanya menyembunyikan menu di frontend.
- FastAPI internal endpoint harus dilindungi internal token.
- Semua aktivitas agent dan penggunaan skill harus dicatat di agent_logs.
- Semua perubahan setting provider/model/skill harus dicatat di audit_logs.
- Tuduhan/kontroversi tidak boleh ditulis sebagai fakta tanpa sumber resmi.
```

---

## 23. Kesimpulan MVP

MVP Political Intelligence Platform difokuskan pada satu fitur utama: **Screening Tokoh Politik**.

Input dibuat sangat sederhana, hanya **Nama Tokoh**. Sistem AI bertugas mencari, menganalisis, dan menyusun laporan lengkap dalam 12 bagian. Hasil ditampilkan sebagai laporan satu kolom yang rapi, bukan kumpulan card.

Tech stack utama adalah **React untuk frontend**, **Laravel 11 untuk backend core**, **FastAPI untuk AI service**, **PostgreSQL untuk database**, **Redis untuk cache/queue**, dan **Docker untuk deployment**.

Konfigurasi teknis agent, provider, model, API key, dan skill hanya bisa diakses oleh **Super Admin**.
