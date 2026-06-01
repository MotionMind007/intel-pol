# PRD Modul 2 — Media Monitoring
## Political Intelligence Platform

Versi: 1.0  
Stack utama: **React + Laravel 11 + FastAPI**  
Status: MVP lanjutan setelah Modul Screening Tokoh

---

## 1. Ringkasan Modul

**Media Monitoring** adalah modul untuk memantau, mengumpulkan, mengolah, dan menganalisis pemberitaan serta percakapan digital dari berbagai sumber terkait tokoh politik, partai, isu publik, wilayah, organisasi, atau kampanye tertentu.

Sumber utama modul ini mencakup:

- Media sosial
- Portal berita nasional
- Portal berita lokal Papua
- Google Search
- Google Trends
- RSS feed
- Website resmi lembaga
- Blog/opini publik
- Database internal
- Sumber lain yang ditambahkan oleh Super Admin

Modul ini bertujuan membantu tim politik mengetahui isu yang sedang naik, sentimen publik, risiko reputasi, framing media, aktor yang sering disebut, serta rekomendasi respon strategis.

---

## 2. Tujuan Modul

Tujuan Media Monitoring:

1. Memantau berita dan percakapan digital berdasarkan keyword.
2. Mengumpulkan data dari media sosial, portal berita nasional, portal lokal Papua, Google Search, Google Trends, dan sumber lain.
3. Mengklasifikasi isu berdasarkan kategori politik, hukum, pemerintahan, sosial, ekonomi, keamanan, dan lainnya.
4. Menganalisis sentimen positif, negatif, dan netral.
5. Mendeteksi isu negatif dan potensi krisis reputasi.
6. Menemukan aktor, partai, organisasi, wilayah, dan media yang paling sering muncul.
7. Menampilkan ringkasan dan dashboard monitoring.
8. Memberikan rekomendasi respon strategis.
9. Menyimpan hasil monitoring ke database.
10. Menyediakan agent khusus dengan skill browser automation, search, scraping, extraction, sentiment, dan report generation.

---

## 3. User Flow

```txt
User login
↓
Landing Page Modul
↓
Pilih Modul Media Monitoring
↓
User input keyword
↓
Klik Run Monitoring
↓
Laravel validasi role dan request
↓
FastAPI menjalankan Media Monitoring Agent
↓
Agent mencari data dari berbagai sumber
↓
Agent mengekstrak artikel/post/trend
↓
Agent menganalisis sentimen, isu, aktor, risiko, dan tren
↓
Hasil disimpan ke database
↓
Dashboard menampilkan ringkasan, daftar data, insight, dan rekomendasi
```

---

## 4. Input Modul

Untuk MVP awal, input dibuat sederhana.

### 4.1 Input Utama

```txt
Keyword
```

Contoh keyword:

```txt
Jhony Banua Rouw
PSI Papua
Pilkada Jayapura
DOB Papua
Dana Otsus Papua
KPU Papua
Bawaslu Papua
Pendidikan Papua
Infrastruktur Papua
```

### 4.2 Input Opsional Tahap Lanjutan

Untuk versi berikutnya:

```txt
- Rentang tanggal
- Wilayah
- Jenis sumber
- Media tertentu
- Kategori isu
- Tokoh terkait
- Partai terkait
- Sentimen target
- Bahasa
- Jadwal monitoring
```

Untuk MVP Modul 2, cukup:

```txt
Keyword + Run Monitoring
```

---

## 5. Sumber Data

Media Monitoring harus mendukung beberapa kategori sumber.

---

### 5.1 Media Sosial

Sumber sosial media yang dapat dipantau:

```txt
- X / Twitter
- Instagram
- TikTok
- Threads
- Facebook Page/Public Post
- YouTube
- Telegram channel publik
```

Data yang dikumpulkan:

```txt
- Post text
- Caption
- Komentar publik jika tersedia
- Jumlah like
- Jumlah share/repost
- Jumlah komentar
- Hashtag
- Mention
- Tanggal posting
- Link sumber
- Akun sumber
- Screenshot jika diperlukan
```

Catatan penting:

```txt
- Untuk MVP, monitoring sosial media bisa dimulai dari sumber publik.
- Akses akun login pribadi hanya boleh digunakan jika diaktifkan oleh Super Admin.
- Browser automation untuk akun login termasuk High Risk Skill.
- Tidak boleh auto post, auto DM, follow/unfollow, atau ubah data akun tanpa approval manual.
```

---

### 5.2 Portal Berita Nasional

Contoh sumber nasional:

```txt
- Kompas
- Detik
- Tempo
- CNN Indonesia
- CNBC Indonesia
- Liputan6
- Republika
- Antara
- Kumparan
- Tirto
- Media Indonesia
- Metro TV News
- Sindonews
- Okezone
```

Data yang dikumpulkan:

```txt
- Judul berita
- URL
- Nama media
- Tanggal publish
- Penulis jika tersedia
- Isi artikel
- Ringkasan
- Kategori isu
- Sentimen
- Tokoh yang disebut
- Lokasi yang disebut
- Risiko reputasi
```

---

### 5.3 Portal Berita Lokal Papua

Contoh sumber lokal Papua:

```txt
- Cenderawasih Pos
- Jubi
- Kabar Papua
- PapuaSatu
- Teras Papua
- RRI Jayapura
- Antara Papua
- Papua Inside
- Noken Live
- Pasific Pos
- Cepos Online
- Papua Barat News
- Media lokal lain yang ditambahkan Super Admin
```

Data yang dikumpulkan:

```txt
- Judul berita
- URL
- Media
- Tanggal publish
- Wilayah
- Tokoh terkait
- Partai/organisasi terkait
- Isu utama
- Sentimen lokal
- Risiko politik lokal
```

Catatan:

```txt
Portal lokal Papua penting karena sering lebih cepat menangkap isu lokal dibanding media nasional.
```

---

### 5.4 Google Search

Google Search digunakan untuk mencari sumber publik berdasarkan keyword.

Contoh query:

```txt
"Jhony Banua Rouw" Papua
"PSI Papua" Pilkada
"DOB Papua" kritik
"KPU Papua" sengketa
```

Data yang dikumpulkan:

```txt
- Judul hasil pencarian
- Snippet
- URL
- Domain
- Ranking hasil
- Tanggal jika tersedia
```

Teknologi:

```txt
- Google Custom Search API
- SerpAPI
- Tavily
- Bing Search API sebagai fallback
```

Catatan:

```txt
Tidak disarankan scraping langsung halaman Google secara agresif karena rawan terkena pembatasan.
Gunakan API resmi atau search provider yang legal.
```

---

### 5.5 Google Trends

Google Trends digunakan untuk melihat minat pencarian publik terhadap keyword.

Data yang dikumpulkan:

```txt
- Trend interest over time
- Related queries
- Related topics
- Wilayah dengan minat tertinggi
- Perbandingan beberapa keyword
```

Contoh penggunaan:

```txt
Keyword: PSI Papua
Compare: NasDem Papua, Golkar Papua, PDIP Papua
Region: Indonesia / Papua
Time range: 7 hari, 30 hari, 12 bulan
```

Teknologi:

```txt
- Pytrends
- Google Trends unofficial library
- Manual browser automation jika API/library tidak stabil
```

Catatan:

```txt
Google Trends bukan data elektabilitas.
Google Trends hanya indikator minat pencarian.
```

---

### 5.6 Sumber Resmi

Sumber resmi yang bisa dipantau:

```txt
- KPU
- Bawaslu
- DPR / DPRD
- Pemerintah Provinsi
- Pemerintah Kabupaten/Kota
- Website partai politik
- Website tokoh/kampanye
- Siaran pers resmi
```

Data yang dikumpulkan:

```txt
- Dokumen resmi
- Pengumuman
- Berita acara
- Hasil pemilu
- Daftar calon
- Putusan/sengketa
- Siaran pers
```

---

### 5.7 Sumber Lain

Super Admin dapat menambahkan sumber baru:

```txt
- RSS feed
- Blog lokal
- Website komunitas
- Kanal YouTube publik
- Forum publik
- Database internal
- File dokumen internal
```

Sumber baru harus memiliki konfigurasi:

```txt
- Nama sumber
- Domain / URL
- Jenis sumber
- Kredibilitas sumber
- Status aktif/nonaktif
- Metode pengambilan data
```

---

## 6. Output Modul Media Monitoring

Output utama:

```txt
1. Ringkasan Monitoring
2. Total Data Ditemukan
3. Analisis Sentimen
4. Isu Dominan
5. Isu Positif
6. Isu Negatif
7. Aktor yang Disebut
8. Media/Sumber Paling Aktif
9. Tren Pemberitaan dan Percakapan
10. Google Trends Insight
11. Risiko Reputasi
12. Rekomendasi Respon
13. Daftar Artikel/Post
14. Sumber Data
```

---

## 7. Struktur Halaman UI

### 7.1 Dashboard Media Monitoring

Layout:

```txt
Header modul
↓
Keyword input
↓
Source selector optional
↓
Summary metrics
↓
Trend chart
↓
Dominant issues
↓
Sentiment breakdown
↓
Article/Post list
↓
AI insight and recommendations
```

### 7.2 Style UI

Mengikuti tema platform utama:

```txt
- Base putih
- Kombinasi hitam dan merah
- Font Poppins
- Font size compact
- Icon outline
- Bisa switch dark mode
- Layout profesional seperti intelligence dashboard
```

### 7.3 Summary Metrics

Media Monitoring boleh menggunakan compact cards untuk dashboard.

Contoh:

```txt
Total Data: 156
Portal Berita: 48
Media Sosial: 91
Google Search: 12
Google Trends: 5
Sentimen Positif: 35%
Sentimen Netral: 44%
Sentimen Negatif: 21%
Risiko Reputasi: Medium
```

### 7.4 Daftar Artikel/Post

Gunakan compact list/table, bukan card besar.

Format:

```txt
Judul / Caption
Sumber • Tanggal • Platform
Ringkasan singkat
Sentimen
Kategori isu
Risk level
Aksi: Open Source | Analyze | Screenshot
```

---

## 8. Agent Detail

Nama agent:

```txt
Media Monitoring Agent
```

Role:

```txt
AI agent yang bertugas memantau, mengumpulkan, mengekstrak, mengklasifikasi, dan menganalisis data dari media sosial, portal berita, Google Search, Google Trends, dan sumber publik lain untuk kebutuhan intelligence politik.
```

---

## 9. Struktur Multi-Agent

Rekomendasi struktur:

```txt
Media Monitoring Orchestrator
├── Source Discovery Agent
├── News Search Agent
├── Social Media Monitoring Agent
├── Google Search Agent
├── Google Trends Agent
├── Article Extraction Agent
├── Browser Automation Agent
├── Sentiment Analysis Agent
├── Issue Classification Agent
├── Actor & Entity Extraction Agent
├── Risk Detection Agent
├── Trend Analysis Agent
└── Response Recommendation Agent
```

---

## 10. Detail Sub-Agent

### 10.1 Media Monitoring Orchestrator

Tugas:

```txt
- Menerima keyword dari user
- Menentukan sumber mana yang perlu digunakan
- Memanggil sub-agent sesuai kebutuhan
- Menggabungkan hasil dari semua sumber
- Menentukan output final
```

Skill:

```txt
- Workflow Routing
- Task Planning
- Model Routing
- Result Aggregation
```

Risk level:

```txt
Medium
```

Model:

```txt
Medium reasoning model
```

---

### 10.2 Source Discovery Agent

Tugas:

```txt
- Menentukan sumber relevan berdasarkan keyword
- Memilih media nasional/lokal
- Memilih sumber sosial media
- Menentukan query pencarian
```

Skill:

```txt
- Source Selection
- Query Builder
- Domain Whitelist Checker
```

Risk level:

```txt
Low-Medium
```

---

### 10.3 News Search Agent

Tugas:

```txt
- Mencari berita dari portal nasional dan lokal
- Mengambil judul, URL, snippet, tanggal, media
- Menghindari duplikasi
```

Skill:

```txt
- News Search
- Source Collector
- Duplicate Detection
```

Teknologi:

```txt
- Tavily API
- SerpAPI
- Google Custom Search API
- Bing Search API
- RSS Feed
```

Risk level:

```txt
Medium
```

---

### 10.4 Social Media Monitoring Agent

Tugas:

```txt
- Memantau post publik dari media sosial
- Mengambil caption, hashtag, mention, engagement, dan link
- Mengelompokkan percakapan berdasarkan isu
```

Platform:

```txt
- X / Twitter
- Instagram
- TikTok
- Threads
- Facebook public page
- YouTube
- Telegram public channel
```

Skill:

```txt
- Social Search
- Hashtag Monitoring
- Mention Extraction
- Engagement Collector
- Social Sentiment Analysis
```

Teknologi:

```txt
- Official API jika tersedia
- Third-party social listening API
- Browser automation untuk sumber yang tidak punya API stabil
- Playwright
- Puppeteer
```

Risk level:

```txt
High jika memakai akun login/browser automation
Medium jika hanya mengambil data publik via API/search
```

Batasan:

```txt
- Tidak boleh auto-post
- Tidak boleh auto-DM
- Tidak boleh follow/unfollow
- Tidak boleh ubah profil
- Tidak boleh mengakses data pribadi non-publik
- Semua aksi harus dicatat di log
```

---

### 10.5 Google Search Agent

Tugas:

```txt
- Menjalankan pencarian web berdasarkan keyword
- Mengambil hasil paling relevan
- Mengelompokkan hasil berdasarkan sumber
- Mengirim URL ke Article Extraction Agent
```

Skill:

```txt
- Google Search
- Query Expansion
- Result Ranking
```

Teknologi:

```txt
- Google Custom Search API
- SerpAPI
- Tavily
- Bing Search API fallback
```

Risk level:

```txt
Medium
```

---

### 10.6 Google Trends Agent

Tugas:

```txt
- Mengambil data minat pencarian keyword
- Membandingkan beberapa keyword
- Mengambil related queries dan related topics
- Membuat insight trend pencarian
```

Skill:

```txt
- Google Trends Analysis
- Search Interest Analysis
- Related Query Extraction
```

Teknologi:

```txt
- Pytrends
- Google Trends unofficial method
- Browser automation fallback
```

Risk level:

```txt
Medium
```

Catatan:

```txt
Google Trends hanya indikator minat pencarian, bukan data survei atau elektabilitas.
```

---

### 10.7 Article Extraction Agent

Tugas:

```txt
- Membuka URL artikel
- Mengekstrak isi artikel
- Membersihkan iklan, menu, navbar, dan elemen tidak relevan
- Mengambil metadata artikel
```

Skill:

```txt
- Article Parser
- Web Reader
- Metadata Extractor
- Content Cleaner
```

Teknologi:

```txt
- Trafilatura
- BeautifulSoup
- Newspaper3k
- Readability
- Playwright fallback
```

Risk level:

```txt
Medium
```

---

### 10.8 Browser Automation Agent

Tugas:

```txt
- Membuka browser
- Mengakses halaman web
- Klik, scroll, input keyword
- Mengambil screenshot
- Membaca halaman yang butuh rendering JavaScript
- Membantu scraping sumber yang tidak bisa dibaca parser biasa
```

Skill:

```txt
- Open Browser
- Go To URL
- Click
- Scroll
- Type
- Screenshot Capture
- Extract Page Text
- Download Page
```

Teknologi:

```txt
- Playwright
- Puppeteer
- Chrome CDP
- Browserbase optional
```

Risk level:

```txt
High
```

Batasan wajib:

```txt
- Hanya Super Admin yang bisa mengaktifkan skill ini
- Wajib domain whitelist
- Wajib action log
- Wajib screenshot log jika mengambil capture
- Tidak boleh transaksi
- Tidak boleh submit form sensitif
- Tidak boleh ubah password/email
- Tidak boleh hapus data
- Tidak boleh auto-post media sosial
- Tidak boleh kirim DM/pesan tanpa approval manual
- Untuk akun login, session harus dibatasi hanya untuk monitoring
```

Contoh izin:

```txt
Boleh:
✅ buka website
✅ scroll
✅ klik navigasi
✅ search keyword
✅ screenshot
✅ extract text

Tidak boleh tanpa approval:
❌ submit form
❌ post konten
❌ kirim pesan
❌ ubah profil
❌ hapus konten
❌ transaksi/pembayaran
```

---

### 10.9 Sentiment Analysis Agent

Tugas:

```txt
- Menilai sentimen artikel/post
- Memberi label positif/netral/negatif
- Menjelaskan alasan sentimen
```

Skill:

```txt
- Sentiment Analysis
- Tone Detection
- Framing Detection
```

Model:

```txt
Light/medium model
```

Risk level:

```txt
Low
```

---

### 10.10 Issue Classification Agent

Tugas:

```txt
- Mengelompokkan isu berdasarkan kategori
- Mendeteksi isu dominan
- Membuat keyword turunan
```

Kategori awal:

```txt
- Politik
- Pemerintahan
- Hukum
- Korupsi
- Pemilu
- Sosial
- Ekonomi
- Pendidikan
- Kesehatan
- Infrastruktur
- Keamanan
- Konflik internal
- Citra personal
- Adat
- Agama
- Otsus
- DOB
```

Skill:

```txt
- Topic Classification
- Keyword Extraction
- Topic Clustering
```

Risk level:

```txt
Low-Medium
```

---

### 10.11 Actor & Entity Extraction Agent

Tugas:

```txt
- Mendeteksi nama tokoh
- Mendeteksi partai
- Mendeteksi organisasi
- Mendeteksi wilayah
- Mendeteksi institusi
```

Entity type:

```txt
- person
- party
- organization
- government
- region
- media
- community
```

Skill:

```txt
- Named Entity Recognition
- Political Entity Mapping
```

Risk level:

```txt
Low
```

---

### 10.12 Risk Detection Agent

Tugas:

```txt
- Menilai risiko reputasi
- Menandai isu sensitif
- Mendeteksi potensi krisis
```

Risk level:

```txt
Low
Medium
High
Critical
```

Critical jika:

```txt
- Isu hukum berat
- Dugaan korupsi besar
- Konflik massa
- Isu SARA
- Viralitas tinggi
- Banyak media mengangkat isu negatif yang sama
```

Skill:

```txt
- Political Risk Analysis
- Crisis Detection
- Narrative Risk Scoring
```

Model:

```txt
Reasoning model
```

Risk level:

```txt
Medium
```

---

### 10.13 Trend Analysis Agent

Tugas:

```txt
- Menghitung tren jumlah artikel/post
- Menghitung perubahan sentimen
- Menghitung sumber paling aktif
- Menggabungkan Google Trends insight
```

Skill:

```txt
- Trend Analysis
- Time Series Summary
- Media Frequency Analysis
```

Teknologi:

```txt
- Python pandas
- PostgreSQL aggregation
- Frontend chart
```

Risk level:

```txt
Low
```

---

### 10.14 Response Recommendation Agent

Tugas:

```txt
- Membuat rekomendasi respon komunikasi
- Menyusun narasi tandingan
- Memberikan prioritas aksi
- Membantu strategi mitigasi isu negatif
```

Skill:

```txt
- Strategic Communication
- Crisis Response
- Narrative Framing
```

Model:

```txt
Reasoning model
```

Risk level:

```txt
Medium
```

---

## 11. Skill Mapping

| Skill | Fungsi | Teknologi | Risk |
|---|---|---|---|
| News Search | Mencari berita | Tavily, SerpAPI, Google CSE, Bing API | Medium |
| Social Search | Mencari post sosial media | API, third-party, browser automation | Medium/High |
| Google Search | Pencarian web | Google CSE, SerpAPI | Medium |
| Google Trends | Trend pencarian | Pytrends, browser fallback | Medium |
| Article Extraction | Ambil isi artikel | Trafilatura, BS4, Newspaper3k | Medium |
| Browser Automation | Buka web, klik, screenshot | Playwright, Puppeteer, CDP | High |
| Sentiment Analysis | Analisis sentimen | LLM/NLP | Low |
| Issue Classification | Klasifikasi isu | LLM/NLP | Low-Medium |
| Entity Extraction | Ekstrak tokoh/organisasi/wilayah | LLM/NER | Low |
| Risk Detection | Deteksi risiko | Reasoning model | Medium |
| Trend Analysis | Tren data | Pandas, SQL, chart | Low |
| Recommendation | Rekomendasi respon | Reasoning model | Medium |
| Report Generator | Susun laporan | LLM | Low |

---

## 12. Model Routing

Media Monitoring harus hemat biaya dengan multi-provider/model routing.

### 12.1 Pembagian Model

```txt
Search & collection:
- Tidak perlu model mahal
- Pakai search API + light model

Article extraction:
- Parser dulu
- LLM hanya untuk ringkasan/cleaning jika perlu

Sentiment:
- Light/medium model

Issue classification:
- Light/medium model

Risk detection:
- Reasoning model

Strategic recommendation:
- Reasoning model

Final summary:
- Medium model
```

### 12.2 Contoh Routing

```txt
Task: news_search
Model: light-fast-model

Task: social_post_summary
Model: light-fast-model

Task: article_summary
Model: cheap-summary-model

Task: sentiment_analysis
Model: medium-model

Task: issue_classification
Model: medium-model

Task: risk_detection
Model: reasoning-model

Task: strategic_response
Model: reasoning-model

Task: final_report
Model: medium-model
```

Model routing hanya bisa diatur oleh Super Admin.

---

## 13. Database Design

### 13.1 monitoring_keywords

```txt
id
user_id
keyword
status
created_at
updated_at
```

### 13.2 media_sources

```txt
id
name
domain
source_type
platform
credibility_score
is_active
created_at
updated_at
```

source_type:

```txt
news_national
news_local_papua
social_media
google_search
google_trends
official_source
blog
internal
other
```

### 13.3 media_items

Untuk menampung artikel berita dan post sosial media.

```txt
id
keyword_id
source_id
source_type
platform
title
content_text
snippet
url
author
account_name
published_at
captured_at
engagement_json
content_hash
screenshot_path
raw_json
created_at
updated_at
```

engagement_json:

```json
{
  "likes": 0,
  "comments": 0,
  "shares": 0,
  "views": 0,
  "reposts": 0
}
```

### 13.4 media_item_analyses

```txt
id
media_item_id
summary
sentiment
sentiment_score
issue_category
risk_level
risk_reason
framing
recommendation
created_at
updated_at
```

### 13.5 media_entities

```txt
id
media_item_id
entity_name
entity_type
confidence_score
created_at
```

### 13.6 media_monitoring_runs

```txt
id
user_id
keyword_id
status
total_items
news_count
social_count
google_search_count
google_trends_count
positive_count
neutral_count
negative_count
risk_level
started_at
finished_at
error_message
created_at
```

### 13.7 media_monitoring_insights

```txt
id
run_id
executive_summary
dominant_issues_json
positive_issues_json
negative_issues_json
top_actors_json
top_sources_json
trend_json
google_trends_json
risk_assessment
strategic_recommendation
created_at
```

### 13.8 browser_action_logs

```txt
id
user_id
agent_id
run_id
action_type
target_url
domain
input_json
output_json
screenshot_path
status
error_message
created_at
```

---

## 14. API Endpoint

### 14.1 Laravel API

```txt
GET    /api/media-monitoring
POST   /api/media-monitoring/run
GET    /api/media-monitoring/runs
GET    /api/media-monitoring/runs/{id}
GET    /api/media-monitoring/items/{id}
GET    /api/media-monitoring/sources
POST   /api/media-monitoring/sources
PUT    /api/media-monitoring/sources/{id}
DELETE /api/media-monitoring/keywords/{id}
```

### 14.2 FastAPI AI Service

```txt
POST /ai/media-monitoring/run
POST /ai/media-monitoring/search-news
POST /ai/media-monitoring/search-social
POST /ai/media-monitoring/google-search
POST /ai/media-monitoring/google-trends
POST /ai/media-monitoring/extract-article
POST /ai/media-monitoring/analyze-item
POST /ai/media-monitoring/sentiment
POST /ai/media-monitoring/classify-issue
POST /ai/media-monitoring/risk-detection
POST /ai/media-monitoring/recommendation
```

### 14.3 Browser Automation Service

```txt
POST /browser/open
POST /browser/go-to-url
POST /browser/click
POST /browser/type
POST /browser/scroll
POST /browser/screenshot
POST /browser/extract-text
POST /browser/close
```

Browser endpoints harus dilindungi oleh:

```txt
- Super Admin config
- Domain whitelist
- Permission check
- Action logs
- Approval requirement untuk aksi sensitif
```

---

## 15. Backend Workflow

```txt
React
↓
Laravel API
↓
Validate user permission
↓
Create monitoring run
↓
Dispatch queue job
↓
FastAPI Media Monitoring Orchestrator
↓
Source Discovery
↓
News Search + Social Search + Google Search + Google Trends
↓
Article/Post Extraction
↓
Sentiment Analysis
↓
Issue Classification
↓
Entity Extraction
↓
Risk Detection
↓
Trend Analysis
↓
Recommendation
↓
Save to PostgreSQL
↓
React dashboard displays result
```

---

## 16. Agent Settings

Hanya Super Admin yang bisa mengakses Agent Settings.

```txt
Agent Settings
├── Media Monitoring Agent
│   ├── General
│   ├── Prompt
│   ├── Skills
│   ├── Sources
│   ├── Browser Automation
│   ├── Google Search
│   ├── Google Trends
│   ├── Social Media Sources
│   ├── Model Routing
│   ├── Schedule
│   └── Logs
```

### 16.1 Sources Setting

Super Admin bisa atur:

```txt
- Media nasional aktif/nonaktif
- Media lokal Papua aktif/nonaktif
- Social media platform aktif/nonaktif
- Google Search provider
- Google Trends provider
- RSS feed
- Domain whitelist
- Domain blacklist
- Kredibilitas sumber
```

### 16.2 Browser Automation Setting

Field:

```txt
- Enable/disable browser automation
- Allowed domains
- Allowed actions
- Screenshot storage path
- Session mode
- Require approval for high-risk actions
```

Allowed actions:

```txt
- open_url
- click_navigation
- type_search_keyword
- scroll
- screenshot
- extract_text
```

Blocked actions by default:

```txt
- submit_form
- post_content
- send_message
- change_profile
- delete_content
- purchase
- payment
- password_change
```

### 16.3 Model Routing Setting

Super Admin bisa atur:

```txt
- Model untuk search summary
- Model untuk sentiment
- Model untuk risk detection
- Model untuk recommendation
- Fallback model
- Cost limit
- Token limit
```

---

## 17. Permission

### Super Admin

```txt
✅ Akses semua modul
✅ Atur Media Monitoring Agent
✅ Atur source whitelist/blacklist
✅ Atur browser automation
✅ Atur provider/model/API key
✅ Atur skill
✅ Lihat semua logs
```

### Admin

```txt
✅ Run monitoring
✅ Lihat hasil tim
✅ Export report jika diizinkan
❌ Tidak bisa atur agent
❌ Tidak bisa atur source
❌ Tidak bisa atur API key/model
```

### Analyst

```txt
✅ Run monitoring
✅ Lihat hasil sendiri
✅ Buka detail data
❌ Tidak bisa akses setting
```

### Viewer

```txt
✅ Lihat hasil yang dibagikan
❌ Tidak bisa run monitoring jika tidak diberi izin
❌ Tidak bisa akses setting
```

---

## 18. Prompt Dasar Media Monitoring Agent

```txt
You are a Political Media Monitoring Analyst.

Your task is to monitor, collect, extract, classify, and analyze political media data from news portals, local Papua media, social media, Google Search, Google Trends, official sources, and other configured sources.

Rules:
- Do not fabricate facts.
- Separate facts, allegations, clarifications, and opinions.
- Always include source URL, source name, platform, and date when available.
- Classify sentiment as positive, neutral, or negative.
- Classify issue category.
- Extract important actors, organizations, parties, and regions.
- Detect political and reputational risk.
- Google Trends data must be described as search-interest data, not electability.
- Social media engagement must be described as digital engagement, not voter support.
- If data is unavailable, clearly say that the data is unavailable.
- Return structured JSON only.
```

---

## 19. JSON Output Format

```json
{
  "keyword": "PSI Papua",
  "executive_summary": "...",
  "total_items": 156,
  "source_breakdown": {
    "news_national": 20,
    "news_local_papua": 28,
    "social_media": 91,
    "google_search": 12,
    "google_trends": 5
  },
  "sentiment": {
    "positive": 55,
    "neutral": 69,
    "negative": 32,
    "dominant": "neutral"
  },
  "risk_level": "medium",
  "dominant_issues": [
    {
      "issue": "Pilkada Jayapura",
      "count": 18,
      "sentiment": "neutral",
      "risk_level": "medium"
    }
  ],
  "top_actors": [
    {
      "name": "Jhony Banua Rouw",
      "type": "person",
      "mentions": 24
    }
  ],
  "top_sources": [
    {
      "name": "Cenderawasih Pos",
      "source_type": "news_local_papua",
      "item_count": 12
    }
  ],
  "google_trends_insight": {
    "summary": "...",
    "related_queries": [],
    "related_topics": []
  },
  "items": [
    {
      "title": "...",
      "source": "...",
      "platform": "news",
      "url": "...",
      "published_at": "...",
      "summary": "...",
      "sentiment": "neutral",
      "issue_category": "politics",
      "risk_level": "medium"
    }
  ],
  "strategic_recommendation": {
    "high_priority": [],
    "medium_priority": [],
    "low_priority": []
  }
}
```

---

## 20. MVP Scope

### Masuk MVP

```txt
✅ Input keyword
✅ Search portal berita nasional
✅ Search portal berita lokal Papua
✅ Google Search integration
✅ Google Trends integration sederhana
✅ Social media public data collection basic
✅ Article extraction
✅ Sentiment analysis
✅ Issue classification
✅ Entity extraction
✅ Risk detection
✅ Dashboard summary
✅ Daftar artikel/post
✅ AI insight
✅ Strategic recommendation
✅ Source list
✅ Browser automation sebagai optional high-risk skill
✅ Role permission
✅ Agent settings khusus Super Admin
```

### Tidak Masuk MVP Awal

```txt
❌ Auto-post social media
❌ Auto-DM
❌ Follow/unfollow otomatis
❌ Realtime streaming penuh
❌ Crawling brutal tanpa batas
❌ Akses data private/non-publik
❌ Auto response tanpa approval
❌ Payment/billing
```

---

## 21. Acceptance Criteria

Modul dianggap berhasil jika:

```txt
1. User bisa membuka modul Media Monitoring.
2. User bisa input keyword.
3. Sistem bisa mengambil data dari portal berita nasional.
4. Sistem bisa mengambil data dari portal berita lokal Papua.
5. Sistem bisa mengambil hasil dari Google Search.
6. Sistem bisa mengambil insight Google Trends.
7. Sistem bisa mengambil data sosial media publik basic.
8. Sistem bisa menyimpan data ke database.
9. Sistem bisa menganalisis sentimen.
10. Sistem bisa mengklasifikasi isu.
11. Sistem bisa mendeteksi aktor/entity.
12. Sistem bisa memberi risk level.
13. Sistem bisa memberi rekomendasi respon.
14. Sistem menampilkan daftar sumber data.
15. Browser automation bisa diaktifkan/dinonaktifkan oleh Super Admin.
16. Browser automation hanya berjalan di domain whitelist.
17. Semua aksi browser automation tercatat di log.
18. Role selain Super Admin tidak bisa mengakses Agent Settings.
19. UI mengikuti tema putih, hitam, merah, compact, icon outline, dan dark mode.
```

---

## 22. Prioritas Development

### Phase 1 — Basic Monitoring

```txt
- UI Media Monitoring
- Input keyword
- Laravel endpoint run monitoring
- FastAPI workflow dasar
- News search nasional/lokal
- Simpan media items
- Tampilkan daftar data
```

### Phase 2 — AI Analysis

```txt
- Article extraction
- Sentiment analysis
- Issue classification
- Entity extraction
- Risk detection
- Recommendation
```

### Phase 3 — Source Expansion

```txt
- Google Search provider
- Google Trends provider
- Social media public search
- RSS feed
- Source whitelist
```

### Phase 4 — Browser Automation

```txt
- Playwright service
- Screenshot capture
- Extract page text
- Domain whitelist
- Browser action logs
- Approval control
```

### Phase 5 — Dashboard & Reporting

```txt
- Summary metrics
- Trend chart
- Top source
- Top actor
- Dominant issue
- Export report
```

---

## 23. Brief Pendek untuk Developer / Codex

```txt
Build the Media Monitoring module for the Political Intelligence Platform.

Stack:
React frontend, Laravel 11 backend, FastAPI AI service, PostgreSQL, Redis, Docker.

Flow:
Login → Module Landing Page → Media Monitoring → Input keyword → Run monitoring → collect data from social media, national news portals, local Papua news portals, Google Search, Google Trends, official sources, and other configured sources → analyze sentiment, issue category, actors, sources, trends, and reputational risk → show dashboard and source list.

Agent:
Create Media Monitoring Agent with sub-agents:
- Media Monitoring Orchestrator
- Source Discovery Agent
- News Search Agent
- Social Media Monitoring Agent
- Google Search Agent
- Google Trends Agent
- Article Extraction Agent
- Browser Automation Agent
- Sentiment Analysis Agent
- Issue Classification Agent
- Actor & Entity Extraction Agent
- Risk Detection Agent
- Trend Analysis Agent
- Response Recommendation Agent

Browser Automation:
Use Playwright/Puppeteer/Chrome CDP. Mark as High Risk Skill. Only Super Admin can enable it. Must use domain whitelist, action logs, screenshot logs, and approval for sensitive actions.

Permissions:
Only Super Admin can manage agent settings, skills, source whitelist, browser automation, API provider, model, API key, and model routing.
Other roles can only use the module based on permission.

UI:
White, black, red accent, compact Poppins font, outline icons, dark mode. Use compact dashboard cards, trend chart, article/post list, and AI insight section.
```
