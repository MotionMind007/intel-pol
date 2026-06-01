# 📦 Azkka Skills Export — Tokoh Screening System
# Generated: June 2026
# Profile: azkka

---

# ═══════════════════════════════════════════════════════════════
# FILE 1: tokoh-screening/SKILL.md (Master Prompt)
# ═══════════════════════════════════════════════════════════════

---
name: tokoh-screening
description: "Background screening & profiling of Indonesian political figures (tokoh). Covers data scraping, sentiment analysis, electability assessment, regional base analysis, scoring, and PDF export."
triggers:
  - screening tokoh
  - riset tokoh
  - profil politisi
  - elektabilitas
  - analisis sentimen tokoh
  - background check tokoh
---

# Tokoh Screening Framework

Standardized framework for screening Indonesian political figures. User expects ALL sections in every screening — no partial results.

## Required Sections (12 Total)

1. **Profil Tokoh** — Data diri, pendidikan, keluarga, jabatan
2. **Kerier Politik** — Jabatan, aktivitas, sejarah pilkada
3. **Jejak Digital** — YouTube, Instagram, Twitter/X, kehadiran online
4. **Kontroversi & Catatan** — Kasus hukum, skandal, masalah
5. **Analisis Sentimen** — Positif (+), Negatif (-), Netral dengan alasan
6. **Data Elektabilitas** — Survey, polling, hasil pilkada sebelumnya
7. **Analisis Basis Daerah** — Daerah kuat & lemah (Lihat format di bawah)
8. **SWOT Analysis** — Strengths, Weaknesses, Opportunities, Threats
9. **Skor Akhir** — Rating 1-10 untuk setiap aspek (Lihat format di bawah)
10. **Insight Menaikkan Elektabilitas** — Strategi konkret
11. **Rekomendasi Strategis** — Short/Mid/Long term
12. **Sumber Data** — Daftar sumber yang dipakai

## Basis Daerah Format

### Untuk Tokoh Lokal:
- **🟢 DAERAH KUAT**: Sebutkan kota/kabupaten spesifik + alasan
- **🔴 DAERAH LEMAH**: Sebutkan daerah yang belum ada basis + alasan

### Untuk Tokoh Nasional:
- **🟢 PROVINSI KUAT**: Sebutkan provinsi + alasan (basis partai, voting history, dll)
- **🔴 PROVINSI LEMAH**: Sebutkan provinsi + alasan

## Skor Akhir Format

| Aspek | Skor | Keterangan |
|---|---|---|
| Elektabilitas | X/10 | ... |
| Pengalaman politik | X/10 | ... |
| Jaringan partai | X/10 | ... |
| Jaringan sosial/adat | X/10 | ... |
| Risiko kontroversi | X/10 | ... |
| Potensi maju lagi | X/10 | ... |
| King maker / influence | X/10 | ... |

**TOTAL RATA-RATA: X/10**

## Data Sources Priority

1. **Wikipedia** — Profil dasar, karier, kontroversi (English lebih lengkap)
2. **News sites** — Detik.com, Kompas.com, CNN Indonesia, Tempo
3. **Local news** — Papua Terkini, Tribun regional, Antara daerah
4. **YouTube** — Channel stats, konten, views
5. **Survey firms** — Litbang Kompas, IndoChart, LSI, Indikator, Poltracking

## Pitfalls

- **Google sering block** — pakai Bing/DuckDuckGo atau langsung ke news site
- **Social media (IG, X) butuh login** — pakai data dari YouTube atau berita
- **Wikipedia Indonesia kurang lengkap** — coba Wikipedia English
- **Survey elektabilitas jarang ada untuk tokoh lokal** — catatan "tidak ditemukan" jika memang tidak ada
- **Browser tools lambat untuk scraping** — pakai `execute_code` dengan requests jika perlu speed

## Output Language

User prefers **Bahasa Indonesia casual** (pakai "bro/gw/lu"). Professional tone untuk konten client-facing.

## PDF Export

Gunakan `fpdf2` untuk export ke PDF. Install: `pip install fpdf2`

**Reusable script:** `scripts/export-screening-pdf.py` — menerima JSON input, output PDF.
Workflow: (1) Strukturkan hasil screening ke format JSON, (2) Jalankan script:
```bash
python scripts/export-screening-pdf.py screening_data.json output.pdf
```
JSON format: `{ "nama": "...", "sections": [{"title": "...", "content": "..."}], "scores": [{"aspek": "...", "skor": "X/10", "keterangan": "..."}], "total_score": "X/10", "sources": ["..."] }`

## Scoring Rubrik

Lihat `references/skor-rubrik.md` untuk panduan lengkap memberi skor per aspek dan interpretasi total.

---

# ═══════════════════════════════════════════════════════════════
# FILE 2: tokoh-screening/references/skor-rubrik.md
# ═══════════════════════════════════════════════════════════════

# Skor Akhir — Rubrik Penilaian

## Cara Memberi Skor

### Elektabilitas (1-10)
- **1-3**: Belum pernah maju pilkada, tidak dikenal
- **4-5**: Pernah maju tapi kalah, popularitas rendah
- **6-7**: Pernah maju & cukup populer di daerah
- **8-9**: Menang pilkada atau masuk survey nasional
- **10**: Petahana dengan elektabilitas sangat tinggi

### Pengalaman Politik (1-10)
- **1-3**: Baru terjun politik (< 2 tahun)
- **4-5**: Anggota legislatif 1 periode
- **6-7**: Anggota legislatif 2+ periode atau jabatan eksekutif
- **8-9**: Pernah jabatan strategis (Ketua DPR, Menteri, dll)
- **10**: Multi-decade experience, multiple high offices

### Jaringan Partai (1-10)
- **1-3**: Hanya 1 partai, tidak punya koalisi
- **4-5**: 1-2 partai, koalisi terbatas
- **6-7**: 3+ partai, koalisi solid
- **8-9**: King maker, kontrol banyak partai
- **10**: Sentral koalisi nasional

### Jaringan Sosial/Adat (1-10)
- **1-3**: Tidak ada jaringan adat/sosial
- **4-5**: Dukungan dari 1-2 komunitas
- **6-7**: Dukungan dari beberapa komunitas adat/organisasi
- **8-9**: Dukungan luas dari berbagai elemen masyarakat
- **10**: Dukungan massif dari semua elemen

### Risiko Kontroversi (1-10)
*Semakin tinggi = semakin berisiko*
- **1-3**: Bersih, tidak ada catatan
- **4-5**: Ada isu kecil, belum terbukti
- **6-7**: Pernah diperiksa/dilaporkan tapi tidak tersangka
- **8-9**: Tersangka atau kasus serius
- **10**: Terpidana atau kasus sangat berat

### Potensi Maju Lagi (1-10)
- **1-3**: Tidak ada indikasi akan maju
- **4-5**: Mungkin maju, tapi belum jelas
- **6-7**: Sudah deklarasi atau ada sinyal kuat
- **8-9**: Sudah siap & punya modal kuat
- **10**: Sangat pasti maju dengan modal sangat kuat

### King Maker / Influence (1-10)
- **1-3**: Tidak punya pengaruh signifikan
- **4-5**: Pengaruh di tingkat kabupaten/kota
- **6-7**: Pengaruh di tingkat provinsi
- **8-9**: Pengaruh nasional atau king maker partai
- **10**: King maker nasional dengan pengaruh sangat besar

## Interpretasi Total
- **< 4**: Tokoh dengan potensi rendah
- **4-5.9**: Tokoh dengan potensi menengah-rendah
- **6-7.9**: Tokoh dengan potensi menengah-tinggi
- **8-9.9**: Tokoh dengan potensi tinggi
- **10**: Tokoh elite nasional

---

# ═══════════════════════════════════════════════════════════════
# FILE 3: person-profiling/SKILL.md (Support Skill)
# ═══════════════════════════════════════════════════════════════

---
name: person-profiling
description: Background screening & profile research on public figures, tokoh, or individuals. Multi-source web research compiled into structured Indonesian-language reports.
tags: [research, screening, profiling, web-research, indonesia, tokoh]
triggers:
  - screening tokoh
  - profil orang
  - siapa [nama]
  - background check
  - cari info soal
  - who is [name]
  - tokoh [name]
---

# Person Profiling & Background Screening

Class-level skill for conducting background research on public figures, politicians, businesspeople, celebrities, or any named individual. Produces structured screening reports in Indonesian (or English if requested).

## Research Workflow

**Reference files:**
- `references/indonesian-news-sources.md` — Indonesian news outlet URLs and search patterns
- `references/social-media-scraping.md` — Techniques for scraping YouTube, Instagram, X/Twitter, and analytics tools

### Phase 1: Primary Source — Wikipedia
1. Navigate to `https://en.wikipedia.org/wiki/[Name]` (try both English and Indonesian Wikipedia)
2. Extract structured data via `browser_console`:
   ```js
   document.querySelector('#mw-content-text').innerText
   ```
3. Wikipedia infoboxes contain: birth date, education, family, career, party/affiliation, controversies — harvest all of it.
4. If English Wikipedia has no article, try `id.wikipedia.org`.

### Phase 2: Supplementary Sources
5. Search for recent news (last 1-2 years) via news aggregators or direct news sites:
   - `https://www.bing.com/news/search?q=[Name]+[year]`
   - Direct to news portals: Kompas, Tempo, Detik, CNN Indonesia, BBC Indonesia
6. For international figures: Reuters, BBC, Bloomberg, AP News
7. **For Papua/regional figures**: Use `references/papua-news-sources.md` — local outlets like Papua Terkini, Jubi, Cepos Online have better coverage than national sources
8. Check social media presence: Instagram follower count, YouTube channel stats, Twitter/X activity

### Phase 3: Compilation
8. Cross-reference facts across sources for accuracy
9. Note source quality: Wikipedia > major news outlets > blogs > social media

## Report Format (Indonesian)

Structure the output with these sections, using emoji headers and bullet lists (NOT tables — Telegram doesn't render tables well):

### Standard Format
```
🔍 SCREENING TOKOH — [Nama]

📌 DATA PRIBADI
• Nama Lengkap:
• Lahir: tanggal, umur, tempat
• Orang tua / keluarga:
• Pendidikan:
• Status:

💼 KARIER & BISNIS
• [Sektor 1]: detail
• [Sektor 2]: detail

📰 BERITA TERKINI
• [Tanggal]: [Judul] — ringkasan singkat

⚠️ KONTROVERSI / ISU
1. [Judul isu] — ringkasan, dampak, resolusi

📊 ANALISIS SENTIMEN
• Positif: [daftar sentimen positif publik]
• Negatif: [daftar sentimen negatif publik]
• Netral: [daftar sentimen netral]

📈 ELEKTABILITAS (untuk figur politik)
• Data survey: [sumber, tahun, hasil]
• Level elektabilitas: [tinggi/menengah/rendah]
• Strength: [kekuatan elektabilitas]
• Weakness: [kelemahan elektabilitas]

💡 INSIGHT & REKOMENDASI
• Short-term: [rekomendasi 6 bulan]
• Mid-term: [rekomendasi 1-2 tahun]
• Long-term: [rekomendasi 3+ tahun]

📊 RINGKASAN RISIKO
• [Aspek]: [Status]
```

### With SWOT Analysis (when user asks for "kerangka")
Add after the standard sections:
```
🎯 SWOT ANALYSIS

**Kekuatan (Strengths):**
✅ [Strength 1]
✅ [Strength 2]

**Kelemahan (Weaknesses):**
❌ [Weakness 1]
❌ [Weakness 2]

**Peluang (Opportunities):**
🔸 [Opportunity 1]
🔸 [Opportunity 2]

**Ancaman (Threats):**
⚠️ [Threat 1]
⚠️ [Threat 2]
```

### Key Sections to Cover
- **Data Pribadi**: Full name, DOB, age, birthplace, family connections, education
- **Kontrol Bisnis**: Companies, investments, board positions, business partnerships
- **Jalur Politik**: Party affiliation, elected/appointed positions, political network
- **Kontroversi**: Legal issues, public scandals, protests, lawsuits — always include resolution/outcome
- **Jejak Digital**: Social media presence, follower counts, notable online activity
- **Analisis Sentimen**: Public sentiment breakdown (positive, negative, neutral)
- **Elektabilitas**: Survey data, polling results, electability rating (for political figures)
- **Insight & Rekomendasi**: Strategic recommendations to improve public perception/electability
- **Risiko Ringkasan**: Risk level per dimension (political, financial, reputational, legal)

## Sentiment Analysis Methodology

### Sources for Sentiment Data
1. **News article comments** — extract common themes from reader comments on major news sites
2. **Social media mentions** — search Twitter/X, Instagram, TikTok for mentions of the person
3. **Survey data** — look for polling/survey results from reputable firms (Litbang Kompas, IndoChart, LSI, Indikator Politik, Poltracking)
4. **News tone analysis** — categorize recent news articles as positive, negative, or neutral

### Sentiment Categories
- **Positif**: Achievement, good deeds, popular policies, family image, business success
- **Negatif**: Controversies, scandals, failures, corruption allegations, unpopular decisions
- **Netral**: Routine activities, appointments, standard political/business news

### How to Find Survey Data
1. Search: `[Name] elektabilitas survey` or `[Name] survei popularitas`
2. Check major survey firms: Indikator Politik, Poltracking, Litbang Kompas, IndoChart, LSI
3. Look for election-related surveys from reputable media outlets
4. **Note for regional/local figures**: Survey data is often unavailable — national surveys focus on Java-based politicians. If no survey data found, note this limitation and use alternative metrics:
   - Vote count from recent elections
   - Local news coverage frequency
   - Community/church endorsements
   - Organizational positions held

## Electability Assessment Framework

### For Political Figures
- **Level Elektabilitas**:
  - Tinggi: >30% in major surveys, consistent upward trend
  - Menengah: 10-30%, recognized but not leading
  - Rendah: <10%, low recognition or negative perception
- **Strength Factors**: Name recognition, political network, media presence, achievements
- **Weakness Factors**: Controversies, lack of experience, negative perception, strong competitors

### For Business Figures
- **Influence Metrics**: Company revenue, employee count, media mentions, industry ranking
- **Reputation Score**: Based on news sentiment, awards, partnerships, controversies

### For Public Figures/Celebrities
- **Popularity Score**: Social media followers, engagement rate, media mentions
- **Brand Value**: Endorsements, business ventures, public perception

## Strategic Recommendation Framework

### Short-term (6 months)
- Damage control for recent controversies
- Content strategy optimization
- Public appearances and media engagement
- Coalition building and relationship management

### Mid-term (1-2 years)
- Track record building through tangible achievements
- Structural organization development
- Strategic partnerships and alliances
- Skill/credential development

### Long-term (3+ years)
- Legacy positioning and vision articulation
- Platform expansion and diversification
- Succession planning and team building
- Institutional strengthening

## Language & Tone
- Default output: **Bahasa Indonesia** (casual-professional, sesuai konteks user)
- Use casual Indonesian if user communicates casually ("bro/gw/lu")
- Use professional Indonesian for client-facing or formal requests
- If user explicitly requests English, switch to English

## SWOT Analysis Framework

For comprehensive screening, include a SWOT analysis section:

```
🎯 SWOT ANALYSIS

**Kekuatan (Strengths):**
✅ [Strength 1]
✅ [Strength 2]

**Kelemahan (Weaknesses):**
❌ [Weakness 1]
❌ [Weakness 2]

**Peluang (Opportunities):**
🔸 [Opportunity 1]
🔸 [Opportunity 2]

**Ancaman (Threats):**
⚠️ [Threat 1]
⚠️ [Threat 2]
```

Use this when user asks for "kerangka" or comprehensive framework.

## Social Media Scraping

When user requests social media scraping, follow this priority order:

### YouTube (most accessible)
1. Search: `https://www.youtube.com/results?search_query=[Name]+official+channel`
2. Find the official channel from search results (look for verified checkmark or subscriber count)
3. Navigate to channel page: `https://www.youtube.com/@[handle]`
4. Extract stats via browser_console:
   ```js
   // Channel stats
   document.querySelector('#channel-header-container')?.innerText
   
   // Video list (for content analysis)
   Array.from(document.querySelectorAll('ytd-rich-item-renderer')).map(item => ({
     title: item.querySelector('#video-title')?.innerText,
     views: item.querySelector('#metadata-line span')?.innerText
   })).slice(0, 10)
   ```
5. Check About tab for channel description and links

### Instagram (often blocked)
- Direct navigation to `instagram.com/[username]` frequently returns HTTP errors from headless browsers
- **Fallback**: Use web search to find follower counts and recent posts mentioned in news articles
- Alternative: Check if the person's Instagram is embedded in news articles or Wikipedia

### X/Twitter (login required)
- X requires authentication for most content — headless browsers get login wall
- **Fallback**: Use `xurl` CLI tool if available, or search for tweets embedded in news articles
- Check Wikipedia infobox for Twitter handle, then search for mentions in news

### Social Blade / Analytics (blocked)
- SocialBlade blocks headless browsers with Cloudflare protection
- **Fallback**: YouTube channel page already shows subscriber count and video count
- For growth trends, check news articles that mention follower milestones

### Other Platforms
- TikTok: Similar to Instagram — often blocked, use news mentions
- LinkedIn: Requires login, use news/professional mentions
- Facebook: Often public pages accessible, but limited data

## Source Transparency

**Always list your sources** when presenting research results. Users want to know where data came from for credibility assessment.

Format at the end of reports:
```
### 📂 SUMBER DATA
1. **[Source Name]** — [what data was extracted]
2. **[Source Name]** — [what data was extracted]
```

This builds trust and allows users to verify or dive deeper into specific sources.

## Pitfalls

### Search Engine Bot Detection
- Google often blocks headless browsers with CAPTCHA/rate limits — **skip Google**, go directly to Wikipedia
- Bing and DuckDuckGo sometimes return empty/irrelevant results from headless browsers
- **Fallback strategy**: Wikipedia → direct news site URLs → DuckDuckGo lite (`lite.duckduckgo.com`)

### Indonesian News Sites (Most Reliable for Local Figures)
- **Detik.com**: Use internal search `https://www.detik.com/search/searchall?query=[Name]` — works well, returns recent articles
- **Kompas.com**: Use tag pages `https://www.kompas.com/tag/[name-with-dashes]` — comprehensive archive
- Both sites allow browser scraping without login — preferred over international sources for Indonesian figures
- Extract article data via JavaScript:
  ```js
  Array.from(document.querySelectorAll('article')).map(article => ({
    title: article.querySelector('h3 a')?.innerText,
    date: article.querySelector('span.date')?.innerText,
    source: article.querySelector('h2')?.innerText
  })).filter(a => a.title).slice(0, 15)
  ```

### Wikipedia Article Variations
- Not all figures have English Wikipedia pages — try Indonesian Wikipedia (`id.wikipedia.org`)
- Some figures have very short Wikipedia articles — supplement with news search
- Wikipedia may be outdated for recent events — always cross-check with news sources

### Data Freshness
- Wikipedia infoboxes may lag behind real-time events
- Always note the date of information when dealing with fast-moving political/business situations
- For recent controversies, go directly to news portals rather than relying on Wikipedia

### Output Format
- **No tables in Telegram** — they render as bullet lists anyway; use bullet lists directly
- Use emoji section headers for visual scanning
- Keep sections scannable — user should be able to skim in 10 seconds
- Bold key terms for emphasis

## Source Priority (reliability ranking)
1. Official government/institutional websites
2. Wikipedia (well-sourced articles)
3. Major news outlets (Kompas, Tempo, Reuters, BBC, Bloomberg)
4. Industry-specific publications
5. Social media (for current activity only, not facts)
6. Blogs and forums (use with caveat)

---

# ═══════════════════════════════════════════════════════════════
# FILE 4: person-profiling/references/indonesian-news-sources.md
# ═══════════════════════════════════════════════════════════════

# Indonesian News Sources for Person Profiling

## Tier 1 — Major National Outlets (highest reliability)
| Outlet | URL Pattern | Strength |
|--------|------------|----------|
| Kompas | kompas.com | General news, politics, business |
| Tempo | tempo.co | Investigative, politics, deep dives |
| Detik | detik.com | Breaking news, fast coverage |
| CNN Indonesia | cnnindonesia.com | General, business, politics |
| CNBC Indonesia | cnbcindonesia.com | Business, markets, economy |
| Antara News | antaranews.com | Official news agency |
| Jakarta Post | thejakartapost.com | English-language, intl audience |

## Tier 2 — Specialized / Regional
| Outlet | URL Pattern | Strength |
|--------|------------|----------|
| Bisnis.com | bisnis.com | Financial, IDX, corporate |
| Kontan | kontan.co.id | Financial, markets |
| Liputan6 | liputan6.com | General TV news |
| BBC Indonesia | bbc.com/indonesia | International perspective |
| Tirto | tirto.id | Data journalism, fact-check |

## Tier 3 — Business / Tech Specific
- DailySocial (dailysocial.id) — startups, tech
- TechInAsia (techinasia.com) — SEA tech, business
- DealStreetAsia — PE/VC, deals

## Direct URL Patterns for Quick Access
```
# Search specific person on news sites
https://www.google.com/search?q=site:kompas.com+"[Nama]"
https://www.google.com/search?q=site:tempo.co+"[Nama]"
https://www.google.com/search?q=site:detik.com+"[Nama]"

# Direct internal search (more reliable than Google)
https://www.detik.com/search/searchall?query=[Nama]&siteid=2
https://www.kompas.com/tag/[nama-dengan-tanda-baca]
https://www.cnnindonesia.com/nasional?q=[Nama]
```

Note: Google site-search may be blocked by bot detection. Fallback:
- Use site's internal search: `https://www.kompas.com/search?q=[Nama]`
- Or Bing: `site:kompas.com [Nama]`

## Survey & Polling Sources (for Elektabilitas Data)
| Source | Type | URL |
|--------|------|-----|
| Indikator Politik | Survey firm | indikator.co.id |
| Poltracking Indonesia | Survey firm | poltracking.com |
| Litbang Kompas | Survey division | news.kompas.com |
| IndoChart | Survey firm | indochart.com |
| LSI (Lembaga Survey Indonesia) | Survey firm | lsi.id |

**Search pattern**: `[Nama] elektabilitas survey [tahun]` or `[Nama] survei populer`

---

# ═══════════════════════════════════════════════════════════════
# FILE 5: person-profiling/references/papua-news-sources.md
# ═══════════════════════════════════════════════════════════════

# Papua-Specific News Sources for Person Profiling

## Regional Outlets (for Papua-based figures)

### Tier 1 — Major Papua Outlets
| Outlet | URL Pattern | Strength |
|--------|------------|----------|
| Papua Terkini | papuaterkini.com | General news, politics, regional |
| Jubi (Jayapura Post) | jubi.id | Independent, investigative |
| Cepos Online | ceposonline.com | Jayapura-based, comprehensive |
| Radar Papua | radarpapua.com | Regional news |
| Koran Pagi | koranpagi.net | Papua-focused |

### Tier 2 — National with Papua Coverage
| Outlet | URL Pattern | Strength |
|--------|------------|----------|
| Tribun Papua | tribunnews.com/papua | Fast coverage, video |
| Detik Papua | detik.com (regional) | National reach, local content |
| Antara Papua | antaranews.com/papua | Official news agency |

### Search Patterns for Papua Figures

```
# Direct search on regional sites
https://www.papuaterkini.com/?s=[Nama]
https://www.jubi.id/?search=[Nama]
https://www.ceposonline.com/?s=[Nama]

# National sites with Papua filter
https://www.detik.com/search/searchall?query=[Nama]+Papua&siteid=2
https://www.kompas.com/tag/[nama]-papua
```

## YouTube for Papua Figures

### Channel Discovery
1. Search: `youtube.com/results?search_query=[Name]+Papua`
2. Look for local news channels covering the figure:
   - TVRI Papua
   - Tribun Papua
   - Local community channels
   - WARNA Warta Nabire (for Jayapura area)

### Content Analysis
- Most Papua-based figures don't have personal YouTube channels
- Content appears on news channel uploads
- Video titles and view counts indicate public interest level
- Look for: official visits, ceremonies, community events

## Special Considerations for Papua Figures

### Political Context
- Otonomi Khusus (Otsus) — special autonomy funding is key issue
- Pemekaran (regional expansion) — often a political platform
- Indigenous rights — sensitive topic, check public statements
- TNI-Polri presence — security issues in certain areas

### Cultural Factors
- Strong church influence (GIDI, Gereja Masehi Injili di Tanah Papua)
- Adat (customary) law still important
- Communal decision-making in some areas
- Language: Bahasa Indonesia + local languages (Dani, Arso, etc.)

### Data Limitations
- **Survey data**: Very limited for Papua figures — most national surveys focus on Java
- **Social media**: Lower penetration than Java — less digital footprint
- **News coverage**: Concentrated in Jayapura — rural areas underrepresented
- **Wikipedia**: Very few Papua figures have Wikipedia articles

## Session Learnings (June 2026)

### What Worked for Papua Figures
1. **Papua Terkini**: Best source for local political news — comprehensive search function
2. **YouTube news channels**: TVRI Papua, Tribun Papua have good coverage
3. **Wikipedia**: Some figures have English Wikipedia pages (like Yunus Wonda)
4. **Detik search**: National search with "Papua" keyword filters well

### What Didn't Work
1. **National survey data**: No electability surveys for regional Papua figures
2. **Social media scraping**: Most Papua figures have limited social media presence
3. **Kompas tag pages**: Less comprehensive for Papua-specific figures
4. **DuckDuckGo**: Poor results for Papua-specific searches

### Key Patterns for Regional Figures
- **Limited digital footprint**: Don't expect YouTube channels or Instagram
- **Church/community ties**: Strong indicator of local influence
- **Blusukan (field visits)**: Key metric for measuring activity as leader
- **Local news coverage**: More reliable than national for regional figures

---

# ═══════════════════════════════════════════════════════════════
# FILE 6: person-profiling/references/social-media-scraping.md
# ═══════════════════════════════════════════════════════════════

# Social Media Scraping Techniques for Person Profiling

## YouTube Channel Scraping

### Method 1: Direct Channel Navigation
```
1. Search: youtube.com/results?search_query=[Name]+official+channel
2. Identify official channel (verified badge, high subscriber count)
3. Navigate to: youtube.com/@[handle]
4. Extract stats via browser_console
```

### JavaScript Extraction Patterns

**Channel Header Stats:**
```js
document.querySelector('#channel-header-container')?.innerText
// Returns: Channel name, handle, subscriber count, video count
```

**Video List (for content analysis):**
```js
Array.from(document.querySelectorAll('ytd-rich-item-renderer')).map(item => ({
  title: item.querySelector('#video-title')?.innerText,
  views: item.querySelector('#metadata-line span')?.innerText,
  date: item.querySelector('#metadata-line span:nth-child(2)')?.innerText
})).filter(v => v.title).slice(0, 10)
```

**Channel Description:**
```js
// Click "...more" button first, then:
document.querySelector('#description-container')?.innerText
// Or try:
document.querySelector('ytd-channel-about-metadata-renderer')?.innerText
```

### What to Extract
- Subscriber count (indicates influence)
- Total video count (activity level)
- Recent video titles (current interests/focus)
- View counts on recent videos (engagement)
- Channel creation date (if visible)
- Links in description (other social profiles, businesses)

## Instagram Scraping

### Challenge
Instagram blocks headless browsers with HTTP errors (403, 429).

### Fallback Strategies
1. **News mentions**: Search for "[Name] Instagram" in news articles — they often cite follower counts
2. **Wikipedia**: Check if Instagram handle is listed in infobox
3. **Embedded posts**: News articles sometimes embed Instagram posts — extract data from there
4. **Third-party tools**: If available, use Instagram analytics tools (not browser-based)

### What to Look For
- Follower count (influence metric)
- Recent post themes (current activities)
- Account verification status
- Linked businesses/causes

## X/Twitter Scraping

### Challenge
X requires login for most content — headless browsers get redirected to login page.

### Fallback Strategies
1. **xurl CLI tool**: If available, use `xurl` for Twitter data extraction
2. **News mentions**: Search for "[Name] Twitter" or "[Name] tweets" in news
3. **Embedded tweets**: News articles often embed tweets — extract from there
4. **Wikipedia**: Twitter handle often listed in external links section

### What to Look For
- Follower count (influence metric)
- Recent tweet themes (current positions/activities)
- Engagement patterns (controversial statements?)
- Verified status

## Social Blade / Analytics Tools

### Challenge
SocialBlade and similar analytics sites block headless browsers with Cloudflare protection.

### Fallback Strategies
1. **YouTube channel page**: Already shows subscriber count and video count
2. **News milestones**: Articles often mention "reached X million subscribers"
3. **Wayback Machine**: Historical snapshots may show growth trends
4. **Manual estimation**: Compare current stats with archived news mentions

## Other Platforms

### TikTok
- Similar to Instagram — often blocked
- Use news mentions for follower counts
- Check if TikTok videos are embedded in news articles

### LinkedIn
- Requires login for most content
- Use news/professional mentions for career info
- Company pages may be public — check for executive listings

### Facebook
- Public pages sometimes accessible
- Limited data extraction possible
- Use for verifying business ownership or political affiliations

## General Tips

1. **Always cross-reference**: Don't rely on single platform data
2. **Note data freshness**: Social media stats change rapidly — note when you scraped
3. **Verify authenticity**: High follower counts don't always mean influence — check engagement
4. **Privacy considerations**: Only scrape publicly available information
5. **Rate limiting**: Don't make too many requests in quick succession — space them out

## Session Learnings (June 2026)

### What Worked
- **YouTube channel search**: `youtube.com/results?search_query=[Name]+official+channel` reliably finds official channels
- **Indonesian news sites**: Detik.com and Kompas.com internal search work well for scraping
- **Wikipedia extraction**: `document.querySelector('#mw-content-text').innerText` still works

### What Didn't Work
- **Google search**: Blocked headless browsers with CAPTCHA/rate limits
- **Bing search**: Sometimes returns empty/irrelevant results
- **DuckDuckGo**: Occasionally shows no results at all
- **Instagram**: Always blocked with HTTP errors from headless browsers
- **X/Twitter**: Requires login, headless browsers get login wall
- **SocialBlade**: Blocked by Cloudflare protection

### Best Practices Discovered
1. **For YouTube**: Navigate to search results first, identify channel, then click through to channel page
2. **For news**: Use Detik internal search (`/search/searchall?query=`) and Kompas tag pages (`/tag/[name]`)
3. **For article data**: Extract from DOM using `document.querySelectorAll('article')` pattern
4. **For channel stats**: Use `#channel-header-container` or channel page header for subscriber/video counts
5. **For recent news**: Filter by date to get most relevant recent articles

### Papua/Regional Figure Patterns
1. **YouTube search**: Use `+Papua` or `+Jayapura` keywords to find local coverage
2. **Local channels**: TVRI Papua, Tribun Papua, WARNA Warta Nabire have good coverage
3. **No personal channels**: Most regional figures don't have YouTube channels — content appears on news uploads
4. **View counts**: Lower than national figures (hundreds to low thousands) — this is normal
5. **Video titles**: Look for official visits, ceremonies, community events as activity indicators

---

# ═══════════════════════════════════════════════════════════════
# FILE 7: person-profiling/templates/screening-report-template.md
# ═══════════════════════════════════════════════════════════════

# Screening Report Template

Copy this template and fill in for each person profiling request.

---

## 🔍 SCREENING TOKOH — [NAMA LENGKAP]

**📌 DATA PRIBADI**
• **Nama:** [Nama panggilan / nama lengkap]
• **Lahir:** [Tanggal], [umur], [tempat]
• **Orang tua:** [Ayah] ([jabatan/peran]), [Ibu]
• **Istri/Suami:** [Nama] ([status hubungan])
• **Anak:** [jumlah/nama]
• **Pendidikan:** [Institusi] ([gelar])
• **Agama:** [jika relevan/publik]

**💼 KARIER**

*[Sektor 1 — e.g., Politik]*
• [Jabatan/posisi] — [durasi/status]
• [Detail penting]

*[Sektor 2 — e.g., Bisnis]*
• [Perusahaan/venture] — [peran]
• [Detail: nilai investasi, skala, dll.]

*[Sektor 3 — e.g., Media/Sosial]*
• [Platform] — [follower/subscriber count]
• [Notable activity]

**⚠️ KONTROVERSI / ISU**
1. **[Judul Isu] ([tahun])**
   • kronologi singkat
   • dampak terhadap reputasi
   • resolusi: [dibatalkan / masih berlangsung / vonis]

**📊 ANALISIS SENTIMEN**
• **Positif:**
  • [Daftar sentimen positif publik]
• **Negatif:**
  • [Daftar sentimen negatif publik]
• **Netral:**
  • [Daftar sentimen netral]

**📈 ELEKTABILITAS** *(untuk figur politik)*
• **Data Survey:**
  • [Sumber] ([tahun]): [Hasil]
• **Level Elektabilitas:** [Tinggi/Menengah/Rendah]
• **Strength:** [Kekuatan elektabilitas]
• **Weakness:** [Kelemahan elektabilitas]

**💡 INSIGHT & REKOMENDASI**
• **Short-term (6 bulan):**
  • [Rekomendasi 1]
• **Mid-term (1-2 tahun):**
  • [Rekomendasi 1]
• **Long-term (3+ tahun):**
  • [Rekomendasi 1]

**📊 RINGKASAN RISIKO**
• **Latar belakang keluarga:** [Status]
• **Kekayaan:** [Ringkasan aset/bisnis]
• **Politik:** [Posisi & afiliasi]
• **Reputasi:** [Poin positif & negatif]
• **Jejak Digital:** [Aktivitas online]
• **Risiko utama:** [Paling signifikan]

---

## English Version

Use this when user requests English output.

---

## 🔍 PERSON SCREENING — [FULL NAME]

**📌 PERSONAL DATA**
• **Name:** [Preferred / Full]
• **Born:** [Date], [age], [location]
• **Family:** [Parents], [Spouse], [Children]
• **Education:** [Institution] ([Degree])

**💼 CAREER**

*[Sector 1]*
• [Role] — [Duration/Status]

*[Sector 2]*
• [Venture] — [Details]

**⚠️ CONTROVERSY / ISSUES**
1. **[Issue Title] ([Year])**
   • Summary
   • Impact
   • Resolution

**📊 SENTIMENT ANALYSIS**
• **Positive:**
  • [List of positive public sentiment]
• **Negative:**
  • [List of negative public sentiment]
• **Neutral:**
  • [List of neutral sentiment]

**📈 ELECTABILITY** *(for political figures)*
• **Survey Data:**
  • [Source] ([Year]): [Result]
• **Electability Level:** [High/Medium/Low]
• **Strengths:** [Electability strengths]
• **Weaknesses:** [Electability weaknesses]

**💡 INSIGHTS & RECOMMENDATIONS**
• **Short-term (6 months):**
  • [Recommendation 1]
• **Mid-term (1-2 years):**
  • [Recommendation 1]
• **Long-term (3+ years):**
  • [Recommendation 1]

**📊 RISK SUMMARY**
• [Dimension]: [Assessment]

---

# ═══════════════════════════════════════════════════════════════
# END OF EXPORT
# ═══════════════════════════════════════════════════════════════
