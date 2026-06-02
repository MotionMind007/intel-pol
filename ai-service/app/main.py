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


class MediaMonitoringRequest(BaseModel):
    keyword: str = Field(min_length=1, max_length=180)
    agent: AgentConfig


class PolicyIntelligenceRequest(BaseModel):
    policy_topic: str = Field(min_length=1, max_length=220)
    agent: AgentConfig


class CampaignStrategyRequest(BaseModel):
    campaign_object_type: str = Field(min_length=1, max_length=40)
    campaign_object_name: str = Field(min_length=1, max_length=220)
    campaign_goal: str = Field(min_length=1, max_length=260)
    region: str | None = Field(default=None, max_length=160)
    agent: AgentConfig


class CreativePackageRequest(BaseModel):
    campaign_object_type: str | None = None
    campaign_object_name: str = Field(min_length=1, max_length=220)
    campaign_goal: str | None = None
    target_audience: str | None = None
    platform: str | None = None
    content_objective: str | None = None
    tone: str | None = None
    source_strategy_report: dict[str, Any] | None = None
    agent: AgentConfig


class CreativeAssetRequest(BaseModel):
    asset_type: str = Field(min_length=1, max_length=20)
    prompt: str = Field(min_length=1, max_length=12000)
    negative_prompt: str | None = None
    aspect_ratio: str | None = None
    resolution: str | None = None
    duration: str | None = None
    fps: str | None = None
    quality: str | None = None
    style: str | None = None
    camera_style: str | None = None
    output_count: int = 1
    provider: ProviderConfig | None = None
    model: str | None = None


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


@app.post("/internal/ai/media-monitoring/run")
async def run_media_monitoring(payload: MediaMonitoringRequest, x_internal_token: str | None = Header(default=None)) -> dict[str, Any]:
    verify_internal_token(x_internal_token)

    context = await collect_media_monitoring_context(payload.keyword, payload.agent)

    if should_use_provider(payload.agent):
        try:
            return await generate_media_monitoring_with_provider(payload, context)
        except Exception as exc:
            if os.getenv("AI_FALLBACK_ON_PROVIDER_ERROR", "false").lower() not in {"1", "true", "yes"}:
                raise HTTPException(status_code=502, detail=f"Provider AI gagal menghasilkan JSON valid: {exc}") from exc

            fallback = build_mock_media_monitoring(payload.keyword, context)
            fallback["provider_error"] = str(exc)
            fallback["executive_summary"] = (
                fallback["executive_summary"]
                + " Provider AI gagal dipanggil, sehingga hasil ini memakai fallback lokal."
            )
            return fallback

    return build_mock_media_monitoring(payload.keyword, context)


@app.post("/internal/ai/policy-intelligence/analyze")
async def analyze_policy_intelligence(payload: PolicyIntelligenceRequest, x_internal_token: str | None = Header(default=None)) -> dict[str, Any]:
    verify_internal_token(x_internal_token)

    context = await collect_policy_context(payload.policy_topic, payload.agent)

    if should_use_provider(payload.agent):
        try:
            return await generate_policy_with_provider(payload, context)
        except Exception as exc:
            if os.getenv("AI_FALLBACK_ON_PROVIDER_ERROR", "false").lower() not in {"1", "true", "yes"}:
                raise HTTPException(status_code=502, detail=f"Provider AI gagal menghasilkan JSON valid: {exc}") from exc

            fallback = build_mock_policy_report(payload.policy_topic, context)
            fallback["provider_error"] = str(exc)
            fallback["executive_summary"] = (
                fallback["executive_summary"]
                + " Provider AI gagal dipanggil, sehingga laporan ini memakai fallback lokal."
            )
            return fallback

    return build_mock_policy_report(payload.policy_topic, context)


@app.post("/internal/ai/campaign-strategy/generate")
async def generate_campaign_strategy(payload: CampaignStrategyRequest, x_internal_token: str | None = Header(default=None)) -> dict[str, Any]:
    verify_internal_token(x_internal_token)

    context = await collect_campaign_context(payload, payload.agent)

    if should_use_provider(payload.agent):
        try:
            return await generate_campaign_with_provider(payload, context)
        except Exception as exc:
            if os.getenv("AI_FALLBACK_ON_PROVIDER_ERROR", "false").lower() not in {"1", "true", "yes"}:
                raise HTTPException(status_code=502, detail=f"Provider AI gagal menghasilkan JSON valid: {exc}") from exc

            fallback = build_mock_campaign_strategy(payload, context)
            fallback["provider_error"] = str(exc)
            fallback["executive_summary"] = (
                fallback["executive_summary"]
                + " Provider AI gagal dipanggil, sehingga strategi ini memakai fallback lokal."
            )
            return fallback

    return build_mock_campaign_strategy(payload, context)


@app.post("/internal/ai/creative-studio/package")
async def generate_creative_package(payload: CreativePackageRequest, x_internal_token: str | None = Header(default=None)) -> dict[str, Any]:
    verify_internal_token(x_internal_token)

    if should_use_provider(payload.agent):
        try:
            return await generate_creative_package_with_provider(payload)
        except Exception as exc:
            if os.getenv("AI_FALLBACK_ON_PROVIDER_ERROR", "false").lower() not in {"1", "true", "yes"}:
                raise HTTPException(status_code=502, detail=f"Provider AI gagal menghasilkan JSON valid: {exc}") from exc

            fallback = build_mock_creative_package(payload)
            fallback["provider_error"] = str(exc)
            fallback["safety_notes"].append("Provider AI gagal dipanggil; package memakai fallback lokal.")
            return fallback

    return build_mock_creative_package(payload)


@app.post("/internal/ai/creative-studio/asset")
async def generate_creative_asset(payload: CreativeAssetRequest, x_internal_token: str | None = Header(default=None)) -> dict[str, Any]:
    verify_internal_token(x_internal_token)

    return build_mock_creative_asset(payload)


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


async def generate_media_monitoring_with_provider(
    payload: MediaMonitoringRequest,
    context: dict[str, Any],
) -> dict[str, Any]:
    provider = payload.agent.provider
    assert provider is not None

    prompt = build_media_monitoring_prompt(payload.keyword, context, payload.agent.skills)
    request_payload = {
        "model": payload.agent.model,
        "messages": [
            {
                "role": "system",
                "content": build_media_monitoring_system_prompt(payload.agent.system_prompt),
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

    parsed.setdefault("keyword", payload.keyword.strip())
    parsed.setdefault("items", [])
    parsed.setdefault("sources", context.get("sources", []))

    return normalize_media_monitoring_result(parsed, context)


async def generate_policy_with_provider(
    payload: PolicyIntelligenceRequest,
    context: dict[str, Any],
) -> dict[str, Any]:
    provider = payload.agent.provider
    assert provider is not None

    prompt = build_policy_prompt(payload.policy_topic, context, payload.agent.skills)
    request_payload = {
        "model": payload.agent.model,
        "messages": [
            {
                "role": "system",
                "content": build_policy_system_prompt(payload.agent.system_prompt),
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

    parsed.setdefault("policy_topic", payload.policy_topic.strip())
    parsed.setdefault("sources", context.get("sources", []))

    return normalize_policy_result(parsed, context)


async def generate_campaign_with_provider(
    payload: CampaignStrategyRequest,
    context: dict[str, Any],
) -> dict[str, Any]:
    provider = payload.agent.provider
    assert provider is not None

    prompt = build_campaign_prompt(payload, context, payload.agent.skills)
    request_payload = {
        "model": payload.agent.model,
        "messages": [
            {
                "role": "system",
                "content": build_campaign_system_prompt(payload.agent.system_prompt),
            },
            {"role": "user", "content": prompt},
        ],
        "temperature": payload.agent.temperature,
        "max_tokens": max(payload.agent.max_tokens, int(os.getenv("AI_MIN_REPORT_MAX_TOKENS", "18000"))),
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

    parsed.setdefault(
        "campaign_object",
        {
            "type": payload.campaign_object_type,
            "name": payload.campaign_object_name.strip(),
            "goal": payload.campaign_goal.strip(),
            "region": payload.region or "Indonesia",
        },
    )
    parsed.setdefault("sources", context.get("sources", []))

    return normalize_campaign_result(parsed, context)


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


def build_media_monitoring_system_prompt(existing_prompt: str | None) -> str:
    base = existing_prompt or "You are a neutral Political Media Monitoring Analyst."

    return f"""
{base}

Instruksi wajib Media Monitoring:
- Semua output harus menggunakan Bahasa Indonesia.
- Return structured JSON only, tanpa Markdown di luar JSON.
- Jangan mengarang fakta, URL, tanggal publish, engagement, jumlah data, atau sumber.
- Jika data belum tersedia, tulis eksplisit "data belum ditemukan" atau "belum tersedia dari sumber publik".
- Pisahkan fakta, dugaan, opini, klarifikasi, framing media, dan rekomendasi.
- Google Trends wajib dijelaskan sebagai indikator minat pencarian, bukan elektabilitas.
- Engagement sosial media wajib dijelaskan sebagai engagement digital, bukan dukungan pemilih.
- Setiap item wajib punya title/caption, source, platform, url jika tersedia, summary, sentiment, issue_category, dan risk_level.
- Gunakan klasifikasi sentimen: positive, neutral, negative.
- Gunakan risk_level: low, medium, high, critical.
- Fokus isu politik Indonesia dan Papua: Pilkada, DPR/DPRD, KPU, Bawaslu, Otsus, DOB, adat, gereja, pendidikan, kesehatan, ekonomi, keamanan, infrastruktur, dan citra personal.
"""


async def collect_media_monitoring_context(keyword: str, agent: AgentConfig) -> dict[str, Any]:
    query = quote(keyword.strip())
    source_urls = [
        {"name": "Detik Search", "url": f"https://www.detik.com/search/searchall?query={query}", "source_type": "news_national"},
        {"name": "Kompas Search", "url": f"https://search.kompas.com/search/?q={query}", "source_type": "news_national"},
        {"name": "Tempo Search", "url": f"https://www.tempo.co/search?q={query}", "source_type": "news_national"},
        {"name": "Antara Search", "url": f"https://www.antaranews.com/search?q={query}", "source_type": "news_national"},
        {"name": "Jubi Search", "url": f"https://jubi.id/?s={query}", "source_type": "news_local_papua"},
        {"name": "Cenderawasih Pos Search", "url": f"https://www.ceposonline.com/?s={query}", "source_type": "news_local_papua"},
        {"name": "Kabar Papua Search", "url": f"https://kabarpapua.co/?s={query}", "source_type": "news_local_papua"},
        {"name": "YouTube Public Search", "url": f"https://www.youtube.com/results?search_query={query}", "source_type": "social_media"},
        {"name": "Google Trends", "url": f"https://trends.google.com/trends/explore?geo=ID&q={query}", "source_type": "google_trends"},
    ]

    snapshots: list[dict[str, Any]] = []

    if has_active_skill(agent, "browser-automation"):
        max_pages = int(os.getenv("MEDIA_BROWSER_MAX_PAGES", "4"))
        for source in source_urls[:max_pages]:
            try:
                page = await browse_page(source["url"], keyword)
                page["source_name"] = source["name"]
                page["source_type"] = source["source_type"]
                snapshots.append(page)
            except Exception as exc:
                snapshots.append(
                    {
                        "url": source["url"],
                        "source_name": source["name"],
                        "source_type": source["source_type"],
                        "title": "Browser Automation gagal",
                        "text": f"Halaman gagal dibuka oleh browser automation: {exc}",
                        "screenshot_path": None,
                        "status": "failed",
                    }
                )

    sources = [
        {
            "name": source["name"],
            "link": source["url"],
            "accessed_at": date.today().isoformat(),
            "type": source["source_type"],
        }
        for source in source_urls
    ]

    return {
        "keyword": keyword.strip(),
        "source_urls": source_urls,
        "browser_pages": snapshots,
        "sources": sources,
    }


def build_media_monitoring_prompt(
    keyword: str,
    context: dict[str, Any],
    active_skills: list[dict[str, Any]] | None = None,
) -> str:
    browser_block = build_browser_prompt_block(context.get("browser_pages", []))
    skill_block = build_skill_prompt_block(active_skills or [])
    source_lines = "\n".join(
        f"- {source['name']} ({source['source_type']}): {source['url']}"
        for source in context.get("source_urls", [])
    )

    return f"""
Jalankan MEDIA MONITORING untuk keyword: {keyword}

Sumber pencarian yang dikonfigurasi:
{source_lines}

{browser_block}

{skill_block}

Kembalikan JSON saja dengan struktur persis seperti ini:
{{
  "keyword": "{keyword}",
  "executive_summary": "string",
  "total_items": 0,
  "source_breakdown": {{
    "news_national": 0,
    "news_local_papua": 0,
    "social_media": 0,
    "google_search": 0,
    "google_trends": 0
  }},
  "sentiment": {{
    "positive": 0,
    "neutral": 0,
    "negative": 0,
    "dominant": "neutral"
  }},
  "risk_level": "low",
  "risk_assessment": "string",
  "dominant_issues": [
    {{"issue": "string", "count": 0, "sentiment": "neutral", "risk_level": "low"}}
  ],
  "positive_issues": [],
  "negative_issues": [],
  "top_actors": [
    {{"name": "string", "type": "person", "mentions": 0}}
  ],
  "top_sources": [
    {{"name": "string", "source_type": "news_local_papua", "item_count": 0}}
  ],
  "trend": {{
    "summary": "string",
    "signals": []
  }},
  "google_trends_insight": {{
    "summary": "string",
    "related_queries": [],
    "related_topics": []
  }},
  "items": [
    {{
      "title": "string",
      "source": "string",
      "source_type": "news_national",
      "platform": "news",
      "url": "string",
      "published_at": null,
      "summary": "string",
      "sentiment": "neutral",
      "issue_category": "politics",
      "risk_level": "low",
      "risk_reason": "string",
      "entities": []
    }}
  ],
  "strategic_recommendation": {{
    "high_priority": [],
    "medium_priority": [],
    "low_priority": []
  }},
  "sources": [
    {{"name": "string", "link": "string", "accessed_at": "YYYY-MM-DD", "type": "string"}}
  ]
}}

Aturan detail:
- Output wajib Bahasa Indonesia.
- Jangan membuat artikel palsu. Isi "items" hanya dari konteks browser/sumber yang benar-benar tersedia. Jika konteks minim, items boleh sedikit atau kosong.
- Jika total item sebenarnya belum dapat dihitung, gunakan jumlah item yang berhasil dikenali dari konteks, lalu jelaskan keterbatasannya di executive_summary.
- Ringkasan harus menjawab: total data, sumber dominan, sentimen dominan, isu dominan, aktor disebut, Google Trends insight, risiko reputasi, rekomendasi respon.
- Untuk berita lokal Papua, beri perhatian lebih tinggi pada konteks politik lokal dan risiko reputasi lokal.
- Setiap rekomendasi harus konkret dan bisa dieksekusi tim komunikasi/politik.
- sources wajib mencantumkan sumber yang digunakan atau dikonfigurasi pada run ini, dengan tanggal akses {date.today().isoformat()}.
"""


def build_policy_system_prompt(existing_prompt: str | None) -> str:
    base = existing_prompt or "You are a neutral Policy Intelligence Analyst."

    return f"""
{base}

Instruksi wajib Policy Intelligence:
- Semua output harus menggunakan Bahasa Indonesia.
- Return structured JSON only, tanpa Markdown di luar JSON.
- Jangan mengarang fakta, URL, dokumen resmi, angka, skor, sumber, atau klaim dukungan publik.
- Pisahkan fakta terverifikasi, dugaan, opini, asumsi, dan framing media.
- Jika dokumen resmi kebijakan tidak ditemukan, tulis jelas bahwa data resmi kebijakan belum ditemukan.
- Respon publik dari media sosial/online adalah digital public response, bukan survei populasi.
- Google Trends adalah search-interest, bukan dukungan publik dan bukan elektabilitas.
- Setiap dampak positif dan negatif wajib menjelaskan kenapa dampak itu positif/negatif serta data pendukungnya.
- Wajib membuat simulasi skenario optimis, moderat, dan buruk.
- Wajib memberi policy score 0-100 dengan kategori dan alasan.
"""


async def collect_policy_context(policy_topic: str, agent: AgentConfig) -> dict[str, Any]:
    query = quote(policy_topic.strip())
    source_urls = [
        {"name": "Google Trends", "url": f"https://trends.google.com/trends/explore?geo=ID&q={query}", "source_type": "google_trends"},
        {"name": "Detik Search", "url": f"https://www.detik.com/search/searchall?query={query}", "source_type": "news_national"},
        {"name": "Kompas Search", "url": f"https://search.kompas.com/search/?q={query}", "source_type": "news_national"},
        {"name": "Antara Search", "url": f"https://www.antaranews.com/search?q={query}", "source_type": "news_national"},
        {"name": "Jubi Search", "url": f"https://jubi.id/?s={query}", "source_type": "news_local_papua"},
        {"name": "BPS Search", "url": f"https://www.bps.go.id/id/search?q={query}", "source_type": "statistics"},
        {"name": "KemenPAN Search", "url": f"https://www.menpan.go.id/site/search?searchword={query}", "source_type": "official_document"},
        {"name": "YouTube Public Search", "url": f"https://www.youtube.com/results?search_query={query}", "source_type": "social_media"},
    ]

    snapshots: list[dict[str, Any]] = []

    if has_active_skill(agent, "browser-automation"):
        max_pages = int(os.getenv("POLICY_BROWSER_MAX_PAGES", "4"))
        for source in source_urls[:max_pages]:
            try:
                page = await browse_page(source["url"], policy_topic)
                page["source_name"] = source["name"]
                page["source_type"] = source["source_type"]
                snapshots.append(page)
            except Exception as exc:
                snapshots.append(
                    {
                        "url": source["url"],
                        "source_name": source["name"],
                        "source_type": source["source_type"],
                        "title": "Browser Automation gagal",
                        "text": f"Halaman gagal dibuka oleh browser automation: {exc}",
                        "screenshot_path": None,
                        "status": "failed",
                    }
                )

    sources = [
        {
            "name": source["name"],
            "link": source["url"],
            "accessed_at": date.today().isoformat(),
            "type": source["source_type"],
            "data_used": "Sumber pencarian dan konteks awal analisis kebijakan.",
        }
        for source in source_urls
    ]

    return {
        "policy_topic": policy_topic.strip(),
        "source_urls": source_urls,
        "browser_pages": snapshots,
        "sources": sources,
    }


def build_policy_prompt(
    policy_topic: str,
    context: dict[str, Any],
    active_skills: list[dict[str, Any]] | None = None,
) -> str:
    browser_block = build_browser_prompt_block(context.get("browser_pages", []))
    skill_block = build_skill_prompt_block(active_skills or [])
    source_lines = "\n".join(
        f"- {source['name']} ({source['source_type']}): {source['url']}"
        for source in context.get("source_urls", [])
    )

    return f"""
Jalankan POLICY INTELLIGENCE untuk topik kebijakan: {policy_topic}

Sumber pencarian yang dikonfigurasi:
{source_lines}

{browser_block}

{skill_block}

Kembalikan JSON saja dengan struktur persis seperti ini:
{{
  "policy_topic": "{policy_topic}",
  "executive_summary": "string",
  "policy_description": {{
    "name": "string",
    "level": "national/provincial/local/unknown",
    "status": "string",
    "implementing_body": "string",
    "official_sources": []
  }},
  "policy_objectives": [],
  "affected_groups": [
    {{"group": "string", "impact_type": "direct/indirect", "impact_level": "low/medium/high", "notes": "string"}}
  ],
  "public_response": {{
    "summary": "string",
    "supporting_narratives": [],
    "opposing_narratives": [],
    "neutral_narratives": [],
    "data_sources": [],
    "items": []
  }},
  "sentiment_analysis": {{
    "dominant": "mixed",
    "positive": 0,
    "neutral": 0,
    "negative": 0,
    "notes": "string"
  }},
  "positive_impacts": [
    {{"impact": "string", "why_positive": "string", "supporting_data": []}}
  ],
  "negative_impacts": [
    {{"impact": "string", "why_negative": "string", "supporting_data": []}}
  ],
  "implementation_risks": [
    {{"risk": "string", "level": "medium", "reason": "string"}}
  ],
  "political_reputation_risk": {{
    "level": "medium",
    "reason": "string"
  }},
  "scenario_simulation": {{
    "optimistic": "string",
    "moderate": "string",
    "bad": "string",
    "indicators": {{
      "public_acceptance_score": 0,
      "implementation_risk": "medium",
      "political_risk": "medium",
      "social_impact": "medium",
      "media_risk": "medium"
    }}
  }},
  "stakeholders": [
    {{"name": "string", "type": "government", "position": "support/oppose/neutral/unclear", "influence": "low/medium/high", "notes": "string"}}
  ],
  "policy_score": {{
    "score": 55,
    "category": "Perlu Kajian Lanjutan",
    "reason": "string"
  }},
  "policy_improvement_recommendations": {{
    "high_priority": [],
    "medium_priority": [],
    "low_priority": []
  }},
  "public_communication_strategy": {{
    "main_narrative": "string",
    "target_messages": [],
    "channels": [],
    "response_to_criticism": []
  }},
  "sources": [
    {{"name": "string", "link": "string", "accessed_at": "YYYY-MM-DD", "type": "string", "data_used": "string"}}
  ]
}}

Aturan detail:
- Output wajib Bahasa Indonesia.
- Jangan membuat dokumen resmi, berita, komentar, URL, atau data palsu.
- Jika data resmi kebijakan belum ditemukan, nyatakan eksplisit di policy_description dan executive_summary.
- Respon publik harus menyebut basis data: berita, sosial publik, Google Search, Google Trends, survei internal, atau laporan masyarakat.
- Sentimen online tidak boleh dianggap mewakili seluruh populasi tanpa survei.
- Google Trends wajib dijelaskan sebagai minat pencarian.
- Dampak positif dan negatif wajib menjawab "kenapa".
- Skenario wajib memuat konsekuensi politik, sosial, implementasi, dan media.
- Policy score wajib konservatif jika sumber terbatas.
- sources wajib memakai tanggal akses {date.today().isoformat()}.
"""


def build_campaign_system_prompt(existing_prompt: str | None) -> str:
    base = existing_prompt or "You are a neutral Strategic Campaign Intelligence Analyst."

    return f"""
{base}

Instruksi wajib Campaign Strategy:
- Semua output harus menggunakan Bahasa Indonesia.
- Return structured JSON only, tanpa Markdown di luar JSON.
- Strategi harus operasional, etis, berbasis data, dan bisa dijalankan tim kampanye/komunikasi.
- Jangan mengarang survei, elektabilitas, endorsement, dukungan publik, sumber, URL, angka, atau fakta.
- Jika data tidak tersedia, tulis "data belum ditemukan" dan beri asumsi strategis yang diberi label jelas.
- Dilarang membuat fitnah, disinformasi, black campaign, provokasi SARA, ujaran kebencian, atau taktik ilegal.
- Mitigasi isu negatif harus berbasis klarifikasi, bukti pendukung, empati publik, dan narasi tandingan yang etis.
- Campaign object boleh berupa candidate, party, policy, issue, organization, atau other.
"""


async def collect_campaign_context(payload: CampaignStrategyRequest, agent: AgentConfig) -> dict[str, Any]:
    search_text = " ".join(
        item
        for item in [
            payload.campaign_object_name.strip(),
            payload.region or "",
            payload.campaign_goal.strip(),
        ]
        if item
    )
    query = quote(search_text)
    source_urls = [
        {"name": "Google Trends", "url": f"https://trends.google.com/trends/explore?geo=ID&q={query}", "source_type": "google_trends"},
        {"name": "Detik Search", "url": f"https://www.detik.com/search/searchall?query={query}", "source_type": "news_national"},
        {"name": "Kompas Search", "url": f"https://search.kompas.com/search/?q={query}", "source_type": "news_national"},
        {"name": "Tempo Search", "url": f"https://www.tempo.co/search?q={query}", "source_type": "news_national"},
        {"name": "Antara Search", "url": f"https://www.antaranews.com/search?q={query}", "source_type": "news_national"},
        {"name": "Jubi Search", "url": f"https://jubi.id/?s={query}", "source_type": "news_local_papua"},
        {"name": "Cenderawasih Pos Search", "url": f"https://www.ceposonline.com/?s={query}", "source_type": "news_local_papua"},
        {"name": "Kabar Papua Search", "url": f"https://kabarpapua.co/?s={query}", "source_type": "news_local_papua"},
        {"name": "YouTube Public Search", "url": f"https://www.youtube.com/results?search_query={query}", "source_type": "social_media"},
    ]

    snapshots: list[dict[str, Any]] = []

    if has_active_skill(agent, "browser-automation"):
        max_pages = int(os.getenv("CAMPAIGN_BROWSER_MAX_PAGES", "4"))
        for source in source_urls[:max_pages]:
            try:
                page = await browse_page(source["url"], search_text)
                page["source_name"] = source["name"]
                page["source_type"] = source["source_type"]
                snapshots.append(page)
            except Exception as exc:
                snapshots.append(
                    {
                        "url": source["url"],
                        "source_name": source["name"],
                        "source_type": source["source_type"],
                        "title": "Browser Automation gagal",
                        "text": f"Halaman gagal dibuka oleh browser automation: {exc}",
                        "screenshot_path": None,
                        "status": "failed",
                    }
                )

    sources = [
        {
            "name": source["name"],
            "link": source["url"],
            "accessed_at": date.today().isoformat(),
            "type": source["source_type"],
            "data_used": "Sumber pencarian dan konteks awal strategi kampanye.",
        }
        for source in source_urls
    ]

    return {
        "campaign_object": {
            "type": payload.campaign_object_type,
            "name": payload.campaign_object_name.strip(),
            "goal": payload.campaign_goal.strip(),
            "region": payload.region or "Indonesia",
        },
        "source_urls": source_urls,
        "browser_pages": snapshots,
        "sources": sources,
    }


def build_campaign_prompt(
    payload: CampaignStrategyRequest,
    context: dict[str, Any],
    active_skills: list[dict[str, Any]] | None = None,
) -> str:
    browser_block = build_browser_prompt_block(context.get("browser_pages", []))
    skill_block = build_skill_prompt_block(active_skills or [])
    source_lines = "\n".join(
        f"- {source['name']} ({source['source_type']}): {source['url']}"
        for source in context.get("source_urls", [])
    )
    region = payload.region or "Indonesia"

    return f"""
Jalankan CAMPAIGN STRATEGY untuk:
- Tipe objek: {payload.campaign_object_type}
- Objek kampanye: {payload.campaign_object_name}
- Tujuan kampanye: {payload.campaign_goal}
- Wilayah: {region}

Sumber pencarian yang dikonfigurasi:
{source_lines}

{browser_block}

{skill_block}

Kembalikan JSON saja dengan struktur persis seperti ini:
{{
  "campaign_object": {{
    "type": "{payload.campaign_object_type}",
    "name": "{payload.campaign_object_name}",
    "goal": "{payload.campaign_goal}",
    "region": "{region}"
  }},
  "metrics": {{
    "strategic_fit": 82,
    "message_clarity": "high",
    "risk_level": "medium",
    "priority_segment": "string"
  }},
  "strategic_fit": {{"score": 82, "reason": "string"}},
  "executive_summary": "string",
  "campaign_context": {{"summary": "string", "data_used": []}},
  "campaign_goal_analysis": "string",
  "situation_analysis": "string",
  "positioning": {{
    "statement": "string",
    "core_identity": "string",
    "differentiator": "string",
    "perception_gap": "string"
  }},
  "target_segments": [
    {{"segment": "string", "priority": "high", "needs": "string", "main_issue": "string", "message": "string", "channel": "string"}}
  ],
  "priority_issues": [
    {{"issue": "string", "priority": "high", "reason": "string", "risk": "medium", "recommended_narrative": "string"}}
  ],
  "main_narrative": "string",
  "key_messages": [
    {{"message": "string", "target": "string", "reason": "string", "channel": "string"}}
  ],
  "regional_strategy": [
    {{"region": "string", "status": "swing", "strategy": "string", "actions": [], "risk": "string"}}
  ],
  "social_media_strategy": {{
    "platforms": [],
    "content_style": "string",
    "posting_frequency": "string",
    "formats": [],
    "hashtags": []
  }},
  "local_media_pr_strategy": {{
    "priority_media": [],
    "story_angles": [],
    "press_release_agenda": [],
    "negative_news_response": "string"
  }},
  "ground_campaign_strategy": [
    {{"activity": "string", "target": "string", "region": "string", "expected_output": "string"}}
  ],
  "negative_issue_mitigation": [
    {{"issue": "string", "risk": "medium", "main_response": "string", "counter_narrative": "string", "supporting_evidence": []}}
  ],
  "content_recommendations": [
    {{"hook": "string", "format": "short video", "target": "string", "message": "string", "cta": "string"}}
  ],
  "action_plan_30_days": {{
    "week_1": [],
    "week_2": [],
    "week_3": [],
    "week_4": []
  }},
  "success_indicators": [],
  "risks_and_notes": [],
  "sources": [
    {{"name": "string", "link": "string", "accessed_at": "YYYY-MM-DD", "type": "string", "data_used": "string"}}
  ]
}}

Aturan detail:
- Output wajib Bahasa Indonesia.
- Laporan harus satu alur strategi, bukan template kosong.
- Jangan membuat data palsu: survei, elektabilitas, jumlah pemilih, endorsement, dukungan organisasi, atau sumber.
- Jika basis data minim, tulis keterbatasan pada campaign_context, risks_and_notes, dan sources.
- Tujuan kampanye harus diterjemahkan menjadi positioning, target segment, narasi, pesan, wilayah, media, darat, konten, timeline, dan KPI.
- Target segment minimal 4 segmen jika data cukup; jika data terbatas, buat segmen berbasis asumsi dan beri label "asumsi".
- Priority issues minimal 4 isu, termasuk isu positif yang bisa diangkat dan isu negatif yang harus dimitigasi.
- Key messages minimal 5 pesan, masing-masing punya target dan channel.
- Regional strategy wajib menyebut wilayah target {region}; jika tidak ada data detail, buat strategi wilayah umum dan sebutkan keterbatasan.
- Negative issue mitigation harus etis, berbasis klarifikasi dan bukti. Dilarang menyerang personal/kelompok.
- Content recommendations minimal 6 ide konten dengan hook, format, target, message, CTA.
- Action plan 30 hari harus konkret per minggu.
- Success indicators harus realistis dan terukur tanpa mengklaim angka survei palsu.
- sources wajib memakai tanggal akses {date.today().isoformat()}.
"""


def normalize_campaign_result(result: dict[str, Any], context: dict[str, Any]) -> dict[str, Any]:
    result.setdefault("campaign_object", context.get("campaign_object", {}))
    result.setdefault("metrics", {})
    result.setdefault("strategic_fit", {"score": result["metrics"].get("strategic_fit", 55), "reason": "Skor konservatif karena data masih terbatas."})
    result.setdefault("executive_summary", "Strategi kampanye selesai, tetapi konteks data perlu diverifikasi manual.")
    result.setdefault("campaign_context", {"summary": "Data awal kampanye belum lengkap.", "data_used": []})
    result.setdefault("campaign_goal_analysis", "Tujuan kampanye perlu diterjemahkan ke aksi komunikasi dan lapangan.")
    result.setdefault("situation_analysis", "Analisis situasi belum tersedia penuh.")
    result.setdefault("positioning", {})
    result.setdefault("target_segments", [])
    result.setdefault("priority_issues", [])
    result.setdefault("main_narrative", "")
    result.setdefault("key_messages", [])
    result.setdefault("regional_strategy", [])
    result.setdefault("social_media_strategy", {})
    result.setdefault("local_media_pr_strategy", {})
    result.setdefault("ground_campaign_strategy", [])
    result.setdefault("negative_issue_mitigation", [])
    result.setdefault("content_recommendations", [])
    result.setdefault("action_plan_30_days", {"week_1": [], "week_2": [], "week_3": [], "week_4": []})
    result.setdefault("success_indicators", [])
    result.setdefault("risks_and_notes", [])

    metrics = result["metrics"]
    metrics.setdefault("strategic_fit", result.get("strategic_fit", {}).get("score", 55))
    metrics.setdefault("message_clarity", "medium")
    metrics.setdefault("risk_level", "medium")
    metrics.setdefault("priority_segment", data_get_first(result.get("target_segments", []), "segment", "Segmen prioritas belum ditentukan"))
    result.setdefault("risk_level", metrics.get("risk_level", "medium"))

    positioning = result["positioning"]
    positioning.setdefault("statement", "Positioning belum tersedia penuh.")
    positioning.setdefault("core_identity", "Identitas inti perlu dipertajam dari data lapangan.")
    positioning.setdefault("differentiator", "Diferensiasi belum cukup kuat tanpa data pembanding.")
    positioning.setdefault("perception_gap", "Gap persepsi perlu diverifikasi melalui monitoring dan riset lapangan.")

    social = result["social_media_strategy"]
    social.setdefault("platforms", [])
    social.setdefault("content_style", "Informatif, lokal, dan berbasis bukti.")
    social.setdefault("posting_frequency", "Perlu disesuaikan dengan kapasitas tim.")
    social.setdefault("formats", [])
    social.setdefault("hashtags", [])

    pr = result["local_media_pr_strategy"]
    pr.setdefault("priority_media", [])
    pr.setdefault("story_angles", [])
    pr.setdefault("press_release_agenda", [])
    pr.setdefault("negative_news_response", "Respon berita negatif harus memakai klarifikasi faktual dan bukti pendukung.")

    plan = result["action_plan_30_days"]
    for week in ["week_1", "week_2", "week_3", "week_4"]:
        plan.setdefault(week, [])

    sources = result.setdefault("sources", [])
    known_links = {source.get("link") for source in sources if isinstance(source, dict)}
    for source in context.get("sources", []):
        if source["link"] not in known_links:
            sources.append(source)

    return result


def data_get_first(items: Any, key: str, default: str) -> str:
    if isinstance(items, list) and items and isinstance(items[0], dict):
        value = items[0].get(key)
        if value:
            return str(value)

    return default


def build_mock_campaign_strategy(payload: CampaignStrategyRequest, context: dict[str, Any]) -> dict[str, Any]:
    obj_name = payload.campaign_object_name.strip()
    region = payload.region or "Indonesia"
    goal = payload.campaign_goal.strip()
    pages = [page for page in context.get("browser_pages", []) if page.get("status") == "ok"]

    result = {
        "campaign_object": {
            "type": payload.campaign_object_type,
            "name": obj_name,
            "goal": goal,
            "region": region,
        },
        "metrics": {
            "strategic_fit": 58,
            "message_clarity": "medium",
            "risk_level": "medium",
            "priority_segment": "Pemilih/target publik yang paling terdampak isu",
        },
        "strategic_fit": {
            "score": 58,
            "reason": "Skor konservatif karena fallback lokal belum membaca seluruh insight lintas modul dan sumber lapangan.",
        },
        "executive_summary": (
            f"Strategi awal untuk {obj_name} dengan tujuan {goal} di wilayah {region} sudah disusun sebagai briefing operasional awal. "
            "Kekuatan strategi berada pada penajaman positioning, segmentasi audiens, narasi utama, dan rencana aksi 30 hari. "
            "Namun data lintas modul, survei, dan respon publik belum cukup untuk klaim elektabilitas atau dukungan. "
            "Karena itu strategi ini harus dipakai sebagai draft awal yang perlu diverifikasi dengan data monitoring, riset kebijakan, dan temuan lapangan."
        ),
        "campaign_context": {
            "summary": f"Konteks {obj_name} dibaca dari sumber publik yang dikonfigurasi dan tujuan kampanye yang diberikan user. Data aktual yang berhasil dibuka: {len(pages)} halaman.",
            "data_used": ["Input user", "Sumber pencarian publik", "Browser automation jika aktif"],
        },
        "campaign_goal_analysis": f"Tujuan '{goal}' perlu dijalankan melalui narasi positif, bukti manfaat, aktivasi wilayah, dan kontrol risiko isu negatif.",
        "situation_analysis": "Situasi kampanye masih perlu diperkaya dari screening, media monitoring, policy intelligence, dan riset lapangan. Tanpa itu, strategi harus berhati-hati dan tidak memakai klaim kuantitatif.",
        "positioning": {
            "statement": f"{obj_name} diposisikan sebagai opsi yang dekat dengan kebutuhan publik {region}, membawa solusi konkret, dan terbuka terhadap evaluasi.",
            "core_identity": "Dekat, solutif, transparan, dan relevan dengan isu lokal.",
            "differentiator": "Mengubah isu menjadi rencana aksi yang bisa diawasi publik, bukan sekadar slogan.",
            "perception_gap": "Gap utama perlu diuji: apakah publik melihat objek kampanye sebagai aktor yang benar-benar mampu mengeksekusi janji atau baru sebatas narasi.",
        },
        "target_segments": [
            {"segment": "Pemilih muda / audiens muda", "priority": "high", "needs": "Bahasa sederhana, bukti visual, ruang partisipasi", "main_issue": "Pekerjaan, pendidikan, akses digital, dan representasi", "message": "Kampanye ini memberi ruang anak muda untuk ikut menentukan agenda lokal.", "channel": "TikTok, Instagram, YouTube Shorts, komunitas kampus"},
            {"segment": "Keluarga dan komunitas lokal", "priority": "high", "needs": "Solusi langsung dan rasa aman", "main_issue": "Harga kebutuhan, layanan publik, pendidikan, kesehatan", "message": "Fokus kampanye adalah manfaat nyata yang bisa dirasakan keluarga.", "channel": "Pertemuan warga, media lokal, WhatsApp komunitas"},
            {"segment": "Tokoh komunitas/adat/agama", "priority": "medium", "needs": "Dialog, penghormatan lokal, dan bukti komitmen", "main_issue": "Kepercayaan dan keberlanjutan program", "message": "Agenda dijalankan dengan mendengar struktur sosial lokal.", "channel": "Audiensi, forum kecil, radio/media lokal"},
            {"segment": "Pemilih kritis dan media", "priority": "medium", "needs": "Data, transparansi, dan respons cepat", "main_issue": "Akuntabilitas dan risiko janji kosong", "message": "Setiap klaim kampanye harus bisa dicek dan dievaluasi.", "channel": "Press briefing, artikel opini, dashboard progres"},
        ],
        "priority_issues": [
            {"issue": "Kebutuhan ekonomi dan layanan dasar", "priority": "high", "reason": "Isu ini paling mudah diterjemahkan menjadi manfaat konkret.", "risk": "medium", "recommended_narrative": "Solusi bertahap, terukur, dan dekat dengan keluarga."},
            {"issue": "Kepercayaan publik terhadap aktor/program", "priority": "high", "reason": "Tanpa kepercayaan, narasi sulit diterima.", "risk": "medium", "recommended_narrative": "Transparansi, rekam kerja, dan kanal evaluasi publik."},
            {"issue": "Keterlibatan anak muda", "priority": "medium", "reason": "Segmen muda bisa menjadi amplifier digital.", "risk": "low", "recommended_narrative": "Anak muda bukan penonton, tetapi bagian dari agenda."},
            {"issue": "Potensi isu negatif atau framing lawan", "priority": "high", "reason": "Framing negatif bisa mengganggu positioning.", "risk": "medium", "recommended_narrative": "Jawab dengan fakta, klarifikasi, dan perbaikan, bukan serangan personal."},
        ],
        "main_narrative": f"{obj_name} hadir untuk membawa agenda {region} yang konkret, bisa dicek, dan berpihak pada kebutuhan publik.",
        "key_messages": [
            {"message": "Kampanye ini fokus pada kerja nyata dan solusi yang bisa diukur.", "target": "Pemilih umum", "reason": "Mengurangi kesan slogan kosong.", "channel": "Media lokal dan sosial media"},
            {"message": "Anak muda dilibatkan sebagai penggerak ide, konten, dan pengawasan agenda.", "target": "Pemilih muda", "reason": "Membangun partisipasi dan kedekatan generasi.", "channel": "TikTok, Instagram, komunitas kampus"},
            {"message": "Setiap isu negatif dijawab dengan fakta dan bukti, bukan saling serang.", "target": "Media dan pemilih kritis", "reason": "Menjaga kampanye tetap kredibel dan etis.", "channel": "Press briefing dan FAQ digital"},
            {"message": "Agenda wilayah disusun dengan mendengar komunitas lokal.", "target": "Tokoh lokal/komunitas", "reason": "Menguatkan legitimasi sosial.", "channel": "Dialog wilayah dan forum kecil"},
            {"message": "Publik bisa ikut memantau progres agenda kampanye.", "target": "Pemilih kritis", "reason": "Meningkatkan transparansi.", "channel": "Dashboard, konten mingguan, media lokal"},
        ],
        "regional_strategy": [
            {"region": region, "status": "swing", "strategy": "Mulai dari wilayah dengan kebutuhan isu paling terasa, bangun dialog kecil, lalu amplifikasi bukti kegiatan ke media lokal dan sosial.", "actions": ["Mapping komunitas prioritas", "Forum warga", "Konten bukti kegiatan mingguan"], "risk": "Data basis wilayah belum cukup; jangan klaim wilayah kuat tanpa bukti."}
        ],
        "social_media_strategy": {
            "platforms": ["TikTok", "Instagram", "Facebook", "YouTube Shorts"],
            "content_style": "Singkat, visual, lokal, berbasis bukti, dan tidak menyerang personal.",
            "posting_frequency": "1-2 konten harian ringan, 2 konten bukti kerja per minggu, 1 recap mingguan.",
            "formats": ["short video", "carousel data", "FAQ isu", "recap kegiatan", "testimoni publik terverifikasi"],
            "hashtags": ["#SENA", "#PapuaBicara", "#KerjaTerukur"],
        },
        "local_media_pr_strategy": {
            "priority_media": ["Media lokal Papua", "Radio lokal", "Portal berita nasional saat isu besar"],
            "story_angles": ["Solusi lokal", "Dialog komunitas", "Transparansi agenda", "Keterlibatan anak muda"],
            "press_release_agenda": ["Peluncuran agenda", "Forum warga", "Klarifikasi isu", "Laporan progres 30 hari"],
            "negative_news_response": "Siapkan holding statement, timeline fakta, bukti pendukung, dan juru bicara yang konsisten.",
        },
        "ground_campaign_strategy": [
            {"activity": "Forum dengar warga", "target": "Komunitas lokal", "region": region, "expected_output": "Daftar isu prioritas per wilayah"},
            {"activity": "Kelas konten relawan muda", "target": "Pemilih muda", "region": region, "expected_output": "Tim mikro konten dan kalender narasi"},
            {"activity": "Audiensi tokoh komunitas", "target": "Tokoh adat/agama/komunitas", "region": region, "expected_output": "Masukan lokal dan peta dukungan/resistensi"},
        ],
        "negative_issue_mitigation": [
            {"issue": "Serangan personal atau framing negatif", "risk": "medium", "main_response": "Jangan menyerang balik secara personal. Jawab dengan fakta, klarifikasi singkat, dan ajak publik mengecek bukti.", "counter_narrative": "Kampanye fokus solusi dan transparansi, bukan konflik personal.", "supporting_evidence": ["Dokumen kegiatan", "Pernyataan resmi", "Data sumber publik terverifikasi"]},
            {"issue": "Klaim janji kosong", "risk": "medium", "main_response": "Tampilkan action plan, indikator, dan progres mingguan.", "counter_narrative": "Setiap agenda punya ukuran keberhasilan.", "supporting_evidence": ["Timeline 30 hari", "KPI kampanye"]},
        ],
        "content_recommendations": [
            {"hook": "Apa masalah paling dekat dengan warga hari ini?", "format": "short video", "target": "Pemilih muda", "message": "Kampanye dimulai dari masalah nyata, bukan slogan.", "cta": "Kirim isu wilayahmu."},
            {"hook": "3 langkah kampanye yang bisa publik cek", "format": "carousel", "target": "Pemilih kritis", "message": "Agenda kampanye transparan dan terukur.", "cta": "Simpan dan cek progres minggu depan."},
            {"hook": "Cerita warga, solusi lokal", "format": "video testimoni terverifikasi", "target": "Keluarga/komunitas", "message": "Isu lokal butuh solusi yang dekat.", "cta": "Ikut forum warga."},
            {"hook": "Jawab isu negatif tanpa gaduh", "format": "FAQ", "target": "Media dan publik kritis", "message": "Klarifikasi berbasis fakta.", "cta": "Baca sumber resminya."},
            {"hook": "Agenda anak muda untuk wilayah", "format": "reels", "target": "Pemilih muda", "message": "Anak muda punya peran nyata.", "cta": "Gabung tim ide."},
            {"hook": "Laporan progres 7 hari", "format": "recap mingguan", "target": "Publik umum", "message": "Kampanye ini bisa dipantau.", "cta": "Pantau progres berikutnya."},
        ],
        "action_plan_30_days": {
            "week_1": ["Audit data dari modul screening/monitoring/policy", "Susun pesan inti dan FAQ isu negatif", "Mapping wilayah dan segmen prioritas"],
            "week_2": ["Mulai forum dengar warga", "Rilis konten positioning", "Briefing juru bicara dan admin media sosial"],
            "week_3": ["Aktivasi relawan konten muda", "Publikasi cerita warga dan bukti kegiatan", "Monitoring framing media harian"],
            "week_4": ["Evaluasi KPI awal", "Perbaiki narasi berdasarkan monitoring", "Rilis laporan progres dan agenda bulan berikutnya"],
        },
        "success_indicators": [
            "Peningkatan share of voice positif/netral di media monitoring",
            "Jumlah isu warga yang masuk dan dipetakan",
            "Jumlah forum warga dan audiensi tokoh lokal",
            "Engagement konten edukatif dan klarifikasi",
            "Penurunan pengulangan isu negatif setelah klarifikasi",
        ],
        "risks_and_notes": [
            "Tidak boleh mengklaim elektabilitas atau dukungan tanpa survei/sumber resmi.",
            "Strategi ini perlu diverifikasi dengan hasil screening, monitoring, policy intelligence, dan data lapangan.",
            "Hindari konten yang memicu SARA, kebencian, atau serangan personal.",
        ],
        "sources": context.get("sources", []),
    }

    return normalize_campaign_result(result, context)


async def generate_creative_package_with_provider(payload: CreativePackageRequest) -> dict[str, Any]:
    provider = payload.agent.provider
    assert provider is not None

    request_payload = {
        "model": payload.agent.model,
        "messages": [
            {"role": "system", "content": build_creative_system_prompt(payload.agent.system_prompt)},
            {"role": "user", "content": build_creative_package_prompt(payload)},
        ],
        "temperature": payload.agent.temperature,
        "max_tokens": max(payload.agent.max_tokens, int(os.getenv("AI_MIN_REPORT_MAX_TOKENS", "16000"))),
    }

    if os.getenv("AI_PROVIDER_RESPONSE_FORMAT", "json_object") == "json_object":
        request_payload["response_format"] = {"type": "json_object"}

    async with httpx.AsyncClient(timeout=float(os.getenv("AI_PROVIDER_TIMEOUT", "7200"))) as client:
        response = await client.post(
            f"{provider.base_url.rstrip('/')}/chat/completions",
            headers={"Authorization": f"Bearer {provider.api_key}", "Content-Type": "application/json"},
            json=request_payload,
        )
        response.raise_for_status()
        content = response.json()["choices"][0]["message"]["content"]
        parsed = await parse_or_repair_json_content(client, provider, payload.agent, content)

    return normalize_creative_package(parsed, payload)


def build_creative_system_prompt(existing_prompt: str | None) -> str:
    base = existing_prompt or "You are a Creative Studio Agent for ethical political campaign production."

    return f"""
{base}

Instruksi wajib Creative Studio:
- Semua output harus Bahasa Indonesia.
- Return JSON object saja.
- Buat creative brief, big idea, angle, hook, caption, CTA, script, storyboard, image prompt, video prompt, dan safety notes.
- Pisahkan fakta, asumsi kreatif, dan klaim yang perlu sumber.
- Jangan membuat fitnah, disinformasi, klaim palsu, hate speech, hasutan SARA, kekerasan politik, manipulasi data, atau deepfake tanpa izin.
- Semua asset harus pending_review secara konsep dan siap approval manual.
"""


def build_creative_package_prompt(payload: CreativePackageRequest) -> str:
    strategy = json.dumps(payload.source_strategy_report or {}, ensure_ascii=False)[:50000]

    return f"""
Buat CREATIVE PACKAGE untuk:
- Campaign object: {payload.campaign_object_name}
- Type: {payload.campaign_object_type or 'other'}
- Goal: {payload.campaign_goal or payload.content_objective or 'Awareness'}
- Target audience: {payload.target_audience or 'Publik target kampanye'}
- Platform: {payload.platform or 'TikTok, Instagram'}
- Content objective: {payload.content_objective or payload.campaign_goal or 'Awareness'}
- Tone: {payload.tone or 'Optimis, dekat, modern, tidak kaku'}

Konteks Campaign Strategy jika tersedia:
{strategy}

Return JSON persis:
{{
  "project_title": "string",
  "creative_brief": "string",
  "big_idea": "string",
  "content_angles": [],
  "hook_options": [],
  "caption_options": [],
  "cta_options": [],
  "visual_style": "string",
  "image_prompts": [
    {{"title": "string", "prompt": "string", "negative_prompt": "string", "aspect_ratio": "1:1", "recommended_platform": "Instagram"}}
  ],
  "video_prompts": [
    {{"title": "string", "prompt": "string", "negative_prompt": "string", "aspect_ratio": "9:16", "duration": "10s", "recommended_platform": "TikTok"}}
  ],
  "script": {{"duration": "30s", "voiceover": "string", "on_screen_text": []}},
  "storyboard": [
    {{"scene": 1, "visual": "string", "voiceover": "string", "on_screen_text": "string", "duration_seconds": 5}}
  ],
  "asset_specs": {{"platform": "string", "format": "string", "aspect_ratio": "string", "resolution": "string"}},
  "safety_notes": []
}}

Aturan:
- Minimal 5 hook, 5 caption, 5 CTA.
- Minimal 4 content angle.
- Minimal 4 image prompt dan 3 video prompt.
- Script harus punya voiceover dan on-screen text.
- Storyboard minimal 5 scene.
- Prompt gambar/video harus detail dan siap provider AI.
- Negative prompt wajib mencegah hoaks, kebencian, SARA, manipulasi data, wajah tokoh nyata tanpa izin, dan visual provokatif.
"""


def normalize_creative_package(result: dict[str, Any], payload: CreativePackageRequest) -> dict[str, Any]:
    result.setdefault("project_title", f"{payload.campaign_object_name} Creative Package")
    result.setdefault("creative_brief", "Creative brief belum tersedia penuh.")
    result.setdefault("big_idea", "Big idea perlu dipertajam dari campaign strategy.")
    result.setdefault("content_angles", [])
    result.setdefault("hook_options", [])
    result.setdefault("caption_options", [])
    result.setdefault("cta_options", [])
    result.setdefault("visual_style", "Modern, bersih, red-accent SENA, sosial media friendly.")
    result.setdefault("image_prompts", [])
    result.setdefault("video_prompts", [])
    result.setdefault("script", {"duration": "30s", "voiceover": "", "on_screen_text": []})
    result.setdefault("storyboard", [])
    result.setdefault("asset_specs", {"platform": payload.platform or "Instagram/TikTok", "format": "social short", "aspect_ratio": "9:16", "resolution": "1080p"})
    result.setdefault("safety_notes", [])

    return result


def build_mock_creative_package(payload: CreativePackageRequest) -> dict[str, Any]:
    name = payload.campaign_object_name.strip()
    audience = payload.target_audience or "Pemilih muda dan publik target kampanye"
    platform = payload.platform or "TikTok, Instagram"
    goal = payload.campaign_goal or payload.content_objective or "Awareness"
    tone = payload.tone or "Optimis, dekat, modern, tidak kaku"
    negative = "hindari fitnah, disinformasi, klaim palsu, ujaran kebencian, SARA, visual kekerasan politik, manipulasi data, dan deepfake tokoh nyata tanpa izin"

    result = {
        "project_title": f"{name} Creative Package",
        "creative_brief": f"Creative package ini bertujuan mendukung {goal} untuk {name}. Target utama adalah {audience}, dengan kanal {platform} dan tone {tone}. Konten harus terasa dekat, visual, singkat, dan berbasis pesan kampanye yang bisa diverifikasi.",
        "big_idea": f"{name}: dekat dengan warga, bekerja dengan bukti, dan membuka ruang partisipasi publik.",
        "content_angles": ["Cerita masalah harian warga dan solusi yang bisa dicek.", "Anak muda sebagai penggerak ide dan pengawasan agenda.", "Klarifikasi isu negatif dengan fakta singkat dan empati.", "Recap mingguan progres kampanye yang transparan."],
        "hook_options": ["Apa isu paling dekat dengan warga hari ini?", "Bukan slogan, ini rencana yang bisa dicek.", "Anak muda tidak cuma jadi penonton.", "Kalau ada kritik, jawabnya pakai data.", "Dalam 30 hari, ini yang harus berubah."],
        "caption_options": [f"{name} mengajak publik melihat agenda dengan cara yang lebih terbuka: dengar warga, susun aksi, dan laporkan progres.", "Kampanye yang sehat dimulai dari masalah nyata dan bukti yang bisa dicek.", "Suara anak muda penting untuk menentukan agenda wilayah.", "Kritik dijawab dengan klarifikasi, data, dan perbaikan.", "Ikuti progres mingguan dan kirim isu yang paling dekat dengan wilayahmu."],
        "cta_options": ["Kirim isu wilayahmu.", "Simpan dan cek progres minggu depan.", "Bagikan kalau ini relevan.", "Ikut forum warga.", "Tulis pertanyaanmu di komentar."],
        "visual_style": "Clean political campaign, red accent, modern editorial, documentary-light, high contrast, optimis, tidak kaku.",
        "image_prompts": [
            {"title": "Poster isu warga", "prompt": f"Create a social media campaign poster for {name}, objective {goal}, target {audience}, red accent, clean editorial layout, Indonesian local civic atmosphere, optimistic modern tone, headline space, no fake statistics, aspect ratio 1:1", "negative_prompt": negative, "aspect_ratio": "1:1", "recommended_platform": "Instagram"},
            {"title": "Youth campaign visual", "prompt": f"Create a youth campaign visual for {name}, young Papuan voters discussing ideas in a modern community space, red accent, cinematic natural light, social media graphic, positive and inclusive, no party attack, aspect ratio 9:16", "negative_prompt": negative, "aspect_ratio": "9:16", "recommended_platform": "TikTok/Reels"},
            {"title": "Progress recap card", "prompt": f"Create a weekly progress recap graphic for {name}, clean infographic style, red and white palette, placeholders for verified action items, transparent and credible mood, aspect ratio 4:5", "negative_prompt": negative, "aspect_ratio": "4:5", "recommended_platform": "Instagram Feed"},
            {"title": "Community listening", "prompt": f"Create a documentary-style campaign image for {name}, local community listening forum, respectful civic dialogue, warm light, authentic but non-identifiable people, no real politician face, aspect ratio 16:9", "negative_prompt": negative, "aspect_ratio": "16:9", "recommended_platform": "YouTube/Facebook"},
        ],
        "video_prompts": [
            {"title": "30s awareness video", "prompt": f"Create a 30 second 9:16 campaign awareness video for {name}. Scene 1 local issue close-up, scene 2 youth discussion, scene 3 action plan graphic, scene 4 community forum, scene 5 CTA. Tone {tone}. Camera slow zoom, documentary social short, on-screen text in Indonesian, avoid fake claims.", "negative_prompt": negative, "aspect_ratio": "9:16", "duration": "30s", "recommended_platform": "TikTok/Reels"},
            {"title": "Issue explainer", "prompt": f"Create a 15 second 1:1 issue explainer for {name}, simple motion graphic, red accent, 3 verified points placeholder, clear CTA, no misleading data.", "negative_prompt": negative, "aspect_ratio": "1:1", "duration": "15s", "recommended_platform": "Instagram"},
            {"title": "PR recap video", "prompt": f"Create a 16:9 campaign progress recap video for {name}, news/documentary style, clean lower-third, community visuals, action checklist, no fake endorsement.", "negative_prompt": negative, "aspect_ratio": "16:9", "duration": "30s", "recommended_platform": "YouTube/Facebook"},
        ],
        "script": {"duration": "30s", "voiceover": f"Kampanye {name} dimulai dari mendengar isu yang paling dekat dengan warga. Setiap masukan dipetakan, setiap agenda dibuat terukur, dan setiap progres bisa dicek. Ini bukan soal janji paling keras, tapi kerja yang paling jelas. Kirim isu wilayahmu dan ikut pantau progresnya.", "on_screen_text": ["Dengar warga", "Susun aksi", "Laporkan progres", "Kirim isu wilayahmu"]},
        "storyboard": [
            {"scene": 1, "visual": "Close-up suasana wilayah dan aktivitas warga", "voiceover": "Kampanye dimulai dari masalah nyata.", "on_screen_text": "Dengar warga", "duration_seconds": 5},
            {"scene": 2, "visual": "Anak muda berdiskusi dengan catatan ide", "voiceover": "Anak muda ikut menentukan agenda.", "on_screen_text": "Anak muda bergerak", "duration_seconds": 6},
            {"scene": 3, "visual": "Grafik checklist aksi 30 hari", "voiceover": "Setiap agenda dibuat terukur.", "on_screen_text": "Aksi 30 hari", "duration_seconds": 6},
            {"scene": 4, "visual": "Forum warga kecil dan dialog lokal", "voiceover": "Masukan warga menjadi dasar kerja.", "on_screen_text": "Forum warga", "duration_seconds": 7},
            {"scene": 5, "visual": "CTA card merah putih", "voiceover": "Kirim isu wilayahmu dan pantau progresnya.", "on_screen_text": "Kirim isu wilayahmu", "duration_seconds": 6},
        ],
        "asset_specs": {"platform": platform, "format": "short video + feed graphic", "aspect_ratio": "9:16 / 1:1", "resolution": "1080p / 1024x1024"},
        "safety_notes": ["Semua klaim faktual perlu dicek dengan sumber sebelum dipublish.", "Hindari wajah tokoh nyata dalam generated visual tanpa izin eksplisit.", "Asset harus melewati approval manual sebelum dipakai publik."],
    }

    return normalize_creative_package(result, payload)


def build_mock_creative_asset(payload: CreativeAssetRequest) -> dict[str, Any]:
    count = max(1, min(payload.output_count, 4))
    width, height = parse_resolution(payload.resolution, payload.aspect_ratio, payload.asset_type)
    assets = []

    for index in range(count):
        extension = "mp4" if payload.asset_type == "video" else "png"
        slug = re.sub(r"[^a-z0-9]+", "-", payload.prompt.lower()).strip("-")[:42] or payload.asset_type
        file_path = f"creative-studio/{payload.asset_type}/{date.today().isoformat()}/{slug}-{index + 1}.{extension}"
        assets.append({"title": f"{payload.asset_type.title()} Asset {index + 1}", "file_path": file_path, "thumbnail_path": file_path if payload.asset_type == "image" else file_path.replace(".mp4", "-thumb.png"), "width": width, "height": height, "prompt_excerpt": payload.prompt[:500], "safety_status": "pending_review", "note": "Placeholder metadata asset. Sambungkan provider image/video real untuk file binary production."})

    return {"provider": payload.provider.name if payload.provider else "Creative Studio Mock Provider", "model": payload.model or f"creative-{payload.asset_type}-mock", "provider_job_id": f"mock-{payload.asset_type}-{datetime.utcnow().timestamp()}", "status": "completed", "cost_final": 0.08 * count if payload.asset_type == "image" else 0.35 * count, "assets": assets}


def parse_resolution(resolution: str | None, aspect_ratio: str | None, asset_type: str) -> tuple[int | None, int | None]:
    if resolution and "x" in resolution:
        left, right = resolution.lower().split("x", 1)
        if left.isdigit() and right.isdigit():
            return int(left), int(right)

    if asset_type == "video":
        if resolution == "720p":
            return (720, 1280) if aspect_ratio == "9:16" else (1280, 720)
        return (1080, 1920) if aspect_ratio == "9:16" else (1920, 1080)

    if aspect_ratio == "9:16":
        return 1024, 1792
    if aspect_ratio == "16:9":
        return 1792, 1024
    if aspect_ratio == "4:5":
        return 1080, 1350

    return 1024, 1024


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


def normalize_media_monitoring_result(result: dict[str, Any], context: dict[str, Any]) -> dict[str, Any]:
    result.setdefault("keyword", context.get("keyword", ""))
    result.setdefault("source_breakdown", {})
    result.setdefault("sentiment", {})
    result.setdefault("dominant_issues", [])
    result.setdefault("positive_issues", [])
    result.setdefault("negative_issues", [])
    result.setdefault("top_actors", [])
    result.setdefault("top_sources", [])
    result.setdefault("trend", {"summary": "Data tren time-series belum tersedia penuh.", "signals": []})
    result.setdefault(
        "google_trends_insight",
        {
            "summary": "Insight Google Trends belum tersedia penuh. Data ini perlu dibaca sebagai minat pencarian, bukan elektabilitas.",
            "related_queries": [],
            "related_topics": [],
        },
    )
    result.setdefault("items", [])
    result.setdefault("strategic_recommendation", {"high_priority": [], "medium_priority": [], "low_priority": []})

    sources = result.setdefault("sources", [])
    known_links = {source.get("link") for source in sources if isinstance(source, dict)}
    for source in context.get("sources", []):
        if source["link"] not in known_links:
            sources.append(source)

    items = result.get("items") if isinstance(result.get("items"), list) else []
    result["items"] = items
    result["total_items"] = int(result.get("total_items") or len(items))

    breakdown = result["source_breakdown"]
    for key in ["news_national", "news_local_papua", "social_media", "google_search", "google_trends"]:
        breakdown.setdefault(key, 0)

    sentiment = result["sentiment"]
    for key in ["positive", "neutral", "negative"]:
        sentiment.setdefault(key, 0)
    sentiment.setdefault("dominant", "neutral")
    result.setdefault("risk_level", "low")
    result.setdefault("risk_assessment", "Risiko reputasi belum dapat dinilai penuh karena data publik yang terkumpul masih terbatas.")
    result.setdefault(
        "executive_summary",
        "Monitoring selesai, tetapi ringkasan AI belum tersedia penuh. Lihat daftar sumber dan item untuk verifikasi manual.",
    )

    return result


def normalize_policy_result(result: dict[str, Any], context: dict[str, Any]) -> dict[str, Any]:
    result.setdefault("policy_topic", context.get("policy_topic", ""))
    result.setdefault("policy_description", {})
    result.setdefault("policy_objectives", [])
    result.setdefault("affected_groups", [])
    result.setdefault("public_response", {})
    result.setdefault("sentiment_analysis", {})
    result.setdefault("positive_impacts", [])
    result.setdefault("negative_impacts", [])
    result.setdefault("implementation_risks", [])
    result.setdefault("political_reputation_risk", {"level": "medium", "reason": "Risiko belum dapat dinilai penuh karena data terbatas."})
    result.setdefault("scenario_simulation", {})
    result.setdefault("stakeholders", [])
    result.setdefault("policy_score", {"score": 55, "category": "Perlu Kajian Lanjutan", "reason": "Skor konservatif karena sumber resmi/data respon publik masih terbatas."})
    result.setdefault("policy_improvement_recommendations", {"high_priority": [], "medium_priority": [], "low_priority": []})
    result.setdefault("public_communication_strategy", {"main_narrative": "", "target_messages": [], "channels": [], "response_to_criticism": []})
    result.setdefault(
        "executive_summary",
        "Analisis kebijakan selesai, tetapi data resmi dan respon publik yang tersedia masih perlu diverifikasi manual.",
    )

    public_response = result["public_response"]
    public_response.setdefault("summary", "Data respon publik belum tersedia penuh.")
    public_response.setdefault("supporting_narratives", [])
    public_response.setdefault("opposing_narratives", [])
    public_response.setdefault("neutral_narratives", [])
    public_response.setdefault("data_sources", [])
    public_response.setdefault("items", [])

    sentiment = result["sentiment_analysis"]
    sentiment.setdefault("dominant", "mixed")
    sentiment.setdefault("positive", 0)
    sentiment.setdefault("neutral", 0)
    sentiment.setdefault("negative", 0)
    sentiment.setdefault("notes", "Sentimen online tidak mewakili seluruh populasi tanpa survei.")

    scenario = result["scenario_simulation"]
    scenario.setdefault("optimistic", "Skenario optimis belum tersedia penuh.")
    scenario.setdefault("moderate", "Skenario moderat belum tersedia penuh.")
    scenario.setdefault("bad", "Skenario buruk belum tersedia penuh.")
    scenario.setdefault("indicators", {})

    sources = result.setdefault("sources", [])
    known_links = {source.get("link") for source in sources if isinstance(source, dict)}
    for source in context.get("sources", []):
        if source["link"] not in known_links:
            sources.append(source)

    return result


def build_mock_policy_report(policy_topic: str, context: dict[str, Any]) -> dict[str, Any]:
    pages = [page for page in context.get("browser_pages", []) if page.get("status") == "ok"]
    page_summaries = [
        {
            "platform": "web",
            "content_text": (page.get("text") or "")[:800],
            "url": page.get("url"),
            "sentiment": "neutral",
            "response_type": "neutral",
            "summary": page.get("title") or page.get("source_name"),
        }
        for page in pages
    ]

    result = {
        "policy_topic": policy_topic.strip(),
        "executive_summary": (
            f"Analisis awal untuk kebijakan {policy_topic.strip()} sudah dijalankan. "
            "Sistem membaca sumber publik yang dikonfigurasi untuk mencari dokumen kebijakan, pemberitaan, respon publik, dan sinyal Google Trends. "
            "Jika dokumen resmi belum ditemukan, laporan ini harus diperlakukan sebagai briefing awal dan bukan kesimpulan final. "
            "Respon online dibaca sebagai digital public response, bukan survei populasi."
        ),
        "policy_description": {
            "name": policy_topic.strip(),
            "level": "unknown",
            "status": "Data resmi kebijakan belum ditemukan atau belum cukup kuat untuk disimpulkan.",
            "implementing_body": "Belum teridentifikasi dari fallback lokal.",
            "official_sources": [],
        },
        "policy_objectives": [
            "Tujuan formal kebijakan perlu diverifikasi dari dokumen resmi.",
            "Masalah yang ingin diselesaikan belum dapat disimpulkan tanpa sumber kebijakan primer.",
        ],
        "affected_groups": [
            {"group": "Masyarakat terdampak kebijakan", "impact_type": "direct/indirect", "impact_level": "medium", "notes": "Kelompok spesifik perlu dikonfirmasi dari dokumen resmi dan data lapangan."}
        ],
        "public_response": {
            "summary": "Fallback lokal belum cukup untuk menyimpulkan dukungan/penolakan publik secara kuat.",
            "supporting_narratives": [],
            "opposing_narratives": ["Risiko kritik muncul jika implementasi tidak jelas, data penerima lemah, atau distribusi tidak merata."],
            "neutral_narratives": ["Sebagian respon publik mungkin menunggu detail pelaksanaan dan bukti manfaat."],
            "data_sources": ["Sumber publik yang dikonfigurasi pada run ini."],
            "items": page_summaries,
        },
        "sentiment_analysis": {
            "dominant": "mixed",
            "positive": 0,
            "neutral": len(page_summaries),
            "negative": 0,
            "notes": "Sentimen online belum bisa dianggap mewakili seluruh populasi tanpa survei atau data lapangan.",
        },
        "positive_impacts": [
            {
                "impact": "Potensi peningkatan manfaat publik jika target kebijakan tepat sasaran.",
                "why_positive": "Kebijakan publik yang dirancang dengan target jelas dapat menjawab kebutuhan layanan atau perlindungan masyarakat.",
                "supporting_data": ["Perlu verifikasi dari dokumen resmi dan data implementasi."],
            }
        ],
        "negative_impacts": [
            {
                "impact": "Risiko ketidakpercayaan publik jika implementasi tidak transparan.",
                "why_negative": "Tanpa data penerima, mekanisme pengawasan, dan kanal aduan, isu ketidakadilan mudah berkembang menjadi framing negatif.",
                "supporting_data": ["Pola umum kritik kebijakan publik; perlu verifikasi dari respon masyarakat aktual."],
            }
        ],
        "implementation_risks": [
            {"risk": "Data penerima/manfaat tidak akurat", "level": "medium", "reason": "Validitas data menentukan persepsi keadilan dan efektivitas implementasi."},
            {"risk": "Koordinasi antar lembaga lemah", "level": "medium", "reason": "Program lintas wilayah/lembaga rawan tumpang tindih dan lambat dieksekusi."},
        ],
        "political_reputation_risk": {
            "level": "medium",
            "reason": "Risiko reputasi meningkat jika manfaat tidak cepat terlihat atau muncul kasus implementasi.",
        },
        "scenario_simulation": {
            "optimistic": "Implementasi berjalan rapi, data penerima jelas, komunikasi publik konsisten, dan sentimen positif meningkat.",
            "moderate": "Manfaat mulai terlihat tetapi isu teknis dan kritik distribusi tetap muncul di media lokal/sosial.",
            "bad": "Implementasi bermasalah, kritik publik meningkat, media mengangkat kasus lapangan, dan lawan politik memakai isu sebagai serangan reputasi.",
            "indicators": {
                "public_acceptance_score": 55,
                "implementation_risk": "medium",
                "political_risk": "medium",
                "social_impact": "medium",
                "media_risk": "medium",
            },
        },
        "stakeholders": [
            {"name": "Pemerintah pelaksana", "type": "government", "position": "support", "influence": "high", "notes": "Aktor utama implementasi dan komunikasi kebijakan."},
            {"name": "Masyarakat terdampak", "type": "citizen_group", "position": "unclear", "influence": "high", "notes": "Penerimaan publik bergantung pada manfaat dan kualitas implementasi."},
            {"name": "Media", "type": "media", "position": "neutral", "influence": "medium", "notes": "Framing media dapat memperkuat dukungan atau kritik."},
        ],
        "policy_score": {
            "score": 55,
            "category": "Perlu Kajian Lanjutan",
            "reason": "Skor konservatif karena dokumen resmi, data respon publik, dan bukti implementasi belum cukup kuat.",
        },
        "policy_improvement_recommendations": {
            "high_priority": ["Verifikasi dokumen resmi dan indikator keberhasilan.", "Perjelas data sasaran/penerima manfaat dan mekanisme pengawasan."],
            "medium_priority": ["Bangun kanal aduan publik dan dashboard progres implementasi.", "Libatkan pemda, tokoh lokal, dan media lokal untuk validasi lapangan."],
            "low_priority": ["Susun konten edukasi kebijakan dengan bahasa sederhana dan visual ringkas."],
        },
        "public_communication_strategy": {
            "main_narrative": "Kebijakan harus diposisikan sebagai solusi bertahap yang transparan, terukur, dan bisa diawasi publik.",
            "target_messages": ["Manfaat konkret untuk kelompok terdampak.", "Cara masyarakat mengakses manfaat dan menyampaikan aduan.", "Komitmen transparansi dan evaluasi berkala."],
            "channels": ["Media lokal", "Media sosial resmi", "Forum warga", "Tokoh komunitas", "Website pemerintah"],
            "response_to_criticism": ["Akui kendala implementasi yang valid.", "Tampilkan data progres dan perbaikan.", "Pisahkan fakta lapangan dari opini atau framing politik."],
        },
        "sources": context.get("sources", []),
    }

    return normalize_policy_result(result, context)


def build_mock_media_monitoring(keyword: str, context: dict[str, Any]) -> dict[str, Any]:
    pages = [page for page in context.get("browser_pages", []) if page.get("status") == "ok"]
    items = []

    for page in pages:
        text = (page.get("text") or "").strip()
        items.append(
            {
                "title": page.get("title") or f"Hasil pemantauan {page.get('source_name', 'sumber publik')}",
                "source": page.get("source_name", "Sumber publik"),
                "source_type": page.get("source_type", "other"),
                "platform": "web",
                "url": page.get("url", ""),
                "published_at": None,
                "summary": text[:700] if text else "Konten halaman belum berhasil diekstrak secara memadai.",
                "sentiment": "neutral",
                "issue_category": "politik",
                "risk_level": "medium" if text else "low",
                "risk_reason": "Perlu analisis lanjutan dari model utama untuk mengukur risiko secara akurat.",
                "entities": [],
                "screenshot_path": page.get("screenshot_path"),
            }
        )

    news_national = len([item for item in items if item["source_type"] == "news_national"])
    news_local = len([item for item in items if item["source_type"] == "news_local_papua"])
    social = len([item for item in items if item["source_type"] == "social_media"])
    trends = len([item for item in items if item["source_type"] == "google_trends"])

    result = {
        "keyword": keyword.strip(),
        "executive_summary": (
            f"Monitoring keyword {keyword.strip()} sudah berjalan di latar belakang. "
            f"Sistem membaca {len(items)} halaman publik dari sumber yang dikonfigurasi. "
            "Ringkasan ini bersifat fallback lokal; analisis paling detail akan muncul ketika provider AI aktif dan berhasil mengembalikan JSON valid. "
            "Google Trends, bila tersedia, harus dibaca sebagai minat pencarian, bukan elektabilitas."
        ),
        "total_items": len(items),
        "source_breakdown": {
            "news_national": news_national,
            "news_local_papua": news_local,
            "social_media": social,
            "google_search": 0,
            "google_trends": trends,
        },
        "sentiment": {
            "positive": 0,
            "neutral": len(items),
            "negative": 0,
            "dominant": "neutral",
        },
        "risk_level": "medium" if items else "low",
        "risk_assessment": "Belum ada sinyal krisis yang dapat diverifikasi dari fallback lokal. Perlu analisis AI penuh untuk membaca framing, isu negatif, dan intensitas pemberitaan.",
        "dominant_issues": [
            {
                "issue": f"Percakapan/pemberitaan terkait {keyword.strip()}",
                "count": len(items),
                "sentiment": "neutral",
                "risk_level": "medium" if items else "low",
            }
        ],
        "positive_issues": [],
        "negative_issues": [],
        "top_actors": [],
        "top_sources": [
            {"name": item["source"], "source_type": item["source_type"], "item_count": 1}
            for item in items[:8]
        ],
        "trend": {
            "summary": "Data tren time-series belum tersedia dari fallback lokal.",
            "signals": [],
        },
        "google_trends_insight": {
            "summary": "Google Trends belum dianalisis penuh. Gunakan data Trends hanya sebagai indikator minat pencarian, bukan elektabilitas.",
            "related_queries": [],
            "related_topics": [],
        },
        "items": items,
        "strategic_recommendation": {
            "high_priority": [
                "Verifikasi manual item sumber utama sebelum dipakai untuk keputusan komunikasi.",
                "Jika ditemukan pemberitaan negatif, pisahkan fakta, dugaan, klarifikasi, dan opini sebelum merespons.",
            ],
            "medium_priority": [
                "Pantau media lokal Papua karena sering lebih cepat menangkap isu daerah.",
                "Tambahkan search provider resmi untuk memperluas cakupan Google Search dan sosial media publik.",
            ],
            "low_priority": [
                "Simpan screenshot sumber penting sebagai arsip monitoring.",
            ],
        },
        "sources": context.get("sources", []),
    }

    return normalize_media_monitoring_result(result, context)


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
