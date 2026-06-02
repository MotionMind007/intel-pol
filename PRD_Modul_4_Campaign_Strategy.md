# PRD Modul 4 — Campaign Strategy
## Strategic Campaign Intelligence
### SENA — Sentiment & Narrative Analytics

Versi: 1.0  
Stack utama: **React + Laravel 11 + FastAPI**  
Status: Modul keempat setelah **Screening Tokoh**, **Media Monitoring**, dan **Policy Intelligence**

---

## 1. Ringkasan Modul

**Campaign Strategy** adalah modul untuk menyusun strategi kampanye politik berbasis data dan AI.

Modul ini tidak hanya digunakan untuk tokoh/kandidat, tetapi juga untuk:

1. **Tokoh / kandidat**
2. **Partai politik**
3. **Kebijakan / program pemerintah**
4. **Isu atau gerakan politik**

Modul ini mengubah hasil analisis dari modul sebelumnya menjadi rencana strategi yang lebih operasional, mulai dari positioning, target audiens, narasi, pesan kunci, strategi wilayah, strategi media, kampanye darat, mitigasi isu negatif, rekomendasi konten, timeline aksi, sampai KPI.

---

## 2. Tujuan Modul

Tujuan utama Campaign Strategy:

1. Membuat strategi kampanye untuk tokoh, partai, kebijakan, atau isu.
2. Menentukan positioning yang paling kuat.
3. Menentukan target pemilih atau target audiens.
4. Menentukan isu prioritas yang harus diangkat.
5. Menentukan isu yang harus dihindari atau dimitigasi.
6. Membuat narasi utama dan pesan kunci.
7. Membuat strategi wilayah.
8. Membuat strategi media sosial dan media lokal.
9. Membuat strategi kampanye darat.
10. Memberi rekomendasi konten.
11. Membuat action plan kampanye.
12. Menentukan indikator keberhasilan.

---

## 3. Posisi Modul dalam Platform SENA

Urutan modul:

```txt
1. Screening Tokoh
   Menganalisis tokoh, kekuatan, kelemahan, kontroversi, elektabilitas, dan SWOT.

2. Media Monitoring
   Mengumpulkan berita, media sosial, Google Search, Google Trends, dan sumber digital.

3. Policy Intelligence
   Menganalisis kebijakan, respon masyarakat, dampak, risiko, dan simulasi skenario.

4. Campaign Strategy
   Mengubah semua insight menjadi strategi kampanye yang bisa dijalankan.
```

Campaign Strategy menjawab:

```txt
- Sekarang harus melakukan apa?
- Narasi apa yang harus dibawa?
- Segmen mana yang harus disasar?
- Wilayah mana yang harus diprioritaskan?
- Isu negatif dijawab bagaimana?
- Konten apa yang harus dibuat?
```

---

## 4. Target Objek Kampanye

### 4.1 Tokoh / Kandidat

Contoh:

```txt
Jhony Banua Rouw
Calon Wali Kota Jayapura
Calon Gubernur Papua
Tokoh muda partai
Anggota DPR/DPRD
```

Fokus analisis:

```txt
- Personal branding
- Rekam jejak
- Elektabilitas
- Basis daerah
- Kontroversi
- Narasi personal
- Strategi menaikkan citra
- Strategi mitigasi serangan personal
```

### 4.2 Partai Politik

Contoh:

```txt
PSI Papua
NasDem Papua
PDIP Papua
Golkar Papua
Partai lokal/organisasi politik
```

Fokus analisis:

```txt
- Brand partai
- Positioning ideologi/gagasan
- Persepsi publik terhadap partai
- Segmentasi pemilih
- Basis wilayah
- Isu prioritas partai
- Tokoh/kader lokal
- Strategi rekrutmen simpatisan
- Strategi memperluas penerimaan publik
```

### 4.3 Kebijakan / Program

Contoh:

```txt
Makan Bergizi Gratis
Otonomi Khusus Papua
Pemekaran DOB
Bantuan sosial
Pendidikan gratis
Pajak daerah
```

Fokus analisis:

```txt
- Manfaat program
- Resistensi publik
- Narasi edukasi
- Stakeholder terdampak
- Strategi sosialisasi
- Mitigasi kritik
- Rekomendasi komunikasi kebijakan
```

### 4.4 Isu atau Gerakan Politik

Contoh:

```txt
Isu pendidikan Papua
Isu air bersih Jayapura
Isu lapangan kerja anak muda
Isu transparansi anggaran
Isu infrastruktur
```

Fokus analisis:

```txt
- Pemetaan isu
- Narasi publik
- Kelompok pendukung/penolak
- Risiko eskalasi
- Peluang kampanye
- Strategi framing
```

---

## 5. User Flow

```txt
User login
↓
Landing Page Modul
↓
Pilih Modul Campaign Strategy
↓
User memilih tipe objek kampanye
↓
User input objek kampanye
↓
User input tujuan kampanye
↓
Opsional: input wilayah
↓
Klik Generate Strategy
↓
Laravel validasi role dan request
↓
FastAPI menjalankan Campaign Strategy Agent
↓
Agent mengambil insight dari modul lain
↓
Agent menyusun positioning, segmentasi, narasi, strategi wilayah, mitigasi, dan action plan
↓
Hasil disimpan ke database
↓
Frontend menampilkan Strategic Campaign Report
```

---

## 6. Input Modul

### 6.1 Input MVP

Untuk MVP, input dibuat sederhana tapi tetap fleksibel.

```txt
1. Tipe Objek Kampanye
2. Objek Kampanye
3. Tujuan Kampanye
4. Wilayah
```

### 6.2 Tipe Objek Kampanye

Dropdown:

```txt
- Tokoh / Kandidat
- Partai Politik
- Kebijakan / Program
- Isu Politik
```

### 6.3 Objek Kampanye

Contoh:

```txt
Jhony Banua Rouw
PSI Papua
Makan Bergizi Gratis
Isu air bersih Jayapura
```

### 6.4 Tujuan Kampanye

Contoh:

```txt
Menaikkan elektabilitas
Meningkatkan penerimaan publik
Memperkuat citra partai
Menjawab isu negatif
Mengedukasi masyarakat
Memperkuat basis anak muda
Memperluas dukungan di wilayah tertentu
```

### 6.5 Wilayah

Contoh:

```txt
Papua
Kota Jayapura
Kabupaten Jayapura
Papua Pegunungan
Papua Tengah
Indonesia
```

### 6.6 Input Opsional Tahap Lanjutan

```txt
- Target pemilih
- Durasi kampanye
- Anggaran kampanye
- Platform prioritas
- Isu yang ingin diangkat
- Isu yang ingin dihindari
- Tone komunikasi
- Data survei internal
- Link laporan screening/media/policy
```

---

## 7. Output Modul Campaign Strategy

Hasil harus berbentuk laporan strategi satu halaman/kolom rapi, bukan banyak card terpisah.

Struktur output:

```txt
1. Ringkasan Strategi
2. Konteks Kampanye
3. Tujuan Kampanye
4. Analisis Situasi
5. Positioning Tokoh/Partai/Kebijakan/Isu
6. Target Audiens / Segmentasi Pemilih
7. Isu Prioritas
8. Narasi Utama
9. Pesan Kunci
10. Strategi Wilayah
11. Strategi Media Sosial
12. Strategi Media Lokal & PR
13. Strategi Kampanye Darat
14. Mitigasi Isu Negatif
15. Rekomendasi Konten
16. Rencana Aksi / Timeline
17. Indikator Keberhasilan
18. Risiko & Catatan Strategis
19. Sumber Data
```

---

## 8. Detail Output

### 8.1 Ringkasan Strategi

Berisi ringkasan 3–5 paragraf:

```txt
- Strategi utama
- Segmen prioritas
- Isu utama
- Risiko utama
- Rekomendasi aksi cepat
```

### 8.2 Konteks Kampanye

Berisi:

```txt
- Objek kampanye
- Jenis objek: tokoh/partai/kebijakan/isu
- Wilayah
- Kondisi politik/media saat ini
- Data yang digunakan
```

### 8.3 Tujuan Kampanye

Contoh tujuan:

```txt
- Menaikkan elektabilitas tokoh
- Memperkuat brand partai
- Meningkatkan penerimaan kebijakan
- Mengurangi sentimen negatif
- Menguasai isu tertentu
- Meningkatkan dukungan anak muda
```

### 8.4 Analisis Situasi

Menggunakan data dari modul lain:

```txt
Screening Tokoh:
- SWOT
- elektabilitas
- kontroversi
- basis daerah

Media Monitoring:
- sentimen
- isu dominan
- aktor yang muncul
- media aktif

Policy Intelligence:
- respon publik
- dampak kebijakan
- risiko implementasi
```

Jika data belum tersedia:

```txt
Data modul terkait belum tersedia. Analisis dibuat berdasarkan input user dan sumber publik yang ditemukan.
```

### 8.5 Positioning

Positioning menyesuaikan objek kampanye.

Jika objeknya tokoh:

```txt
Tokoh diposisikan sebagai figur berpengalaman, dekat dengan masyarakat, dan memiliki kemampuan menyelesaikan masalah lokal.
```

Jika objeknya partai:

```txt
Partai diposisikan sebagai kekuatan politik yang membawa gagasan segar, dekat dengan anak muda, dan fokus pada isu pelayanan publik.
```

Jika objeknya kebijakan:

```txt
Kebijakan diposisikan sebagai solusi konkret terhadap masalah masyarakat, dengan penekanan pada manfaat langsung dan transparansi implementasi.
```

Jika objeknya isu:

```txt
Isu diposisikan sebagai agenda publik yang relevan, dekat dengan kebutuhan masyarakat, dan dapat menjadi basis gerakan komunikasi.
```

### 8.6 Target Audiens / Segmentasi Pemilih

Contoh segmentasi:

```txt
- Pemilih muda
- Pemilih perempuan
- Pemilih urban
- Pemilih pedesaan
- Komunitas adat
- Tokoh agama
- Pelaku UMKM
- ASN
- Mahasiswa
- Pekerja informal
- Orang tua siswa
- Relawan komunitas
```

Format output:

```txt
Segmen:
Kebutuhan:
Masalah utama:
Pesan yang cocok:
Kanal komunikasi:
Prioritas:
```

### 8.7 Isu Prioritas

Isu yang bisa diangkat:

```txt
- Air bersih
- Pendidikan
- Lapangan kerja
- Kesehatan
- Infrastruktur
- Transparansi anggaran
- Pelayanan publik
- Anak muda
- UMKM
- Keamanan
- Adat dan budaya
- Otsus
- DOB
```

Format:

```txt
Isu prioritas:
Alasan:
Risiko:
Narasi:
```

### 8.8 Narasi Utama

Contoh:

```txt
Membawa politik yang lebih dekat dengan kebutuhan masyarakat.
```

Atau:

```txt
Bukan sekadar hadir saat kampanye, tetapi bekerja untuk masalah nyata warga.
```

Untuk partai:

```txt
Partai yang membawa suara baru, energi muda, dan solusi konkret untuk Papua.
```

### 8.9 Pesan Kunci

Format:

```txt
Pesan:
Target:
Alasan:
Kanal:
```

Contoh:

```txt
Pesan:
Fokus pada air bersih, pendidikan, dan lapangan kerja.

Target:
Pemilih urban dan keluarga muda.

Alasan:
Isu ini dekat dengan kebutuhan harian masyarakat.
```

### 8.10 Strategi Wilayah

Berisi:

```txt
- Wilayah prioritas
- Wilayah basis kuat
- Wilayah lemah
- Wilayah swing
- Strategi per wilayah
- Agenda lapangan per wilayah
```

Format:

```txt
Wilayah:
Status: Basis kuat / Lemah / Swing / Potensial
Strategi:
Aksi:
Risiko:
```

### 8.11 Strategi Media Sosial

Berisi:

```txt
- Platform prioritas
- Gaya konten
- Frekuensi posting
- Format konten
- Narasi harian
- Hashtag
- Komunitas digital target
```

Platform:

```txt
- Instagram
- TikTok
- X / Twitter
- Facebook
- Threads
- YouTube Shorts
```

Format konten:

```txt
- Video pendek
- Carousel edukasi
- Testimoni masyarakat
- Klarifikasi isu
- Behind the scene
- Program highlight
- Live discussion
```

### 8.12 Strategi Media Lokal & PR

Berisi:

```txt
- Media lokal prioritas
- Sudut pemberitaan
- Agenda press release
- Tokoh pendukung yang perlu ditampilkan
- Isu yang cocok masuk media lokal
- Strategi merespons berita negatif
```

Contoh media Papua:

```txt
- Cenderawasih Pos
- Jubi
- Kabar Papua
- RRI Jayapura
- Antara Papua
- PapuaSatu
- Teras Papua
```

### 8.13 Strategi Kampanye Darat

Berisi:

```txt
- Pertemuan komunitas
- Kunjungan wilayah
- Diskusi warga
- Forum anak muda
- Kegiatan sosial
- Relawan door-to-door
- Posko aspirasi
- Temu tokoh adat/agama
```

Format:

```txt
Aktivitas:
Target:
Tujuan:
Wilayah:
Output yang diharapkan:
```

### 8.14 Mitigasi Isu Negatif

Format:

```txt
Isu negatif:
Potensi serangan:
Risiko:
Respons utama:
Narasi tandingan:
Bukti pendukung:
Kanal respons:
```

Contoh:

```txt
Isu:
Dicap sebagai elite lama.

Respons:
Tekankan pengalaman sebagai modal menyelesaikan masalah, bukan sekadar status politik lama.

Narasi tandingan:
Pengalaman yang terbukti lebih penting daripada janji kosong.
```

### 8.15 Rekomendasi Konten

Format:

```txt
Hook:
Format:
Target:
Pesan:
CTA:
```

Contoh:

```txt
Hook:
Kenapa air bersih masih jadi masalah utama warga?

Format:
Video pendek 45 detik

Target:
Pemilih urban dan keluarga muda

Pesan:
Kampanye harus bicara masalah nyata warga.
```

### 8.16 Rencana Aksi / Timeline

Untuk MVP, timeline sederhana 30 hari.

```txt
Minggu 1:
- Audit isu
- Tentukan narasi utama
- Mulai konten pengenalan

Minggu 2:
- Aktivasi media sosial
- Kunjungan komunitas
- Dorong isu prioritas

Minggu 3:
- Perkuat testimoni
- Mitigasi isu negatif
- Naikkan konten program

Minggu 4:
- Konsolidasi basis
- Evaluasi sentimen
- Perkuat ajakan aksi
```

### 8.17 Indikator Keberhasilan

Contoh KPI:

```txt
- Sentimen positif naik
- Sentimen negatif turun
- Engagement media sosial naik
- Pemberitaan positif bertambah
- Isu prioritas mulai diasosiasikan dengan objek kampanye
- Relawan aktif bertambah
- Survei internal membaik
- Partisipasi kegiatan meningkat
```

### 8.18 Risiko & Catatan Strategis

Berisi:

```txt
- Risiko isu lawan
- Risiko salah komunikasi
- Risiko backlash media sosial
- Risiko konflik internal
- Risiko narasi tidak dipercaya
- Risiko overclaim
```

### 8.19 Sumber Data

Format:

```txt
- Nama sumber
- Link
- Tanggal akses
- Jenis sumber
- Modul asal
```

Jenis sumber:

```txt
- Screening Tokoh
- Media Monitoring
- Policy Intelligence
- Media nasional
- Media lokal
- Media sosial
- Google Search
- Google Trends
- Survei internal
- Data manual
```

---

## 9. UI Requirement

### 9.1 Tema

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

### 9.2 Layout Halaman Campaign Strategy

```txt
Header
↓
Form input strategi
↓
Status loading
↓
Ringkasan metrik
↓
Strategic Campaign Report satu kolom
↓
Sumber data
```

### 9.3 Input UI

```txt
Campaign Strategy

Rancang strategi kampanye untuk tokoh, partai, kebijakan, atau isu politik berbasis data.

[Tipe Objek Kampanye v]
[Objek Kampanye........................]
[Tujuan Kampanye.......................]
[Wilayah...............................]

[Generate Strategy]
```

### 9.4 Output UI

Hasil harus seperti laporan strategis.

```txt
Strategic Campaign Report
Objek: PSI Papua
Tipe: Partai Politik
Tujuan: Meningkatkan penerimaan publik
Wilayah: Papua
Generated at

Ringkasan Strategi
...

1. Konteks Kampanye
...

2. Tujuan Kampanye
...
```

Compact metric cards boleh dipakai di bagian atas:

```txt
Strategic Fit: 82/100
Message Clarity: High
Risk Level: Medium
Priority Segment: Pemilih Muda
```

---

## 10. Agent Detail

Nama agent utama:

```txt
Campaign Strategy Agent
```

Role:

```txt
AI agent yang bertugas menyusun strategi kampanye untuk tokoh, partai, kebijakan, atau isu politik berdasarkan data screening, media monitoring, policy intelligence, respon publik, dan konteks wilayah.
```

---

## 11. Struktur Multi-Agent

```txt
Campaign Strategy Orchestrator
├── Campaign Context Agent
├── Candidate/Party Positioning Agent
├── Voter Segment Agent
├── Issue Priority Agent
├── Narrative Strategy Agent
├── Message Framing Agent
├── Regional Strategy Agent
├── Media Strategy Agent
├── Ground Campaign Agent
├── Negative Issue Mitigation Agent
├── Content Recommendation Agent
├── Action Plan Generator Agent
└── KPI & Evaluation Agent
```

---

## 12. Detail Sub-Agent & Skill

### 12.1 Campaign Strategy Orchestrator

Tugas:

```txt
- Menerima input user
- Menentukan jenis objek kampanye
- Mengambil data dari modul terkait
- Memanggil sub-agent
- Menggabungkan hasil menjadi laporan strategi
```

Skill:

```txt
- Workflow Routing
- Task Planning
- Module Data Retrieval
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

### 12.2 Campaign Context Agent

Tugas:

```txt
- Membaca konteks kampanye
- Menentukan kondisi politik/media saat ini
- Menghubungkan data dari Screening Tokoh, Media Monitoring, dan Policy Intelligence
```

Skill:

```txt
- Context Analysis
- Data Synthesis
- Situation Mapping
```

Model:

```txt
Medium model
```

Risk:

```txt
Low-Medium
```

### 12.3 Candidate/Party Positioning Agent

Tugas:

```txt
- Menentukan positioning tokoh
- Menentukan positioning partai
- Menentukan positioning kebijakan/isu
- Menyusun diferensiasi utama
```

Skill:

```txt
- Political Positioning
- Brand Positioning
- Differentiation Analysis
- Persona Framing
```

Model:

```txt
Reasoning model
```

Risk:

```txt
Medium
```

Output:

```txt
- Positioning statement
- Core identity
- Differentiator
- Public perception gap
```

### 12.4 Voter Segment Agent

Tugas:

```txt
- Menentukan target pemilih/audiens
- Mengelompokkan segmen prioritas
- Menentukan kebutuhan tiap segmen
- Menentukan kanal komunikasi tiap segmen
```

Skill:

```txt
- Voter Segmentation
- Audience Mapping
- Demographic Analysis
- Psychographic Analysis
```

Model:

```txt
Medium/reasoning model
```

Risk:

```txt
Medium
```

### 12.5 Issue Priority Agent

Tugas:

```txt
- Menentukan isu prioritas yang harus diangkat
- Menentukan isu yang harus dihindari
- Menentukan isu yang perlu dimitigasi
```

Skill:

```txt
- Issue Prioritization
- Issue Risk Assessment
- Opportunity Detection
```

Model:

```txt
Medium/reasoning model
```

Risk:

```txt
Medium
```

Sumber:

```txt
- Media Monitoring
- Policy Intelligence
- Survey/internal data
- Input user
```

### 12.6 Narrative Strategy Agent

Tugas:

```txt
- Membuat narasi utama
- Membuat narasi pendukung
- Membuat narasi tandingan untuk isu negatif
```

Skill:

```txt
- Narrative Framing
- Political Messaging
- Public Communication Strategy
```

Model:

```txt
Reasoning/creative model
```

Risk:

```txt
Medium
```

Output:

```txt
- Main narrative
- Supporting narrative
- Counter narrative
- Tone of voice
```

### 12.7 Message Framing Agent

Tugas:

```txt
- Membuat pesan kunci untuk tiap segmen
- Menyesuaikan gaya bahasa dengan audiens
- Membuat versi pesan untuk media sosial, media lokal, dan kampanye darat
```

Skill:

```txt
- Message Framing
- Audience-Specific Messaging
- Tone Adaptation
```

Model:

```txt
Medium/creative model
```

Risk:

```txt
Medium
```

### 12.8 Regional Strategy Agent

Tugas:

```txt
- Menentukan strategi wilayah
- Mengidentifikasi basis kuat/lemah/swing
- Menentukan rencana aksi per wilayah
```

Skill:

```txt
- Regional Mapping
- Electoral Territory Analysis
- Local Issue Matching
```

Model:

```txt
Reasoning model
```

Risk:

```txt
Medium
```

Sumber:

```txt
- Data wilayah
- Data hasil pemilu jika tersedia
- Data media lokal
- Data survey/internal
```

### 12.9 Media Strategy Agent

Tugas:

```txt
- Membuat strategi media sosial
- Membuat strategi media lokal dan PR
- Menentukan platform prioritas
- Menentukan format konten
```

Skill:

```txt
- Social Media Strategy
- Local Media Strategy
- PR Planning
- Channel Strategy
```

Model:

```txt
Medium/creative model
```

Risk:

```txt
Medium
```

### 12.10 Ground Campaign Agent

Tugas:

```txt
- Membuat strategi kampanye darat
- Menentukan agenda komunitas
- Menentukan aktivitas relawan
- Menentukan pendekatan lapangan
```

Skill:

```txt
- Ground Campaign Planning
- Community Outreach
- Volunteer Mobilization
- Field Activity Planning
```

Model:

```txt
Medium/reasoning model
```

Risk:

```txt
Medium
```

### 12.11 Negative Issue Mitigation Agent

Tugas:

```txt
- Mendeteksi isu negatif yang berpotensi menyerang objek kampanye
- Membuat respons utama
- Membuat narasi tandingan
- Membuat prioritas mitigasi
```

Skill:

```txt
- Risk Mitigation
- Crisis Communication
- Counter Narrative
- Reputation Defense
```

Model:

```txt
Reasoning model
```

Risk:

```txt
Medium-High
```

Catatan:

```txt
Agent tidak boleh membuat fitnah, disinformasi, manipulasi, atau serangan personal tanpa dasar.
Semua respons harus berbasis data, klarifikasi, dan strategi komunikasi yang aman.
```

### 12.12 Content Recommendation Agent

Tugas:

```txt
- Membuat ide konten awal
- Membuat hook
- Membuat format konten
- Membuat CTA
- Menyesuaikan konten dengan segmen dan platform
```

Skill:

```txt
- Content Ideation
- Hook Generator
- CTA Generator
- Platform Adaptation
```

Model:

```txt
Creative/light-medium model
```

Risk:

```txt
Low-Medium
```

Output:

```txt
- Ide video pendek
- Ide carousel
- Ide caption
- Ide press release
- Ide forum warga
```

### 12.13 Action Plan Generator Agent

Tugas:

```txt
- Membuat rencana aksi 30 hari
- Menentukan prioritas mingguan
- Menentukan aktivitas utama
- Menentukan output yang diharapkan
```

Skill:

```txt
- Campaign Timeline
- Action Planning
- Priority Planning
```

Model:

```txt
Medium model
```

Risk:

```txt
Low-Medium
```

### 12.14 KPI & Evaluation Agent

Tugas:

```txt
- Menentukan indikator keberhasilan
- Menentukan cara evaluasi kampanye
- Menentukan metrik digital dan lapangan
```

Skill:

```txt
- KPI Planning
- Performance Metrics
- Evaluation Framework
```

Model:

```txt
Medium model
```

Risk:

```txt
Low
```

---

## 13. Skill Mapping

| Skill | Fungsi | Teknologi | Risk |
|---|---|---|---|
| Module Data Retrieval | Mengambil data dari modul lain | Laravel API, PostgreSQL | Low |
| Context Analysis | Membaca konteks kampanye | LLM | Low-Medium |
| Political Positioning | Menentukan positioning | Reasoning model | Medium |
| Brand Positioning | Positioning partai/program | Reasoning model | Medium |
| Voter Segmentation | Segmentasi pemilih | LLM + data survey/internal | Medium |
| Issue Prioritization | Prioritas isu | LLM + Media Monitoring DB | Medium |
| Narrative Framing | Membuat narasi utama | Reasoning/creative model | Medium |
| Message Framing | Pesan per segmen | Creative model | Medium |
| Regional Mapping | Strategi wilayah | SQL, data pemilu/survey, LLM | Medium |
| Social Media Strategy | Strategi platform sosial | LLM + Media Monitoring | Medium |
| Local Media Strategy | Strategi media lokal | LLM + source database | Medium |
| Ground Campaign Planning | Strategi lapangan | LLM | Medium |
| Risk Mitigation | Mitigasi isu negatif | Reasoning model | Medium-High |
| Crisis Communication | Respons isu krisis | Reasoning model | Medium-High |
| Content Ideation | Ide konten | Creative model | Low-Medium |
| Action Planning | Timeline aksi | LLM | Low-Medium |
| KPI Planning | Indikator sukses | LLM | Low |
| Report Generator | Susun laporan | LLM | Low |

---

## 14. Risk & Safety Rules

Campaign Strategy termasuk modul strategis, sehingga perlu batasan.

### 14.1 Yang Boleh

```txt
✅ Membuat strategi komunikasi berbasis data
✅ Membuat narasi positif
✅ Membuat klarifikasi berbasis fakta
✅ Membuat rekomendasi konten edukatif
✅ Membuat strategi wilayah
✅ Membuat rencana aksi kampanye
✅ Membuat mitigasi isu negatif
```

### 14.2 Yang Tidak Boleh

```txt
❌ Membuat fitnah
❌ Membuat disinformasi
❌ Membuat black campaign tanpa dasar
❌ Membuat ujaran kebencian
❌ Menghasut konflik SARA
❌ Mengarang data survei/elektabilitas
❌ Mengklaim dukungan publik tanpa data
❌ Menyuruh melakukan tindakan ilegal
```

### 14.3 Prinsip Output

```txt
- Pisahkan fakta, asumsi, dan rekomendasi.
- Setiap rekomendasi utama harus punya dasar data atau alasan.
- Jika data tidak tersedia, sistem harus menyebut data belum tersedia.
- Jangan mengarang angka elektabilitas.
- Jangan menulis strategi yang melanggar hukum atau etika.
```

---

## 15. Model Routing

Campaign Strategy dapat menggunakan multi-provider/model routing.

### 15.1 Routing Task

```txt
data_retrieval:
- tidak perlu model mahal
- query database dan modul internal

context_summary:
- medium model

positioning:
- reasoning model

voter_segmentation:
- medium/reasoning model

issue_prioritization:
- medium/reasoning model

narrative_strategy:
- reasoning/creative model

message_framing:
- creative model

regional_strategy:
- reasoning model

media_strategy:
- medium/creative model

negative_issue_mitigation:
- reasoning model

content_recommendation:
- creative/light-medium model

action_plan:
- medium model

final_report:
- medium model
```

### 15.2 Super Admin Control

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

## 16. Database Design

### 16.1 campaign_strategy_requests

```txt
id
user_id
agent_id
campaign_object_type
campaign_object_name
campaign_goal
region
status
created_at
updated_at
```

campaign_object_type:

```txt
candidate
party
policy
issue
organization
other
```

### 16.2 campaign_data_sources

```txt
id
request_id
source_type
source_name
source_module
url
title
content_text
raw_json
created_at
updated_at
```

source_module:

```txt
screening_tokoh
media_monitoring
policy_intelligence
manual_input
web_search
google_trends
survey_internal
other
```

### 16.3 campaign_segments

```txt
id
request_id
segment_name
segment_type
priority_level
needs
main_issue
recommended_message
recommended_channel
raw_json
created_at
updated_at
```

segment_type:

```txt
youth
women
urban
rural
community
religious
traditional
student
worker
business
general
other
```

### 16.4 campaign_issues

```txt
id
request_id
issue_name
issue_type
priority_level
sentiment
risk_level
opportunity_level
recommended_narrative
raw_json
created_at
updated_at
```

### 16.5 campaign_regions

```txt
id
request_id
region_name
region_status
priority_level
strategy
action_plan
risk_note
raw_json
created_at
updated_at
```

region_status:

```txt
strong_base
weak_base
swing
potential
unknown
```

### 16.6 campaign_recommendations

```txt
id
request_id
recommendation_type
priority_level
title
description
target_segment
channel
timeline
raw_json
created_at
updated_at
```

recommendation_type:

```txt
positioning
narrative
media_social
media_local
ground_campaign
risk_mitigation
content
action_plan
kpi
```

### 16.7 campaign_strategy_reports

```txt
id
request_id
executive_summary
positioning_statement
main_narrative
final_strategy_json
result_markdown
risk_level
strategic_score
sources_json
created_at
updated_at
```

### 16.8 campaign_agent_logs

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

## 17. API Endpoint

### 17.1 Laravel API

```txt
GET    /api/campaign-strategy
POST   /api/campaign-strategy/generate
GET    /api/campaign-strategy/reports
GET    /api/campaign-strategy/reports/{id}
DELETE /api/campaign-strategy/reports/{id}
```

### 17.2 FastAPI AI Service

```txt
POST /ai/campaign-strategy/generate
POST /ai/campaign-strategy/context
POST /ai/campaign-strategy/positioning
POST /ai/campaign-strategy/segmentation
POST /ai/campaign-strategy/issues
POST /ai/campaign-strategy/narrative
POST /ai/campaign-strategy/regional
POST /ai/campaign-strategy/media
POST /ai/campaign-strategy/ground
POST /ai/campaign-strategy/mitigation
POST /ai/campaign-strategy/content
POST /ai/campaign-strategy/action-plan
POST /ai/campaign-strategy/kpi
```

---

## 18. Backend Workflow

```txt
React
↓
POST /api/campaign-strategy/generate
↓
Laravel validate user & permission
↓
Laravel create campaign strategy request
↓
Laravel dispatch queue job
↓
FastAPI Campaign Strategy Orchestrator
↓
Retrieve related module data
↓
Analyze context
↓
Generate positioning
↓
Segment audience/voters
↓
Prioritize issues
↓
Create narrative and key messages
↓
Create regional strategy
↓
Create media and ground campaign strategy
↓
Create negative issue mitigation
↓
Create content recommendation
↓
Create action plan and KPI
↓
Save result to PostgreSQL
↓
React displays strategy report
```

---

## 19. Agent Settings

Hanya Super Admin yang bisa mengakses Agent Settings.

```txt
Agent Settings
├── Campaign Strategy Agent
│   ├── General
│   ├── Prompt
│   ├── Skills
│   ├── Data Sources
│   ├── Model Routing
│   ├── Safety Rules
│   ├── Output Template
│   └── Logs
```

### 19.1 Data Sources Setting

Super Admin dapat mengatur sumber:

```txt
- Screening Tokoh
- Media Monitoring
- Policy Intelligence
- Survey Internal
- Manual Input
- Google Search
- Google Trends
- Database wilayah
```

### 19.2 Safety Rules Setting

Super Admin dapat mengatur:

```txt
- Tidak boleh black campaign
- Tidak boleh disinformasi
- Tidak boleh hate speech
- Wajib sumber data
- Wajib pisahkan fakta/asumsi/rekomendasi
- Wajib tampilkan data unavailable jika tidak ada
```

---

## 20. Permission

### Super Admin

```txt
✅ Akses semua laporan
✅ Generate strategi kampanye
✅ Atur Campaign Strategy Agent
✅ Atur skill
✅ Atur data sources
✅ Atur provider/model/API key
✅ Atur model routing
✅ Atur safety rules
✅ Lihat semua logs
```

### Admin

```txt
✅ Generate strategi kampanye
✅ Lihat laporan tim
✅ Export report jika diizinkan
❌ Tidak bisa akses Agent Settings
```

### Analyst

```txt
✅ Generate strategi kampanye
✅ Lihat laporan sendiri
❌ Tidak bisa akses Agent Settings
```

### Viewer

```txt
✅ Lihat laporan yang dibagikan
❌ Tidak bisa generate jika tidak diberi izin
❌ Tidak bisa akses Agent Settings
```

---

## 21. Prompt Dasar Campaign Strategy Agent

```txt
You are a Strategic Campaign Intelligence Analyst.

Your task is to create campaign strategy for a political candidate, political party, public policy, or political issue based on available data from candidate screening, media monitoring, policy intelligence, internal data, public response, and regional context.

Rules:
- Do not fabricate facts, survey numbers, or public support.
- Separate facts, assumptions, and recommendations.
- Do not create disinformation, hate speech, illegal tactics, or baseless negative campaign.
- Strategy must be ethical, data-informed, and suitable for public communication.
- If data is unavailable, clearly say that the data is unavailable.
- Create positioning, voter/audience segmentation, issue priority, main narrative, key messages, regional strategy, social media strategy, local media strategy, ground campaign strategy, negative issue mitigation, content recommendation, action plan, KPI, and sources.
- Adjust strategy based on campaign object type: candidate, party, policy, or issue.
- Return structured JSON only.
```

---

## 22. JSON Output Format

```json
{
  "campaign_object": {
    "type": "party",
    "name": "PSI Papua",
    "goal": "Meningkatkan penerimaan publik dan memperkuat basis anak muda",
    "region": "Papua"
  },
  "executive_summary": "...",
  "campaign_context": {
    "summary": "...",
    "data_used": []
  },
  "positioning": {
    "statement": "...",
    "core_identity": "...",
    "differentiator": "...",
    "perception_gap": "..."
  },
  "target_segments": [
    {
      "segment": "Pemilih muda",
      "priority": "high",
      "needs": "...",
      "main_issue": "...",
      "message": "...",
      "channel": "Instagram, TikTok, komunitas kampus"
    }
  ],
  "priority_issues": [
    {
      "issue": "Lapangan kerja anak muda",
      "priority": "high",
      "reason": "...",
      "risk": "...",
      "recommended_narrative": "..."
    }
  ],
  "main_narrative": "...",
  "key_messages": [
    {
      "message": "...",
      "target": "...",
      "reason": "...",
      "channel": "..."
    }
  ],
  "regional_strategy": [
    {
      "region": "Kota Jayapura",
      "status": "swing",
      "strategy": "...",
      "actions": [],
      "risk": "..."
    }
  ],
  "social_media_strategy": {
    "platforms": [],
    "content_style": "...",
    "posting_frequency": "...",
    "formats": [],
    "hashtags": []
  },
  "local_media_pr_strategy": {
    "priority_media": [],
    "story_angles": [],
    "press_release_agenda": [],
    "negative_news_response": "..."
  },
  "ground_campaign_strategy": [
    {
      "activity": "...",
      "target": "...",
      "region": "...",
      "expected_output": "..."
    }
  ],
  "negative_issue_mitigation": [
    {
      "issue": "...",
      "risk": "medium",
      "main_response": "...",
      "counter_narrative": "...",
      "supporting_evidence": []
    }
  ],
  "content_recommendations": [
    {
      "hook": "...",
      "format": "short video",
      "target": "...",
      "message": "...",
      "cta": "..."
    }
  ],
  "action_plan_30_days": {
    "week_1": [],
    "week_2": [],
    "week_3": [],
    "week_4": []
  },
  "success_indicators": [],
  "risks_and_notes": [],
  "sources": []
}
```

---

## 23. MVP Scope

### Masuk MVP

```txt
✅ Input tipe objek kampanye
✅ Input objek kampanye
✅ Input tujuan kampanye
✅ Input wilayah
✅ Generate strategy report
✅ Positioning
✅ Segmentasi pemilih/audiens
✅ Isu prioritas
✅ Narasi utama
✅ Pesan kunci
✅ Strategi wilayah
✅ Strategi media sosial
✅ Strategi media lokal/PR
✅ Strategi kampanye darat
✅ Mitigasi isu negatif
✅ Rekomendasi konten
✅ Timeline 30 hari
✅ KPI
✅ Source list
✅ Simpan laporan
✅ Role permission
✅ Agent Settings khusus Super Admin
```

### Tidak Masuk MVP Awal

```txt
❌ Auto posting media sosial
❌ Auto kirim pesan/DM
❌ Real-time campaign execution
❌ Budget optimization detail
❌ Microtargeting data personal
❌ Payment/billing
❌ Field team mobile app
❌ Black campaign/disinformation generation
```

---

## 24. Acceptance Criteria

Modul dianggap berhasil jika:

```txt
1. User bisa membuka modul Campaign Strategy.
2. User bisa memilih tipe objek kampanye.
3. User bisa input objek kampanye, tujuan, dan wilayah.
4. Sistem bisa membuat laporan strategi.
5. Output menyesuaikan tipe objek: tokoh, partai, kebijakan, atau isu.
6. Output berisi positioning.
7. Output berisi segmentasi target.
8. Output berisi isu prioritas.
9. Output berisi narasi utama dan pesan kunci.
10. Output berisi strategi wilayah.
11. Output berisi strategi media sosial.
12. Output berisi strategi media lokal/PR.
13. Output berisi strategi kampanye darat.
14. Output berisi mitigasi isu negatif.
15. Output berisi rekomendasi konten.
16. Output berisi timeline 30 hari.
17. Output berisi indikator keberhasilan.
18. Sistem tidak mengarang data.
19. Sistem tidak membuat disinformasi/black campaign.
20. Hasil tersimpan di database.
21. Role selain Super Admin tidak bisa akses Agent Settings.
22. UI mengikuti tema SENA: putih, hitam, merah, Poppins, compact, dark mode.
```

---

## 25. Prioritas Development

### Phase 1 — Basic Campaign Strategy

```txt
- UI Campaign Strategy
- Input tipe objek, objek, tujuan, wilayah
- Laravel endpoint generate
- FastAPI workflow dasar
- Generate strategy report
- Simpan report
```

### Phase 2 — Data Integration

```txt
- Integrasi data Screening Tokoh
- Integrasi data Media Monitoring
- Integrasi data Policy Intelligence
- Data source list
```

### Phase 3 — Strategic Output

```txt
- Positioning
- Segmentasi
- Isu prioritas
- Narasi
- Pesan kunci
- Strategi wilayah
```

### Phase 4 — Campaign Execution Plan

```txt
- Strategi media sosial
- Strategi media lokal
- Strategi kampanye darat
- Mitigasi isu negatif
- Rekomendasi konten
- Timeline 30 hari
- KPI
```

### Phase 5 — Agent Settings

```txt
- Campaign Strategy Agent settings
- Skill toggle
- Data source setting
- Model routing
- Safety rules
- Logs
```

---

## 26. Brief Pendek untuk Developer / Codex

```txt
Build the fourth module for SENA: Campaign Strategy.

Stack:
React frontend, Laravel 11 backend, FastAPI AI service, PostgreSQL, Redis, Docker.

Flow:
Login → Module Landing Page → Campaign Strategy → Select campaign object type → Input object name, goal, and region → Generate Strategy → retrieve related data from Screening Tokoh, Media Monitoring, Policy Intelligence, internal data, and public sources → create positioning, target segmentation, issue priority, main narrative, key messages, regional strategy, social media strategy, local media/PR strategy, ground campaign strategy, negative issue mitigation, content recommendation, 30-day action plan, KPI, and sources.

Scope:
This module is not only for candidates. It must support candidate, party, policy, and political issue campaign strategy.

UI:
White, black, red accent, compact Poppins font, outline icons, dark mode. Result should be rendered as a clean one-column strategic campaign report, not many separate cards.

Agent:
Create Campaign Strategy Agent with sub-agents:
- Campaign Strategy Orchestrator
- Campaign Context Agent
- Candidate/Party Positioning Agent
- Voter Segment Agent
- Issue Priority Agent
- Narrative Strategy Agent
- Message Framing Agent
- Regional Strategy Agent
- Media Strategy Agent
- Ground Campaign Agent
- Negative Issue Mitigation Agent
- Content Recommendation Agent
- Action Plan Generator Agent
- KPI & Evaluation Agent

Permissions:
Only Super Admin can manage agent settings, skills, data sources, provider, model, API key, model routing, safety rules, and logs.
Other roles can use the module based on permission only.
```
