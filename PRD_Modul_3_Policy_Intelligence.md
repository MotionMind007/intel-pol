# PRD Modul 3 — Policy Intelligence
## Riset Kebijakan & Simulasi Dampak
### SENA — Sentiment & Narrative Analytics

Versi: 1.0  
Stack utama: React + Laravel 11 + FastAPI  
Status: Modul ketiga setelah Screening Tokoh dan Media Monitoring

---

# 1. Ringkasan Modul

**Policy Intelligence** adalah modul untuk melakukan riset, analisis, dan simulasi dampak sebuah kebijakan pemerintah berdasarkan dokumen kebijakan, data publik, data media, respon masyarakat, opini stakeholder, serta analisis AI.

Modul ini digunakan untuk menjawab pertanyaan seperti:

```txt
- Apa isi utama kebijakan ini?
- Siapa kelompok masyarakat yang terdampak?
- Respon masyarakat terhadap kebijakan ini cenderung positif atau negatif?
- Kenapa responnya positif?
- Kenapa responnya negatif?
- Apa risiko implementasi kebijakan ini?
- Apa risiko politik dan reputasinya?
- Apa dampak sosial, ekonomi, politik, dan komunikasi publiknya?
- Bagaimana skenario jika kebijakan ini diterapkan?
- Bagaimana strategi komunikasi atau perbaikannya?
```

Modul ini bukan hanya membaca kebijakan, tapi juga membuat **simulasi dampak kebijakan** dan **rekomendasi strategis**.

---

# 2. Tujuan Modul

Tujuan utama Policy Intelligence:

```txt
1. Menganalisis kebijakan pemerintah yang sedang berjalan atau akan diterapkan.
2. Membaca respon masyarakat terhadap kebijakan tersebut.
3. Menentukan dampak positif dan negatif kebijakan.
4. Menjelaskan alasan kenapa dampak tersebut positif atau negatif.
5. Mensimulasikan beberapa skenario implementasi kebijakan.
6. Mendeteksi risiko sosial, politik, ekonomi, reputasi, dan implementasi.
7. Memberikan rekomendasi perbaikan kebijakan.
8. Memberikan rekomendasi strategi komunikasi publik.
9. Menggabungkan data dari dokumen resmi, media monitoring, media sosial, Google Search, Google Trends, survei internal, dan sumber lapangan.
```

---

# 3. Posisi Modul dalam Platform SENA

Urutan modul:

```txt
1. Screening Tokoh
   Menganalisis figur/tokoh politik.

2. Media Monitoring
   Mengumpulkan berita, sosial media, Google Search, Google Trends, dan sumber digital.

3. Policy Intelligence
   Menganalisis kebijakan dan mensimulasikan dampaknya berdasarkan data publik dan respon masyarakat.
```

Policy Intelligence dapat menggunakan data dari modul Media Monitoring agar tidak perlu scraping ulang semua sumber.

---

# 4. User Flow

```txt
User login
↓
Landing Page Modul
↓
Pilih Modul Policy Intelligence
↓
User input nama/topik kebijakan
↓
Opsional: user paste deskripsi/dokumen kebijakan
↓
Klik Analyze Policy
↓
Laravel validasi role dan request
↓
FastAPI menjalankan Policy Intelligence Agent
↓
Agent mencari dan membaca sumber kebijakan
↓
Agent mengumpulkan respon masyarakat
↓
Agent menganalisis sentimen, dampak, risiko, dan skenario
↓
Hasil disimpan ke database
↓
Frontend menampilkan laporan analisis kebijakan satu halaman rapi
```

---

# 5. Input Modul

## 5.1 Input Utama MVP

Untuk MVP, input dibuat sederhana:

```txt
Nama / Topik Kebijakan
```

Contoh:

```txt
Makan Bergizi Gratis di Papua
Kebijakan Otonomi Khusus Papua
Pemekaran DOB Papua
Kenaikan Pajak Kendaraan Bermotor
Pembatasan BBM Subsidi
Program Bantuan Sosial Pemerintah
Kebijakan Pendidikan Gratis
Kebijakan Dana Desa
```

## 5.2 Input Opsional

Untuk tahap lanjutan:

```txt
- Deskripsi kebijakan
- Wilayah
- Level kebijakan: nasional/provinsi/kabupaten/kota
- Tahun kebijakan
- Link dokumen kebijakan
- Upload dokumen PDF
- Tujuan simulasi
- Kelompok terdampak
- Rentang waktu monitoring respon publik
```

Untuk MVP awal:

```txt
Cukup input Nama / Topik Kebijakan.
```

---

# 6. Sumber Data Respon Masyarakat

Respon masyarakat tidak boleh hanya berdasarkan asumsi AI. Sistem harus mengambil data dari sumber yang jelas.

Data respon masyarakat dibagi menjadi beberapa lapis.

---

## 6.1 Media Sosial

Media sosial digunakan untuk membaca respon spontan publik.

Sumber:

```txt
- X / Twitter
- Facebook public page/post
- Instagram public post/comment
- TikTok public video/comment
- Threads
- YouTube comments
- Telegram public channel
```

Data yang dikumpulkan:

```txt
- Post publik
- Komentar publik
- Caption
- Hashtag
- Mention
- Jumlah like
- Jumlah share/repost
- Jumlah komentar
- Jumlah views jika tersedia
- Narasi pro
- Narasi kontra
- Keluhan dominan
- Dukungan dominan
```

Kegunaan:

```txt
- Membaca sentimen spontan
- Mendeteksi isu viral
- Mendeteksi kritik publik
- Mendeteksi keresahan masyarakat
- Mendeteksi narasi yang berkembang
```

Catatan penting:

```txt
Media sosial tidak selalu mewakili seluruh masyarakat.
Data sosial media harus dianggap sebagai digital public response, bukan opini seluruh populasi.
```

---

## 6.2 Portal Berita Nasional

Sumber:

```txt
- Kompas
- Detik
- Tempo
- CNN Indonesia
- CNBC Indonesia
- Antara
- Liputan6
- Kumparan
- Tirto
- Republika
- Media Indonesia
- Metro TV News
- Sindonews
- Okezone
```

Data yang dikumpulkan:

```txt
- Berita tentang kebijakan
- Pernyataan pejabat
- Kritik publik
- Opini pengamat
- Pernyataan akademisi
- Pernyataan LSM
- Pernyataan partai politik
- Kasus implementasi
```

Kegunaan:

```txt
- Membaca framing nasional
- Melihat sikap elite dan stakeholder
- Melihat isu implementasi yang masuk media nasional
```

---

## 6.3 Portal Berita Lokal Papua

Sumber lokal Papua penting karena sering menangkap isu lapangan lebih cepat dan detail.

Sumber:

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
- Media lokal lain yang ditambahkan Super Admin
```

Data yang dikumpulkan:

```txt
- Keluhan masyarakat lokal
- Respons tokoh adat
- Respons tokoh agama
- Respons pemerintah daerah
- Isu distribusi dan implementasi
- Dampak kebijakan di wilayah tertentu
- Konflik atau penerimaan masyarakat
```

Kegunaan:

```txt
- Membaca realitas lokal
- Menangkap isu spesifik Papua
- Mengukur sensitivitas sosial dan politik lokal
```

---

## 6.4 Google Search

Google Search digunakan untuk menemukan sumber publik yang luas.

Contoh query:

```txt
"kebijakan makan bergizi gratis" Papua kritik
"program makan bergizi gratis" respon masyarakat
"kebijakan Otsus Papua" dampak masyarakat
"pemekaran DOB Papua" pro kontra masyarakat
"kenaikan pajak kendaraan" keluhan warga
"bansos pemerintah" kritik penyaluran
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
- Bing Search API fallback
```

Catatan:

```txt
Tidak disarankan scraping langsung halaman Google secara agresif.
Gunakan API resmi atau search provider.
```

---

## 6.5 Google Trends

Google Trends digunakan untuk membaca minat pencarian publik.

Data yang dikumpulkan:

```txt
- Interest over time
- Related queries
- Related topics
- Wilayah dengan minat tertinggi
- Perbandingan beberapa keyword
```

Contoh:

```txt
Keyword utama: makan bergizi gratis
Compare: bansos, makan siang gratis, program gizi
Region: Indonesia / Papua
Time range: 7 hari, 30 hari, 12 bulan
```

Catatan penting:

```txt
Google Trends bukan data dukungan publik.
Google Trends bukan data elektabilitas.
Google Trends adalah indikator minat pencarian.
```

---

## 6.6 Survei Internal / Polling

Ini sumber paling kuat jika platform punya data lapangan.

Bentuk data:

```txt
- Survei online
- Survei lapangan
- Polling WhatsApp
- Polling Telegram
- Form aspirasi masyarakat
- Wawancara relawan
- Data CRM politik
```

Data yang dikumpulkan:

```txt
- Setuju/tidak setuju
- Alasan mendukung
- Alasan menolak
- Usia
- Jenis kelamin
- Pekerjaan
- Wilayah
- Prioritas masalah
```

Kegunaan:

```txt
- Validasi respon masyarakat
- Segmentasi kelompok terdampak
- Membandingkan data online vs data lapangan
```

---

## 6.7 Aspirasi dan Laporan Masyarakat

Sumber:

```txt
- Aduan publik
- Aspirasi DPRD
- Hasil reses
- Laporan relawan
- Notulen pertemuan masyarakat
- Forum warga
- Komentar di kanal resmi pemerintah
```

Kegunaan:

```txt
- Menangkap masalah implementasi
- Mengetahui keluhan langsung masyarakat
- Menyusun rekomendasi perbaikan kebijakan
```

---

## 6.8 Dokumen Resmi Pemerintah

Dokumen resmi digunakan untuk memahami kebijakan secara benar.

Sumber:

```txt
- Undang-undang
- Peraturan Pemerintah
- Perpres
- Permendagri
- Perda
- Pergub
- Perbup/Perwali
- RPJMN
- RPJMD
- RKPD
- APBD
- Renstra
- Dokumen kementerian/lembaga
- Data BPS
- Data pemerintah daerah
```

Data ini bukan respon masyarakat, tapi menjadi **policy source** dan dasar analisis.

---

# 7. Struktur Data Analisis

Policy Intelligence harus membagi data menjadi 4 lapis:

```txt
1. Policy Source
   Dokumen resmi, aturan, program, anggaran, tujuan kebijakan.

2. Public Response
   Komentar, berita, post, opini masyarakat, media sosial.

3. Stakeholder Response
   Pemerintah, akademisi, LSM, tokoh adat, tokoh agama, partai, media.

4. Impact Data
   Data statistik, ekonomi, sosial, wilayah, demografi, anggaran, dan implementasi.
```

---

# 8. Output Modul Policy Intelligence

Hasil analisis ditampilkan sebagai laporan satu halaman/kolom rapi, mirip modul Screening Tokoh.

Struktur output:

```txt
1. Ringkasan Eksekutif
2. Deskripsi Kebijakan
3. Tujuan Kebijakan
4. Kelompok Terdampak
5. Respon Masyarakat
6. Analisis Sentimen Publik
7. Dampak Positif
8. Dampak Negatif
9. Risiko Implementasi
10. Risiko Politik & Reputasi
11. Simulasi Skenario
12. Stakeholder Mapping
13. Policy Score
14. Rekomendasi Perbaikan Kebijakan
15. Strategi Komunikasi Publik
16. Sumber Data
```

---

## 8.1 Ringkasan Eksekutif

Berisi ringkasan 3–5 paragraf:

```txt
- Apa kebijakannya
- Respon publik dominan
- Dampak utama
- Risiko utama
- Rekomendasi inti
```

---

## 8.2 Deskripsi Kebijakan

Berisi:

```txt
- Nama kebijakan
- Level kebijakan
- Instansi/pemerintah yang menjalankan
- Wilayah penerapan
- Status kebijakan
- Sumber resmi
```

Jika data tidak tersedia:

```txt
Data resmi kebijakan belum ditemukan atau belum cukup kuat untuk disimpulkan.
```

---

## 8.3 Tujuan Kebijakan

Berisi:

```txt
- Tujuan formal
- Masalah yang ingin diselesaikan
- Target penerima manfaat
- Indikator keberhasilan jika tersedia
```

---

## 8.4 Kelompok Terdampak

Contoh kelompok:

```txt
- Masyarakat umum
- Pelajar
- Orang tua
- Petani
- Nelayan
- UMKM
- ASN
- Pelaku usaha
- Masyarakat adat
- Pemerintah daerah
- Sekolah
- Tenaga kesehatan
```

Format:

```txt
Kelompok:
Jenis dampak:
Tingkat dampak:
Catatan:
```

---

## 8.5 Respon Masyarakat

Berisi rangkuman respon publik dari berbagai sumber:

```txt
- Respon mendukung
- Respon menolak
- Respon netral
- Keluhan dominan
- Harapan masyarakat
- Narasi pro
- Narasi kontra
```

Wajib menyebut sumber:

```txt
Media sosial, portal berita, Google Search, survei internal, laporan masyarakat, atau sumber lain.
```

---

## 8.6 Analisis Sentimen Publik

Output:

```txt
Sentimen Umum: Positif / Netral / Negatif / Campuran

Positif:
- ...

Negatif:
- ...

Netral:
- ...
```

Catatan:

```txt
Sentimen publik dari data online tidak boleh dianggap mewakili seluruh populasi tanpa survei.
```

---

## 8.7 Dampak Positif

Wajib menjelaskan **kenapa positif**.

Format:

```txt
Dampak Positif:
1. ...
Kenapa positif:
- ...
Data pendukung:
- ...
```

Contoh:

```txt
Dampak positif: Kebijakan berpotensi meningkatkan akses layanan dasar.
Kenapa positif: Karena target penerima manfaat sesuai dengan masalah yang sering dikeluhkan masyarakat.
```

---

## 8.8 Dampak Negatif

Wajib menjelaskan **kenapa negatif**.

Format:

```txt
Dampak Negatif:
1. ...
Kenapa negatif:
- ...
Data pendukung:
- ...
```

Contoh:

```txt
Dampak negatif: Risiko kecemburuan sosial jika penerima bantuan tidak merata.
Kenapa negatif: Karena respon publik sering menyoroti ketidakjelasan data penerima dan potensi pilih kasih.
```

---

## 8.9 Risiko Implementasi

Kategori risiko:

```txt
- Distribusi
- Anggaran
- SDM
- Infrastruktur
- Data penerima
- Pengawasan
- Koordinasi antar lembaga
- Wilayah terpencil
- Korupsi/penyalahgunaan
```

Level:

```txt
Low
Medium
High
Critical
```

---

## 8.10 Risiko Politik & Reputasi

Berisi:

```txt
- Potensi serangan lawan politik
- Potensi framing negatif media
- Potensi kritik masyarakat
- Potensi isu viral
- Potensi konflik sosial
- Potensi turunnya kepercayaan publik
```

---

## 8.11 Simulasi Skenario

Minimal 3 skenario:

```txt
Skenario Optimis:
- Kebijakan diterima publik
- Implementasi berjalan baik
- Sentimen positif meningkat

Skenario Moderat:
- Kebijakan diterima sebagian
- Masalah teknis muncul
- Sentimen campuran

Skenario Buruk:
- Implementasi bermasalah
- Kritik publik meningkat
- Isu negatif dipakai lawan politik
```

Tambahkan indikator:

```txt
Public Acceptance Score
Implementation Risk
Political Risk
Social Impact
Media Risk
```

---

## 8.12 Stakeholder Mapping

Stakeholder:

```txt
- Pemerintah pusat
- Pemerintah daerah
- DPR/DPRD
- Partai politik
- Tokoh adat
- Tokoh agama
- Akademisi
- LSM
- Media
- Komunitas terdampak
- Kelompok oposisi
```

Format:

```txt
Stakeholder:
Posisi: Mendukung / Menolak / Netral / Belum jelas
Pengaruh: Low / Medium / High
Catatan:
```

---

## 8.13 Policy Score

Contoh indikator:

```txt
- Public Acceptance
- Social Benefit
- Implementation Feasibility
- Budget Risk
- Political Risk
- Media Risk
- Equity/Fairness
- Long-term Impact
```

Format:

```txt
Policy Score: 74/100
Kategori: Layak dengan perbaikan
Alasan:
- ...
```

Kategori:

```txt
85–100: Sangat Layak
70–84: Layak dengan Perbaikan
55–69: Perlu Kajian Lanjutan
<55: Risiko Tinggi
```

---

## 8.14 Rekomendasi Perbaikan Kebijakan

Format:

```txt
Prioritas Tinggi:
- ...

Prioritas Sedang:
- ...

Prioritas Rendah:
- ...
```

Contoh rekomendasi:

```txt
- Perjelas data penerima.
- Tambahkan mekanisme pengawasan publik.
- Perkuat koordinasi dengan pemerintah daerah.
- Buat kanal aduan masyarakat.
- Lakukan pilot project sebelum perluasan wilayah.
```

---

## 8.15 Strategi Komunikasi Publik

Berisi:

```txt
- Narasi utama
- Pesan untuk kelompok terdampak
- Kanal komunikasi
- Narasi untuk menjawab kritik
- Strategi klarifikasi
- Konten edukasi kebijakan
```

Contoh:

```txt
Narasi utama:
Kebijakan ini bukan sekadar program bantuan, tetapi upaya memperbaiki kualitas hidup masyarakat secara bertahap.

Kanal:
- Media lokal
- Media sosial
- Tokoh komunitas
- Forum warga
- Sekolah/kampung/distrik
```

---

## 8.16 Sumber Data

Format:

```txt
- Nama sumber
- Link
- Tanggal akses
- Jenis sumber
- Data yang digunakan
```

Jenis sumber:

```txt
- Dokumen resmi
- Media nasional
- Media lokal Papua
- Media sosial
- Google Search
- Google Trends
- Survei internal
- Laporan masyarakat
- Data statistik
```

---

# 9. UI Requirement

## 9.1 Tema

Mengikuti brand SENA:

```txt
Base color: White
Accent: Red
Text: Black / dark gray
Dark mode: Available
Font: Poppins
Icon: Outline
Font size: Compact
```

## 9.2 Layout Halaman Policy Intelligence

```txt
Header
↓
Input kebijakan
↓
Status analisis/loading
↓
Ringkasan metrik
↓
Laporan satu halaman/kolom
↓
Sumber data
```

## 9.3 Input UI

```txt
Policy Intelligence

Analisis dampak kebijakan dan respon publik berbasis AI.

[Nama / Topik Kebijakan.........................]

[Analyze Policy]
```

Placeholder:

```txt
Contoh: Makan Bergizi Gratis di Papua
```

## 9.4 Output UI

Hasil harus berbentuk laporan rapi, bukan banyak card.

Layout:

```txt
Policy Report
Nama kebijakan
Generated at

Ringkasan Eksekutif
...

1. Deskripsi Kebijakan
...

2. Tujuan Kebijakan
...
```

Metrik ringkas boleh memakai compact cards:

```txt
Public Acceptance: 72/100
Implementation Risk: Medium
Political Risk: High
Overall Policy Score: 74/100
```

---

# 10. Agent Detail

Nama agent utama:

```txt
Policy Intelligence Agent
```

Role:

```txt
AI agent yang bertugas melakukan riset kebijakan, membaca respon masyarakat, menganalisis dampak positif/negatif, mensimulasikan skenario implementasi, serta menyusun rekomendasi kebijakan dan komunikasi publik.
```

---

# 11. Struktur Multi-Agent

```txt
Policy Intelligence Orchestrator
├── Policy Source Discovery Agent
├── Policy Document Reader Agent
├── Public Response Collector Agent
├── Social Sentiment Agent
├── Media Framing Agent
├── Stakeholder Mapping Agent
├── Impact Analysis Agent
├── Scenario Simulation Agent
├── Risk Analysis Agent
├── Policy Scoring Agent
└── Recommendation Agent
```

---

## 11.1 Policy Intelligence Orchestrator

Tugas:

```txt
- Menerima input kebijakan
- Menentukan workflow analisis
- Memanggil sub-agent
- Menggabungkan hasil
- Menyusun output akhir
```

Skill:

```txt
- Workflow Routing
- Task Planning
- Result Aggregation
- Model Routing
```

Model:

```txt
Medium/heavy reasoning model
```

Risk:

```txt
Medium
```

---

## 11.2 Policy Source Discovery Agent

Tugas:

```txt
- Mencari dokumen resmi kebijakan
- Menemukan sumber aturan/program
- Mencari berita kebijakan
- Mencari data pendukung
```

Skill:

```txt
- Web Search
- Government Source Search
- Source Collector
```

Teknologi:

```txt
- Google Custom Search API
- SerpAPI
- Tavily
- Bing Search API
- Website resmi pemerintah
```

Risk:

```txt
Medium
```

---

## 11.3 Policy Document Reader Agent

Tugas:

```txt
- Membaca dokumen kebijakan
- Meringkas isi kebijakan
- Menemukan tujuan, target, anggaran, dan indikator
- Membaca PDF/dokumen resmi
```

Skill:

```txt
- Document Reader
- PDF Reader
- OCR optional
- Policy Summarizer
```

Teknologi:

```txt
- PyMuPDF
- pdfplumber
- Unstructured
- OCR jika perlu
```

Risk:

```txt
Low-Medium
```

---

## 11.4 Public Response Collector Agent

Tugas:

```txt
- Mengambil respon publik dari media sosial, berita, Google Search, laporan internal
- Mengelompokkan respon pro/kontra/netral
- Menyimpan bukti sumber
```

Skill:

```txt
- Social Search
- News Search
- Comment Extraction
- Source Collector
```

Risk:

```txt
Medium/High jika memakai browser automation login
```

---

## 11.5 Social Sentiment Agent

Tugas:

```txt
- Menilai sentimen komentar/post publik
- Menemukan narasi pro dan kontra
- Menilai intensitas emosi publik
```

Skill:

```txt
- Sentiment Analysis
- Emotion Detection
- Narrative Extraction
```

Model:

```txt
Light/medium model
```

Risk:

```txt
Low
```

---

## 11.6 Media Framing Agent

Tugas:

```txt
- Membaca framing media terhadap kebijakan
- Menilai apakah media cenderung mendukung, netral, atau kritis
- Menemukan headline/narasi dominan
```

Skill:

```txt
- Media Framing Analysis
- Headline Analysis
- Narrative Mapping
```

Model:

```txt
Medium model
```

Risk:

```txt
Medium
```

---

## 11.7 Stakeholder Mapping Agent

Tugas:

```txt
- Mengidentifikasi stakeholder
- Menentukan posisi stakeholder
- Menilai pengaruh stakeholder terhadap kebijakan
```

Skill:

```txt
- Entity Extraction
- Stakeholder Mapping
- Influence Scoring
```

Model:

```txt
Medium model
```

Risk:

```txt
Medium
```

---

## 11.8 Impact Analysis Agent

Tugas:

```txt
- Menganalisis dampak positif
- Menganalisis dampak negatif
- Menjelaskan alasan dampak
- Menghubungkan dampak dengan data/respon masyarakat
```

Skill:

```txt
- Policy Impact Analysis
- Social Impact Analysis
- Economic Impact Analysis
- Political Impact Analysis
```

Model:

```txt
Reasoning model
```

Risk:

```txt
Medium
```

---

## 11.9 Scenario Simulation Agent

Tugas:

```txt
- Membuat skenario optimis
- Membuat skenario moderat
- Membuat skenario buruk
- Menilai kemungkinan risiko tiap skenario
```

Skill:

```txt
- Scenario Simulation
- Risk Forecasting
- Implementation Modeling
```

Model:

```txt
Heavy reasoning model
```

Risk:

```txt
Medium
```

---

## 11.10 Risk Analysis Agent

Tugas:

```txt
- Menilai risiko implementasi
- Menilai risiko politik
- Menilai risiko reputasi
- Menilai risiko sosial
```

Skill:

```txt
- Risk Scoring
- Crisis Detection
- Political Risk Analysis
```

Model:

```txt
Reasoning model
```

Risk:

```txt
Medium
```

---

## 11.11 Policy Scoring Agent

Tugas:

```txt
- Menghitung skor kebijakan
- Memberi kategori
- Menjelaskan alasan skor
```

Skill:

```txt
- Policy Scoring
- Weighted Scoring
- Evidence-Based Assessment
```

Model:

```txt
Medium/reasoning model
```

Risk:

```txt
Medium
```

---

## 11.12 Recommendation Agent

Tugas:

```txt
- Memberi rekomendasi perbaikan kebijakan
- Memberi rekomendasi strategi komunikasi
- Memberi prioritas aksi
```

Skill:

```txt
- Policy Recommendation
- Strategic Communication
- Crisis Mitigation
```

Model:

```txt
Reasoning model
```

Risk:

```txt
Medium
```

---

# 12. Skill Mapping

| Skill | Fungsi | Teknologi | Risk |
|---|---|---|---|
| Web Search | Cari sumber publik | Google CSE, SerpAPI, Tavily | Medium |
| Government Source Search | Cari dokumen resmi | Search API, crawler whitelist | Medium |
| Document Reader | Baca dokumen kebijakan | PyMuPDF, pdfplumber, Unstructured | Low-Medium |
| PDF OCR | Baca PDF scan | OCR | Medium |
| Social Response Collector | Ambil respon publik | API, browser automation | Medium/High |
| News Monitoring | Ambil berita | Media Monitoring DB, search API | Medium |
| Google Trends Analysis | Minat pencarian | Pytrends, API/fallback | Medium |
| Sentiment Analysis | Analisis sentimen | LLM/NLP | Low |
| Narrative Extraction | Ambil narasi pro/kontra | LLM | Low-Medium |
| Stakeholder Mapping | Pemetaan stakeholder | LLM/NER | Medium |
| Impact Simulation | Simulasi dampak | Reasoning model | Medium |
| Risk Scoring | Skor risiko | Reasoning model | Medium |
| Policy Scoring | Skor kebijakan | Weighted scoring + LLM | Medium |
| Recommendation | Rekomendasi kebijakan | Reasoning model | Medium |
| Report Generator | Susun laporan | LLM | Low |

---

# 13. Browser Automation

Policy Intelligence dapat menggunakan browser automation jika sumber tidak bisa diakses lewat API/parser.

Teknologi:

```txt
- Playwright
- Puppeteer
- Chrome CDP
```

Risk level:

```txt
High
```

Batasan:

```txt
- Hanya Super Admin yang bisa mengaktifkan.
- Wajib domain whitelist.
- Wajib action log.
- Wajib screenshot log jika capture.
- Tidak boleh submit form sensitif.
- Tidak boleh auto post.
- Tidak boleh kirim pesan.
- Tidak boleh ubah data akun.
- Tidak boleh transaksi/pembayaran.
- Tidak boleh akses data private/non-publik.
```

Allowed actions:

```txt
✅ open_url
✅ search keyword
✅ scroll
✅ click navigation
✅ screenshot
✅ extract text
```

Blocked by default:

```txt
❌ submit form
❌ post content
❌ send message
❌ change profile
❌ delete content
❌ purchase/payment
```

---

# 14. Model Routing

Policy Intelligence membutuhkan multi-model routing agar hemat biaya.

## 14.1 Routing Task

```txt
policy_source_search:
- light/medium model + search API

document_summary:
- medium model

public_response_collection:
- light/medium model

sentiment_analysis:
- light/medium model

media_framing:
- medium model

impact_analysis:
- reasoning model

scenario_simulation:
- heavy reasoning model

risk_analysis:
- reasoning model

policy_scoring:
- medium/reasoning model

final_report:
- medium model
```

## 14.2 Super Admin Control

Hanya Super Admin yang bisa mengatur:

```txt
- Provider
- Base URL
- API key
- Model per task
- Fallback model
- Cost limit
- Token limit
- Skill aktif/nonaktif
```

---

# 15. Database Design

## 15.1 policy_research_requests

```txt
id
user_id
agent_id
policy_topic
status
created_at
updated_at
```

## 15.2 policy_sources

```txt
id
request_id
source_type
source_name
url
title
published_at
content_text
raw_json
created_at
updated_at
```

source_type:

```txt
official_document
news_national
news_local
social_media
google_search
google_trends
survey_internal
community_report
statistics
other
```

## 15.3 public_responses

```txt
id
request_id
source_id
platform
author_or_account
content_text
url
published_at
engagement_json
sentiment
sentiment_score
response_type
created_at
updated_at
```

response_type:

```txt
support
oppose
neutral
question
complaint
suggestion
```

## 15.4 policy_stakeholders

```txt
id
request_id
name
type
position
influence_level
notes
created_at
updated_at
```

type:

```txt
government
political_party
community
academic
ngo
media
religious_leader
traditional_leader
business
citizen_group
other
```

position:

```txt
support
oppose
neutral
unclear
```

## 15.5 policy_impact_analyses

```txt
id
request_id
positive_impact_json
negative_impact_json
implementation_risk_json
political_risk_json
reputation_risk_json
scenario_json
policy_score_json
recommendation_json
created_at
updated_at
```

## 15.6 policy_reports

```txt
id
request_id
executive_summary
result_json
result_markdown
sources_json
final_score
risk_level
created_at
updated_at
```

## 15.7 policy_agent_logs

```txt
id
user_id
agent_id
request_id
skill_id
action
input_json
output_json
status
error_message
created_at
```

---

# 16. API Endpoint

## 16.1 Laravel API

```txt
GET    /api/policy-intelligence
POST   /api/policy-intelligence/analyze
GET    /api/policy-intelligence/reports
GET    /api/policy-intelligence/reports/{id}
DELETE /api/policy-intelligence/reports/{id}
```

## 16.2 FastAPI AI Service

```txt
POST /ai/policy-intelligence/analyze
POST /ai/policy-intelligence/source-search
POST /ai/policy-intelligence/document-read
POST /ai/policy-intelligence/public-response
POST /ai/policy-intelligence/sentiment
POST /ai/policy-intelligence/media-framing
POST /ai/policy-intelligence/stakeholder-map
POST /ai/policy-intelligence/impact-analysis
POST /ai/policy-intelligence/scenario-simulation
POST /ai/policy-intelligence/risk-analysis
POST /ai/policy-intelligence/recommendation
```

---

# 17. Backend Workflow

```txt
React
↓
POST /api/policy-intelligence/analyze
↓
Laravel validate user & permission
↓
Laravel create policy research request
↓
Laravel dispatch queue job
↓
FastAPI Policy Intelligence Orchestrator
↓
Search policy source
↓
Read official document if available
↓
Collect public response
↓
Analyze sentiment
↓
Analyze media framing
↓
Map stakeholders
↓
Analyze impact
↓
Simulate scenarios
↓
Score policy
↓
Generate recommendation
↓
Save result to PostgreSQL
↓
React displays report
```

---

# 18. Agent Settings

Hanya Super Admin yang bisa mengakses Agent Settings.

```txt
Agent Settings
├── Policy Intelligence Agent
│   ├── General
│   ├── Prompt
│   ├── Skills
│   ├── Sources
│   ├── Browser Automation
│   ├── Model Routing
│   ├── Scoring Formula
│   └── Logs
```

## 18.1 Scoring Formula

Super Admin dapat mengatur bobot:

```txt
Public Acceptance: 20%
Social Benefit: 20%
Implementation Feasibility: 20%
Political Risk: 15%
Budget Risk: 10%
Media Risk: 10%
Equity/Fairness: 5%
```

---

# 19. Permission

## Super Admin

```txt
✅ Akses semua laporan
✅ Run analisis kebijakan
✅ Atur Policy Intelligence Agent
✅ Atur skill
✅ Atur source whitelist
✅ Atur browser automation
✅ Atur provider/model/API key
✅ Atur scoring formula
✅ Lihat semua logs
```

## Admin

```txt
✅ Run analisis kebijakan
✅ Lihat laporan tim
✅ Export report jika diizinkan
❌ Tidak bisa akses Agent Settings
```

## Analyst

```txt
✅ Run analisis kebijakan
✅ Lihat laporan sendiri
❌ Tidak bisa akses Agent Settings
```

## Viewer

```txt
✅ Lihat laporan yang dibagikan
❌ Tidak bisa run analisis jika tidak diberi izin
❌ Tidak bisa akses Agent Settings
```

---

# 20. Prompt Dasar Policy Intelligence Agent

```txt
You are a Policy Intelligence Analyst.

Your task is to analyze government policy based on official policy sources, public response, media coverage, social media signals, stakeholder opinion, and available impact data.

Rules:
- Do not fabricate facts.
- Separate verified facts, allegations, opinions, and assumptions.
- Always explain why an impact is positive or negative.
- Always mention the data basis for public response.
- Social media response must be described as digital public response, not as a full population survey.
- Google Trends must be described as search-interest data, not public support.
- If official policy data is unavailable, clearly say it is unavailable.
- Provide scenario simulation: optimistic, moderate, and bad scenario.
- Provide policy score with reasoning.
- Provide strategic recommendations.
- Always include source references when available.
- Return structured JSON only.
```

---

# 21. JSON Output Format

```json
{
  "policy_topic": "Makan Bergizi Gratis di Papua",
  "executive_summary": "...",
  "policy_description": {
    "name": "...",
    "level": "national/provincial/local",
    "status": "...",
    "implementing_body": "...",
    "official_sources": []
  },
  "policy_objectives": [],
  "affected_groups": [
    {
      "group": "Pelajar",
      "impact_type": "direct",
      "impact_level": "high",
      "notes": "..."
    }
  ],
  "public_response": {
    "summary": "...",
    "supporting_narratives": [],
    "opposing_narratives": [],
    "neutral_narratives": [],
    "data_sources": []
  },
  "sentiment_analysis": {
    "dominant": "mixed",
    "positive": 0,
    "neutral": 0,
    "negative": 0,
    "notes": "..."
  },
  "positive_impacts": [
    {
      "impact": "...",
      "why_positive": "...",
      "supporting_data": []
    }
  ],
  "negative_impacts": [
    {
      "impact": "...",
      "why_negative": "...",
      "supporting_data": []
    }
  ],
  "implementation_risks": [
    {
      "risk": "...",
      "level": "medium",
      "reason": "..."
    }
  ],
  "political_reputation_risk": {
    "level": "high",
    "reason": "..."
  },
  "scenario_simulation": {
    "optimistic": "...",
    "moderate": "...",
    "bad": "..."
  },
  "stakeholders": [
    {
      "name": "...",
      "type": "...",
      "position": "support/oppose/neutral/unclear",
      "influence": "low/medium/high"
    }
  ],
  "policy_score": {
    "score": 74,
    "category": "Layak dengan Perbaikan",
    "reason": "..."
  },
  "policy_improvement_recommendations": {
    "high_priority": [],
    "medium_priority": [],
    "low_priority": []
  },
  "public_communication_strategy": {
    "main_narrative": "...",
    "target_messages": [],
    "channels": [],
    "response_to_criticism": []
  },
  "sources": []
}
```

---

# 22. MVP Scope

## Masuk MVP

```txt
✅ Input nama/topik kebijakan
✅ Search sumber kebijakan
✅ Search respon masyarakat dari berita dan sumber publik
✅ Integrasi data Media Monitoring jika tersedia
✅ Google Search
✅ Google Trends basic
✅ Analisis sentimen
✅ Analisis dampak positif
✅ Analisis dampak negatif
✅ Penjelasan kenapa positif/negatif
✅ Simulasi 3 skenario
✅ Risk scoring
✅ Policy scoring
✅ Rekomendasi perbaikan kebijakan
✅ Strategi komunikasi publik
✅ Source list
✅ Simpan laporan
✅ Role permission
✅ Agent Settings khusus Super Admin
```

## Tidak Masuk MVP Awal

```txt
❌ Simulasi ekonomi kuantitatif kompleks
❌ Prediksi statistik mendalam
❌ Real-time policy monitoring
❌ Auto alert Telegram
❌ Auto response publik
❌ Data private/non-publik
❌ Crawling agresif tanpa batas
❌ Payment/billing
```

---

# 23. Acceptance Criteria

Modul dianggap berhasil jika:

```txt
1. User bisa membuka modul Policy Intelligence.
2. User bisa input nama/topik kebijakan.
3. Sistem bisa mencari sumber kebijakan.
4. Sistem bisa mengambil respon masyarakat dari berita/media publik.
5. Sistem bisa mengambil Google Search result.
6. Sistem bisa mengambil Google Trends insight basic.
7. Sistem bisa menganalisis sentimen publik.
8. Sistem bisa menjelaskan dampak positif dan alasannya.
9. Sistem bisa menjelaskan dampak negatif dan alasannya.
10. Sistem bisa membuat simulasi skenario optimis, moderat, dan buruk.
11. Sistem bisa memberi risk level.
12. Sistem bisa memberi policy score.
13. Sistem bisa memberi rekomendasi perbaikan kebijakan.
14. Sistem bisa memberi strategi komunikasi publik.
15. Sistem menampilkan sumber data.
16. Hasil tersimpan di database.
17. Role selain Super Admin tidak bisa akses Agent Settings.
18. UI mengikuti tema SENA: putih, hitam, merah, Poppins, compact, dark mode.
```

---

# 24. Prioritas Development

## Phase 1 — Basic Policy Analysis

```txt
- UI Policy Intelligence
- Input topik kebijakan
- Laravel endpoint analyze
- FastAPI workflow dasar
- Search sumber publik
- Simpan request dan report
```

## Phase 2 — Public Response & Sentiment

```txt
- Ambil data dari Media Monitoring DB
- Search berita tambahan
- Google Search
- Google Trends basic
- Sentiment analysis
```

## Phase 3 — Impact & Scenario

```txt
- Positive/negative impact analysis
- Implementation risk
- Political/reputation risk
- Scenario simulation
- Policy score
```

## Phase 4 — Recommendation & Report

```txt
- Rekomendasi perbaikan kebijakan
- Strategi komunikasi publik
- Source list
- Report view satu halaman
- Export report
```

## Phase 5 — Agent Settings

```txt
- Policy Intelligence Agent settings
- Skill toggle
- Source whitelist
- Browser automation setting
- Model routing
- Scoring formula
- Logs
```

---

# 25. Brief Pendek untuk Developer / Codex

```txt
Build the third module for SENA: Policy Intelligence.

Stack:
React frontend, Laravel 11 backend, FastAPI AI service, PostgreSQL, Redis, Docker.

Flow:
Login → Module Landing Page → Policy Intelligence → Input policy topic → Analyze Policy → search official policy sources, public response, media coverage, social media signals, Google Search, Google Trends, and internal monitoring data → analyze positive impact, negative impact, public sentiment, implementation risk, political/reputation risk → simulate optimistic, moderate, and bad scenarios → generate policy score, improvement recommendations, and public communication strategy.

UI:
White, black, red accent, compact Poppins font, outline icons, dark mode. Result should be rendered as a clean one-column policy report, not many separate cards.

Agent:
Create Policy Intelligence Agent with sub-agents:
- Policy Intelligence Orchestrator
- Policy Source Discovery Agent
- Policy Document Reader Agent
- Public Response Collector Agent
- Social Sentiment Agent
- Media Framing Agent
- Stakeholder Mapping Agent
- Impact Analysis Agent
- Scenario Simulation Agent
- Risk Analysis Agent
- Policy Scoring Agent
- Recommendation Agent

Permissions:
Only Super Admin can manage agent settings, skill settings, source whitelist, browser automation, provider, model, API key, model routing, and scoring formula.
Other roles can use the module based on permission only.
```
