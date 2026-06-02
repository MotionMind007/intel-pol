# PRD Modul 5 — Creative Studio
## Production-Scale Image & Video Creative Generator
### SENA — Sentiment & Narrative Analytics

Versi: 1.0  
Stack utama: **React + Laravel 11 + FastAPI**  
Status: Modul kelima setelah **Screening Tokoh**, **Media Monitoring**, **Policy Intelligence**, dan **Campaign Strategy**

---

## 1. Ringkasan Modul

**Creative Studio** adalah modul production-scale untuk membuat aset kreatif kampanye berbasis AI.

Modul ini tidak hanya membuat prompt, tetapi juga dapat langsung menghasilkan:

```txt
- Creative brief
- Ide konten
- Hook
- Caption
- CTA
- Script video
- Storyboard
- Image prompt
- Video prompt
- Gambar AI
- Video AI
- Variasi konten per platform
- Asset library
```

Creative Studio menjadi jembatan antara **Campaign Strategy** dan eksekusi konten.

Alur strategis platform:

```txt
Screening Tokoh
↓
Media Monitoring
↓
Policy Intelligence
↓
Campaign Strategy
↓
Creative Studio
↓
Content Planner / Publishing / Monitoring
```

---

## 2. Tujuan Modul

Tujuan utama Creative Studio:

```txt
1. Mengubah strategi kampanye menjadi aset kreatif.
2. Membuat creative package lengkap dari campaign strategy report.
3. Generate prompt gambar dan video yang rapi.
4. Generate gambar langsung dari provider image AI.
5. Generate video langsung dari provider video AI.
6. Menyediakan pilihan aspect ratio, resolusi, durasi, style, quality, dan output count.
7. Menyimpan semua aset ke asset library.
8. Mengatur provider image dan video secara terpisah.
9. Memberi kontrol penuh kepada Super Admin untuk API key, model, routing, fallback, cost limit, dan storage.
10. Menjaga brand consistency dan safety untuk konten politik.
```

---

## 3. Production Scope

Modul ini langsung dirancang untuk skala production.

Masuk production scope:

```txt
✅ Generate creative brief
✅ Generate caption
✅ Generate hook
✅ Generate CTA
✅ Generate image prompt
✅ Generate video prompt
✅ Generate script video
✅ Generate storyboard
✅ Generate image langsung dari provider AI
✅ Generate video langsung dari provider AI
✅ Pilihan aspect ratio gambar
✅ Pilihan aspect ratio video
✅ Pilihan resolusi gambar
✅ Pilihan resolusi video
✅ Pilihan durasi video
✅ Pilihan style visual
✅ Pilihan quality
✅ Queue job
✅ Asset library
✅ Storage gambar/video
✅ Provider image/video terpisah
✅ API key encrypted
✅ Model routing
✅ Fallback provider/model
✅ Cost control
✅ Usage logs
✅ Approval workflow
✅ Safety review
```

Tidak masuk production awal:

```txt
❌ Auto-posting ke sosial media
❌ Auto-ads/pasang iklan otomatis
❌ Auto-DM
❌ Auto-publish tanpa approval
❌ Payment/billing eksternal penuh
```

---

## 4. Konsep Utama

Creative Studio punya 3 mode besar:

```txt
1. Creative Package Mode
   Membuat brief, ide konten, caption, script, prompt, storyboard.

2. Image Generation Mode
   Membuat gambar AI langsung dari prompt dan setting output.

3. Video Generation Mode
   Membuat video AI langsung dari prompt, gambar, storyboard, dan setting output.
```

---

## 5. User Flow

### 5.1 Flow dari Campaign Strategy

```txt
User buka Campaign Strategy Report
↓
Klik Generate Creative Package
↓
Sistem membuka Creative Studio
↓
AI mengambil positioning, narasi, segmentasi, isu prioritas, dan rekomendasi konten
↓
Creative Studio membuat creative brief, caption, script, prompt gambar, prompt video, dan storyboard
↓
User memilih generate image/video
↓
User memilih setting aspect ratio, resolusi, durasi, style, quality
↓
Job masuk queue
↓
Provider image/video dipanggil
↓
Asset disimpan ke storage
↓
Hasil tampil di Asset Library
```

### 5.2 Flow Manual

```txt
User buka Creative Studio
↓
Pilih mode: Image / Video / Creative Package
↓
Input objek kampanye
↓
Input tujuan konten
↓
Pilih platform
↓
Pilih format konten
↓
Pilih tone komunikasi
↓
Input prompt manual atau generate prompt otomatis
↓
Pilih setting output
↓
Generate
↓
Asset tersimpan
```

---

## 6. Input Modul

### 6.1 Input Creative Package

```txt
- Campaign object
- Campaign object type
- Campaign goal
- Target audience
- Platform
- Content objective
- Tone
- Source strategy report
```

Contoh:

```txt
Campaign object: PSI Papua
Type: Partai Politik
Goal: Meningkatkan penerimaan publik anak muda
Target audience: Pemilih muda Papua
Platform: TikTok, Instagram
Content objective: Awareness
Tone: Optimis, dekat, modern, tidak kaku
```

### 6.2 Input Image Generation

```txt
- Prompt
- Negative prompt
- Aspect ratio
- Resolution
- Output count
- Quality
- Style
- Brand color
- Reference image optional
- Seed optional jika provider support
```

### 6.3 Input Video Generation

```txt
- Prompt
- Negative prompt
- Video type
- Aspect ratio
- Resolution
- Duration
- Frame rate
- Camera movement
- Style
- Reference image optional
- Storyboard optional
- Audio setting optional
```

---

## 7. Image Generator Settings

Creative Studio harus memiliki pengaturan gambar seperti web image generator profesional.

### 7.1 Aspect Ratio Gambar

Pilihan minimal:

```txt
1:1   Square
16:9  Landscape
9:16  Portrait / Story / Reels / TikTok
4:5   Instagram Portrait
3:4   Poster
21:9  Wide Banner
```

### 7.2 Resolusi Gambar

Pilihan default:

```txt
512x512
768x768
1024x1024
1024x1792
1792x1024
1080x1350
1350x1080
1920x1080
1080x1920
Custom jika provider support
```

Catatan:

```txt
UI harus menyesuaikan opsi berdasarkan capabilities model/provider.
Jika provider tidak mendukung resolusi tertentu, opsi harus disabled.
```

### 7.3 Output Count

```txt
1 image
2 images
4 images
```

Max output count dapat diatur Super Admin.

### 7.4 Quality

```txt
Standard
High
Ultra jika provider support
```

### 7.5 Style

```txt
Realistic
Semi-realistic
Illustration
Poster campaign
Editorial
Cinematic
Social media graphic
Minimalist
Vector
Flat design
Documentary
Youth campaign
Government/public service
```

### 7.6 Image Modes

```txt
Text to Image
Image to Image
Reference Image
Brand Template Image
Campaign Poster
Social Media Graphic
Infographic Style
```

---

## 8. Video Generator Settings

Creative Studio harus memiliki pengaturan video seperti web video generator profesional.

### 8.1 Aspect Ratio Video

```txt
9:16  Portrait / TikTok / Reels / Shorts
16:9  Landscape / YouTube / TV
1:1   Square
4:5   Feed
21:9  Cinematic wide jika provider support
```

### 8.2 Resolusi Video

```txt
720p
1080p
2K jika provider support
4K jika provider support
```

### 8.3 Duration

```txt
5 seconds
10 seconds
15 seconds
30 seconds jika provider support
60 seconds jika provider support
```

Max duration dapat dibatasi Super Admin.

### 8.4 Frame Rate

```txt
24 fps
30 fps
60 fps jika provider support
```

### 8.5 Video Type

```txt
Text to Video
Image to Video
Storyboard to Video
Campaign Ad
Explainer
Cinematic
Social Short
Public Service Announcement
Issue Awareness Video
Candidate/Party Branding Video
```

### 8.6 Camera Style

```txt
Static
Slow zoom
Pan
Dolly in
Dolly out
Drone-like
Handheld documentary
Cinematic tracking
News/documentary style
```

### 8.7 Video Output

Setiap video harus menyimpan:

```txt
- Final video file
- Thumbnail
- Prompt used
- Negative prompt
- Provider
- Model
- Duration
- Resolution
- Aspect ratio
- Cost estimate
- Job status
```

---

## 9. AI Provider Architecture

Creative Studio production wajib memisahkan model berdasarkan modality.

```txt
Text Model      ≠ Image Model ≠ Video Model ≠ Embedding Model
```

### 9.1 Provider Types

```txt
text
image
video
embedding
audio
browser
search
```

### 9.2 Text Provider

Digunakan untuk:

```txt
- Creative brief
- Caption
- Hook
- CTA
- Script
- Storyboard
- Prompt refinement
- Safety review
```

Contoh provider/model:

```txt
- OpenAI
- OpenRouter
- Anthropic
- DeepSeek
- Qwen
- Gemini
- Local LLM
```

### 9.3 Image Provider

Digunakan untuk generate gambar.

Contoh provider/model:

```txt
- OpenAI Images
- Ideogram
- Stability AI
- Leonardo
- Flux provider
- Custom image provider
```

### 9.4 Video Provider

Digunakan untuk generate video.

Contoh provider/model:

```txt
- Runway
- Kling
- Pika
- Luma
- Veo jika tersedia API
- Custom video provider
```

### 9.5 Provider Setting

Semua provider hanya bisa diatur oleh Super Admin.

Field:

```txt
- Provider name
- Provider type
- Base URL
- API key encrypted
- Status active/inactive
- Default model
- Fallback model
- Rate limit
- Cost limit
- Timeout
```

API key:

```txt
- Masked di frontend
- Encrypted di database
- Tidak pernah dikirim balik ke frontend
- Hanya bisa update, tidak bisa lihat ulang full key
```

---

## 10. Agent Settings

Menu khusus Super Admin:

```txt
Agent Settings
├── Creative Studio Agent
│   ├── General
│   ├── Text Model
│   ├── Image Model
│   ├── Video Model
│   ├── Prompt Settings
│   ├── Image Settings
│   ├── Video Settings
│   ├── Skills
│   ├── Provider Routing
│   ├── Cost Control
│   ├── Storage
│   ├── Approval
│   ├── Safety Rules
│   └── Logs
```

### 10.1 Text Model Settings

```txt
Provider
Base URL
API Key
Default text model
Fallback text model
Temperature
Max tokens
Timeout
```

### 10.2 Image Model Settings

```txt
Provider
Base URL
API Key
Default image model
Fallback image model
Default aspect ratio
Default resolution
Default quality
Max output count
Enable negative prompt
Enable reference image
```

### 10.3 Video Model Settings

```txt
Provider
Base URL
API Key
Default video model
Fallback video model
Default aspect ratio
Default resolution
Default duration
Max duration
Default FPS
Enable image-to-video
Enable storyboard-to-video
```

### 10.4 Cost Control Settings

```txt
Max image generations per user per day
Max video generations per user per day
Max video duration allowed
Max cost per request
Max cost per user per day
Require approval above cost threshold
```

### 10.5 Storage Settings

```txt
Storage provider
Bucket name
Image path
Video path
Thumbnail path
Retention policy
Max file size
```

Storage options:

```txt
- Local storage
- S3 compatible storage
- MinIO
- Cloudflare R2
- AWS S3
```

---

## 11. Provider Capability System

Setiap model harus punya capabilities agar UI bisa menyesuaikan opsi.

### 11.1 Image Model Capabilities

```txt
supports_text_to_image
supports_image_to_image
supports_reference_image
supports_negative_prompt
supports_seed
supports_1_1
supports_16_9
supports_9_16
supports_4_5
supports_3_4
supports_21_9
max_resolution
max_outputs
supported_qualities
supported_styles
```

### 11.2 Video Model Capabilities

```txt
supports_text_to_video
supports_image_to_video
supports_storyboard_to_video
supports_negative_prompt
supports_9_16
supports_16_9
supports_1_1
supports_4_5
supports_21_9
max_duration
max_resolution
supported_fps
supports_camera_control
supports_audio
supports_seed
```

### 11.3 UI Behavior

```txt
Jika user memilih model yang tidak support 30 detik,
maka opsi 30 detik disabled.

Jika user memilih model yang tidak support 4K,
maka opsi 4K disabled.

Jika provider tidak support negative prompt,
field negative prompt hidden/disabled.
```

---

## 12. Agent Detail

Nama agent utama:

```txt
Creative Studio Agent
```

Role:

```txt
AI agent yang bertugas membuat creative package, prompt gambar, prompt video, caption, script, storyboard, serta mengelola proses generate gambar dan video untuk kebutuhan kampanye politik berbasis data.
```

---

## 13. Struktur Multi-Agent

```txt
Creative Studio Orchestrator
├── Campaign Context Reader Agent
├── Creative Brief Agent
├── Content Angle Agent
├── Copywriting Agent
├── Image Prompt Agent
├── Image Generation Agent
├── Video Prompt Agent
├── Video Generation Agent
├── Scriptwriting Agent
├── Storyboard Agent
├── Visual Direction Agent
├── Platform Adaptation Agent
├── Brand Consistency Agent
├── Safety Review Agent
└── Asset Manager Agent
```

---

## 14. Detail Sub-Agent & Skill

### 14.1 Creative Studio Orchestrator

Tugas:

```txt
- Menerima request user
- Menentukan workflow kreatif
- Mengambil data dari Campaign Strategy
- Memilih sub-agent yang dibutuhkan
- Mengatur prompt, image, video, dan asset flow
```

Skill:

```txt
- Workflow Routing
- Task Planning
- Model Routing
- Result Aggregation
```

Risk:

```txt
Medium
```

### 14.2 Campaign Context Reader Agent

Tugas:

```txt
- Membaca campaign strategy report
- Mengambil positioning, narasi, segmentasi, isu prioritas, dan tone
- Menentukan konteks kreatif
```

Skill:

```txt
- Campaign Context Retrieval
- Strategy Summary
- Data Extraction
```

Risk:

```txt
Low
```

### 14.3 Creative Brief Agent

Tugas:

```txt
- Membuat creative brief
- Menentukan big idea
- Menentukan objective konten
- Menentukan audience dan key message
```

Skill:

```txt
- Creative Brief Generator
- Big Idea Generator
- Objective Mapping
```

Risk:

```txt
Low-Medium
```

### 14.4 Content Angle Agent

Tugas:

```txt
- Membuat angle konten
- Menentukan sudut cerita
- Menghubungkan isu dengan emosi publik
```

Skill:

```txt
- Content Angle Generator
- Narrative Angle
- Emotional Hook
```

Risk:

```txt
Medium
```

### 14.5 Copywriting Agent

Tugas:

```txt
- Membuat hook
- Membuat caption
- Membuat CTA
- Membuat headline
- Membuat variasi copy per platform
```

Skill:

```txt
- Hook Generator
- Caption Generator
- CTA Generator
- Headline Generator
- Platform Copy Adaptation
```

Risk:

```txt
Low-Medium
```

### 14.6 Image Prompt Agent

Tugas:

```txt
- Membuat prompt gambar
- Membuat negative prompt
- Menentukan style visual
- Menentukan komposisi
- Menyesuaikan brand SENA atau brand kampanye
```

Skill:

```txt
- Image Prompt Generator
- Negative Prompt Generator
- Visual Style Direction
- Composition Planning
```

Risk:

```txt
Medium
```

### 14.7 Image Generation Agent

Tugas:

```txt
- Memanggil provider image AI
- Mengirim prompt dan setting
- Mengelola job generate image
- Menyimpan hasil ke storage
```

Skill:

```txt
- Generate Image
- Provider Adapter
- Image Job Queue
- Image Storage
```

Risk:

```txt
Medium
```

### 14.8 Video Prompt Agent

Tugas:

```txt
- Membuat prompt video
- Membuat negative prompt video
- Menentukan camera movement
- Menentukan scene direction
```

Skill:

```txt
- Video Prompt Generator
- Scene Direction
- Camera Direction
- Motion Description
```

Risk:

```txt
Medium
```

### 14.9 Video Generation Agent

Tugas:

```txt
- Memanggil provider video AI
- Mengirim prompt, image reference, atau storyboard
- Mengelola queue video
- Polling status job
- Menyimpan video dan thumbnail
```

Skill:

```txt
- Generate Video
- Video Provider Adapter
- Video Job Queue
- Job Polling
- Video Storage
```

Risk:

```txt
Medium-High
```

### 14.10 Scriptwriting Agent

Tugas:

```txt
- Membuat script video
- Membuat voiceover
- Membuat on-screen text
- Membuat struktur 15–60 detik
```

Skill:

```txt
- Video Script Generator
- Voiceover Script
- On-screen Text Generator
```

Risk:

```txt
Low-Medium
```

### 14.11 Storyboard Agent

Tugas:

```txt
- Membuat storyboard scene by scene
- Menentukan visual, voiceover, text, durasi tiap scene
```

Skill:

```txt
- Storyboard Generator
- Scene Breakdown
- Shot Planning
```

Risk:

```txt
Low-Medium
```

### 14.12 Visual Direction Agent

Tugas:

```txt
- Menentukan warna, gaya visual, mood, tone, camera style
- Menyesuaikan format untuk poster, reels, TikTok, YouTube
```

Skill:

```txt
- Visual Direction
- Style Guide Matching
- Moodboard Description
```

Risk:

```txt
Low-Medium
```

### 14.13 Platform Adaptation Agent

Tugas:

```txt
- Menyesuaikan aset ke platform
- Membuat versi TikTok/Reels/Shorts
- Membuat versi feed, story, banner
```

Skill:

```txt
- Platform Adaptation
- Aspect Ratio Adaptation
- Format Recommendation
```

Risk:

```txt
Low
```

### 14.14 Brand Consistency Agent

Tugas:

```txt
- Mengecek kesesuaian dengan brand
- Mengecek tone, warna, font, gaya komunikasi
- Memberi catatan perbaikan
```

Skill:

```txt
- Brand Consistency Check
- Tone Check
- Visual Compliance Check
```

Risk:

```txt
Low
```

### 14.15 Safety Review Agent

Tugas:

```txt
- Mengecek prompt dan output agar aman
- Mencegah disinformasi, fitnah, hate speech, atau konten berbahaya
- Menandai konten yang perlu approval
```

Skill:

```txt
- Political Safety Review
- Misinformation Check
- Hate Speech Check
- Sensitive Content Check
```

Risk:

```txt
Medium-High
```

### 14.16 Asset Manager Agent

Tugas:

```txt
- Menyimpan asset
- Mengatur metadata
- Membuat thumbnail
- Menampilkan asset library
- Mengelola status approval
```

Skill:

```txt
- Save Asset
- Asset Metadata
- Thumbnail Management
- Approval Status
```

Risk:

```txt
Low
```

---

## 15. Skill Mapping

| Skill | Fungsi | Teknologi | Risk |
|---|---|---|---|
| Creative Brief Generator | Membuat brief | Text LLM | Low |
| Hook Generator | Membuat hook | Text LLM | Low |
| Caption Generator | Membuat caption | Text LLM | Low |
| CTA Generator | Membuat CTA | Text LLM | Low |
| Image Prompt Generator | Prompt gambar | Text LLM | Medium |
| Negative Prompt Generator | Negative prompt | Text LLM | Low |
| Generate Image | Generate gambar | Image AI Provider | Medium |
| Video Prompt Generator | Prompt video | Text LLM | Medium |
| Generate Video | Generate video | Video AI Provider | Medium-High |
| Script Generator | Script video | Text LLM | Low-Medium |
| Storyboard Generator | Storyboard | Text LLM | Low-Medium |
| Visual Direction | Style visual | Text LLM | Low |
| Platform Adaptation | Adaptasi platform | Text LLM | Low |
| Brand Consistency Check | Cek brand | LLM/rules | Low |
| Safety Review | Cek keamanan konten | LLM/rules | Medium-High |
| Asset Storage | Simpan file | S3/MinIO/local | Low |
| Job Queue | Antrian generate | Redis queue | Low |
| Provider Logs | Catat provider/cost | PostgreSQL | Low |

---

## 16. Risk Level

```txt
Low Risk:
- caption
- hook
- storyboard
- prompt
- creative brief

Medium Risk:
- generate image
- campaign visual
- image prompt untuk isu sensitif

Medium-High Risk:
- generate video
- video politik
- crisis response visual

High Risk:
- auto-posting
- publish iklan
- kirim konten ke publik
- ubah akun sosial media
```

Untuk production awal:

```txt
✅ Generate prompt
✅ Generate image
✅ Generate video
✅ Simpan asset
✅ Approval manual

❌ Jangan auto-post dulu
❌ Jangan auto-ads dulu
❌ Jangan auto-DM dulu
```

---

## 17. Safety Rules

Creative Studio harus punya safety layer.

Tidak boleh menghasilkan:

```txt
- Fitnah
- Disinformasi
- Klaim palsu
- Manipulasi data
- Hate speech
- Hasutan SARA
- Kekerasan politik
- Deepfake tokoh nyata tanpa izin
- Konten yang menyesatkan publik
- Klaim dukungan publik tanpa data
```

Wajib:

```txt
- Pisahkan fakta dan narasi kreatif
- Gunakan sumber data jika menyebut klaim
- Tandai konten yang perlu approval
- Simpan prompt dan output untuk audit
- Tampilkan warning untuk konten sensitif
```

---

## 18. Approval Workflow

Creative asset punya status:

```txt
draft
generated
pending_review
approved
rejected
archived
```

Flow:

```txt
Creative generated
↓
Status: pending_review
↓
Admin/Super Admin review
↓
Approve / Reject / Request revision
↓
Jika approved, asset bisa dipakai untuk Content Planner
```

Catatan:

```txt
Auto-publish tidak masuk scope awal.
```

---

## 19. Queue & Job System

Generate image dan video harus menggunakan queue.

### 19.1 Queue Flow

```txt
User klik Generate
↓
Laravel membuat creative_generation_job
↓
Job masuk Redis queue
↓
Worker/FastAPI memproses request
↓
Provider dipanggil
↓
Status job diperbarui
↓
Asset disimpan
↓
User mendapat hasil
```

### 19.2 Job Status

```txt
queued
processing
polling
completed
failed
cancelled
expired
```

### 19.3 Video Job Polling

Karena generate video bisa lama:

```txt
Submit video job
↓
Provider return job_id
↓
System polling status
↓
Jika completed, download/save video
↓
Jika failed, simpan error
```

---

## 20. Asset Library

Creative Studio harus memiliki asset library.

Fitur:

```txt
- Lihat semua asset
- Filter by type: image/video/script/prompt/storyboard
- Filter by campaign
- Filter by platform
- Filter by status
- Preview image/video
- Download asset
- Copy prompt
- Duplicate project
- Mark approved/rejected
- Archive asset
```

Asset types:

```txt
caption
hook
cta
image_prompt
video_prompt
image
video
script
storyboard
carousel
poster
thumbnail
```

---

## 21. Database Design

### 21.1 ai_providers

```txt
id
name
provider_type
base_url
api_key_encrypted
status
created_by
updated_by
created_at
updated_at
```

provider_type:

```txt
text
image
video
embedding
audio
browser
search
```

### 21.2 ai_models

```txt
id
provider_id
modality
model_name
display_name
capabilities_json
input_price
output_price
unit_price
status
created_at
updated_at
```

modality:

```txt
text
image
video
embedding
audio
```

### 21.3 creative_projects

```txt
id
user_id
campaign_strategy_report_id
title
campaign_object_type
campaign_object_name
objective
platform
tone
status
created_at
updated_at
```

### 21.4 creative_packages

```txt
id
project_id
creative_brief
big_idea
content_angles_json
hook_options_json
caption_options_json
cta_options_json
visual_style
script_json
storyboard_json
image_prompts_json
video_prompts_json
asset_specs_json
created_at
updated_at
```

### 21.5 creative_generation_jobs

```txt
id
user_id
project_id
asset_type
provider_id
model_id
prompt
negative_prompt
aspect_ratio
resolution
duration
fps
quality
style
camera_style
output_count
reference_asset_id
status
provider_job_id
cost_estimate
cost_final
error_message
started_at
finished_at
created_at
updated_at
```

asset_type:

```txt
image
video
```

### 21.6 creative_assets

```txt
id
project_id
job_id
asset_type
title
file_path
thumbnail_path
prompt_used
negative_prompt_used
provider_used
model_used
width
height
duration
fps
aspect_ratio
resolution
status
approval_status
metadata_json
created_at
updated_at
```

approval_status:

```txt
draft
pending_review
approved
rejected
archived
```

### 21.7 creative_asset_reviews

```txt
id
asset_id
reviewer_id
status
notes
created_at
updated_at
```

### 21.8 creative_provider_logs

```txt
id
user_id
provider_id
model_id
job_id
request_payload_json
response_payload_json
status
error_message
cost_estimate
cost_final
latency_ms
created_at
```

### 21.9 creative_usage_limits

```txt
id
role
max_images_per_day
max_videos_per_day
max_video_duration
max_cost_per_day
requires_approval_above_cost
created_at
updated_at
```

---

## 22. API Endpoint

### 22.1 Laravel API

```txt
GET    /api/creative-studio
POST   /api/creative-studio/projects
GET    /api/creative-studio/projects
GET    /api/creative-studio/projects/{id}
PUT    /api/creative-studio/projects/{id}
DELETE /api/creative-studio/projects/{id}

POST   /api/creative-studio/packages/generate
POST   /api/creative-studio/images/generate
POST   /api/creative-studio/videos/generate

GET    /api/creative-studio/jobs/{id}
GET    /api/creative-studio/assets
GET    /api/creative-studio/assets/{id}
POST   /api/creative-studio/assets/{id}/approve
POST   /api/creative-studio/assets/{id}/reject
DELETE /api/creative-studio/assets/{id}
```

### 22.2 FastAPI AI Service

```txt
POST /ai/creative-studio/package
POST /ai/creative-studio/image-prompt
POST /ai/creative-studio/video-prompt
POST /ai/creative-studio/script
POST /ai/creative-studio/storyboard
POST /ai/creative-studio/safety-review
POST /ai/creative-studio/brand-check
```

### 22.3 Image Provider Adapter

```txt
POST /ai/image/generate
POST /ai/image/status
POST /ai/image/download
```

### 22.4 Video Provider Adapter

```txt
POST /ai/video/generate
GET  /ai/video/status/{provider_job_id}
POST /ai/video/download
```

---

## 23. Frontend UI Structure

### 23.1 Creative Studio Main Page

```txt
Creative Studio
Buat prompt, gambar, dan video kampanye berbasis strategi.

Tabs:
- Creative Package
- Generate Image
- Generate Video
- Asset Library
```

### 23.2 Generate Image UI

```txt
Prompt
Negative Prompt
Aspect Ratio
Resolution
Output Count
Quality
Style
Reference Image
Generate Button
```

### 23.3 Generate Video UI

```txt
Prompt
Negative Prompt
Video Type
Aspect Ratio
Resolution
Duration
FPS
Camera Style
Reference Image
Storyboard
Generate Button
```

### 23.4 Asset Library UI

```txt
Filters:
- Type
- Platform
- Campaign
- Status
- Date

Asset list/grid:
- Thumbnail
- Type
- Model
- Status
- Approval
- Actions
```

---

## 24. UI Theme

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

Dark mode:

```txt
Background: #0B0B0B
Surface: #151515
Text: #FFFFFF
Accent Red: #EF233C
Border: #2A2A2A
```

Light mode:

```txt
Background: #FFFFFF
Surface: #F8F8F8
Text: #111111
Accent Red: #D71920
Border: #E5E5E5
```

---

## 25. Permission

### Super Admin

```txt
✅ Semua akses Creative Studio
✅ Atur provider text/image/video
✅ Atur API key
✅ Atur model default/fallback
✅ Atur cost limit
✅ Atur storage
✅ Atur approval rules
✅ Lihat semua logs
```

### Admin

```txt
✅ Generate creative package
✅ Generate image/video jika diizinkan
✅ Review/approve asset jika diberi izin
✅ Lihat asset tim
❌ Tidak bisa atur provider/API key/model
```

### Analyst

```txt
✅ Generate prompt/package
✅ Generate image/video sesuai limit
✅ Lihat asset sendiri
❌ Tidak bisa akses settings
```

### Viewer

```txt
✅ Lihat asset yang dibagikan
❌ Tidak bisa generate jika tidak diberi izin
❌ Tidak bisa akses settings
```

---

## 26. Model Routing

### 26.1 Creative Package Routing

```txt
creative_brief:
- text reasoning/creative model

caption_hook_cta:
- creative text model

script_storyboard:
- creative/reasoning model

image_prompt:
- text prompt model

video_prompt:
- text prompt model

safety_review:
- safety/reasoning model
```

### 26.2 Image Routing

```txt
image_generation:
- image model

image_fallback:
- fallback image model
```

### 26.3 Video Routing

```txt
video_generation:
- video model

video_fallback:
- fallback video model
```

### 26.4 Routing Rules

```txt
Jika image provider gagal:
→ coba fallback image provider.

Jika video provider gagal:
→ coba fallback video provider.

Jika request melebihi cost limit:
→ require Super Admin approval.

Jika model tidak support opsi user:
→ UI disable opsi tersebut.
```

---

## 27. Cost Control

Creative Studio wajib punya cost control karena image/video bisa mahal.

Rules:

```txt
- Estimate cost before generate
- Show cost estimate to user
- Daily limit per role
- Max video duration per role
- Max output count per request
- Require approval above threshold
- Log provider cost
```

Contoh:

```txt
Analyst:
- Max image/day: 20
- Max video/day: 3
- Max video duration: 10s

Admin:
- Max image/day: 100
- Max video/day: 10
- Max video duration: 30s
```

---

## 28. Prompt Template

### 28.1 Image Prompt Template

```txt
Create a campaign visual for [campaign_object].
Objective: [objective]
Target audience: [target_audience]
Platform: [platform]
Aspect ratio: [aspect_ratio]
Visual style: [style]
Tone: [tone]
Brand colors: [brand_colors]
Key message: [key_message]
Scene description: [scene]
Avoid: [negative_prompt]
```

### 28.2 Video Prompt Template

```txt
Create a [duration] second [aspect_ratio] campaign video for [campaign_object].
Objective: [objective]
Target audience: [target_audience]
Platform: [platform]
Tone: [tone]
Visual style: [style]
Camera movement: [camera_style]
Scene sequence:
1. [scene_1]
2. [scene_2]
3. [scene_3]
On-screen text: [text]
Avoid: [negative_prompt]
```

---

## 29. JSON Output Format

### 29.1 Creative Package JSON

```json
{
  "project_title": "PSI Papua Youth Awareness Campaign",
  "creative_brief": "...",
  "big_idea": "...",
  "content_angles": [],
  "hook_options": [],
  "caption_options": [],
  "cta_options": [],
  "visual_style": "...",
  "image_prompts": [
    {
      "title": "...",
      "prompt": "...",
      "negative_prompt": "...",
      "aspect_ratio": "1:1",
      "recommended_platform": "Instagram"
    }
  ],
  "video_prompts": [
    {
      "title": "...",
      "prompt": "...",
      "negative_prompt": "...",
      "aspect_ratio": "9:16",
      "duration": "10s",
      "recommended_platform": "TikTok"
    }
  ],
  "script": {
    "duration": "30s",
    "voiceover": "...",
    "on_screen_text": []
  },
  "storyboard": [
    {
      "scene": 1,
      "visual": "...",
      "voiceover": "...",
      "on_screen_text": "...",
      "duration_seconds": 5
    }
  ],
  "asset_specs": {
    "platform": "...",
    "format": "...",
    "aspect_ratio": "...",
    "resolution": "..."
  },
  "safety_notes": []
}
```

### 29.2 Generation Job JSON

```json
{
  "job_id": "uuid",
  "asset_type": "image",
  "status": "queued",
  "provider": "OpenAI Images",
  "model": "image-model",
  "prompt": "...",
  "negative_prompt": "...",
  "aspect_ratio": "1:1",
  "resolution": "1024x1024",
  "quality": "high",
  "output_count": 4,
  "cost_estimate": 0.12
}
```

---

## 30. Acceptance Criteria

Modul dianggap berhasil jika:

```txt
1. User bisa membuka Creative Studio.
2. User bisa membuat creative package dari Campaign Strategy.
3. User bisa membuat image prompt.
4. User bisa membuat video prompt.
5. User bisa generate gambar.
6. User bisa generate video.
7. User bisa memilih aspect ratio gambar: 1:1, 16:9, 9:16, 4:5.
8. User bisa memilih aspect ratio video: 9:16, 16:9, 1:1, 4:5.
9. User bisa memilih resolusi gambar.
10. User bisa memilih resolusi video.
11. User bisa memilih durasi video.
12. Opsi yang tidak didukung model otomatis disabled.
13. Generate image/video berjalan lewat queue.
14. Hasil gambar/video tersimpan di storage.
15. Asset muncul di Asset Library.
16. Prompt dan metadata tersimpan.
17. API key provider masked dan encrypted.
18. Text/image/video provider dipisah.
19. Super Admin bisa mengatur provider/model/API key.
20. Role selain Super Admin tidak bisa akses provider/API key/model settings.
21. Sistem menampilkan cost estimate.
22. Sistem menerapkan daily limit.
23. Sistem punya approval status.
24. Sistem punya safety review.
25. UI mengikuti tema SENA.
```

---

## 31. Prioritas Development

### Phase 1 — Core Creative Package

```txt
- UI Creative Studio
- Generate creative brief
- Generate caption/hook/CTA
- Generate script/storyboard
- Generate image/video prompt
```

### Phase 2 — Provider Settings

```txt
- Text provider settings
- Image provider settings
- Video provider settings
- API key encryption
- Model capabilities
- Model routing
```

### Phase 3 — Image Generation

```txt
- Image generation UI
- Aspect ratio/resolution selector
- Image provider adapter
- Queue job
- Save image to storage
- Asset library image preview
```

### Phase 4 — Video Generation

```txt
- Video generation UI
- Aspect ratio/resolution/duration selector
- Video provider adapter
- Queue job + polling
- Save video + thumbnail
- Asset library video preview
```

### Phase 5 — Production Controls

```txt
- Cost estimate
- Daily limits
- Approval workflow
- Safety review
- Provider logs
- Usage analytics
```

---

## 32. Brief Pendek untuk Developer / Codex

```txt
Build the fifth module for SENA: Creative Studio.

Stack:
React frontend, Laravel 11 backend, FastAPI AI service, PostgreSQL, Redis queue, S3/MinIO-compatible storage, Docker.

Scope:
Production-scale creative generator for political campaign assets. It must generate creative package, captions, hooks, CTAs, scripts, storyboards, image prompts, video prompts, AI images, and AI videos.

AI Provider:
Separate providers and models for text, image, and video. Super Admin can manage provider, base URL, API key, default model, fallback model, capabilities, cost limit, and routing. API keys must be encrypted and masked.

Image Generator:
Support aspect ratios 1:1, 16:9, 9:16, 4:5, 3:4, 21:9 if provider supports. Support resolution, quality, output count, style, negative prompt, and reference image.

Video Generator:
Support portrait, landscape, square, feed, duration, resolution, FPS, camera movement, text-to-video, image-to-video, and storyboard-to-video if provider supports.

Queue:
Image and video generation must run in queue. Video requires provider job polling.

Storage:
Save generated images, videos, thumbnails, prompts, metadata, provider/model info, cost estimate, and approval status.

UI:
White, black, red accent, compact Poppins font, outline icons, dark mode. Build tabs: Creative Package, Generate Image, Generate Video, Asset Library.

Permissions:
Only Super Admin can manage agent settings, provider settings, API keys, model routing, cost control, storage, and safety rules. Other roles can generate assets based on permission and limits.

Safety:
No disinformation, hate speech, baseless attacks, fake data, or unauthorized deepfake. Add safety review and approval workflow before assets are used in Content Planner or publishing.
```
