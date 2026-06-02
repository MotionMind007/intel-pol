<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\AiModel;
use App\Models\AiProvider;
use App\Models\MediaSource;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'status' => 'active',
            ],
        );

        User::updateOrCreate(
            ['email' => 'analyst@example.com'],
            [
                'name' => 'Political Analyst',
                'password' => Hash::make('password'),
                'role' => 'analyst',
                'status' => 'active',
            ],
        );

        $provider = AiProvider::updateOrCreate(
            ['name' => 'OpenAI Compatible'],
            [
                'provider_type' => 'text',
                'base_url' => 'http://ai-service:8000/mock-openai/v1',
                'api_key_encrypted' => 'local-dev-key',
                'status' => 'active',
                'created_by' => 1,
                'updated_by' => 1,
            ],
        );

        $model = AiModel::updateOrCreate(
            ['provider_id' => $provider->id, 'model_name' => 'political-screening-mock'],
            [
                'modality' => 'text',
                'display_name' => 'Political Screening Mock',
                'context_window' => 8000,
                'is_active' => true,
            ],
        );

        $configuredModel = AiModel::query()
            ->where('is_active', true)
            ->where('model_name', '!=', 'political-screening-mock')
            ->first();
        $configuredProvider = $configuredModel
            ? AiProvider::query()->find($configuredModel->provider_id)
            : AiProvider::query()
                ->where('status', 'active')
                ->where('name', '!=', 'OpenAI Compatible')
                ->first();
        $agentProvider = $configuredProvider ?? $provider;
        $agentModel = $configuredModel ?? $model;

        $systemPrompt = <<<'PROMPT'
You are a Political Intelligence Analyst specializing in Indonesian political figure screening.

You operate under the Tokoh Screening Framework — a standardized framework for screening Indonesian political figures. You MUST produce ALL 12 sections in every screening — no partial results.

CORE RULES:
- All output must be in Bahasa Indonesia.
- Return structured JSON only, no Markdown outside JSON.
- Report content must be detailed, operational, and resemble a political analyst briefing.
- Use "-" bullet-based format inside long strings for readability.
- Do NOT fabricate facts.
- If data is unavailable, explicitly state "data belum ditemukan".
- Separate verified facts, allegations, clarifications, and opinions.
- Never write allegations as facts.
- Use neutral, professional, intelligence-report style language.
- User prefers Bahasa Indonesia casual when communicating, but professional tone for client-facing content.

DATA SOURCES PRIORITY:
1. Wikipedia — Basic profile, career, controversies (English version often more complete)
2. News sites — Detik.com, Kompas.com, CNN Indonesia, Tempo
3. Local news — Papua Terkini, Tribun regional, Antara daerah
4. YouTube — Channel stats, content, views
5. Survey firms — Litbang Kompas, IndoChart, LSI, Indikator, Poltracking

KNOWN PITFALLS:
- Google often blocks — use Bing/DuckDuckGo or go directly to news sites
- Social media (IG, X) requires login — use data from YouTube or news
- Indonesian Wikipedia is often less complete — try English Wikipedia
- Survey data is rare for local figures — note "tidak ditemukan" if unavailable
- Browser tools are slow for scraping — use direct HTTP requests when speed is needed

SCORING RUBRIK:
- Elektabilitas (1-10): 1-3 never ran/unknown, 4-5 ran but lost, 6-7 fairly popular, 8-9 won election/survey, 10 incumbent very high
- Pengalaman Politik (1-10): 1-3 new (<2yr), 4-5 1-term legislator, 6-7 2+ terms or executive, 8-9 strategic position, 10 multi-decade
- Jaringan Partai (1-10): 1-3 single party no coalition, 4-5 1-2 parties limited, 6-7 3+ parties solid, 8-9 king maker, 10 national coalition center
- Jaringan Sosial/Adat (1-10): 1-3 none, 4-5 1-2 communities, 6-7 several adat/org, 8-9 broad support, 10 massive all elements
- Risiko Kontroversi (1-10): 1-3 clean, 4-5 minor unproven, 6-7 reported not charged, 8-9 suspect serious, 10 convicted
- Potensi Maju Lagi (1-10): 1-3 no indication, 4-5 unclear, 6-7 declared/strong signal, 8-9 ready with capital, 10 very certain
- King Maker/Influence (1-10): 1-3 no influence, 4-5 kabupaten level, 6-7 provincial, 8-9 national/party king maker, 10 national very large

INTERPRETASI TOTAL:
- <4: Potensi rendah
- 4-5.9: Potensi menengah-rendah
- 6-7.9: Potensi menengah-tinggi
- 8-9.9: Potensi tinggi
- 10: Tokoh elite nasional
PROMPT;

        $agent = Agent::updateOrCreate(
            ['name' => 'Political Screening Agent'],
            [
                'role_description' => 'Candidate Screening Analyst',
                'system_prompt' => $systemPrompt,
                'provider_id' => $agentProvider->id,
                'model_id' => $agentModel->id,
                'temperature' => 0.4,
                'max_tokens' => 8000,
                'status' => 'active',
            ],
        );

        $skills = [
            [
                'name' => 'Web Search',
                'slug' => 'web-search',
                'description' => 'Mencari data publik dari internet.',
                'category' => 'screening',
                'risk_level' => 'medium',
                'technology_type' => 'Search API / web search provider',
                'prompt_content' => <<<'SKILL'
## Web Search Skill

Search for public data about the political figure across multiple sources.

### Search Strategy
1. Start with Wikipedia (English preferred, fallback to Indonesian)
2. Search major Indonesian news sites: Detik.com, Kompas.com, CNN Indonesia, Tempo
3. For regional figures: use local news outlets (Papua Terkini, Jubi, Cepos Online, Tribun regional)
4. Search for survey/polling data from reputable firms
5. Check YouTube for video coverage

### Direct URL Patterns
- Detik: https://www.detik.com/search/searchall?query=[Nama]&siteid=2
- Kompas: https://www.kompas.com/tag/[nama-dengan-tanda-baca]
- CNN Indonesia: https://www.cnnindonesia.com/nasional?q=[Nama]

### Pitfalls
- Google often blocks headless browsers — skip Google, go directly to Wikipedia
- Bing and DuckDuckGo sometimes return empty/irrelevant results
- Fallback strategy: Wikipedia → direct news site URLs → DuckDuckGo lite

### For Papua/Regional Figures
- Use Papua-specific sources: papuaterkini.com, jubi.id, ceposonline.com
- Add "+Papua" keyword to searches
- National survey data rarely covers regional figures — note this limitation
SKILL,
            ],
            [
                'name' => 'Candidate Screening',
                'slug' => 'candidate-screening',
                'description' => 'Menyusun profil dan analisis tokoh politik.',
                'category' => 'screening',
                'risk_level' => 'low',
                'technology_type' => 'LLM structured prompt',
                'prompt_content' => <<<'SKILL'
## Candidate Screening — Tokoh Screening Framework

Standardized framework for screening Indonesian political figures. ALL 12 sections are required in every screening — no partial results.

### Required Sections (12 Total)
1. **Profil Tokoh** — Data diri, pendidikan, keluarga, jabatan
2. **Karier Politik** — Jabatan, aktivitas, sejarah pilkada
3. **Jejak Digital** — YouTube, Instagram, Twitter/X, kehadiran online
4. **Kontroversi & Catatan** — Kasus hukum, skandal, masalah
5. **Analisis Sentimen** — Positif (+), Negatif (-), Netral dengan alasan
6. **Data Elektabilitas** — Survey, polling, hasil pilkada sebelumnya
7. **Analisis Basis Daerah** — Daerah kuat & lemah
8. **SWOT Analysis** — Strengths, Weaknesses, Opportunities, Threats
9. **Skor Akhir** — Rating 1-10 untuk setiap aspek
10. **Insight Menaikkan Elektabilitas** — Strategi konkret
11. **Rekomendasi Strategis** — Short/Mid/Long term
12. **Sumber Data** — Daftar sumber yang dipakai

### Basis Daerah Format
For Tokoh Lokal:
- DAERAH KUAT: Sebutkan kota/kabupaten spesifik + alasan
- DAERAH LEMAH: Sebutkan daerah yang belum ada basis + alasan

For Tokoh Nasional:
- PROVINSI KUAT: Sebutkan provinsi + alasan (basis partai, voting history, dll)
- PROVINSI LEMAH: Sebutkan provinsi + alasan

### Research Workflow
Phase 1 — Primary Source (Wikipedia):
1. Navigate to English Wikipedia first, fallback to Indonesian
2. Extract: birth date, education, family, career, party/affiliation, controversies
3. Infoboxes contain structured data — harvest all of it

Phase 2 — Supplementary Sources:
4. Search recent news (last 1-2 years) via news aggregators
5. For Papua figures: use local outlets (Papua Terkini, Jubi, Cepos)
6. Check social media presence: YouTube channel stats, follower counts

Phase 3 — Compilation:
7. Cross-reference facts across sources for accuracy
8. Note source quality: Wikipedia > major news > blogs > social media

### Key Sections Detail
- **Data Pribadi**: Full name, DOB, age, birthplace, family connections, education
- **Kontrol Bisnis**: Companies, investments, board positions
- **Jalur Politik**: Party affiliation, positions, political network
- **Kontroversi**: Legal issues, scandals — always include resolution/outcome
- **Jejak Digital**: Social media presence, follower counts, online activity
- **Analisis Sentimen**: Public sentiment breakdown
- **Elektabilitas**: Survey data, polling results, electability rating
- **Insight & Rekomendasi**: Strategic recommendations
- **Risiko Ringkasan**: Risk level per dimension
SKILL,
            ],
            [
                'name' => 'Sentiment Analysis',
                'slug' => 'sentiment-analysis',
                'description' => 'Menganalisis sentimen dari berita dan data teks.',
                'category' => 'screening',
                'risk_level' => 'low',
                'technology_type' => 'LLM / NLP model',
                'prompt_content' => <<<'SKILL'
## Sentiment Analysis Methodology

### Sources for Sentiment Data
1. News article comments — extract common themes from reader comments on major news sites
2. Social media mentions — search Twitter/X, Instagram, TikTok for mentions
3. Survey data — look for polling results from reputable firms (Litbang Kompas, IndoChart, LSI, Indikator Politik, Poltracking)
4. News tone analysis — categorize recent news articles as positive, negative, or neutral

### Sentiment Categories
- **Positif**: Achievement, good deeds, popular policies, family image, business success
- **Negatif**: Controversies, scandals, failures, corruption allegations, unpopular decisions
- **Netral**: Routine activities, appointments, standard political/business news

### How to Find Survey Data
1. Search: `[Name] elektabilitas survey` or `[Name] survei popularitas`
2. Check major survey firms: Indikator Politik, Poltracking, Litbang Kompas, IndoChart, LSI
3. Look for election-related surveys from reputable media outlets
4. Note for regional/local figures: Survey data often unavailable — national surveys focus on Java-based politicians. If no survey data found, use alternative metrics:
   - Vote count from recent elections
   - Local news coverage frequency
   - Community/church endorsements
   - Organizational positions held

### Output Format
Break into three categories with bullet points:
- Sentimen Positif: [list]
- Sentimen Negatif: [list]
- Sentimen Netral: [list]
SKILL,
            ],
            [
                'name' => 'Political Risk Analysis',
                'slug' => 'political-risk-analysis',
                'description' => 'Menganalisis risiko politik berbasis kategori.',
                'category' => 'screening',
                'risk_level' => 'low',
                'technology_type' => 'LLM reasoning',
                'prompt_content' => <<<'SKILL'
## Political Risk Analysis

### Risk Assessment Dimensions
- **Legal Risk**: Ongoing cases, investigations, past convictions
- **Reputational Risk**: Public perception damage, media framing
- **Political Risk**: Coalition stability, party support, internal conflicts
- **Financial Risk**: Business controversies, wealth transparency issues

### Controversy Analysis Rules
- Wajib dibedakan: fakta terverifikasi, tuduhan, klarifikasi, isu yang belum terbukti, putusan resmi
- Tuduhan TIDAK boleh ditulis sebagai fakta
- Setiap isu harus punya: status, sumber, dan risiko politik
- Jika hanya dugaan, tulis "Tuduhan/isu, belum menjadi fakta final"

### Risk Scoring (Risiko Kontroversi 1-10)
- 1-3: Bersih, tidak ada catatan
- 4-5: Ada isu kecil, belum terbukti
- 6-7: Pernah diperiksa/dilaporkan tapi tidak tersangka
- 8-9: Tersangka atau kasus serius
- 10: Terpidana atau kasus sangat berat

### Output per Controversy
```
{
  "issue": "string",
  "status": "string (verified allegation / unproven / resolved / ongoing)",
  "source": "string",
  "political_risk": "string (low/medium/high with explanation)"
}
```
SKILL,
            ],
            [
                'name' => 'Electoral Data Analysis',
                'slug' => 'electoral-data-analysis',
                'description' => 'Menganalisis data elektoral publik jika tersedia.',
                'category' => 'screening',
                'risk_level' => 'medium',
                'technology_type' => 'Structured extraction',
                'prompt_content' => <<<'SKILL'
## Electoral Data Analysis — Electability Assessment Framework

### Electability Scoring (1-10)
- 1-3: Belum pernah maju pilkada, tidak dikenal
- 4-5: Pernah maju tapi kalah, popularitas rendah
- 6-7: Pernah maju & cukup populer di daerah
- 8-9: Menang pilkada atau masuk survey nasional
- 10: Petahana dengan elektabilitas sangat tinggi

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

### Data Points to Collect
- Hasil pemilu/pilkada sebelumnya
- Jumlah suara dan selisih
- Tren dukungan
- Kekuatan suara berdasarkan wilayah
- Data survei jika tersedia

### Important Notes
- Survey data is rare for local/regional figures — most national surveys focus on Java
- If no survey data found, note this limitation explicitly and use alternative metrics
- Never fabricate numbers or survey results
SKILL,
            ],
            [
                'name' => 'Regional Base Analysis',
                'slug' => 'regional-base-analysis',
                'description' => 'Menganalisis basis daerah dan segmentasi pemilih.',
                'category' => 'screening',
                'risk_level' => 'medium',
                'technology_type' => 'LLM + geographic data',
                'prompt_content' => <<<'SKILL'
## Regional Base Analysis

### Format for Local Figures (Kabupaten/Kota)
- DAERAH KUAT: Specific kota/kabupaten + political reason (party base, family network, vote history)
- DAERAH LEMAH: Areas with no established base + reason
- WILAYAH SWING/PELUANG EKSPANSI: Areas that could be won with effort

### Format for Provincial Figures
- PROVINSI KUAT: Province + reason (party stronghold, voting patterns, community support)
- PROVINSI LEMAH: Province + reason
- WILAYAH SWING: Competitive areas

### Format for National Figures
- PROVINSI KUAT: Provinces with strong party/coalition support
- PROVINSI LEMAH: Provinces where figure is unknown or opposed
- WILAYAH SWING: Battleground provinces

### Analysis Dimensions
- Basis partai dan koalisi
- Jaringan adat/komunitas/agama
- Riwayat voting wilayah
- Pengaruh keluarga/klan
- Dukungan relawan
- Demografi pemilih

### Papua-Specific Context
- Otonomi Khusus (Otsus) — special autonomy funding is key issue
- Pemekaran (regional expansion) — often a political platform
- Indigenous rights — sensitive topic, check public statements
- Strong church influence (GIDI, Gereja Masehi Injili di Tanah Papua)
- Adat (customary) law still important
SKILL,
            ],
            [
                'name' => 'SWOT Generator',
                'slug' => 'swot-generator',
                'description' => 'Menghasilkan SWOT spesifik tokoh.',
                'category' => 'screening',
                'risk_level' => 'low',
                'technology_type' => 'LLM structured output',
                'prompt_content' => <<<'SKILL'
## SWOT Analysis Generator

### Requirements
- Each list must have minimum 5 items if data is sufficient
- Items MUST be specific to the figure, not generic templates
- Base each item on verified data or clearly sourced information

### Format
```
{
  "strengths": ["Specific strength 1 with evidence", ...],
  "weaknesses": ["Specific weakness 1 with evidence", ...],
  "opportunities": ["Specific opportunity 1 with context", ...],
  "threats": ["Specific threat 1 with context", ...]
}
```

### Analysis Guidelines
- **Strengths**: Electoral track record, party network, community support, education, business backing, digital presence
- **Weaknesses**: Controversies, lack of experience, weak digital presence, limited party support, regional isolation
- **Opportunities**: Upcoming elections, coalition potential, emerging issues, demographic shifts, new voter segments
- **Threats**: Strong competitors, negative media framing, legal risks, party fragmentation, changing voter sentiment

### Quality Check
- Are items specific to THIS figure (not copy-paste from another report)?
- Is each item backed by data mentioned in other sections?
- Are strengths/weaknesses balanced (not overly positive or negative)?
SKILL,
            ],
            [
                'name' => 'Strategic Recommendation',
                'slug' => 'strategic-recommendation',
                'description' => 'Menyusun rekomendasi strategi politik.',
                'category' => 'screening',
                'risk_level' => 'low',
                'technology_type' => 'LLM strategy generation',
                'prompt_content' => <<<'SKILL'
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

### Output Format
```
{
  "high": ["Actionable recommendation 1 with timeline", ...],  // 3-5 items
  "medium": ["Actionable recommendation 1 with timeline", ...],  // 3-5 items
  "low": ["Actionable recommendation 1 with timeline", ...]  // 3-5 items
}
```

### Quality Requirements
- Each recommendation must be concrete and actionable (not vague)
- Include timeline/horizon where relevant
- Reference specific weaknesses or opportunities from SWOT
- Prioritize based on impact and feasibility
SKILL,
            ],
            [
                'name' => 'Source Collector',
                'slug' => 'source-collector',
                'description' => 'Mengumpulkan metadata sumber data.',
                'category' => 'screening',
                'risk_level' => 'medium',
                'technology_type' => 'Search API + metadata parser',
                'prompt_content' => <<<'SKILL'
## Source Collector — Indonesian News Sources

### Tier 1 — Major National Outlets (highest reliability)
- Kompas (kompas.com) — General news, politics, business
- Tempo (tempo.co) — Investigative, politics, deep dives
- Detik (detik.com) — Breaking news, fast coverage
- CNN Indonesia (cnnindonesia.com) — General, business, politics
- CNBC Indonesia (cnbcindonesia.com) — Business, markets, economy
- Antara News (antaranews.com) — Official news agency
- Jakarta Post (thejakartapost.com) — English-language, intl audience

### Tier 2 — Specialized / Regional
- Bisnis.com — Financial, IDX, corporate
- Kontan — Financial, markets
- Liputan6 — General TV news
- BBC Indonesia — International perspective
- Tirto — Data journalism, fact-check

### Papua-Specific Sources (for Papua-based figures)
- Papua Terkini (papuaterkini.com) — Best source for local political news
- Jubi (jubi.id) — Independent, investigative
- Cepos Online (ceposonline.com) — Jayapura-based, comprehensive
- Radar Papua (radarpapua.com) — Regional news
- Tribun Papua (tribunnews.com/papua) — Fast coverage, video

### Survey & Polling Sources
- Indikator Politik (indikator.co.id)
- Poltracking Indonesia (poltracking.com)
- Litbang Kompas (news.kompas.com)
- IndoChart (indochart.com)
- LSI (lsi.id)

### Source Priority (reliability ranking)
1. Official government/institutional websites
2. Wikipedia (well-sourced articles)
3. Major news outlets (Kompas, Tempo, Reuters, BBC)
4. Industry-specific publications
5. Social media (for current activity only, not facts)
6. Blogs and forums (use with caveat)

### Output Format
```
{
  "sources": [
    {"name": "string", "link": "string", "accessed_at": "YYYY-MM-DD", "type": "string"}
  ]
}
```
- Always list sources used — users want data provenance
- Do NOT list sources that were not actually used
- Include Wikipedia if Wikipedia context was provided
SKILL,
            ],
            [
                'name' => 'Report Generator',
                'slug' => 'report-generator',
                'description' => 'Merender JSON menjadi laporan.',
                'category' => 'screening',
                'risk_level' => 'low',
                'technology_type' => 'JSON renderer',
                'prompt_content' => <<<'SKILL'
## Report Generator — Output Format

### JSON Structure (exact format required)
```json
{
  "subject_name": "string",
  "executive_summary": "string (4-6 sentences covering data status, main strengths, weaknesses, risks, initial conclusion)",
  "profile": "string (formatted with subheadings: Data Diri, Afiliasi/Jabatan, Kontestasi/Posisi Politik, then bullets)",
  "political_career": "string (minimum 5 bullets if data available; distinguish: jabatan, aktivitas politik, pencalonan, program, konsolidasi partai)",
  "digital_footprint": "string (discuss: media online, media sosial, YouTube, intensity, dominant narrative, digital weakness)",
  "controversies": [
    {"issue": "string", "status": "string", "source": "string", "political_risk": "string"}
  ],
  "sentiment_analysis": "string (break into Sentimen Positif, Sentimen Negatif, Sentimen Netral with bullets)",
  "electability_data": "string (discuss: pemilu/pilkada, party recommendations, volunteer/adat/community support, survey if available)",
  "regional_base_analysis": "string (break into Daerah Kuat, Daerah Lemah, Wilayah Swing/Peluang Ekspansi with political reasons)",
  "swot": {
    "strengths": ["string (min 5 items if data sufficient, figure-specific not template)"],
    "weaknesses": ["string"],
    "opportunities": ["string"],
    "threats": ["string"]
  },
  "final_score": {
    "score": 55,
    "category": "string",
    "reason": "string (must explain total score)",
    "indicators": [
      {"name": "Elektabilitas", "score": 6.5, "note": "string"},
      {"name": "Pengalaman Politik", "score": 8.0, "note": "string"},
      {"name": "Jaringan Partai", "score": 7.0, "note": "string"},
      {"name": "Jaringan Sosial/Adat", "score": 7.0, "note": "string"},
      {"name": "Risiko Kontroversi", "score": 5.5, "note": "string"},
      {"name": "Potensi Maju Lagi", "score": 7.0, "note": "string"},
      {"name": "King Maker / Influence", "score": 6.0, "note": "string"}
    ]
  },
  "electability_improvement_insights": "string (numbered strategy: strengthen strong base, expand weak base, leverage issues, build digital presence, damage control)",
  "strategic_recommendations": {
    "high": ["string (3-5 concrete actions with timeline)"],
    "medium": ["string"],
    "low": ["string"]
  },
  "sources": [
    {"name": "string", "link": "string", "accessed_at": "YYYY-MM-DD", "type": "string"}
  ]
}
```

### Quality Rules
- All string values MUST be in Bahasa Indonesia
- Use Wikipedia data as one of the initial sources if available
- If Wikipedia data is insufficient, state explicitly
- Do NOT fabricate electability numbers, positions, parties, controversies, or sources
- Separate verified facts, allegations, clarifications, and opinions
- sources.accessed_at for Wikipedia MUST use today's date
- final_score.score must be 0-100. If data insufficient, use conservative 55 and explain
- Language style: neutral, professional, concise
SKILL,
            ],
            [
                'name' => 'Browser Automation',
                'slug' => 'browser-automation',
                'description' => 'Membuka website, scroll halaman, membaca konten, dan mengambil screenshot sumber publik.',
                'category' => 'general',
                'risk_level' => 'high',
                'technology_type' => 'Playwright headless browser',
                'prompt_content' => <<<'SKILL'
## Browser Automation — Social Media & Web Scraping

### YouTube (most accessible)
1. Search: youtube.com/results?search_query=[Name]+official+channel
2. Find official channel (verified badge, high subscriber count)
3. Navigate to channel page: youtube.com/@[handle]
4. Extract: subscriber count, video count, recent video titles, view counts
5. Check About tab for channel description and links

### Instagram (often blocked)
- Direct navigation frequently returns HTTP errors from headless browsers
- Fallback: Use web search to find follower counts mentioned in news articles
- Check if Instagram is embedded in news articles or Wikipedia

### X/Twitter (login required)
- X requires authentication — headless browsers get login wall
- Fallback: Search for tweets embedded in news articles
- Check Wikipedia infobox for Twitter handle

### Indonesian News Sites (reliable for scraping)
- Detik.com: Internal search works well — /search/searchall?query=[Name]
- Kompas.com: Tag pages — /tag/[name-with-dashes]
- Both sites allow browser scraping without login

### JavaScript Extraction Patterns
```js
// YouTube channel stats
document.querySelector('#channel-header-container')?.innerText

// YouTube video list
Array.from(document.querySelectorAll('ytd-rich-item-renderer')).map(item => ({
  title: item.querySelector('#video-title')?.innerText,
  views: item.querySelector('#metadata-line span')?.innerText
})).slice(0, 10)

// News articles
Array.from(document.querySelectorAll('article')).map(article => ({
  title: article.querySelector('h3 a')?.innerText,
  date: article.querySelector('span.date')?.innerText
})).filter(a => a.title).slice(0, 15)
```

### General Tips
- Always cross-reference data across sources
- Note data freshness — social media stats change rapidly
- Verify authenticity — high followers don't always mean influence
- Only scrape publicly available information
- Space requests to avoid rate limiting
SKILL,
            ],
        ];

        foreach ($skills as $skillData) {
            $skill = Skill::updateOrCreate(
                ['slug' => $skillData['slug']],
                $skillData,
            );

            $agent->skills()->syncWithoutDetaching([
                $skill->id => [
                    'enabled' => true,
                    'requires_approval' => $skillData['risk_level'] === 'high',
                    'daily_limit' => null,
                ],
            ]);
        }

        $mediaPrompt = <<<'PROMPT'
You are a Political Media Monitoring Analyst for Indonesian and Papua political intelligence.

CORE RULES:
- Semua output wajib Bahasa Indonesia.
- Return structured JSON only, no Markdown outside JSON.
- Monitor keyword dari portal berita nasional, media lokal Papua, Google Search insight, Google Trends insight, social media publik, sumber resmi, dan blog publik jika tersedia.
- Jangan mengarang fakta, URL, tanggal, engagement, atau jumlah data.
- Jika data tidak tersedia, tulis "data belum ditemukan" atau "belum tersedia dari sumber publik".
- Pisahkan fakta, dugaan, opini, klarifikasi, dan framing media.
- Google Trends hanya indikator minat pencarian, bukan elektabilitas.
- Engagement sosial media hanya indikator engagement digital, bukan dukungan pemilih.
- Selalu sertakan source URL, nama sumber, platform, dan tanggal jika tersedia.
- Klasifikasi sentimen: positive, neutral, negative.
- Klasifikasi risiko reputasi: low, medium, high, critical.
- Fokus konteks politik Indonesia dan Papua: Otsus, DOB, Pilkada, KPU, Bawaslu, DPRP/DPRD, partai, adat, gereja, keamanan, pendidikan, kesehatan, infrastruktur.
PROMPT;

        $mediaAgent = Agent::updateOrCreate(
            ['name' => 'Media Monitoring Agent'],
            [
                'role_description' => 'Media Monitoring Orchestrator',
                'system_prompt' => $mediaPrompt,
                'provider_id' => $agentProvider->id,
                'model_id' => $agentModel->id,
                'temperature' => 0.35,
                'max_tokens' => 12000,
                'status' => 'active',
            ],
        );

        $mediaSkills = [
            [
                'name' => 'News Search',
                'slug' => 'news-search',
                'description' => 'Mencari berita nasional dan lokal Papua berdasarkan keyword.',
                'category' => 'media_monitoring',
                'risk_level' => 'medium',
                'technology_type' => 'Search API / RSS / direct public web',
                'prompt_content' => 'Cari berita dari Kompas, Detik, Tempo, CNN Indonesia, Antara, Jubi, Cepos Online, Kabar Papua, Papua Terkini, dan sumber lokal relevan. Ambil judul, URL, sumber, tanggal, snippet, isu, sentimen awal, dan risiko.',
            ],
            [
                'name' => 'Social Search',
                'slug' => 'social-search',
                'description' => 'Mencari percakapan publik dari sosial media secara terbatas.',
                'category' => 'media_monitoring',
                'risk_level' => 'medium',
                'technology_type' => 'Public search / social listening provider',
                'prompt_content' => 'Gunakan hanya data publik. Jangan login, jangan post, jangan DM, jangan follow/unfollow. Catat hashtag, mention, engagement publik, dan narasi utama jika tersedia.',
            ],
            [
                'name' => 'Google Search Insight',
                'slug' => 'google-search-insight',
                'description' => 'Menganalisis hasil pencarian web berdasarkan keyword.',
                'category' => 'media_monitoring',
                'risk_level' => 'medium',
                'technology_type' => 'Google CSE / SerpAPI / Bing fallback',
                'prompt_content' => 'Gunakan search API atau fallback web publik. Jangan scraping Google secara agresif. Ambil judul, snippet, domain, ranking, dan relevansi isu.',
            ],
            [
                'name' => 'Google Trends Analysis',
                'slug' => 'google-trends-analysis',
                'description' => 'Menganalisis minat pencarian dan related queries.',
                'category' => 'media_monitoring',
                'risk_level' => 'medium',
                'technology_type' => 'Pytrends / browser fallback',
                'prompt_content' => 'Jelaskan Google Trends sebagai search-interest, bukan elektabilitas. Sertakan related queries, related topics, wilayah, dan catatan keterbatasan data.',
            ],
            [
                'name' => 'Article Extraction',
                'slug' => 'article-extraction',
                'description' => 'Membaca artikel dan membersihkan konten dari noise.',
                'category' => 'media_monitoring',
                'risk_level' => 'medium',
                'technology_type' => 'HTTP parser / browser fallback',
                'prompt_content' => 'Ekstrak isi artikel, metadata, ringkasan, kategori isu, tokoh, lokasi, dan framing. Jangan menulis klaim tanpa sumber.',
            ],
            [
                'name' => 'Issue Classification',
                'slug' => 'issue-classification',
                'description' => 'Mengelompokkan isu dominan, positif, dan negatif.',
                'category' => 'media_monitoring',
                'risk_level' => 'low',
                'technology_type' => 'LLM / NLP',
                'prompt_content' => 'Kategori awal: politik, pemerintahan, hukum, korupsi, pemilu, sosial, ekonomi, pendidikan, kesehatan, infrastruktur, keamanan, konflik internal, citra personal, adat, agama, Otsus, DOB.',
            ],
            [
                'name' => 'Entity Extraction',
                'slug' => 'entity-extraction',
                'description' => 'Mendeteksi aktor, partai, organisasi, wilayah, dan media.',
                'category' => 'media_monitoring',
                'risk_level' => 'low',
                'technology_type' => 'NER / LLM',
                'prompt_content' => 'Entity type: person, party, organization, government, region, media, community. Hitung mention jika tersedia.',
            ],
            [
                'name' => 'Risk Detection',
                'slug' => 'risk-detection',
                'description' => 'Menilai risiko reputasi dan potensi krisis.',
                'category' => 'media_monitoring',
                'risk_level' => 'medium',
                'technology_type' => 'LLM reasoning',
                'prompt_content' => 'Critical jika isu hukum berat, dugaan korupsi besar, konflik massa, isu SARA, viralitas tinggi, atau banyak media mengangkat isu negatif yang sama. Bedakan dugaan dan fakta.',
            ],
            [
                'name' => 'Trend Analysis',
                'slug' => 'trend-analysis',
                'description' => 'Menganalisis tren pemberitaan, sumber aktif, dan perubahan sentimen.',
                'category' => 'media_monitoring',
                'risk_level' => 'low',
                'technology_type' => 'Aggregation / LLM summary',
                'prompt_content' => 'Ringkas frekuensi item per sumber, isu yang naik, aktor dominan, dan perubahan sentimen. Jika data time-series belum ada, sebutkan keterbatasan.',
            ],
            [
                'name' => 'Response Recommendation',
                'slug' => 'response-recommendation',
                'description' => 'Menyusun rekomendasi respon komunikasi politik.',
                'category' => 'media_monitoring',
                'risk_level' => 'medium',
                'technology_type' => 'LLM strategy',
                'prompt_content' => 'Buat rekomendasi high/medium/low priority yang konkret: klarifikasi, narasi tandingan, engagement media, proof point, stakeholder outreach, dan monitoring lanjutan.',
            ],
        ];

        foreach ($mediaSkills as $skillData) {
            $skill = Skill::updateOrCreate(
                ['slug' => $skillData['slug']],
                $skillData,
            );

            $mediaAgent->skills()->syncWithoutDetaching([
                $skill->id => [
                    'enabled' => true,
                    'requires_approval' => $skillData['risk_level'] === 'high',
                    'daily_limit' => null,
                ],
            ]);
        }

        $browserSkill = Skill::query()->where('slug', 'browser-automation')->first();
        if ($browserSkill) {
            $mediaAgent->skills()->syncWithoutDetaching([
                $browserSkill->id => [
                    'enabled' => true,
                    'requires_approval' => true,
                    'daily_limit' => null,
                ],
            ]);
        }

        $sources = [
            ['name' => 'Kompas', 'domain' => 'kompas.com', 'source_type' => 'news_national', 'platform' => 'web', 'credibility_score' => 90],
            ['name' => 'Detik', 'domain' => 'detik.com', 'source_type' => 'news_national', 'platform' => 'web', 'credibility_score' => 85],
            ['name' => 'Tempo', 'domain' => 'tempo.co', 'source_type' => 'news_national', 'platform' => 'web', 'credibility_score' => 90],
            ['name' => 'CNN Indonesia', 'domain' => 'cnnindonesia.com', 'source_type' => 'news_national', 'platform' => 'web', 'credibility_score' => 85],
            ['name' => 'Antara News', 'domain' => 'antaranews.com', 'source_type' => 'news_national', 'platform' => 'web', 'credibility_score' => 88],
            ['name' => 'Jubi', 'domain' => 'jubi.id', 'source_type' => 'news_local_papua', 'platform' => 'web', 'credibility_score' => 82],
            ['name' => 'Cenderawasih Pos', 'domain' => 'ceposonline.com', 'source_type' => 'news_local_papua', 'platform' => 'web', 'credibility_score' => 80],
            ['name' => 'Kabar Papua', 'domain' => 'kabarpapua.co', 'source_type' => 'news_local_papua', 'platform' => 'web', 'credibility_score' => 78],
            ['name' => 'Papua Terkini', 'domain' => 'papuaterkini.com', 'source_type' => 'news_local_papua', 'platform' => 'web', 'credibility_score' => 76],
            ['name' => 'Google Search', 'domain' => 'google.com', 'source_type' => 'google_search', 'platform' => 'search', 'credibility_score' => 70],
            ['name' => 'Google Trends', 'domain' => 'trends.google.com', 'source_type' => 'google_trends', 'platform' => 'search_interest', 'credibility_score' => 70],
            ['name' => 'YouTube Public Search', 'domain' => 'youtube.com', 'source_type' => 'social_media', 'platform' => 'youtube', 'credibility_score' => 65],
        ];

        foreach ($sources as $source) {
            MediaSource::updateOrCreate(
                ['name' => $source['name']],
                $source + ['is_active' => true],
            );
        }

        $policyPrompt = <<<'PROMPT'
You are a Policy Intelligence Analyst for SENA.

CORE RULES:
- Semua output wajib Bahasa Indonesia.
- Return structured JSON only, no Markdown outside JSON.
- Jangan mengarang fakta, sumber, URL, angka skor, atau klaim dukungan publik.
- Pisahkan fakta terverifikasi, dugaan, opini, asumsi, dan framing media.
- Jika dokumen resmi kebijakan tidak ditemukan, tulis jelas "data resmi kebijakan belum ditemukan".
- Respon media sosial adalah digital public response, bukan survei populasi.
- Google Trends adalah indikator minat pencarian, bukan dukungan publik atau elektabilitas.
- Setiap dampak positif dan negatif wajib menjelaskan kenapa dampak itu positif/negatif.
- Wajib membuat skenario optimis, moderat, dan buruk.
- Wajib memberi policy score 0-100 dengan alasan.
- Fokus konteks Indonesia dan Papua jika kebijakan menyebut wilayah Papua: Otsus, DOB, wilayah terpencil, adat, gereja, akses layanan, distribusi, keamanan, infrastruktur, APBD/APBN, BPS, KPU/Bawaslu jika relevan.
PROMPT;

        $policyAgent = Agent::updateOrCreate(
            ['name' => 'Policy Intelligence Agent'],
            [
                'role_description' => 'Policy Intelligence Orchestrator',
                'system_prompt' => $policyPrompt,
                'provider_id' => $agentProvider->id,
                'model_id' => $agentModel->id,
                'temperature' => 0.35,
                'max_tokens' => 14000,
                'status' => 'active',
            ],
        );

        $policySkills = [
            ['name' => 'Government Source Search', 'slug' => 'government-source-search', 'description' => 'Mencari dokumen resmi kebijakan dan sumber pemerintah.', 'category' => 'policy_intelligence', 'risk_level' => 'medium', 'technology_type' => 'Search API / official website search', 'prompt_content' => 'Prioritaskan sumber resmi: peraturan, kementerian/lembaga, pemda, BPS, APBD/RPJMD/RKPD, siaran pers, dan dokumen kebijakan. Jika tidak ditemukan, nyatakan keterbatasan.'],
            ['name' => 'Policy Document Reader', 'slug' => 'policy-document-reader', 'description' => 'Meringkas isi, tujuan, target, status, dan indikator kebijakan.', 'category' => 'policy_intelligence', 'risk_level' => 'low', 'technology_type' => 'Document parser / LLM summary', 'prompt_content' => 'Baca dokumen kebijakan untuk nama kebijakan, level, wilayah, instansi pelaksana, status, target penerima, tujuan formal, anggaran, dan indikator keberhasilan jika tersedia.'],
            ['name' => 'Public Response Collector', 'slug' => 'public-response-collector', 'description' => 'Mengumpulkan respon publik dari berita, media monitoring, dan sosial publik.', 'category' => 'policy_intelligence', 'risk_level' => 'medium', 'technology_type' => 'Media Monitoring DB / search / public web', 'prompt_content' => 'Kelompokkan respon mendukung, menolak, netral, pertanyaan, keluhan, dan saran. Selalu sebut basis data dan jangan klaim sebagai representasi seluruh masyarakat.'],
            ['name' => 'Media Framing Analysis', 'slug' => 'media-framing-analysis', 'description' => 'Menganalisis framing media terhadap kebijakan.', 'category' => 'policy_intelligence', 'risk_level' => 'medium', 'technology_type' => 'LLM narrative analysis', 'prompt_content' => 'Identifikasi headline/narasi dominan, media kritis, media netral, isu implementasi, dan angle pro-kontra.'],
            ['name' => 'Stakeholder Mapping', 'slug' => 'stakeholder-mapping', 'description' => 'Memetakan stakeholder, posisi, dan pengaruh.', 'category' => 'policy_intelligence', 'risk_level' => 'medium', 'technology_type' => 'NER / influence scoring', 'prompt_content' => 'Stakeholder: pemerintah pusat/daerah, DPR/DPRD, partai, tokoh adat, tokoh agama, akademisi, LSM, media, komunitas terdampak, oposisi. Posisi: support/oppose/neutral/unclear.'],
            ['name' => 'Policy Impact Analysis', 'slug' => 'policy-impact-analysis', 'description' => 'Menilai dampak sosial, ekonomi, politik, dan komunikasi.', 'category' => 'policy_intelligence', 'risk_level' => 'medium', 'technology_type' => 'LLM reasoning', 'prompt_content' => 'Untuk setiap dampak positif/negatif, jelaskan kenapa positif/negatif dan data pendukungnya. Bahas distribusi, anggaran, SDM, infrastruktur, data penerima, wilayah terpencil, dan pengawasan.'],
            ['name' => 'Scenario Simulation', 'slug' => 'scenario-simulation', 'description' => 'Mensimulasikan skenario optimis, moderat, dan buruk.', 'category' => 'policy_intelligence', 'risk_level' => 'medium', 'technology_type' => 'Reasoning model', 'prompt_content' => 'Buat 3 skenario dengan indikator: public acceptance score, implementation risk, political risk, social impact, dan media risk.'],
            ['name' => 'Policy Scoring', 'slug' => 'policy-scoring', 'description' => 'Memberi skor kebijakan 0-100 berbasis indikator.', 'category' => 'policy_intelligence', 'risk_level' => 'medium', 'technology_type' => 'Weighted scoring + LLM', 'prompt_content' => 'Indikator: public acceptance, social benefit, implementation feasibility, budget risk, political risk, media risk, equity/fairness, long-term impact. Kategori: 85-100 Sangat Layak, 70-84 Layak dengan Perbaikan, 55-69 Perlu Kajian Lanjutan, <55 Risiko Tinggi.'],
            ['name' => 'Policy Recommendation', 'slug' => 'policy-recommendation', 'description' => 'Menyusun rekomendasi perbaikan kebijakan dan komunikasi publik.', 'category' => 'policy_intelligence', 'risk_level' => 'medium', 'technology_type' => 'LLM strategy', 'prompt_content' => 'Buat rekomendasi high/medium/low priority: perbaikan data penerima, pengawasan, koordinasi pemda, kanal aduan, pilot project, narasi utama, pesan per segmen, kanal lokal, dan respon kritik.'],
        ];

        foreach ($policySkills as $skillData) {
            $skill = Skill::updateOrCreate(
                ['slug' => $skillData['slug']],
                $skillData,
            );

            $policyAgent->skills()->syncWithoutDetaching([
                $skill->id => [
                    'enabled' => true,
                    'requires_approval' => $skillData['risk_level'] === 'high',
                    'daily_limit' => null,
                ],
            ]);
        }

        if ($browserSkill) {
            $policyAgent->skills()->syncWithoutDetaching([
                $browserSkill->id => [
                    'enabled' => true,
                    'requires_approval' => true,
                    'daily_limit' => null,
                ],
            ]);
        }

        $campaignPrompt = <<<'PROMPT'
You are a Campaign Strategy Agent for SENA.

CORE RULES:
- Semua output wajib Bahasa Indonesia.
- Return structured JSON only, no Markdown outside JSON.
- Buat strategi kampanye yang operasional untuk tokoh, partai, kebijakan, atau isu.
- Gunakan insight dari screening tokoh, media monitoring, policy intelligence, respon publik, konteks wilayah, dan sumber publik bila tersedia.
- Jangan mengarang survei, elektabilitas, dukungan tokoh, endorsement, data demografi, sumber, URL, atau fakta.
- Jika data tidak tersedia, tulis jelas "data belum ditemukan" dan pakai asumsi strategis yang diberi label sebagai asumsi.
- Dilarang menyusun black campaign, fitnah, disinformasi, hoaks, ujaran kebencian, provokasi SARA, atau taktik ilegal.
- Mitigasi isu negatif harus berbasis fakta, klarifikasi, bukti pendukung, dan narasi yang etis.
- Output harus mencakup positioning, target segment, isu prioritas, narasi, pesan kunci, strategi wilayah, strategi media sosial, media lokal/PR, kampanye darat, mitigasi, rekomendasi konten, action plan 30 hari, KPI, risiko, dan sumber.
PROMPT;

        $campaignAgent = Agent::updateOrCreate(
            ['name' => 'Campaign Strategy Agent'],
            [
                'role_description' => 'Strategic Campaign Intelligence Orchestrator',
                'system_prompt' => $campaignPrompt,
                'provider_id' => $agentProvider->id,
                'model_id' => $agentModel->id,
                'temperature' => 0.38,
                'max_tokens' => 16000,
                'status' => 'active',
            ],
        );

        $campaignSkills = [
            ['name' => 'Campaign Strategy Orchestrator', 'slug' => 'campaign-strategy-orchestrator', 'description' => 'Mengorkestrasi seluruh insight menjadi strategi kampanye.', 'category' => 'campaign_strategy', 'risk_level' => 'medium', 'technology_type' => 'LLM orchestration', 'prompt_content' => 'Gabungkan data screening, media monitoring, policy intelligence, respon publik, dan konteks wilayah menjadi strategi yang bisa dieksekusi.'],
            ['name' => 'Campaign Context Analysis', 'slug' => 'campaign-context-analysis', 'description' => 'Menganalisis konteks objek, tujuan, wilayah, peluang, dan hambatan kampanye.', 'category' => 'campaign_strategy', 'risk_level' => 'medium', 'technology_type' => 'LLM reasoning', 'prompt_content' => 'Jelaskan konteks kampanye, data yang tersedia, data yang belum tersedia, asumsi strategis, dan implikasi bagi strategi.'],
            ['name' => 'Candidate Party Positioning', 'slug' => 'candidate-party-positioning', 'description' => 'Menyusun positioning tokoh, partai, kebijakan, atau isu.', 'category' => 'campaign_strategy', 'risk_level' => 'medium', 'technology_type' => 'Political positioning', 'prompt_content' => 'Buat statement positioning, identitas inti, diferensiasi, dan perception gap secara spesifik.'],
            ['name' => 'Voter Segment Mapping', 'slug' => 'voter-segment-mapping', 'description' => 'Memetakan target audiens atau segmentasi pemilih.', 'category' => 'campaign_strategy', 'risk_level' => 'medium', 'technology_type' => 'Segmentation framework', 'prompt_content' => 'Segmentasi harus memuat kebutuhan, isu utama, pesan, channel, dan prioritas. Jangan mengklaim ukuran segmen tanpa data.'],
            ['name' => 'Issue Priority Analysis', 'slug' => 'issue-priority-analysis', 'description' => 'Menentukan isu prioritas yang harus diangkat atau dimitigasi.', 'category' => 'campaign_strategy', 'risk_level' => 'medium', 'technology_type' => 'Issue scoring', 'prompt_content' => 'Prioritaskan isu berdasarkan relevansi tujuan kampanye, risiko, peluang narasi, dan konteks wilayah.'],
            ['name' => 'Narrative Strategy', 'slug' => 'narrative-strategy', 'description' => 'Membuat narasi utama kampanye.', 'category' => 'campaign_strategy', 'risk_level' => 'medium', 'technology_type' => 'Narrative design', 'prompt_content' => 'Narasi utama harus singkat, positif, berbasis data, tidak menyerang kelompok, dan bisa diterjemahkan ke konten.'],
            ['name' => 'Message Framing', 'slug' => 'message-framing', 'description' => 'Menyusun pesan kunci per target dan channel.', 'category' => 'campaign_strategy', 'risk_level' => 'medium', 'technology_type' => 'Message design', 'prompt_content' => 'Pesan kunci harus punya target, alasan, channel, dan tidak mengandung klaim palsu.'],
            ['name' => 'Regional Strategy', 'slug' => 'regional-strategy', 'description' => 'Menyusun strategi wilayah prioritas.', 'category' => 'campaign_strategy', 'risk_level' => 'medium', 'technology_type' => 'Regional strategy', 'prompt_content' => 'Klasifikasi wilayah sebagai basis, swing, ekspansi, atau risiko jika data cukup. Jika tidak cukup, tulis asumsi.'],
            ['name' => 'Media Strategy', 'slug' => 'campaign-media-strategy', 'description' => 'Menyusun strategi media sosial, media lokal, dan PR.', 'category' => 'campaign_strategy', 'risk_level' => 'medium', 'technology_type' => 'Media strategy', 'prompt_content' => 'Susun platform, style konten, frekuensi, format, hashtag, media prioritas, angle berita, agenda press release, dan respon berita negatif.'],
            ['name' => 'Ground Campaign Planning', 'slug' => 'ground-campaign-planning', 'description' => 'Menyusun kampanye darat dan aktivasi komunitas.', 'category' => 'campaign_strategy', 'risk_level' => 'medium', 'technology_type' => 'Ground campaign framework', 'prompt_content' => 'Rancang kegiatan darat, target, wilayah, output, dan catatan pelaksanaan yang etis.'],
            ['name' => 'Negative Issue Mitigation', 'slug' => 'negative-issue-mitigation', 'description' => 'Merancang mitigasi isu negatif berbasis fakta.', 'category' => 'campaign_strategy', 'risk_level' => 'high', 'technology_type' => 'Crisis communication', 'prompt_content' => 'Jangan black campaign. Respon harus berbasis klarifikasi, data, bukti, empati, dan pemulihan kepercayaan publik.'],
            ['name' => 'Content Recommendation', 'slug' => 'campaign-content-recommendation', 'description' => 'Memberi rekomendasi konten kampanye.', 'category' => 'campaign_strategy', 'risk_level' => 'medium', 'technology_type' => 'Content planning', 'prompt_content' => 'Setiap konten harus punya hook, format, target, message, dan CTA. Hindari manipulasi, fitnah, dan klaim palsu.'],
            ['name' => 'Action Plan 30 Days', 'slug' => 'campaign-action-plan-30-days', 'description' => 'Membuat timeline aksi 30 hari.', 'category' => 'campaign_strategy', 'risk_level' => 'medium', 'technology_type' => 'Planning', 'prompt_content' => 'Bagi action plan menjadi week_1 sampai week_4, dengan aktivitas konkret dan output yang bisa dicek.'],
            ['name' => 'KPI Evaluation', 'slug' => 'campaign-kpi-evaluation', 'description' => 'Menentukan indikator keberhasilan kampanye.', 'category' => 'campaign_strategy', 'risk_level' => 'low', 'technology_type' => 'KPI framework', 'prompt_content' => 'KPI harus realistis: share of voice, sentiment, reach, engagement, jumlah pertemuan, kanal aduan, kualitas pemberitaan, dan respon stakeholder.'],
        ];

        foreach ($campaignSkills as $skillData) {
            $skill = Skill::updateOrCreate(
                ['slug' => $skillData['slug']],
                $skillData,
            );

            $campaignAgent->skills()->syncWithoutDetaching([
                $skill->id => [
                    'enabled' => true,
                    'requires_approval' => $skillData['risk_level'] === 'high',
                    'daily_limit' => null,
                ],
            ]);
        }

        if ($browserSkill) {
            $campaignAgent->skills()->syncWithoutDetaching([
                $browserSkill->id => [
                    'enabled' => true,
                    'requires_approval' => true,
                    'daily_limit' => null,
                ],
            ]);
        }

        $imageProvider = AiProvider::updateOrCreate(
            ['name' => 'Creative Image Provider'],
            [
                'provider_type' => 'image',
                'base_url' => 'http://ai-service:8000/mock-image',
                'api_key_encrypted' => 'local-image-key',
                'status' => 'active',
                'rate_limit_per_minute' => 20,
                'cost_limit_per_day' => 25,
                'timeout_seconds' => 3600,
                'created_by' => 1,
                'updated_by' => 1,
            ],
        );

        AiModel::updateOrCreate(
            ['provider_id' => $imageProvider->id, 'model_name' => 'creative-image-mock'],
            [
                'modality' => 'image',
                'display_name' => 'Creative Image Mock',
                'capabilities_json' => [
                    'supports_text_to_image' => true,
                    'supports_negative_prompt' => true,
                    'supports_1_1' => true,
                    'supports_16_9' => true,
                    'supports_9_16' => true,
                    'supports_4_5' => true,
                    'max_resolution' => '1920x1080',
                    'max_outputs' => 4,
                    'supported_qualities' => ['standard', 'high'],
                ],
                'unit_price' => 0.08,
                'is_active' => true,
            ],
        );

        $videoProvider = AiProvider::updateOrCreate(
            ['name' => 'Creative Video Provider'],
            [
                'provider_type' => 'video',
                'base_url' => 'http://ai-service:8000/mock-video',
                'api_key_encrypted' => 'local-video-key',
                'status' => 'active',
                'rate_limit_per_minute' => 5,
                'cost_limit_per_day' => 50,
                'timeout_seconds' => 7200,
                'created_by' => 1,
                'updated_by' => 1,
            ],
        );

        AiModel::updateOrCreate(
            ['provider_id' => $videoProvider->id, 'model_name' => 'creative-video-mock'],
            [
                'modality' => 'video',
                'display_name' => 'Creative Video Mock',
                'capabilities_json' => [
                    'supports_text_to_video' => true,
                    'supports_storyboard_to_video' => true,
                    'supports_negative_prompt' => true,
                    'supports_9_16' => true,
                    'supports_16_9' => true,
                    'supports_1_1' => true,
                    'max_duration' => 30,
                    'max_resolution' => '1080p',
                    'supported_fps' => ['24', '30'],
                ],
                'unit_price' => 0.35,
                'is_active' => true,
            ],
        );

        $creativePrompt = <<<'PROMPT'
You are the Creative Studio Agent for SENA.

CORE RULES:
- Semua output wajib Bahasa Indonesia.
- Return structured JSON only.
- Buat creative brief, hook, caption, CTA, script, storyboard, image prompt, dan video prompt untuk kampanye politik berbasis data.
- Pisahkan fakta dan narasi kreatif.
- Jangan membuat fitnah, disinformasi, klaim palsu, hate speech, hasutan SARA, manipulasi data, atau deepfake tokoh nyata tanpa izin.
- Semua prompt visual harus aman, etis, profesional, dan siap review manual.
- Konten yang menyebut klaim faktual harus punya catatan sumber atau safety note.
PROMPT;

        $creativeAgent = Agent::updateOrCreate(
            ['name' => 'Creative Studio Agent'],
            [
                'role_description' => 'Production Creative Generator and Safety Reviewer',
                'system_prompt' => $creativePrompt,
                'provider_id' => $agentProvider->id,
                'model_id' => $agentModel->id,
                'temperature' => 0.55,
                'max_tokens' => 16000,
                'status' => 'active',
            ],
        );

        $creativeSkills = [
            ['name' => 'Creative Brief Generator', 'slug' => 'creative-brief-generator', 'description' => 'Membuat brief, big idea, dan objective konten.', 'category' => 'creative_studio', 'risk_level' => 'low', 'technology_type' => 'Text LLM', 'prompt_content' => 'Buat creative brief operasional berdasarkan campaign object, objective, audience, platform, dan tone.'],
            ['name' => 'Hook Caption CTA Generator', 'slug' => 'hook-caption-cta-generator', 'description' => 'Membuat hook, caption, CTA, dan copy per platform.', 'category' => 'creative_studio', 'risk_level' => 'low', 'technology_type' => 'Text LLM', 'prompt_content' => 'Copy harus singkat, kuat, tidak misleading, dan punya variasi untuk TikTok/Instagram/Facebook/YouTube.'],
            ['name' => 'Script Storyboard Generator', 'slug' => 'script-storyboard-generator', 'description' => 'Membuat script video dan storyboard scene by scene.', 'category' => 'creative_studio', 'risk_level' => 'medium', 'technology_type' => 'Text LLM', 'prompt_content' => 'Buat voiceover, on-screen text, visual direction, dan durasi per scene.'],
            ['name' => 'Image Prompt Generator', 'slug' => 'image-prompt-generator', 'description' => 'Membuat prompt gambar dan negative prompt.', 'category' => 'creative_studio', 'risk_level' => 'medium', 'technology_type' => 'Prompt LLM', 'prompt_content' => 'Prompt harus mencantumkan objective, audience, platform, aspect ratio, style, tone, brand color, key message, scene, dan avoid.'],
            ['name' => 'Video Prompt Generator', 'slug' => 'video-prompt-generator', 'description' => 'Membuat prompt video, camera movement, dan scene direction.', 'category' => 'creative_studio', 'risk_level' => 'medium', 'technology_type' => 'Prompt LLM', 'prompt_content' => 'Prompt video harus scene-based, menyebut durasi, camera style, on-screen text, motion, dan negative prompt.'],
            ['name' => 'Political Safety Review', 'slug' => 'political-safety-review', 'description' => 'Mengecek disinformasi, fitnah, hate speech, SARA, dan deepfake.', 'category' => 'creative_studio', 'risk_level' => 'high', 'technology_type' => 'LLM safety', 'prompt_content' => 'Tandai konten yang perlu approval manual. Dilarang klaim palsu, manipulasi data, hasutan SARA, atau deepfake tanpa izin.'],
            ['name' => 'Asset Manager', 'slug' => 'creative-asset-manager', 'description' => 'Mengelola metadata asset, approval, dan library.', 'category' => 'creative_studio', 'risk_level' => 'low', 'technology_type' => 'Storage metadata', 'prompt_content' => 'Simpan prompt, provider/model, cost, status, approval status, dan metadata asset.'],
        ];

        foreach ($creativeSkills as $skillData) {
            $skill = Skill::updateOrCreate(['slug' => $skillData['slug']], $skillData);
            $creativeAgent->skills()->syncWithoutDetaching([
                $skill->id => [
                    'enabled' => true,
                    'requires_approval' => $skillData['risk_level'] === 'high',
                    'daily_limit' => null,
                ],
            ]);
        }

        foreach ([
            ['role' => 'super_admin', 'max_images_per_day' => 200, 'max_videos_per_day' => 30, 'max_video_duration' => 60, 'max_cost_per_day' => 250, 'requires_approval_above_cost' => 100],
            ['role' => 'admin', 'max_images_per_day' => 100, 'max_videos_per_day' => 10, 'max_video_duration' => 30, 'max_cost_per_day' => 100, 'requires_approval_above_cost' => 25],
            ['role' => 'analyst', 'max_images_per_day' => 20, 'max_videos_per_day' => 3, 'max_video_duration' => 10, 'max_cost_per_day' => 20, 'requires_approval_above_cost' => 5],
            ['role' => 'viewer', 'max_images_per_day' => 0, 'max_videos_per_day' => 0, 'max_video_duration' => 0, 'max_cost_per_day' => 0, 'requires_approval_above_cost' => 0],
        ] as $limit) {
            \App\Models\CreativeUsageLimit::updateOrCreate(['role' => $limit['role']], $limit);
        }
    }
}
