from datetime import date, datetime
import json
import os
from pathlib import Path
import re
from typing import Any
from urllib.parse import quote

import httpx
from fastapi import FastAPI, Header, HTTPException
from pydantic import BaseModel, Field

try:
    from playwright.async_api import async_playwright
except Exception:  # pragma: no cover - optional runtime dependency
    async_playwright = None


app = FastAPI(title="Political Intelligence AI Service", version="1.0.0")


class ProviderConfig(BaseModel):
    name: str | None = None
    base_url: str | None = None
    api_key: str | None = None


class AgentConfig(BaseModel):
    name: str
    role: str
    system_prompt: str | None = None
    temperature: float = 0.4
    max_tokens: int = 8000
    provider: ProviderConfig | None = None
    model: str | None = None
    skills: list[dict[str, Any]] = Field(default_factory=list)


class ScreeningRequest(BaseModel):
    subject_name: str = Field(min_length=1, max_length=160)
    agent: AgentConfig


def verify_internal_token(token: str | None) -> None:
    expected = os.getenv("INTERNAL_SERVICE_TOKEN", "local-dev-token")

    if not token or token != expected:
        raise HTTPException(status_code=401, detail="Invalid internal token.")


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok"}


@app.post("/internal/ai/provider/test-connection")
def test_provider(payload: ProviderConfig, x_internal_token: str | None = Header(default=None)) -> dict[str, Any]:
    verify_internal_token(x_internal_token)

    return {
        "ok": True,
        "provider": payload.name,
        "base_url": payload.base_url,
        "message": "Provider configuration accepted by AI service.",
    }


@app.post("/internal/ai/screening/generate")
async def generate_screening(payload: ScreeningRequest, x_internal_token: str | None = Header(default=None)) -> dict[str, Any]:
    verify_internal_token(x_internal_token)

    wikipedia = await fetch_wikipedia_context(payload.subject_name)
    browser_pages = await collect_browser_context(payload.subject_name, wikipedia, payload.agent)

    if should_use_provider(payload.agent):
        try:
            result = await generate_with_provider(payload, wikipedia, browser_pages)
            return attach_browser_sources(attach_wikipedia_source(result, wikipedia), browser_pages)
        except Exception as exc:
            if os.getenv("AI_FALLBACK_ON_PROVIDER_ERROR", "false").lower() not in {"1", "true", "yes"}:
                raise HTTPException(status_code=502, detail=f"Provider AI gagal menghasilkan JSON valid: {exc}") from exc

            fallback = build_mock_report(payload.subject_name, wikipedia, browser_pages)
            fallback["provider_error"] = str(exc)
            fallback["executive_summary"] = (
                fallback["executive_summary"]
                + " Provider AI gagal dipanggil, sehingga laporan ini memakai fallback lokal."
            )
            return fallback

    return build_mock_report(payload.subject_name, wikipedia, browser_pages)


def should_use_provider(agent: AgentConfig) -> bool:
    provider = agent.provider

    return bool(
        provider
        and provider.base_url
        and provider.api_key
        and agent.model
        and agent.model != "political-screening-mock"
    )


async def generate_with_provider(
    payload: ScreeningRequest,
    wikipedia: dict[str, str] | None,
    browser_pages: list[dict[str, Any]],
) -> dict[str, Any]:
    provider = payload.agent.provider
    assert provider is not None

    prompt = build_screening_prompt(payload.subject_name, wikipedia, browser_pages, payload.agent.skills)
    request_payload = {
        "model": payload.agent.model,
        "messages": [
            {
                "role": "system",
                "content": build_system_prompt(payload.agent.system_prompt),
            },
            {"role": "user", "content": prompt},
        ],
        "temperature": payload.agent.temperature,
        "max_tokens": max(payload.agent.max_tokens, int(os.getenv("AI_MIN_REPORT_MAX_TOKENS", "16000"))),
    }

    if os.getenv("AI_PROVIDER_RESPONSE_FORMAT", "json_object") == "json_object":
        request_payload["response_format"] = {"type": "json_object"}

    timeout = float(os.getenv("AI_PROVIDER_TIMEOUT", "7200"))

    async with httpx.AsyncClient(timeout=timeout) as client:
        response = await client.post(
            f"{provider.base_url.rstrip('/')}/chat/completions",
            headers={
                "Authorization": f"Bearer {provider.api_key}",
                "Content-Type": "application/json",
            },
            json=request_payload,
        )
        response.raise_for_status()

        content = response.json()["choices"][0]["message"]["content"]
        parsed = await parse_or_repair_json_content(client, provider, payload.agent, content)

    parsed.setdefault("subject_name", payload.subject_name.strip())
    parsed.setdefault("sources", [])

    return parsed


async def parse_or_repair_json_content(
    client: httpx.AsyncClient,
    provider: ProviderConfig,
    agent: AgentConfig,
    content: str,
) -> dict[str, Any]:
    try:
        return parse_json_content(content)
    except Exception as parse_error:
        repaired = await repair_json_with_provider(client, provider, agent, content, str(parse_error))
        return parse_json_content(repaired)


async def repair_json_with_provider(
    client: httpx.AsyncClient,
    provider: ProviderConfig,
    agent: AgentConfig,
    broken_content: str,
    parse_error: str,
) -> str:
    max_raw_chars = int(os.getenv("AI_JSON_REPAIR_MAX_RAW_CHARS", "70000"))
    repair_payload: dict[str, Any] = {
        "model": agent.model,
        "messages": [
            {
                "role": "system",
                "content": (
                    "Kamu adalah mesin perbaikan JSON. Tugasmu hanya memperbaiki JSON rusak "
                    "menjadi JSON valid. Jangan meringkas, jangan mengubah makna, jangan tambah Markdown."
                ),
            },
            {
                "role": "user",
                "content": (
                    "Perbaiki output berikut menjadi JSON valid sesuai struktur screening report. "
                    "Return JSON object saja. Error parser: "
                    f"{parse_error}\n\nOUTPUT RUSAK:\n{broken_content[:max_raw_chars]}"
                ),
            },
        ],
        "temperature": 0,
        "max_tokens": max(agent.max_tokens, int(os.getenv("AI_MIN_REPORT_MAX_TOKENS", "16000"))),
    }

    if os.getenv("AI_PROVIDER_RESPONSE_FORMAT", "json_object") == "json_object":
        repair_payload["response_format"] = {"type": "json_object"}

    response = await client.post(
        f"{provider.base_url.rstrip('/')}/chat/completions",
        headers={
            "Authorization": f"Bearer {provider.api_key}",
            "Content-Type": "application/json",
        },
        json=repair_payload,
    )
    response.raise_for_status()

    return response.json()["choices"][0]["message"]["content"]


def has_active_skill(agent: AgentConfig, slug: str) -> bool:
    return any(skill.get("slug") == slug for skill in agent.skills)


async def collect_browser_context(
    subject_name: str,
    wikipedia: dict[str, str] | None,
    agent: AgentConfig,
) -> list[dict[str, Any]]:
    if not has_active_skill(agent, "browser-automation"):
        return []

    urls = []

    if wikipedia and wikipedia.get("url"):
        urls.append(wikipedia["url"])

    snapshots = []

    for url in urls[: int(os.getenv("BROWSER_MAX_PAGES", "1"))]:
        try:
            snapshots.append(await browse_page(url, subject_name))
        except Exception as exc:
            snapshots.append(
                {
                    "url": url,
                    "title": "Browser Automation gagal",
                    "text": f"Halaman gagal dibuka oleh browser automation: {exc}",
                    "screenshot_path": None,
                    "status": "failed",
                }
            )

    return snapshots


async def browse_page(url: str, subject_name: str) -> dict[str, Any]:
    if async_playwright is None:
        return {
            "url": url,
            "title": "Browser Automation belum aktif",
            "text": "Dependency Playwright belum tersedia di runtime AI service.",
            "screenshot_path": None,
            "status": "unavailable",
        }

    timeout_ms = int(float(os.getenv("BROWSER_TIMEOUT", "12")) * 1000)
    screenshot_dir = Path(os.getenv("BROWSER_SCREENSHOT_DIR", "storage/browser-screenshots"))
    screenshot_dir.mkdir(parents=True, exist_ok=True)
    safe_name = re.sub(r"[^a-zA-Z0-9_-]+", "-", subject_name.strip()).strip("-") or "subject"
    screenshot_path = screenshot_dir / f"{safe_name}-{datetime.utcnow().strftime('%Y%m%d%H%M%S')}.png"

    async with async_playwright() as playwright:
        browser = await playwright.chromium.launch(headless=True, args=["--no-sandbox"])
        page = await browser.new_page(viewport={"width": 1366, "height": 900})

        try:
            response = await page.goto(url, wait_until="domcontentloaded", timeout=timeout_ms)
            await page.wait_for_timeout(900)
            await page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
            await page.wait_for_timeout(900)
            title = await page.title()
            text = await page.evaluate("document.body ? document.body.innerText : ''")
            await page.screenshot(path=str(screenshot_path), full_page=False)
        finally:
            await browser.close()

    return {
        "url": url,
        "title": title,
        "status_code": response.status if response else None,
        "text": text[: int(os.getenv("BROWSER_TEXT_LIMIT", "6000"))],
        "screenshot_path": str(screenshot_path),
        "status": "ok",
    }


async def fetch_wikipedia_context(subject_name: str) -> dict[str, str] | None:
    headers = {
        "User-Agent": os.getenv(
            "WIKIPEDIA_USER_AGENT",
            "PoliticalIntelligenceMVP/1.0 (local-development; contact: admin@example.com)",
        ),
        "Accept": "application/json",
    }

    async with httpx.AsyncClient(timeout=20, headers=headers) as client:
        title = await find_wikipedia_title(client, subject_name, "id")
        lang = "id"

        if not title:
            title = await find_wikipedia_title(client, subject_name, "en")
            lang = "en"

        if not title:
            return None

        response = await client.get(f"https://{lang}.wikipedia.org/api/rest_v1/page/summary/{quote(title)}")

        if response.status_code != 200:
            return None

        data = response.json()

        return {
            "title": data.get("title", title.replace("_", " ")),
            "extract": data.get("extract", ""),
            "url": data.get("content_urls", {}).get("desktop", {}).get("page", f"https://{lang}.wikipedia.org/wiki/{title}"),
            "lang": lang,
        }


async def find_wikipedia_title(client: httpx.AsyncClient, subject_name: str, lang: str) -> str | None:
    response = await client.get(
        f"https://{lang}.wikipedia.org/w/api.php",
        params={
            "action": "query",
            "list": "search",
            "srsearch": subject_name,
            "format": "json",
            "srlimit": 1,
        },
    )

    if response.status_code != 200:
        return None

    results = response.json().get("query", {}).get("search", [])

    if not results:
        return None

    return results[0]["title"].replace(" ", "_")


def attach_wikipedia_source(result: dict[str, Any], wikipedia: dict[str, str] | None) -> dict[str, Any]:
    if not wikipedia:
        return result

    sources = result.setdefault("sources", [])
    wiki_url = wikipedia["url"]
    today = date.today().isoformat()

    existing_source = next(
        (source for source in sources if isinstance(source, dict) and source.get("link") == wiki_url),
        None,
    )

    if existing_source:
        existing_source["name"] = f"Wikipedia - {wikipedia['title']}"
        existing_source["accessed_at"] = today
        existing_source["type"] = "Ensiklopedia publik"
    else:
        sources.append(
            {
                "name": f"Wikipedia - {wikipedia['title']}",
                "link": wiki_url,
                "accessed_at": today,
                "type": "Ensiklopedia publik",
            }
        )

    return result


def attach_browser_sources(result: dict[str, Any], browser_pages: list[dict[str, Any]]) -> dict[str, Any]:
    if not browser_pages:
        return result

    sources = result.setdefault("sources", [])

    for page in browser_pages:
        if page.get("status") != "ok":
            continue

        sources.append(
            {
                "name": f"Browser Automation - {page.get('title') or page['url']}",
                "link": page["url"],
                "accessed_at": date.today().isoformat(),
                "type": f"Halaman web dibaca via browser; screenshot: {page.get('screenshot_path')}",
            }
        )

    return result


def build_system_prompt(existing_prompt: str | None) -> str:
    base = existing_prompt or "You are a neutral Political Intelligence Analyst."

    return f"""
{base}

Instruksi wajib:
- Semua output harus menggunakan Bahasa Indonesia.
- Return structured JSON only, tanpa Markdown di luar JSON.
- Isi laporan harus detail, operasional, dan mirip format briefing analis politik.
- Gunakan bullet berbasis tanda "-" di dalam string panjang agar mudah dibaca.
- Jangan mengarang fakta.
- Jika data tidak tersedia, tulis bahwa data belum ditemukan.
- Pisahkan fakta terverifikasi, tuduhan, klarifikasi, dan opini.
- Jangan menulis tuduhan sebagai fakta.
- Gunakan gaya laporan intelijen yang netral dan profesional.

Konteks Tokoh Screening Framework:
- Framework ini menghasilkan 12 bagian wajib: Profil Tokoh, Karier Politik, Jejak Digital, Kontroversi, Analisis Sentimen, Data Elektabilitas, Analisis Basis Daerah, SWOT Analysis, Skor Akhir, Insight Menaikkan Elektabilitas, Rekomendasi Strategis, Sumber Data.
- Tidak boleh ada bagian yang di-skip atau partial.
- Skor akhir menggunakan indikator 1-10: Elektabilitas, Pengalaman Politik, Jaringan Partai, Jaringan Sosial/Adat, Risiko Kontroversi, Potensi Maju Lagi, King Maker/Influence.
- Analisis basis daerah harus spesifik: untuk tokoh lokal sebutkan kota/kabupaten, untuk tokoh nasional sebutkan provinsi, masing-masing dengan alasan politik.
- Data sumber prioritas: Wikipedia > Detik/Kompas/CNN/Tempo > Local news > YouTube > Survey firms.
- Survey elektabilitas jarang ada untuk tokoh lokal — catat "tidak ditemukan" jika memang tidak ada.
"""


def parse_json_content(content: str) -> dict[str, Any]:
    cleaned = content.strip()

    if cleaned.startswith("```"):
        cleaned = cleaned.strip("`")
        if cleaned.startswith("json"):
            cleaned = cleaned[4:].strip()

    start = cleaned.find("{")
    end = cleaned.rfind("}")

    if start == -1 or end == -1:
        raise ValueError("Provider response did not contain JSON.")

    return json.loads(cleaned[start : end + 1])


def build_screening_prompt(
    subject_name: str,
    wikipedia: dict[str, str] | None,
    browser_pages: list[dict[str, Any]] | None = None,
    active_skills: list[dict[str, Any]] | None = None,
) -> str:
    wiki_block = (
        f"""
Konteks Wikipedia yang boleh digunakan:
- Judul: {wikipedia["title"]}
- Bahasa sumber: {wikipedia["lang"]}
- Ringkasan: {wikipedia["extract"]}
- Link: {wikipedia["url"]}
"""
        if wikipedia
        else "Konteks Wikipedia: artikel Wikipedia belum ditemukan untuk nama ini."
    )
    browser_block = build_browser_prompt_block(browser_pages or [])
    skill_block = build_skill_prompt_block(active_skills or [])

    return f"""
Buat SCREENING LENGKAP tokoh politik untuk: {subject_name}

{wiki_block}

{browser_block}

{skill_block}

Kembalikan JSON saja dengan struktur persis seperti ini:
{{
  "subject_name": "{subject_name}",
  "executive_summary": "string",
  "profile": "string",
  "political_career": "string",
  "digital_footprint": "string",
  "controversies": [
    {{"issue": "string", "status": "string", "source": "string", "political_risk": "string"}}
  ],
  "sentiment_analysis": "string",
  "electability_data": "string",
  "regional_base_analysis": "string",
  "swot": {{"strengths": [], "weaknesses": [], "opportunities": [], "threats": []}},
  "final_score": {{
    "score": 55,
    "category": "string",
    "reason": "string",
    "indicators": [
      {{"name": "Elektabilitas", "score": 6.5, "note": "string"}},
      {{"name": "Pengalaman Politik", "score": 8.0, "note": "string"}},
      {{"name": "Jaringan Partai", "score": 7.0, "note": "string"}},
      {{"name": "Jaringan Sosial/Adat", "score": 7.0, "note": "string"}},
      {{"name": "Risiko Kontroversi", "score": 5.5, "note": "string"}},
      {{"name": "Potensi Maju Lagi", "score": 7.0, "note": "string"}},
      {{"name": "King Maker / Influence", "score": 6.0, "note": "string"}}
    ]
  }},
  "electability_improvement_insights": "string",
  "strategic_recommendations": {{"high": [], "medium": [], "low": []}},
  "sources": [
    {{"name": "string", "link": "string", "accessed_at": "YYYY-MM-DD", "type": "string"}}
  ]
}}

Aturan:
- Semua value string wajib Bahasa Indonesia.
- Gunakan data Wikipedia sebagai salah satu sumber awal jika tersedia.
- Jika data Wikipedia tidak cukup, tulis bahwa data belum ditemukan atau belum cukup kuat.
- Jangan mengarang angka elektabilitas, jabatan, partai, kontroversi, atau sumber.
- Pisahkan fakta terverifikasi, tuduhan, klarifikasi, dan opini.
- Jangan menulis tuduhan sebagai fakta.
- Bagian "sources" wajib mencantumkan Wikipedia jika konteks Wikipedia tersedia.
- Field sources.accessed_at untuk Wikipedia wajib memakai tanggal akses hari ini: {date.today().isoformat()}.
- final_score.score harus berupa angka 0-100 hasil penilaian. Jika data belum cukup, gunakan skor konservatif 55 dan jelaskan alasannya.
- Gaya bahasa netral, profesional, dan ringkas.

Kedalaman wajib per bagian:
- executive_summary: 4-6 kalimat, jelaskan status data, kekuatan utama, kelemahan utama, risiko utama, dan kesimpulan awal.
- profile: formatkan sebagai beberapa subjudul seperti "Data Diri:", "Afiliasi/Jabatan:", "Kontestasi/Posisi Politik:" lalu bullet. Jika tidak ada data, tulis eksplisit "belum ditemukan".
- political_career: minimal 5 bullet jika data tersedia. Bedakan jabatan, aktivitas politik, pencalonan, program, dan konsolidasi partai.
- digital_footprint: bahas media online, media sosial, YouTube, intensitas kemunculan, narasi dominan, dan kelemahan digital.
- controversies: tulis array isu. Setiap isu wajib punya status, sumber, dan risiko politik. Jika hanya dugaan, tulis "Tuduhan/isu, belum menjadi fakta final".
- sentiment_analysis: pecah menjadi "Sentimen Positif", "Sentimen Negatif", dan "Sentimen Netral" dengan bullet.
- electability_data: bahas pemilu/pilkada, rekomendasi partai, dukungan relawan/adat/komunitas, survei jika ada, dan tulis jelas jika hasil akhir/survei tidak ditemukan.
- regional_base_analysis: pecah menjadi "Daerah Kuat", "Daerah Lemah", dan "Wilayah Swing/Peluang Ekspansi". Untuk tiap wilayah, tulis alasan politiknya. Untuk tokoh lokal sebutkan kota/kabupaten spesifik, untuk tokoh nasional sebutkan provinsi.
- swot: tiap daftar minimal 5 item jika data cukup; item harus spesifik terhadap tokoh, bukan template umum.
- final_score.indicators: beri skor 0-10 per indikator beserta alasan singkat. Wajib 7 indikator: Elektabilitas, Pengalaman Politik, Jaringan Partai, Jaringan Sosial/Adat, Risiko Kontroversi, Potensi Maju Lagi, King Maker/Influence. final_score.reason wajib menjelaskan total.
- electability_improvement_insights: buat strategi bernomor: perkuat basis kuat, perluas basis lemah, manfaatkan isu, build digital presence, damage control isu negatif.
- strategic_recommendations: high/medium/low masing-masing 3-5 aksi konkret dengan horizon waktu jika relevan.
- sources: tulis semua sumber yang benar-benar digunakan. Jangan mencantumkan sumber yang tidak dipakai.

Rubrik Skor Indikator (0-10):
- Elektabilitas: 1-3 belum pernah maju/tidak dikenal, 4-5 pernah maju kalah, 6-7 cukup populer di daerah, 8-9 menang pilkada/masuk survey nasional, 10 petahana sangat tinggi
- Pengalaman Politik: 1-3 baru terjun <2 tahun, 4-5 legislatif 1 periode, 6-7 2+ periode atau jabatan eksekutif, 8-9 jabatan strategis (Ketua DPR/Menteri), 10 multi-decade multi-office
- Jaringan Partai: 1-3 hanya 1 partai tanpa koalisi, 4-5 1-2 partai terbatas, 6-7 3+ partai solid, 8-9 king maker kontrol banyak partai, 10 sentral koalisi nasional
- Jaringan Sosial/Adat: 1-3 tidak ada jaringan, 4-5 1-2 komunitas, 6-7 beberapa komunitas adat/organisasi, 8-9 dukungan luas berbagai elemen, 10 dukungan massif semua elemen
- Risiko Kontroversi (semakin tinggi semakin berisiko): 1-3 bersih, 4-5 isu kecil belum terbukti, 6-7 pernah diperiksa/dilaporkan, 8-9 tersangka/kasus serius, 10 terpidana/kasus sangat berat
- Potensi Maju Lagi: 1-3 tidak ada indikasi, 4-5 mungkin tapi belum jelas, 6-7 sudah deklarasi/sinyal kuat, 8-9 siap dengan modal kuat, 10 sangat pasti modal sangat kuat
- King Maker/Influence: 1-3 tidak punya pengaruh signifikan, 4-5 pengaruh kabupaten/kota, 6-7 pengaruh provinsi, 8-9 pengaruh nasional/king maker partai, 10 king maker nasional pengaruh sangat besar

Interpretasi Total Skor:
- < 4: Tokoh dengan potensi rendah
- 4-5.9: Tokoh dengan potensi menengah-rendah
- 6-7.9: Tokoh dengan potensi menengah-tinggi
- 8-9.9: Tokoh dengan potensi tinggi
- 10: Tokoh elite nasional
"""


def build_browser_prompt_block(browser_pages: list[dict[str, Any]]) -> str:
    if not browser_pages:
        return "Konteks Browser Automation: tidak ada halaman yang dibuka pada run ini."

    lines = ["Konteks Browser Automation yang boleh digunakan:"]

    for index, page in enumerate(browser_pages, start=1):
        lines.extend(
            [
                f"Halaman {index}:",
                f"- URL: {page.get('url')}",
                f"- Judul: {page.get('title')}",
                f"- Status: {page.get('status')}",
                f"- Screenshot: {page.get('screenshot_path') or 'tidak tersedia'}",
                f"- Teks halaman hasil baca/scroll: {page.get('text', '')}",
            ]
        )

    return "\n".join(lines)


def build_skill_prompt_block(active_skills: list[dict[str, Any]]) -> str:
    if not active_skills:
        return "Skill aktif agent: tidak ada skill aktif yang dikirim."

    lines = ["Skill aktif agent pada run ini. Gunakan instruksi skill di bawah sebagai panduan tambahan:"]

    for skill in active_skills:
        name = skill.get("name") or skill.get("slug", "Unknown")
        slug = skill.get("slug", "")
        prompt_content = skill.get("prompt_content")

        lines.append(f"\n### Skill: {name} ({slug})")

        if prompt_content:
            lines.append(prompt_content.strip())
        else:
            lines.append("(Tidak ada instruksi tambahan untuk skill ini.)")

    return "\n".join(lines)


def build_mock_report(
    subject_name: str,
    wikipedia: dict[str, str] | None = None,
    browser_pages: list[dict[str, Any]] | None = None,
) -> dict[str, Any]:
    name = subject_name.strip()
    today = date.today().isoformat()
    wiki_summary = wikipedia["extract"] if wikipedia else None

    report = {
        "subject_name": name,
        "executive_summary": (
            f"Laporan awal untuk {name} disusun sebagai screening MVP. "
            + (f"Ringkasan Wikipedia yang ditemukan: {wiki_summary} " if wiki_summary else "")
            + (
                "Browser Automation berhasil membuka halaman sumber dan mengambil screenshot. "
                if browser_pages
                else "Data tambahan di luar Wikipedia belum dipindai secara penuh, sehingga bagian faktual tetap perlu verifikasi."
            )
        ),
        "profile": (
            f"Nama tokoh: {name}. "
            + (f"Berdasarkan ringkasan Wikipedia: {wiki_summary}" if wiki_summary else "Artikel Wikipedia belum ditemukan untuk nama ini.")
        ),
        "political_career": (
            "Riwayat jabatan, pengalaman legislatif/eksekutif, riwayat pencalonan, dan posisi struktur partai "
            "belum cukup kuat untuk disimpulkan tanpa integrasi sumber publik atau database internal."
        ),
        "digital_footprint": (
            "Jejak digital belum dipindai secara langsung. Setelah Web Search aktif, bagian ini perlu memuat "
            "pemberitaan dominan, isu yang dikaitkan, gaya komunikasi digital, dan intensitas eksposur media."
        ),
        "controversies": [
            {
                "issue": "Belum ada isu kontroversi yang diverifikasi pada mode MVP lokal.",
                "status": "Data belum ditemukan",
                "source": "Tidak tersedia",
                "political_risk": "Tidak dapat dinilai tanpa sumber.",
            }
        ],
        "sentiment_analysis": (
            "Sentimen umum belum dapat disimpulkan karena data berita dan media sosial belum dikumpulkan. "
            "Sistem tidak membuat estimasi sentimen tanpa sumber."
        ),
        "electability_data": (
            "Data elektabilitas publik belum ditemukan atau belum cukup kuat untuk disimpulkan."
        ),
        "regional_base_analysis": (
            "Basis daerah, wilayah lemah, wilayah swing, segmentasi pemilih, dan pengaruh jaringan komunitas "
            "belum bisa dinilai tanpa data elektoral dan sumber lokal."
        ),
        "swot": {
            "strengths": ["Nama tokoh sudah menjadi input screening dan siap diperkaya data publik."],
            "weaknesses": ["Belum ada sumber terverifikasi yang diproses pada mode lokal."],
            "opportunities": ["Integrasi web search dan database internal dapat mempercepat profiling awal."],
            "threats": ["Risiko analisis bias meningkat jika laporan digunakan tanpa verifikasi sumber."],
        },
        "final_score": {
            "score": 55,
            "category": "Sedang",
            "reason": (
                "Skor default MVP diberikan rendah-sedang karena belum ada data elektabilitas, sentimen, "
                "basis daerah, dan sumber kontroversi yang terverifikasi."
            ),
            "indicators": [
                {"name": "Elektabilitas", "score": 5.0, "note": "Data elektabilitas belum ditemukan."},
                {"name": "Pengalaman Politik", "score": 5.0, "note": "Data pengalaman politik belum cukup."},
                {"name": "Jaringan Partai", "score": 5.0, "note": "Data jaringan partai belum diverifikasi."},
                {"name": "Jaringan Sosial/Adat", "score": 5.0, "note": "Data jaringan sosial/adat belum tersedia."},
                {"name": "Risiko Kontroversi", "score": 5.0, "note": "Data kontroversi belum ditemukan."},
                {"name": "Potensi Maju Lagi", "score": 5.0, "note": "Data potensi maju belum cukup."},
                {"name": "King Maker / Influence", "score": 5.0, "note": "Data pengaruh politik belum tersedia."},
            ],
        },
        "electability_improvement_insights": (
            "Lengkapi data suara sebelumnya, peta basis wilayah, narasi publik utama, serta kanal komunikasi "
            "yang paling aktif sebelum menyusun insight peningkatan elektabilitas."
        ),
        "strategic_recommendations": {
            "high": [
                "Aktifkan Web Search dan Source Collector sebelum laporan dipakai operasional.",
                "Pisahkan fakta, tuduhan, klarifikasi, dan opini pada setiap isu negatif.",
            ],
            "medium": [
                "Tambahkan database internal untuk riwayat pemilu, jaringan lokal, dan catatan lapangan.",
                "Gunakan format laporan satu kolom agar analis mudah melakukan review manual.",
            ],
            "low": [
                "Tambahkan export PDF setelah struktur laporan stabil.",
            ],
        },
        "sources": [
            {
                "name": "MVP Local AI Service",
                "link": "internal://ai-service/mock-screening",
                "accessed_at": today,
                "type": "Internal generator",
            }
        ],
    }

    return attach_browser_sources(attach_wikipedia_source(report, wikipedia), browser_pages or [])
