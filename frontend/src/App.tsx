import { useCallback, useEffect, useMemo, useState } from 'react'
import type { FormEvent, ReactNode } from 'react'
import {
  Activity,
  AlertTriangle,
  BarChart3,
  ChevronRight,
  Database,
  Download,
  FileText,
  Globe,
  Trash2,
  Layers,
  Lock,
  LogOut,
  Moon,
  Search,
  Settings,
  Shield,
  Sun,
  TrendingUp,
  User,
  Zap,
} from 'lucide-react'
import './App.css'

const API_BASE = import.meta.env.VITE_API_BASE_URL ?? '/api'
const APP_LOGO = '/logo.png'

type AppUser = {
  id: number
  name: string
  email: string
  role: 'super_admin' | 'admin' | 'analyst' | 'viewer'
}

type ModuleItem = {
  slug: string
  name: string
  description: string
  status: 'active' | 'coming_soon'
}

type ScreeningResult = {
  subject_name: string
  executive_summary: string
  profile: string
  political_career: string
  digital_footprint: string
  controversies: Array<{
    issue: string
    status: string
    source: string
    political_risk: string
  }>
  sentiment_analysis: string
  electability_data: string
  regional_base_analysis: string
  swot: {
    strengths: string[]
    weaknesses: string[]
    opportunities: string[]
    threats: string[]
  }
  final_score: {
    score: number
    category: string
    reason: string
    indicators?: Array<{
      name: string
      score: number
      note: string
    }>
  }
  electability_improvement_insights: string
  strategic_recommendations: {
    high: string[]
    medium: string[]
    low: string[]
  }
  sources: Array<{
    name: string
    link: string
    accessed_at: string
    type: string
  }>
}

type ScreeningReport = {
  id: number
  subject_name: string
  status: 'pending' | 'processing' | 'completed' | 'failed'
  final_score: number | null
  created_at: string
  started_at?: string | null
  completed_at?: string | null
  error_message?: string | null
  result_json: ScreeningResult | null
}

type MediaMonitoringInsight = {
  executive_summary?: string | null
  dominant_issues_json?: Array<{ issue: string; count?: number; sentiment?: string; risk_level?: string }> | null
  positive_issues_json?: string[] | null
  negative_issues_json?: string[] | null
  top_actors_json?: Array<{ name: string; type?: string; mentions?: number }> | null
  top_sources_json?: Array<{ name: string; source_type?: string; item_count?: number }> | null
  trend_json?: { summary?: string; signals?: string[] } | null
  google_trends_json?: { summary?: string; related_queries?: string[]; related_topics?: string[] } | null
  risk_assessment?: string | null
  strategic_recommendation?: {
    high_priority?: string[]
    medium_priority?: string[]
    low_priority?: string[]
  } | null
  raw_json?: Record<string, unknown> | null
}

type MediaItem = {
  id: number
  title?: string | null
  source_type: string
  platform: string
  snippet?: string | null
  url?: string | null
  published_at?: string | null
  source?: { name: string; source_type: string } | null
  analysis?: {
    summary?: string | null
    sentiment?: string | null
    issue_category?: string | null
    risk_level?: string | null
    risk_reason?: string | null
    recommendation?: string | null
  } | null
}

type MediaMonitoringRun = {
  id: number
  status: 'pending' | 'processing' | 'completed' | 'failed'
  total_items: number
  news_count: number
  social_count: number
  google_search_count: number
  google_trends_count: number
  positive_count: number
  neutral_count: number
  negative_count: number
  risk_level?: string | null
  error_message?: string | null
  created_at: string
  started_at?: string | null
  finished_at?: string | null
  keyword?: { id: number; keyword: string } | null
  insight?: MediaMonitoringInsight | null
  items?: MediaItem[]
}

type PolicyResult = {
  policy_topic: string
  executive_summary: string
  policy_description: {
    name?: string
    level?: string
    status?: string
    implementing_body?: string
    official_sources?: string[]
  }
  policy_objectives: string[]
  affected_groups: Array<{ group: string; impact_type?: string; impact_level?: string; notes?: string }>
  public_response: {
    summary?: string
    supporting_narratives?: string[]
    opposing_narratives?: string[]
    neutral_narratives?: string[]
    data_sources?: string[]
  }
  sentiment_analysis: { dominant?: string; positive?: number; neutral?: number; negative?: number; notes?: string }
  positive_impacts: Array<{ impact: string; why_positive?: string; supporting_data?: string[] }>
  negative_impacts: Array<{ impact: string; why_negative?: string; supporting_data?: string[] }>
  implementation_risks: Array<{ risk: string; level?: string; reason?: string }>
  political_reputation_risk: { level?: string; reason?: string }
  scenario_simulation: { optimistic?: string; moderate?: string; bad?: string; indicators?: Record<string, unknown> }
  stakeholders: Array<{ name: string; type?: string; position?: string; influence?: string; notes?: string }>
  policy_score: { score?: number; category?: string; reason?: string }
  policy_improvement_recommendations: { high_priority?: string[]; medium_priority?: string[]; low_priority?: string[] }
  public_communication_strategy: {
    main_narrative?: string
    target_messages?: string[]
    channels?: string[]
    response_to_criticism?: string[]
  }
  sources: Array<{ name?: string; link?: string; accessed_at?: string; type?: string; data_used?: string }>
}

type PolicyReport = {
  id: number
  executive_summary?: string | null
  result_json: PolicyResult | null
  sources_json?: PolicyResult['sources'] | null
  final_score?: number | null
  risk_level?: string | null
}

type PolicyRequest = {
  id: number
  policy_topic: string
  status: 'pending' | 'processing' | 'completed' | 'failed'
  error_message?: string | null
  created_at: string
  started_at?: string | null
  finished_at?: string | null
  report?: PolicyReport | null
}

type CampaignStrategyResult = {
  campaign_object: {
    type: string
    name: string
    goal: string
    region: string
  }
  metrics?: {
    strategic_fit?: number
    message_clarity?: string
    risk_level?: string
    priority_segment?: string
  }
  strategic_fit?: { score?: number; reason?: string }
  executive_summary: string
  campaign_context: { summary?: string; data_used?: unknown[] }
  campaign_goal_analysis?: string
  situation_analysis?: string
  positioning: { statement?: string; core_identity?: string; differentiator?: string; perception_gap?: string }
  target_segments: Array<{ segment?: string; priority?: string; needs?: string; main_issue?: string; message?: string; channel?: string }>
  priority_issues: Array<{ issue?: string; priority?: string; reason?: string; risk?: string; recommended_narrative?: string }>
  main_narrative: string
  key_messages: Array<{ message?: string; target?: string; reason?: string; channel?: string }>
  regional_strategy: Array<{ region?: string; status?: string; strategy?: string; actions?: unknown[]; risk?: string }>
  social_media_strategy: { platforms?: unknown[]; content_style?: string; posting_frequency?: string; formats?: unknown[]; hashtags?: unknown[] }
  local_media_pr_strategy: { priority_media?: unknown[]; story_angles?: unknown[]; press_release_agenda?: unknown[]; negative_news_response?: string }
  ground_campaign_strategy: Array<{ activity?: string; target?: string; region?: string; expected_output?: string }>
  negative_issue_mitigation: Array<{ issue?: string; risk?: string; main_response?: string; counter_narrative?: string; supporting_evidence?: unknown[] }>
  content_recommendations: Array<{ hook?: string; format?: string; target?: string; message?: string; cta?: string }>
  action_plan_30_days: { week_1?: unknown[]; week_2?: unknown[]; week_3?: unknown[]; week_4?: unknown[] }
  success_indicators: unknown[]
  risks_and_notes: unknown[]
  sources: Array<{ name?: string; link?: string; accessed_at?: string; type?: string; data_used?: string }>
}

type CampaignStrategyReport = {
  id: number
  executive_summary?: string | null
  positioning_statement?: string | null
  main_narrative?: string | null
  final_strategy_json: CampaignStrategyResult | null
  sources_json?: CampaignStrategyResult['sources'] | null
  strategic_score?: number | null
  risk_level?: string | null
}

type CampaignStrategyRequest = {
  id: number
  campaign_object_type: string
  campaign_object_name: string
  campaign_goal: string
  region?: string | null
  status: 'pending' | 'processing' | 'completed' | 'failed'
  error_message?: string | null
  created_at: string
  started_at?: string | null
  finished_at?: string | null
  report?: CampaignStrategyReport | null
}

type CreativePackage = {
  id: number
  creative_brief?: string | null
  big_idea?: string | null
  content_angles_json?: unknown[] | null
  hook_options_json?: unknown[] | null
  caption_options_json?: unknown[] | null
  cta_options_json?: unknown[] | null
  visual_style?: string | null
  script_json?: Record<string, unknown> | null
  storyboard_json?: unknown[] | null
  image_prompts_json?: Array<{ title?: string; prompt?: string; negative_prompt?: string; aspect_ratio?: string; recommended_platform?: string }> | null
  video_prompts_json?: Array<{ title?: string; prompt?: string; negative_prompt?: string; aspect_ratio?: string; duration?: string; recommended_platform?: string }> | null
  asset_specs_json?: Record<string, unknown> | null
  safety_notes_json?: unknown[] | null
  raw_json?: Record<string, unknown> | null
}

type CreativeProject = {
  id: number
  title: string
  campaign_object_type?: string | null
  campaign_object_name: string
  objective?: string | null
  platform?: string | null
  tone?: string | null
  status: string
  created_at: string
  package?: CreativePackage | null
}

type CreativeJob = {
  id: number
  asset_type: 'image' | 'video'
  prompt: string
  status: 'queued' | 'processing' | 'polling' | 'completed' | 'failed' | 'cancelled' | 'expired'
  aspect_ratio?: string | null
  resolution?: string | null
  duration?: string | null
  quality?: string | null
  style?: string | null
  output_count: number
  cost_estimate?: string | number | null
  error_message?: string | null
  created_at: string
  assets?: CreativeAsset[]
}

type CreativeAsset = {
  id: number
  asset_type: string
  title?: string | null
  file_path?: string | null
  thumbnail_path?: string | null
  prompt_used?: string | null
  provider_used?: string | null
  model_used?: string | null
  aspect_ratio?: string | null
  resolution?: string | null
  status: string
  approval_status: string
  metadata_json?: Record<string, unknown> | null
  created_at: string
}

type Agent = {
  id: number
  name: string
  role_description: string
  system_prompt?: string | null
  provider_id?: number | null
  model_id?: number | null
  temperature: number
  max_tokens: number
  status: string
  provider?: { id: number; name: string; base_url: string; status: string; masked_api_key: string | null }
  model?: { id: number; provider_id: number; display_name: string; model_name: string; is_active: boolean; context_window?: number | null }
  skills: AgentSkill[]
}

type AgentSkill = {
  id: number
  name: string
  slug?: string
  description?: string
  risk_level: string
  pivot: {
    enabled: boolean
    requires_approval?: boolean
    daily_limit?: number | null
  }
}

type AgentDraft = {
  providerName: string
  baseUrl: string
  apiKey: string
  modelName: string
  displayName: string
  temperature: string
  maxTokens: string
}

type View = 'modules' | 'screening' | 'media-monitoring' | 'policy-intelligence' | 'campaign-strategy' | 'creative-studio' | 'reports' | 'settings'

const fallbackModules = [
  {
    slug: 'screening-tokoh',
    name: 'Screening Tokoh',
    description: 'Analisis profil, karier politik, kontroversi, sentimen, basis daerah, SWOT, dan rekomendasi strategis.',
    status: 'active' as const,
  },
  {
    slug: 'monitoring-isu',
    name: 'Monitoring Isu',
    description: 'Pantau isu publik dan framing media secara berkala.',
    status: 'coming_soon' as const,
  },
  {
    slug: 'peta-elektoral',
    name: 'Peta Elektoral',
    description: 'Analisis wilayah, segmentasi pemilih, dan kekuatan basis daerah.',
    status: 'coming_soon' as const,
  },
  {
    slug: 'media-intelligence',
    name: 'Media Intelligence',
    description: 'Tracking media online, sosial media, dan percakapan publik.',
    status: 'coming_soon' as const,
  },
]

function App() {
  const [token, setToken] = useState(() => localStorage.getItem('pi_token') ?? '')
  const [user, setUser] = useState<AppUser | null>(() => {
    const saved = localStorage.getItem('pi_user')
    return saved ? JSON.parse(saved) as AppUser : null
  })
  const [modules, setModules] = useState<ModuleItem[]>([])
  const [adminMenus, setAdminMenus] = useState<string[]>([])
  const [reports, setReports] = useState<ScreeningReport[]>([])
  const [activeReport, setActiveReport] = useState<ScreeningReport | null>(null)
  const [mediaRuns, setMediaRuns] = useState<MediaMonitoringRun[]>([])
  const [activeMediaRun, setActiveMediaRun] = useState<MediaMonitoringRun | null>(null)
  const [policyRequests, setPolicyRequests] = useState<PolicyRequest[]>([])
  const [activePolicyRequest, setActivePolicyRequest] = useState<PolicyRequest | null>(null)
  const [campaignRequests, setCampaignRequests] = useState<CampaignStrategyRequest[]>([])
  const [activeCampaignRequest, setActiveCampaignRequest] = useState<CampaignStrategyRequest | null>(null)
  const [creativeProjects, setCreativeProjects] = useState<CreativeProject[]>([])
  const [creativeAssets, setCreativeAssets] = useState<CreativeAsset[]>([])
  const [creativeJobs, setCreativeJobs] = useState<CreativeJob[]>([])
  const [activeCreativeProject, setActiveCreativeProject] = useState<CreativeProject | null>(null)
  const [agents, setAgents] = useState<Agent[]>([])
  const [settingsDraft, setSettingsDraft] = useState<Record<number, AgentDraft>>({})
  const [savingSkillAgentId, setSavingSkillAgentId] = useState<number | null>(null)
  const [deletingReportId, setDeletingReportId] = useState<number | null>(null)
  const [deletingMediaRunId, setDeletingMediaRunId] = useState<number | null>(null)
  const [deletingPolicyId, setDeletingPolicyId] = useState<number | null>(null)
  const [deletingCampaignId, setDeletingCampaignId] = useState<number | null>(null)
  const [view, setView] = useState<View>('modules')
  const [email, setEmail] = useState('superadmin@example.com')
  const [password, setPassword] = useState('password')
  const [subjectName, setSubjectName] = useState('Jhony Banua Rouw')
  const [mediaKeyword, setMediaKeyword] = useState('PSI Papua')
  const [policyTopic, setPolicyTopic] = useState('Makan Bergizi Gratis di Papua')
  const [campaignObjectType, setCampaignObjectType] = useState('party')
  const [campaignObjectName, setCampaignObjectName] = useState('PSI Papua')
  const [campaignGoal, setCampaignGoal] = useState('Meningkatkan penerimaan publik dan memperkuat basis anak muda')
  const [campaignRegion, setCampaignRegion] = useState('Papua')
  const [creativeObjectName, setCreativeObjectName] = useState('PSI Papua')
  const [creativeAudience, setCreativeAudience] = useState('Pemilih muda Papua')
  const [creativePlatform, setCreativePlatform] = useState('TikTok, Instagram')
  const [creativeObjective, setCreativeObjective] = useState('Awareness')
  const [creativeTone, setCreativeTone] = useState('Optimis, dekat, modern, tidak kaku')
  const [assetPrompt, setAssetPrompt] = useState('Create a clean red-accent youth campaign poster for PSI Papua, optimistic, modern, social media graphic, no fake data.')
  const [assetNegativePrompt, setAssetNegativePrompt] = useState('hoaks, fitnah, hate speech, SARA, fake statistics, unauthorized real face, violent political imagery')
  const [assetAspectRatio, setAssetAspectRatio] = useState('1:1')
  const [assetResolution, setAssetResolution] = useState('1024x1024')
  const [videoDuration, setVideoDuration] = useState('10s')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const [dark, setDark] = useState(false)

  const authedHeaders = useMemo(() => ({
    Authorization: `Bearer ${token}`,
    Accept: 'application/json',
    'Content-Type': 'application/json',
  }), [token])

  const visibleModules = modules.length > 0 ? modules : fallbackModules
  const hasRunningReports = reports.some((report) => ['pending', 'processing'].includes(report.status))
  const hasRunningMediaRuns = mediaRuns.some((run) => ['pending', 'processing'].includes(run.status))
  const hasRunningPolicyRequests = policyRequests.some((request) => ['pending', 'processing'].includes(request.status))
  const hasRunningCampaignRequests = campaignRequests.some((request) => ['pending', 'processing'].includes(request.status))
  const hasRunningCreativeJobs = creativeJobs.some((job) => ['queued', 'processing', 'polling'].includes(job.status))
  const isSuperAdmin = user?.role === 'super_admin'

  async function api<T>(path: string, options: RequestInit = {}): Promise<T> {
    const response = await fetch(`${API_BASE}${path}`, {
      ...options,
      headers: {
        ...(token ? authedHeaders : { Accept: 'application/json', 'Content-Type': 'application/json' }),
        ...(options.headers ?? {}),
      },
    })

    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      const errors = 'errors' in data && data.errors && typeof data.errors === 'object'
        ? Object.values(data.errors as Record<string, string[]>).flat()
        : []
      const message = 'message' in data ? data.message : errors[0] ?? 'Request gagal.'
      throw new Error(String(message))
    }

    return data as T
  }

  const loadDashboard = useCallback(async (nextToken = token) => {
    const headers = { Authorization: `Bearer ${nextToken}`, Accept: 'application/json', 'Content-Type': 'application/json' }
    const [moduleResponse, reportResponse, mediaResponse, policyResponse, campaignResponse, creativeResponse] = await Promise.all([
      fetch(`${API_BASE}/modules`, { headers }).then((response) => response.json()),
      fetch(`${API_BASE}/screening-reports`, { headers }).then((response) => response.json()),
      fetch(`${API_BASE}/media-monitoring/runs`, { headers }).then((response) => response.json()).catch(() => ({ data: [] })),
      fetch(`${API_BASE}/policy-intelligence/reports`, { headers }).then((response) => response.json()).catch(() => ({ data: [] })),
      fetch(`${API_BASE}/campaign-strategy/reports`, { headers }).then((response) => response.json()).catch(() => ({ data: [] })),
      fetch(`${API_BASE}/creative-studio`, { headers }).then((response) => response.json()).catch(() => ({ data: { projects: [], assets: [], jobs: [] } })),
    ])

    setModules(moduleResponse.modules ?? [])
    setAdminMenus(moduleResponse.admin_menus ?? [])
    const nextReports = reportResponse.data ?? []
    setReports(nextReports)
    setActiveReport((current) => {
      if (!current) return nextReports[0] ?? null

      return nextReports.find((report: ScreeningReport) => report.id === current.id) ?? current
    })

    const nextMediaRuns = mediaResponse.data ?? []
    setMediaRuns(nextMediaRuns)
    setActiveMediaRun((current) => {
      if (!current) return nextMediaRuns[0] ?? null

      return nextMediaRuns.find((run: MediaMonitoringRun) => run.id === current.id) ?? current
    })

    const nextPolicyRequests = policyResponse.data ?? []
    setPolicyRequests(nextPolicyRequests)
    setActivePolicyRequest((current) => {
      if (!current) return nextPolicyRequests[0] ?? null

      return nextPolicyRequests.find((request: PolicyRequest) => request.id === current.id) ?? current
    })

    const nextCampaignRequests = campaignResponse.data ?? []
    setCampaignRequests(nextCampaignRequests)
    setActiveCampaignRequest((current) => {
      if (!current) return nextCampaignRequests[0] ?? null

      return nextCampaignRequests.find((request: CampaignStrategyRequest) => request.id === current.id) ?? current
    })

    const nextCreativeProjects = creativeResponse.data?.projects ?? []
    setCreativeProjects(nextCreativeProjects)
    setCreativeAssets(creativeResponse.data?.assets ?? [])
    setCreativeJobs(creativeResponse.data?.jobs ?? [])
    setActiveCreativeProject((current) => {
      if (!current) return nextCreativeProjects[0] ?? null

      return nextCreativeProjects.find((project: CreativeProject) => project.id === current.id) ?? current
    })
  }, [token])

  useEffect(() => {
    if (!token || !user) return

    const timer = window.setTimeout(() => {
      void loadDashboard()
    }, 0)

    return () => window.clearTimeout(timer)
  }, [token, user, loadDashboard])

  useEffect(() => {
    if (!token || !user) return

    const interval = window.setInterval(() => {
      if (view === 'screening' || view === 'reports' || view === 'media-monitoring' || view === 'policy-intelligence' || view === 'campaign-strategy' || view === 'creative-studio' || hasRunningReports || hasRunningMediaRuns || hasRunningPolicyRequests || hasRunningCampaignRequests || hasRunningCreativeJobs) {
        void loadDashboard()
      }
    }, hasRunningReports || hasRunningMediaRuns || hasRunningPolicyRequests || hasRunningCampaignRequests || hasRunningCreativeJobs ? 5000 : 15000)

    return () => window.clearInterval(interval)
  }, [token, user, view, hasRunningReports, hasRunningMediaRuns, hasRunningPolicyRequests, hasRunningCampaignRequests, hasRunningCreativeJobs, loadDashboard])

  async function handleLogin(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError('')
    setLoading(true)

    try {
      const data = await api<{ token: string; user: AppUser }>('/auth/login', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
      })
      localStorage.setItem('pi_token', data.token)
      localStorage.setItem('pi_user', JSON.stringify(data.user))
      setToken(data.token)
      setUser(data.user)
      await loadDashboard(data.token)
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Login gagal.')
    } finally {
      setLoading(false)
    }
  }

  async function handleGenerate(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError('')

    if (!subjectName.trim()) {
      setError('Nama tokoh wajib diisi.')
      return
    }

    setLoading(true)
    try {
      const data = await api<{ data: ScreeningReport }>('/screening-reports', {
        method: 'POST',
        body: JSON.stringify({ subject_name: subjectName }),
      })
      setActiveReport(data.data)
      setReports((current) => [data.data, ...current])
      setView('screening')
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Generate screening gagal.')
    } finally {
      setLoading(false)
    }
  }

  function saveReport(report: ScreeningReport) {
    const filename = `screening-${report.subject_name.toLowerCase().replace(/[^a-z0-9]+/gi, '-')}-${report.id}.json`
    const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(url)
  }

  async function deleteReport(report: ScreeningReport) {
    const confirmed = window.confirm(`Hapus laporan screening "${report.subject_name}"?`)
    if (!confirmed) return

    setError('')
    setDeletingReportId(report.id)

    try {
      await api(`/screening-reports/${report.id}`, { method: 'DELETE' })
      const nextReports = reports.filter((item) => item.id !== report.id)
      setReports(nextReports)
      setActiveReport((currentActive) => {
        if (currentActive?.id !== report.id) return currentActive

        return nextReports[0] ?? null
      })
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Gagal menghapus laporan.')
    } finally {
      setDeletingReportId(null)
    }
  }

  async function handleRunMediaMonitoring(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError('')

    if (!mediaKeyword.trim()) {
      setError('Keyword monitoring wajib diisi.')
      return
    }

    setLoading(true)
    try {
      const data = await api<{ data: MediaMonitoringRun }>('/media-monitoring/run', {
        method: 'POST',
        body: JSON.stringify({ keyword: mediaKeyword }),
      })
      setActiveMediaRun(data.data)
      setMediaRuns((current) => [data.data, ...current])
      setView('media-monitoring')
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Run media monitoring gagal.')
    } finally {
      setLoading(false)
    }
  }

  function saveMediaRun(run: MediaMonitoringRun) {
    const keyword = run.keyword?.keyword ?? 'media-monitoring'
    const filename = `media-monitoring-${keyword.toLowerCase().replace(/[^a-z0-9]+/gi, '-')}-${run.id}.json`
    const blob = new Blob([JSON.stringify(run, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(url)
  }

  async function deleteMediaRun(run: MediaMonitoringRun) {
    const confirmed = window.confirm(`Hapus hasil monitoring "${run.keyword?.keyword ?? run.id}"?`)
    if (!confirmed) return

    setError('')
    setDeletingMediaRunId(run.id)

    try {
      await api(`/media-monitoring/runs/${run.id}`, { method: 'DELETE' })
      const nextRuns = mediaRuns.filter((item) => item.id !== run.id)
      setMediaRuns(nextRuns)
      setActiveMediaRun((currentActive) => {
        if (currentActive?.id !== run.id) return currentActive

        return nextRuns[0] ?? null
      })
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Gagal menghapus hasil monitoring.')
    } finally {
      setDeletingMediaRunId(null)
    }
  }

  async function handleAnalyzePolicy(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError('')

    if (!policyTopic.trim()) {
      setError('Topik kebijakan wajib diisi.')
      return
    }

    setLoading(true)
    try {
      const data = await api<{ data: PolicyRequest }>('/policy-intelligence/analyze', {
        method: 'POST',
        body: JSON.stringify({ policy_topic: policyTopic }),
      })
      setActivePolicyRequest(data.data)
      setPolicyRequests((current) => [data.data, ...current])
      setView('policy-intelligence')
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Analyze policy gagal.')
    } finally {
      setLoading(false)
    }
  }

  function savePolicyRequest(request: PolicyRequest) {
    const filename = `policy-${request.policy_topic.toLowerCase().replace(/[^a-z0-9]+/gi, '-')}-${request.id}.json`
    const blob = new Blob([JSON.stringify(request, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(url)
  }

  async function deletePolicyRequest(request: PolicyRequest) {
    const confirmed = window.confirm(`Hapus laporan policy "${request.policy_topic}"?`)
    if (!confirmed) return

    setError('')
    setDeletingPolicyId(request.id)

    try {
      await api(`/policy-intelligence/reports/${request.id}`, { method: 'DELETE' })
      const nextRequests = policyRequests.filter((item) => item.id !== request.id)
      setPolicyRequests(nextRequests)
      setActivePolicyRequest((currentActive) => {
        if (currentActive?.id !== request.id) return currentActive

        return nextRequests[0] ?? null
      })
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Gagal menghapus laporan policy.')
    } finally {
      setDeletingPolicyId(null)
    }
  }

  async function handleGenerateCampaign(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError('')

    if (!campaignObjectName.trim()) {
      setError('Objek kampanye wajib diisi.')
      return
    }

    if (!campaignGoal.trim()) {
      setError('Tujuan kampanye wajib diisi.')
      return
    }

    setLoading(true)
    try {
      const data = await api<{ data: CampaignStrategyRequest }>('/campaign-strategy/generate', {
        method: 'POST',
        body: JSON.stringify({
          campaign_object_type: campaignObjectType,
          campaign_object_name: campaignObjectName,
          campaign_goal: campaignGoal,
          region: campaignRegion,
        }),
      })
      setActiveCampaignRequest(data.data)
      setCampaignRequests((current) => [data.data, ...current])
      setView('campaign-strategy')
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Generate campaign strategy gagal.')
    } finally {
      setLoading(false)
    }
  }

  function saveCampaignRequest(request: CampaignStrategyRequest) {
    const filename = `campaign-${request.campaign_object_name.toLowerCase().replace(/[^a-z0-9]+/gi, '-')}-${request.id}.json`
    const blob = new Blob([JSON.stringify(request, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(url)
  }

  async function deleteCampaignRequest(request: CampaignStrategyRequest) {
    const confirmed = window.confirm(`Hapus campaign strategy "${request.campaign_object_name}"?`)
    if (!confirmed) return

    setError('')
    setDeletingCampaignId(request.id)

    try {
      await api(`/campaign-strategy/reports/${request.id}`, { method: 'DELETE' })
      const nextRequests = campaignRequests.filter((item) => item.id !== request.id)
      setCampaignRequests(nextRequests)
      setActiveCampaignRequest((currentActive) => {
        if (currentActive?.id !== request.id) return currentActive

        return nextRequests[0] ?? null
      })
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Gagal menghapus campaign strategy.')
    } finally {
      setDeletingCampaignId(null)
    }
  }

  async function handleGenerateCreativePackage(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError('')

    if (!creativeObjectName.trim()) {
      setError('Objek kampanye wajib diisi.')
      return
    }

    setLoading(true)
    try {
      const data = await api<{ data: CreativeProject }>('/creative-studio/packages/generate', {
        method: 'POST',
        body: JSON.stringify({
          campaign_object_type: 'party',
          campaign_object_name: creativeObjectName,
          campaign_goal: creativeObjective,
          target_audience: creativeAudience,
          platform: creativePlatform,
          content_objective: creativeObjective,
          tone: creativeTone,
          source_strategy_report: activeCampaignRequest?.report?.final_strategy_json ?? null,
        }),
      })
      setActiveCreativeProject(data.data)
      setCreativeProjects((current) => [data.data, ...current.filter((project) => project.id !== data.data.id)])
      setView('creative-studio')
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Generate creative package gagal.')
    } finally {
      setLoading(false)
    }
  }

  async function handleGenerateCreativeAsset(assetType: 'image' | 'video') {
    setError('')

    if (!assetPrompt.trim()) {
      setError('Prompt asset wajib diisi.')
      return
    }

    setLoading(true)
    try {
      const data = await api<{ data: CreativeJob }>(assetType === 'image' ? '/creative-studio/images/generate' : '/creative-studio/videos/generate', {
        method: 'POST',
        body: JSON.stringify({
          project_id: activeCreativeProject?.id ?? null,
          prompt: assetPrompt,
          negative_prompt: assetNegativePrompt,
          aspect_ratio: assetAspectRatio,
          resolution: assetType === 'image' ? assetResolution : '1080p',
          duration: assetType === 'video' ? videoDuration : null,
          fps: assetType === 'video' ? '30' : null,
          quality: 'high',
          style: 'Social media graphic',
          camera_style: assetType === 'video' ? 'Slow zoom' : null,
          output_count: assetType === 'image' ? 2 : 1,
        }),
      })
      setCreativeJobs((current) => [data.data, ...current])
      setView('creative-studio')
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Generate asset gagal.')
    } finally {
      setLoading(false)
    }
  }

  async function reviewCreativeAsset(asset: CreativeAsset, status: 'approve' | 'reject') {
    setError('')

    try {
      const data = await api<{ data: CreativeAsset }>(`/creative-studio/assets/${asset.id}/${status}`, {
        method: 'POST',
        body: JSON.stringify({ notes: status === 'approve' ? 'Approved from Creative Studio.' : 'Rejected from Creative Studio.' }),
      })
      setCreativeAssets((current) => current.map((item) => item.id === asset.id ? data.data : item))
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Review asset gagal.')
    }
  }

  async function openSettings(nextView: View = 'settings') {
    setView(nextView)
    setError('')

    if (!isSuperAdmin) return

    try {
      const data = await api<{ data: Agent[] }>('/admin/agents')
      setAgents(data.data)
      setSettingsDraft(Object.fromEntries(data.data.map((agent) => [agent.id, draftFromAgent(agent)])))
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Gagal memuat agent settings.')
    }
  }

  function updateDraft(agentId: number, key: keyof AgentDraft, value: string) {
    setSettingsDraft((current) => ({
      ...current,
      [agentId]: {
        ...(current[agentId] ?? emptyDraft()),
        [key]: value,
      },
    }))
  }

  async function saveAgentSettings(agent: Agent) {
    const draft = settingsDraft[agent.id]
    if (!draft || !agent.provider || !agent.model) return

    setError('')
    setLoading(true)

    try {
      await api(`/admin/ai-providers/${agent.provider.id}`, {
        method: 'PUT',
        body: JSON.stringify({
          name: draft.providerName,
          base_url: draft.baseUrl,
          api_key: draft.apiKey,
          status: agent.provider.status ?? 'active',
        }),
      })

      await api(`/admin/ai-models/${agent.model.id}`, {
        method: 'PUT',
        body: JSON.stringify({
          provider_id: agent.provider.id,
          model_name: draft.modelName,
          display_name: draft.displayName || draft.modelName,
          context_window: agent.model.context_window,
          input_price_per_million_tokens: null,
          output_price_per_million_tokens: null,
          is_active: true,
        }),
      })

      await api(`/admin/agents/${agent.id}`, {
        method: 'PUT',
        body: JSON.stringify({
          name: agent.name,
          role_description: agent.role_description,
          system_prompt: agent.system_prompt,
          provider_id: agent.provider.id,
          model_id: agent.model.id,
          temperature: Number(draft.temperature),
          max_tokens: Number(draft.maxTokens),
          status: agent.status,
        }),
      })

      const data = await api<{ data: Agent[] }>('/admin/agents')
      setAgents(data.data)
      setSettingsDraft(Object.fromEntries(data.data.map((item) => [item.id, draftFromAgent(item)])))
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Gagal menyimpan Agent Settings.')
    } finally {
      setLoading(false)
    }
  }

  async function toggleAgentSkill(agent: Agent, skillId: number) {
    setError('')
    setSavingSkillAgentId(agent.id)

    const nextSkills = agent.skills.map((skill) => ({
      ...skill,
      pivot: {
        ...skill.pivot,
        enabled: skill.id === skillId ? !skill.pivot.enabled : skill.pivot.enabled,
      },
    }))

    try {
      const data = await api<{ data: Agent }>(`/admin/agents/${agent.id}/skills`, {
        method: 'PUT',
        body: JSON.stringify({
          skills: nextSkills.map((skill) => ({
            skill_id: skill.id,
            enabled: skill.pivot.enabled,
            requires_approval: skill.pivot.requires_approval ?? false,
            daily_limit: skill.pivot.daily_limit ?? null,
          })),
        }),
      })

      setAgents((current) => current.map((item) => (
        item.id === agent.id
          ? { ...item, skills: data.data.skills ?? nextSkills }
          : item
      )))
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'Gagal menyimpan skill agent.')
    } finally {
      setSavingSkillAgentId(null)
    }
  }

  async function logout() {
    try {
      await api('/auth/logout', { method: 'POST' })
    } catch {
      // Token lokal tetap dibersihkan walau API logout gagal.
    }

    localStorage.removeItem('pi_token')
    localStorage.removeItem('pi_user')
    setToken('')
    setUser(null)
    setReports([])
    setActiveReport(null)
    setMediaRuns([])
    setActiveMediaRun(null)
    setPolicyRequests([])
    setActivePolicyRequest(null)
    setCampaignRequests([])
    setActiveCampaignRequest(null)
    setCreativeProjects([])
    setCreativeAssets([])
    setCreativeJobs([])
    setActiveCreativeProject(null)
    setView('modules')
  }

  if (!user || !token) {
    return (
      <main className={dark ? 'app dark' : 'app'}>
        <section className="login-shell">
          <div className="login-copy">
            <div className="logo-lockup">
              <LogoMark />
              <div>
                <strong>SENA Intelligence</strong>
                <small>Sentiment & Narrative Analytics</small>
              </div>
            </div>
            <img className="login-brand-mark" src={APP_LOGO} alt="SENA logo" />
            <h1>Political intelligence berbasis agent AI.</h1>
            <p>Masuk untuk menjalankan screening tokoh, media monitoring, analisis sentimen, dan insight narasi politik dalam satu dashboard operasional.</p>
          </div>
          <form className="login-panel" onSubmit={handleLogin}>
            <span className="panel-kicker">Secure Access</span>
            <h2>Login Dashboard</h2>
            <label>Email</label>
            <input value={email} onChange={(event) => setEmail(event.target.value)} type="email" />
            <label>Password</label>
            <input value={password} onChange={(event) => setPassword(event.target.value)} type="password" />
            {error && <p className="error">{error}</p>}
            <button disabled={loading}>{loading ? 'Memproses...' : 'Login'}</button>
            <p className="hint">Demo: superadmin@example.com / password atau analyst@example.com / password</p>
          </form>
        </section>
      </main>
    )
  }

  return (
    <main className={dark ? 'app dark' : 'app'}>
      <div className={view === 'modules' ? 'app-shell modules-shell' : 'app-shell'}>
        {view !== 'modules' && (
          <Sidebar
            currentView={view}
            isSuperAdmin={isSuperAdmin}
            onNavigate={(nextView) => {
              if (nextView === 'settings') {
                void openSettings(nextView)
                return
              }
              setView(nextView)
            }}
          />
        )}

        <div className="main-pane">
          <Topbar
            dark={dark}
            user={user}
            view={view}
            onToggleDark={() => setDark((value) => !value)}
            onLogout={logout}
          />

          {error && <div className="toast error">{error}</div>}

          {view === 'modules' && (
            <ModulesPage
              modules={visibleModules}
              adminMenus={adminMenus}
              onOpenModule={(module) => {
                if (module.slug === 'media-monitoring') {
                  setView('media-monitoring')
                  return
                }

                if (module.slug === 'policy-intelligence') {
                  setView('policy-intelligence')
                  return
                }

                if (module.slug === 'campaign-strategy') {
                  setView('campaign-strategy')
                  return
                }

                if (module.slug === 'creative-studio') {
                  setView('creative-studio')
                  return
                }

                setView('screening')
              }}
            />
          )}

          {view === 'screening' && (
            <ScreeningPage
              activeReport={activeReport}
              deletingReportId={deletingReportId}
              loading={loading}
              reports={reports}
              subjectName={subjectName}
              onDeleteReport={deleteReport}
              onGenerate={handleGenerate}
              onPickReport={setActiveReport}
              onSaveReport={saveReport}
              onSubjectChange={setSubjectName}
            />
          )}

          {view === 'media-monitoring' && (
            <MediaMonitoringPage
              activeRun={activeMediaRun}
              deletingRunId={deletingMediaRunId}
              keyword={mediaKeyword}
              loading={loading}
              runs={mediaRuns}
              onDeleteRun={deleteMediaRun}
              onKeywordChange={setMediaKeyword}
              onPickRun={setActiveMediaRun}
              onRun={handleRunMediaMonitoring}
              onSaveRun={saveMediaRun}
            />
          )}

          {view === 'policy-intelligence' && (
            <PolicyIntelligencePage
              activeRequest={activePolicyRequest}
              deletingRequestId={deletingPolicyId}
              loading={loading}
              onAnalyze={handleAnalyzePolicy}
              onDeleteRequest={deletePolicyRequest}
              onPickRequest={setActivePolicyRequest}
              onSaveRequest={savePolicyRequest}
              onTopicChange={setPolicyTopic}
              requests={policyRequests}
              topic={policyTopic}
            />
          )}

          {view === 'campaign-strategy' && (
            <CampaignStrategyPage
              activeRequest={activeCampaignRequest}
              deletingRequestId={deletingCampaignId}
              goal={campaignGoal}
              loading={loading}
              objectName={campaignObjectName}
              objectType={campaignObjectType}
              onDeleteRequest={deleteCampaignRequest}
              onGenerate={handleGenerateCampaign}
              onGoalChange={setCampaignGoal}
              onObjectNameChange={setCampaignObjectName}
              onObjectTypeChange={setCampaignObjectType}
              onPickRequest={setActiveCampaignRequest}
              onRegionChange={setCampaignRegion}
              onSaveRequest={saveCampaignRequest}
              region={campaignRegion}
              requests={campaignRequests}
            />
          )}

          {view === 'creative-studio' && (
            <CreativeStudioPage
              activeProject={activeCreativeProject}
              aspectRatio={assetAspectRatio}
              assets={creativeAssets}
              audience={creativeAudience}
              jobs={creativeJobs}
              loading={loading}
              negativePrompt={assetNegativePrompt}
              objective={creativeObjective}
              objectName={creativeObjectName}
              platform={creativePlatform}
              projects={creativeProjects}
              prompt={assetPrompt}
              resolution={assetResolution}
              tone={creativeTone}
              videoDuration={videoDuration}
              onAspectRatioChange={setAssetAspectRatio}
              onGenerateAsset={handleGenerateCreativeAsset}
              onGeneratePackage={handleGenerateCreativePackage}
              onNegativePromptChange={setAssetNegativePrompt}
              onPickProject={setActiveCreativeProject}
              onPromptChange={setAssetPrompt}
              onResolutionChange={setAssetResolution}
              onReviewAsset={reviewCreativeAsset}
              onVideoDurationChange={setVideoDuration}
              onAudienceChange={setCreativeAudience}
              onObjectiveChange={setCreativeObjective}
              onObjectNameChange={setCreativeObjectName}
              onPlatformChange={setCreativePlatform}
              onToneChange={setCreativeTone}
            />
          )}

          {view === 'reports' && (
            <ReportsPage
              reports={reports}
              activeReport={activeReport}
              deletingReportId={deletingReportId}
              onDeleteReport={deleteReport}
              onPickReport={(report) => {
                setActiveReport(report)
                setView('screening')
              }}
              onSaveReport={saveReport}
            />
          )}

          {view === 'settings' && (
            <AgentSettingsPage
              activeView={view}
              agents={agents}
              drafts={settingsDraft}
              isSuperAdmin={isSuperAdmin}
              loading={loading}
              savingSkillAgentId={savingSkillAgentId}
              onSave={saveAgentSettings}
              onToggleSkill={toggleAgentSkill}
              onUpdateDraft={updateDraft}
            />
          )}
        </div>
      </div>
    </main>
  )
}

function Sidebar({
  currentView,
  isSuperAdmin,
  onNavigate,
}: {
  currentView: View
  isSuperAdmin: boolean
  onNavigate: (view: View) => void
}) {
  const isModuleView = currentView === 'screening' || currentView === 'media-monitoring' || currentView === 'policy-intelligence' || currentView === 'campaign-strategy' || currentView === 'creative-studio' || currentView === 'reports'
  const activeModule = currentView === 'media-monitoring'
    ? { key: 'media-monitoring' as const, label: 'Media Monitoring', icon: Globe }
    : currentView === 'policy-intelligence'
      ? { key: 'policy-intelligence' as const, label: 'Policy Intelligence', icon: FileText }
      : currentView === 'campaign-strategy'
        ? { key: 'campaign-strategy' as const, label: 'Campaign Strategy', icon: BarChart3 }
        : currentView === 'creative-studio'
          ? { key: 'creative-studio' as const, label: 'Creative Studio', icon: Zap }
          : currentView === 'reports'
            ? { key: 'reports' as const, label: 'Reports', icon: FileText }
            : { key: 'screening' as const, label: 'Screening Tokoh', icon: Search }
  const nav = isModuleView
    ? [
        { key: currentView, label: activeModule.label, icon: activeModule.icon, locked: true },
        { key: 'modules' as const, label: 'Kembali ke Modules', icon: Layers },
      ]
    : [
        { key: 'modules' as const, label: 'Modules', icon: Layers },
      ]
  const adminNav = [
    { key: 'settings' as const, label: 'Agent Settings', icon: Settings },
  ]

  return (
    <aside className="sidebar">
      <div className="logo-lockup sidebar-logo">
        <LogoMark />
        <div>
          <strong>SENA</strong>
          <small>Political Intelligence</small>
        </div>
      </div>

      <nav className="sidebar-nav">
        {nav.map((item) => {
          const Icon = item.icon
          return (
            <button
              className={currentView === item.key ? 'active' : ''}
              key={item.key}
              onClick={() => !item.locked && onNavigate(item.key)}
            >
              <Icon size={17} /> {item.label}
            </button>
          )
        })}
      </nav>

      {isSuperAdmin && (
        <div className="admin-nav">
          <p>Super Admin</p>
          <nav className="sidebar-nav">
            {adminNav.map((item) => {
              const Icon = item.icon
              return (
                <button
                  className={currentView === item.key ? 'active dark-active' : ''}
                  key={item.key}
                  onClick={() => onNavigate(item.key)}
                >
                  <Icon size={17} /> {item.label}
                </button>
              )
            })}
          </nav>
        </div>
      )}

      <div className="secure-box">
        <div><Lock size={15} /> <strong>Secure AI Control</strong></div>
        <p>Agent settings, API key, model, routing, dan skill hanya untuk Super Admin.</p>
      </div>
    </aside>
  )
}

function Topbar({
  dark,
  user,
  view,
  onToggleDark,
  onLogout,
}: {
  dark: boolean
  user: AppUser
  view: View
  onToggleDark: () => void
  onLogout: () => void
}) {
  return (
    <header className="topbar">
      <div className="topbar-title">
        <LogoMark compact />
        <div>
          <strong>{viewTitle(view)}</strong>
          <small>SENA Political Intelligence Platform</small>
        </div>
      </div>
      <div className="topbar-actions">
        <span className={user.role === 'super_admin' ? 'role-pill super' : 'role-pill'}>{user.role.replace('_', ' ')}</span>
        <button className="icon-button" onClick={onToggleDark} aria-label="Toggle dark mode">
          {dark ? <Sun size={16} /> : <Moon size={16} />}
        </button>
        <span className="user-chip"><User size={15} /> {user.name}</span>
        <button className="icon-button" onClick={onLogout} aria-label="Logout"><LogOut size={16} /></button>
      </div>
    </header>
  )
}

function LogoMark({ compact = false }: { compact?: boolean }) {
  return (
    <span className={compact ? 'logo-mark compact' : 'logo-mark'}>
      <img src={APP_LOGO} alt="SENA logo" />
    </span>
  )
}

function ModulesPage({
  modules,
  adminMenus,
  onOpenModule,
}: {
  modules: ModuleItem[]
  adminMenus: string[]
  onOpenModule: (module: ModuleItem) => void
}) {
  return (
    <section className="page">
      <div className="page-heading">
        <div>
          <h1>Pilih Modul Intelligence</h1>
          <p>Mulai dari modul Screening Tokoh. Modul lain dapat ditambahkan bertahap setelah MVP stabil.</p>
        </div>
      </div>

      <div className="module-grid">
        {modules.map((module) => {
          const Icon = moduleIcon(module.slug)
          const isActive = module.status === 'active'
          return (
            <button
              key={module.slug}
              className={isActive ? 'module-card active-module' : 'module-card'}
              disabled={!isActive}
              onClick={() => onOpenModule(module)}
            >
              <div className="module-card-top">
                <span className="module-icon"><Icon size={20} /></span>
                <small>{isActive ? 'Active' : 'Coming Soon'}</small>
              </div>
              <strong>{module.name}</strong>
              <span>{module.description}</span>
              <em>Open module <ChevronRight size={14} /></em>
            </button>
          )
        })}
      </div>

      {adminMenus.length > 0 && (
        <div className="admin-strip">
          {adminMenus.map((menu) => <span key={menu}><Settings size={14} /> {menu}</span>)}
        </div>
      )}
    </section>
  )
}

function ScreeningPage({
  activeReport,
  deletingReportId,
  loading,
  reports,
  subjectName,
  onDeleteReport,
  onGenerate,
  onPickReport,
  onSaveReport,
  onSubjectChange,
}: {
  activeReport: ScreeningReport | null
  deletingReportId: number | null
  loading: boolean
  reports: ScreeningReport[]
  subjectName: string
  onDeleteReport: (report: ScreeningReport) => void
  onGenerate: (event: FormEvent<HTMLFormElement>) => void
  onPickReport: (report: ScreeningReport) => void
  onSaveReport: (report: ScreeningReport) => void
  onSubjectChange: (value: string) => void
}) {
  return (
    <section className="screening-page">
      <div className="page-heading compact">
        <div>
          <h1>Screening Tokoh Politik</h1>
          <p>Input cukup nama tokoh. Sistem akan menjalankan screening detail di latar belakang.</p>
        </div>
      </div>

      <div className="screening-layout">
        <aside className="control-panel">
          <form onSubmit={onGenerate} className="screening-form">
            <label>Nama Tokoh</label>
            <div className="search-input">
              <Search size={16} />
              <input value={subjectName} onChange={(event) => onSubjectChange(event.target.value)} placeholder="Contoh: Jhony Banua Rouw" />
            </div>
            <button disabled={loading}><Search size={16} /> {loading ? 'Masuk antrian...' : 'Generate Screening'}</button>
          </form>

          <div className="history">
            <div className="mini-heading">
              <h3>History</h3>
              <span>{reports.length} laporan</span>
            </div>
            {reports.length === 0 && <p>Belum ada laporan.</p>}
            {reports.map((report) => (
              <div className={activeReport?.id === report.id ? 'history-row selected' : 'history-row'} key={report.id}>
                <button onClick={() => onPickReport(report)}>
                  <span>{report.subject_name}</span>
                  <small>{statusLabel(report)} {report.final_score ? `- Skor ${report.final_score}` : ''}</small>
                </button>
                <button
                  className="mini-danger"
                  disabled={deletingReportId === report.id}
                  onClick={() => onDeleteReport(report)}
                  title="Hapus laporan"
                  type="button"
                >
                  <Trash2 size={14} />
                </button>
              </div>
            ))}
          </div>
        </aside>

        <Report
          deletingReportId={deletingReportId}
          onDeleteReport={onDeleteReport}
          onSaveReport={onSaveReport}
          report={activeReport}
        />
      </div>
    </section>
  )
}

function MediaMonitoringPage({
  activeRun,
  deletingRunId,
  keyword,
  loading,
  runs,
  onDeleteRun,
  onKeywordChange,
  onPickRun,
  onRun,
  onSaveRun,
}: {
  activeRun: MediaMonitoringRun | null
  deletingRunId: number | null
  keyword: string
  loading: boolean
  runs: MediaMonitoringRun[]
  onDeleteRun: (run: MediaMonitoringRun) => void
  onKeywordChange: (value: string) => void
  onPickRun: (run: MediaMonitoringRun) => void
  onRun: (event: FormEvent<HTMLFormElement>) => void
  onSaveRun: (run: MediaMonitoringRun) => void
}) {
  return (
    <section className="screening-page">
      <div className="page-heading compact">
        <div>
          <h1>Media Monitoring</h1>
          <p>Input keyword, lalu agent memantau berita nasional, media lokal Papua, sosial publik, Google Search, dan Google Trends di latar belakang.</p>
        </div>
      </div>

      <div className="screening-layout">
        <aside className="control-panel">
          <form onSubmit={onRun} className="screening-form">
            <label>Keyword</label>
            <div className="search-input">
              <Globe size={16} />
              <input value={keyword} onChange={(event) => onKeywordChange(event.target.value)} placeholder="Contoh: PSI Papua" />
            </div>
            <button disabled={loading}><Search size={16} /> {loading ? 'Masuk antrian...' : 'Run Monitoring'}</button>
            <p className="hint">Monitoring berjalan seperti cron job/background worker. Hasil akan refresh otomatis.</p>
          </form>

          <div className="history">
            <div className="mini-heading">
              <h3>Monitoring Runs</h3>
              <span>{runs.length} hasil</span>
            </div>
            {runs.length === 0 && <p>Belum ada hasil monitoring.</p>}
            {runs.map((run) => (
              <div className={activeRun?.id === run.id ? 'history-row selected' : 'history-row'} key={run.id}>
                <button onClick={() => onPickRun(run)}>
                  <span>{run.keyword?.keyword ?? `Run #${run.id}`}</span>
                  <small>{mediaStatusLabel(run)} {run.total_items ? `- ${run.total_items} item` : ''}</small>
                </button>
                <button
                  className="mini-danger"
                  disabled={deletingRunId === run.id}
                  onClick={() => onDeleteRun(run)}
                  title="Hapus hasil"
                  type="button"
                >
                  <Trash2 size={14} />
                </button>
              </div>
            ))}
          </div>
        </aside>

        <MediaMonitoringReport
          deletingRunId={deletingRunId}
          onDeleteRun={onDeleteRun}
          onSaveRun={onSaveRun}
          run={activeRun}
        />
      </div>
    </section>
  )
}

function MediaMonitoringReport({
  deletingRunId,
  onDeleteRun,
  onSaveRun,
  run,
}: {
  deletingRunId: number | null
  onDeleteRun: (run: MediaMonitoringRun) => void
  onSaveRun: (run: MediaMonitoringRun) => void
  run: MediaMonitoringRun | null
}) {
  if (!run) {
    return (
      <article className="report empty-state">
        <Globe size={30} />
        <h2>Belum ada monitoring aktif</h2>
        <p>Masukkan keyword untuk mulai memantau media dan percakapan publik.</p>
      </article>
    )
  }

  if (run.status !== 'completed') {
    return (
      <article className="report empty-state">
        <div className="report-actions floating-actions">
          <button
            className="danger"
            disabled={deletingRunId === run.id}
            onClick={() => onDeleteRun(run)}
            type="button"
          >
            <Trash2 size={15} /> Hapus
          </button>
        </div>
        {run.status === 'failed' ? <AlertTriangle size={30} /> : <Activity size={30} />}
        <span className={`status-badge ${run.status}`}>{mediaStatusLabel(run)}</span>
        <h2>{run.keyword?.keyword ?? `Run #${run.id}`}</h2>
        <p>{mediaStatusDescription(run)}</p>
        {run.error_message && <p className="error">{run.error_message}</p>}
      </article>
    )
  }

  const insight = run.insight
  const recommendations = insight?.strategic_recommendation ?? {}

  return (
    <article className="report">
      <header className="report-header">
        <div className="report-header-top">
          <div>
            <span className="status-badge completed">Media Monitoring</span>
            <small>Generated at: {new Date(run.created_at).toLocaleString('id-ID')}</small>
          </div>
          <div className="report-actions">
            <button onClick={() => onSaveRun(run)} type="button"><Download size={15} /> Simpan</button>
            <button
              className="danger"
              disabled={deletingRunId === run.id}
              onClick={() => onDeleteRun(run)}
              type="button"
            >
              <Trash2 size={15} /> Hapus
            </button>
          </div>
        </div>
        <h1>{run.keyword?.keyword ?? `Run #${run.id}`}</h1>
        <p>{insight?.executive_summary ?? 'Ringkasan monitoring belum tersedia.'}</p>
      </header>

      <div className="metric-grid media-metrics">
        <Metric label="Total Data" value={`${run.total_items}`} icon={Database} />
        <Metric label="Portal Berita" value={`${run.news_count}`} icon={FileText} />
        <Metric label="Sosial Publik" value={`${run.social_count}`} icon={Globe} />
        <Metric label="Risiko" value={run.risk_level ?? '-'} icon={AlertTriangle} />
        <Metric label="Positif" value={`${run.positive_count}`} icon={TrendingUp} />
        <Metric label="Netral" value={`${run.neutral_count}`} icon={Activity} />
        <Metric label="Negatif" value={`${run.negative_count}`} icon={AlertTriangle} />
        <Metric label="Google Trends" value={`${run.google_trends_count}`} icon={BarChart3} />
      </div>

      <ReportSection icon={<TrendingUp size={17} />} title="Analisis Sentimen">
        <div className="sentiment-bars">
          <SentimentBar label="Positif" value={run.positive_count} total={run.total_items} />
          <SentimentBar label="Netral" value={run.neutral_count} total={run.total_items} />
          <SentimentBar label="Negatif" value={run.negative_count} total={run.total_items} />
        </div>
      </ReportSection>

      <ReportSection icon={<Activity size={17} />} title="Isu Dominan">
        <CompactList items={(insight?.dominant_issues_json ?? []).map((issue) => `${issue.issue} - ${issue.count ?? 0} item - ${issue.sentiment ?? 'neutral'} - risk ${issue.risk_level ?? 'low'}`)} empty="Belum ada isu dominan yang terdeteksi." />
      </ReportSection>

      <ReportSection icon={<User size={17} />} title="Aktor dan Sumber Paling Aktif">
        <div className="two-column-list">
          <CompactList items={(insight?.top_actors_json ?? []).map((actor) => `${actor.name} (${actor.type ?? 'entity'}) - ${actor.mentions ?? 0} mention`)} empty="Aktor belum terdeteksi." />
          <CompactList items={(insight?.top_sources_json ?? []).map((source) => `${source.name} (${source.source_type ?? 'source'}) - ${source.item_count ?? 0} item`)} empty="Sumber aktif belum terdeteksi." />
        </div>
      </ReportSection>

      <ReportSection icon={<BarChart3 size={17} />} title="Tren dan Google Trends">
        <p>{insight?.trend_json?.summary ?? 'Tren pemberitaan belum tersedia penuh.'}</p>
        <p>{insight?.google_trends_json?.summary ?? 'Google Trends belum tersedia. Data Trends hanya indikator minat pencarian, bukan elektabilitas.'}</p>
      </ReportSection>

      <ReportSection icon={<AlertTriangle size={17} />} title="Risiko Reputasi">
        {insight?.risk_assessment ?? 'Risiko reputasi belum dapat dinilai penuh.'}
      </ReportSection>

      <ReportSection icon={<Settings size={17} />} title="Rekomendasi Respon">
        <Swot title="Prioritas Tinggi" items={recommendations.high_priority ?? []} />
        <Swot title="Prioritas Sedang" items={recommendations.medium_priority ?? []} />
        <Swot title="Prioritas Rendah" items={recommendations.low_priority ?? []} />
      </ReportSection>

      <ReportSection icon={<FileText size={17} />} title="Daftar Artikel/Post">
        <div className="media-item-list">
          {(run.items ?? []).length === 0 && <p>Belum ada artikel/post yang tersimpan.</p>}
          {(run.items ?? []).map((item) => (
            <div className="media-item-row" key={item.id}>
              <div>
                <strong>{item.title ?? 'Tanpa judul'}</strong>
                <small>{item.source?.name ?? item.source_type} - {item.published_at ? new Date(item.published_at).toLocaleDateString('id-ID') : 'tanggal tidak tersedia'} - {item.platform}</small>
                <p>{item.analysis?.summary ?? item.snippet ?? 'Ringkasan belum tersedia.'}</p>
              </div>
              <span className={`status-badge ${item.analysis?.sentiment === 'negative' ? 'failed' : 'completed'}`}>{item.analysis?.sentiment ?? 'neutral'}</span>
              <span className="status-badge">{item.analysis?.issue_category ?? '-'}</span>
              <span className="status-badge">{item.analysis?.risk_level ?? '-'}</span>
              {item.url && <a href={item.url} target="_blank" rel="noreferrer">Open Source</a>}
            </div>
          ))}
        </div>
      </ReportSection>
    </article>
  )
}

function PolicyIntelligencePage({
  activeRequest,
  deletingRequestId,
  loading,
  onAnalyze,
  onDeleteRequest,
  onPickRequest,
  onSaveRequest,
  onTopicChange,
  requests,
  topic,
}: {
  activeRequest: PolicyRequest | null
  deletingRequestId: number | null
  loading: boolean
  onAnalyze: (event: FormEvent<HTMLFormElement>) => void
  onDeleteRequest: (request: PolicyRequest) => void
  onPickRequest: (request: PolicyRequest) => void
  onSaveRequest: (request: PolicyRequest) => void
  onTopicChange: (value: string) => void
  requests: PolicyRequest[]
  topic: string
}) {
  return (
    <section className="screening-page">
      <div className="page-heading compact">
        <div>
          <h1>Policy Intelligence</h1>
          <p>Riset kebijakan, respon publik, simulasi dampak, risk scoring, dan strategi komunikasi dalam satu laporan.</p>
        </div>
      </div>

      <div className="screening-layout">
        <aside className="control-panel">
          <form onSubmit={onAnalyze} className="screening-form">
            <label>Nama / Topik Kebijakan</label>
            <div className="search-input">
              <FileText size={16} />
              <input value={topic} onChange={(event) => onTopicChange(event.target.value)} placeholder="Contoh: Makan Bergizi Gratis di Papua" />
            </div>
            <button disabled={loading}><Search size={16} /> {loading ? 'Masuk antrian...' : 'Analyze Policy'}</button>
            <p className="hint">Analisis berjalan di background worker. Hasil akan refresh otomatis.</p>
          </form>

          <div className="history">
            <div className="mini-heading">
              <h3>Policy Reports</h3>
              <span>{requests.length} laporan</span>
            </div>
            {requests.length === 0 && <p>Belum ada laporan policy.</p>}
            {requests.map((request) => (
              <div className={activeRequest?.id === request.id ? 'history-row selected' : 'history-row'} key={request.id}>
                <button onClick={() => onPickRequest(request)}>
                  <span>{request.policy_topic}</span>
                  <small>{policyStatusLabel(request)} {request.report?.final_score ? `- Skor ${request.report.final_score}` : ''}</small>
                </button>
                <button
                  className="mini-danger"
                  disabled={deletingRequestId === request.id}
                  onClick={() => onDeleteRequest(request)}
                  title="Hapus laporan"
                  type="button"
                >
                  <Trash2 size={14} />
                </button>
              </div>
            ))}
          </div>
        </aside>

        <PolicyReportView
          deletingRequestId={deletingRequestId}
          onDeleteRequest={onDeleteRequest}
          onSaveRequest={onSaveRequest}
          request={activeRequest}
        />
      </div>
    </section>
  )
}

function PolicyReportView({
  deletingRequestId,
  onDeleteRequest,
  onSaveRequest,
  request,
}: {
  deletingRequestId: number | null
  onDeleteRequest: (request: PolicyRequest) => void
  onSaveRequest: (request: PolicyRequest) => void
  request: PolicyRequest | null
}) {
  if (!request) {
    return (
      <article className="report empty-state">
        <FileText size={30} />
        <h2>Belum ada policy report aktif</h2>
        <p>Masukkan topik kebijakan untuk membuat analisis dampak dan rekomendasi.</p>
      </article>
    )
  }

  if (request.status !== 'completed' || !request.report?.result_json) {
    return (
      <article className="report empty-state">
        <div className="report-actions floating-actions">
          <button className="danger" disabled={deletingRequestId === request.id} onClick={() => onDeleteRequest(request)} type="button">
            <Trash2 size={15} /> Hapus
          </button>
        </div>
        {request.status === 'failed' ? <AlertTriangle size={30} /> : <Activity size={30} />}
        <span className={`status-badge ${request.status}`}>{policyStatusLabel(request)}</span>
        <h2>{request.policy_topic}</h2>
        <p>{policyStatusDescription(request)}</p>
        {request.error_message && <p className="error">{request.error_message}</p>}
      </article>
    )
  }

  const result = request.report.result_json

  return (
    <article className="report">
      <header className="report-header">
        <div className="report-header-top">
          <div>
            <span className="status-badge completed">Policy Report</span>
            <small>Generated at: {new Date(request.created_at).toLocaleString('id-ID')}</small>
          </div>
          <div className="report-actions">
            <button onClick={() => onSaveRequest(request)} type="button"><Download size={15} /> Simpan</button>
            <button className="danger" disabled={deletingRequestId === request.id} onClick={() => onDeleteRequest(request)} type="button">
              <Trash2 size={15} /> Hapus
            </button>
          </div>
        </div>
        <h1>{result.policy_topic}</h1>
        <p>{result.executive_summary}</p>
      </header>

      <div className="metric-grid">
        <Metric label="Policy Score" value={`${result.policy_score?.score ?? request.report.final_score ?? '-'}/100`} icon={BarChart3} />
        <Metric label="Kategori" value={result.policy_score?.category ?? '-'} icon={Shield} />
        <Metric label="Risk Level" value={result.political_reputation_risk?.level ?? request.report.risk_level ?? '-'} icon={AlertTriangle} />
        <Metric label="Stakeholder" value={`${result.stakeholders?.length ?? 0}`} icon={User} />
      </div>

      <ReportSection icon={<FileText size={17} />} title="1. Deskripsi Kebijakan">
        <p><strong>Nama:</strong> {result.policy_description?.name ?? result.policy_topic}</p>
        <p><strong>Level:</strong> {result.policy_description?.level ?? 'unknown'}</p>
        <p><strong>Status:</strong> {result.policy_description?.status ?? 'Data resmi belum ditemukan.'}</p>
        <p><strong>Pelaksana:</strong> {result.policy_description?.implementing_body ?? 'Belum teridentifikasi.'}</p>
      </ReportSection>
      <ReportSection icon={<Shield size={17} />} title="2. Tujuan Kebijakan">
        <CompactList items={result.policy_objectives ?? []} empty="Tujuan kebijakan belum tersedia." />
      </ReportSection>
      <ReportSection icon={<User size={17} />} title="3. Kelompok Terdampak">
        {(result.affected_groups ?? []).map((group) => (
          <div className="controversy" key={`${group.group}-${group.impact_level}`}>
            <p><strong>Kelompok:</strong> {group.group}</p>
            <p><strong>Jenis dampak:</strong> {group.impact_type ?? '-'}</p>
            <p><strong>Tingkat:</strong> {group.impact_level ?? '-'}</p>
            <p>{group.notes}</p>
          </div>
        ))}
      </ReportSection>
      <ReportSection icon={<Globe size={17} />} title="4. Respon Masyarakat">{result.public_response?.summary ?? 'Respon publik belum tersedia.'}</ReportSection>
      <ReportSection icon={<TrendingUp size={17} />} title="5. Analisis Sentimen Publik">
        <p><strong>Sentimen umum:</strong> {result.sentiment_analysis?.dominant ?? 'mixed'}</p>
        <p>Positif: {result.sentiment_analysis?.positive ?? 0} | Netral: {result.sentiment_analysis?.neutral ?? 0} | Negatif: {result.sentiment_analysis?.negative ?? 0}</p>
        <p>{result.sentiment_analysis?.notes}</p>
      </ReportSection>
      <ReportSection icon={<TrendingUp size={17} />} title="6. Dampak Positif">
        {(result.positive_impacts ?? []).map((impact) => <ImpactBlock key={impact.impact} title={impact.impact} reason={impact.why_positive} data={impact.supporting_data} />)}
      </ReportSection>
      <ReportSection icon={<AlertTriangle size={17} />} title="7. Dampak Negatif">
        {(result.negative_impacts ?? []).map((impact) => <ImpactBlock key={impact.impact} title={impact.impact} reason={impact.why_negative} data={impact.supporting_data} />)}
      </ReportSection>
      <ReportSection icon={<AlertTriangle size={17} />} title="8. Risiko Implementasi">
        {(result.implementation_risks ?? []).map((risk) => (
          <div className="controversy" key={risk.risk}>
            <p><strong>{risk.risk}</strong> - {risk.level}</p>
            <p>{risk.reason}</p>
          </div>
        ))}
      </ReportSection>
      <ReportSection icon={<Shield size={17} />} title="9. Risiko Politik & Reputasi">{result.political_reputation_risk?.reason ?? 'Belum tersedia.'}</ReportSection>
      <ReportSection icon={<BarChart3 size={17} />} title="10. Simulasi Skenario">
        <Swot title="Optimis" items={[result.scenario_simulation?.optimistic ?? 'Belum tersedia.']} />
        <Swot title="Moderat" items={[result.scenario_simulation?.moderate ?? 'Belum tersedia.']} />
        <Swot title="Buruk" items={[result.scenario_simulation?.bad ?? 'Belum tersedia.']} />
      </ReportSection>
      <ReportSection icon={<User size={17} />} title="11. Stakeholder Mapping">
        {(result.stakeholders ?? []).map((stakeholder) => (
          <div className="controversy" key={stakeholder.name}>
            <p><strong>{stakeholder.name}</strong> - {stakeholder.type}</p>
            <p>Posisi: {stakeholder.position} | Pengaruh: {stakeholder.influence}</p>
            <p>{stakeholder.notes}</p>
          </div>
        ))}
      </ReportSection>
      <ReportSection icon={<BarChart3 size={17} />} title="12. Policy Score">
        <div className="score-box"><strong>{result.policy_score?.score ?? request.report.final_score}/100</strong><span>{result.policy_score?.category}</span></div>
        <p>{result.policy_score?.reason}</p>
      </ReportSection>
      <ReportSection icon={<Settings size={17} />} title="13. Rekomendasi Perbaikan Kebijakan">
        <Swot title="Prioritas Tinggi" items={result.policy_improvement_recommendations?.high_priority ?? []} />
        <Swot title="Prioritas Sedang" items={result.policy_improvement_recommendations?.medium_priority ?? []} />
        <Swot title="Prioritas Rendah" items={result.policy_improvement_recommendations?.low_priority ?? []} />
      </ReportSection>
      <ReportSection icon={<Globe size={17} />} title="14. Strategi Komunikasi Publik">
        <p><strong>Narasi utama:</strong> {result.public_communication_strategy?.main_narrative}</p>
        <Swot title="Target Messages" items={result.public_communication_strategy?.target_messages ?? []} />
        <Swot title="Channels" items={result.public_communication_strategy?.channels ?? []} />
        <Swot title="Response to Criticism" items={result.public_communication_strategy?.response_to_criticism ?? []} />
      </ReportSection>
      <ReportSection icon={<Database size={17} />} title="15. Sumber Data">
        {(result.sources ?? []).map((source) => (
          <p key={`${source.name}-${source.link}`}>{source.name ?? '-'} - {source.link ?? '-'} - {source.accessed_at ?? '-'} - {source.type ?? '-'}</p>
        ))}
      </ReportSection>
    </article>
  )
}

function CampaignStrategyPage({
  activeRequest,
  deletingRequestId,
  goal,
  loading,
  objectName,
  objectType,
  onDeleteRequest,
  onGenerate,
  onGoalChange,
  onObjectNameChange,
  onObjectTypeChange,
  onPickRequest,
  onRegionChange,
  onSaveRequest,
  region,
  requests,
}: {
  activeRequest: CampaignStrategyRequest | null
  deletingRequestId: number | null
  goal: string
  loading: boolean
  objectName: string
  objectType: string
  onDeleteRequest: (request: CampaignStrategyRequest) => void
  onGenerate: (event: FormEvent<HTMLFormElement>) => void
  onGoalChange: (value: string) => void
  onObjectNameChange: (value: string) => void
  onObjectTypeChange: (value: string) => void
  onPickRequest: (request: CampaignStrategyRequest) => void
  onRegionChange: (value: string) => void
  onSaveRequest: (request: CampaignStrategyRequest) => void
  region: string
  requests: CampaignStrategyRequest[]
}) {
  return (
    <section className="screening-page">
      <div className="page-heading compact">
        <div>
          <h1>Campaign Strategy</h1>
          <p>Susun positioning, segmentasi, narasi, strategi wilayah, media, kampanye darat, mitigasi isu, timeline, dan KPI dalam satu laporan.</p>
        </div>
      </div>

      <div className="screening-layout">
        <aside className="control-panel">
          <form onSubmit={onGenerate} className="screening-form">
            <label>Tipe Objek Kampanye</label>
            <select value={objectType} onChange={(event) => onObjectTypeChange(event.target.value)}>
              <option value="candidate">Tokoh / Kandidat</option>
              <option value="party">Partai Politik</option>
              <option value="policy">Kebijakan / Program</option>
              <option value="issue">Isu Politik</option>
              <option value="organization">Organisasi</option>
              <option value="other">Lainnya</option>
            </select>

            <label>Objek Kampanye</label>
            <div className="search-input">
              <BarChart3 size={16} />
              <input value={objectName} onChange={(event) => onObjectNameChange(event.target.value)} placeholder="Contoh: PSI Papua" />
            </div>

            <label>Tujuan Kampanye</label>
            <input value={goal} onChange={(event) => onGoalChange(event.target.value)} placeholder="Contoh: memperkuat basis anak muda" />

            <label>Wilayah</label>
            <input value={region} onChange={(event) => onRegionChange(event.target.value)} placeholder="Contoh: Papua" />

            <button disabled={loading}><Search size={16} /> {loading ? 'Masuk antrian...' : 'Generate Strategy'}</button>
            <p className="hint">Strategi berjalan di background worker. Hasil akan refresh otomatis setelah agent selesai menyusun laporan.</p>
          </form>

          <div className="history">
            <div className="mini-heading">
              <h3>Strategy Reports</h3>
              <span>{requests.length} laporan</span>
            </div>
            {requests.length === 0 && <p>Belum ada campaign strategy.</p>}
            {requests.map((request) => (
              <div className={activeRequest?.id === request.id ? 'history-row selected' : 'history-row'} key={request.id}>
                <button onClick={() => onPickRequest(request)}>
                  <span>{request.campaign_object_name}</span>
                  <small>{campaignStatusLabel(request)} {request.report?.strategic_score ? `- Skor ${request.report.strategic_score}` : ''}</small>
                </button>
                <button
                  className="mini-danger"
                  disabled={deletingRequestId === request.id}
                  onClick={() => onDeleteRequest(request)}
                  title="Hapus laporan"
                  type="button"
                >
                  <Trash2 size={14} />
                </button>
              </div>
            ))}
          </div>
        </aside>

        <CampaignStrategyReportView
          deletingRequestId={deletingRequestId}
          onDeleteRequest={onDeleteRequest}
          onSaveRequest={onSaveRequest}
          request={activeRequest}
        />
      </div>
    </section>
  )
}

function CampaignStrategyReportView({
  deletingRequestId,
  onDeleteRequest,
  onSaveRequest,
  request,
}: {
  deletingRequestId: number | null
  onDeleteRequest: (request: CampaignStrategyRequest) => void
  onSaveRequest: (request: CampaignStrategyRequest) => void
  request: CampaignStrategyRequest | null
}) {
  if (!request) {
    return (
      <article className="report empty-state">
        <BarChart3 size={30} />
        <h2>Belum ada campaign strategy aktif</h2>
        <p>Masukkan objek dan tujuan kampanye untuk membuat Strategic Campaign Report.</p>
      </article>
    )
  }

  if (request.status !== 'completed' || !request.report?.final_strategy_json) {
    return (
      <article className="report empty-state">
        <div className="report-actions floating-actions">
          <button className="danger" disabled={deletingRequestId === request.id} onClick={() => onDeleteRequest(request)} type="button">
            <Trash2 size={15} /> Hapus
          </button>
        </div>
        {request.status === 'failed' ? <AlertTriangle size={30} /> : <Activity size={30} />}
        <span className={`status-badge ${request.status}`}>{campaignStatusLabel(request)}</span>
        <h2>{request.campaign_object_name}</h2>
        <p>{campaignStatusDescription(request)}</p>
        {request.error_message && <p className="error">{request.error_message}</p>}
      </article>
    )
  }

  const result = request.report.final_strategy_json

  return (
    <article className="report">
      <header className="report-header">
        <div className="report-header-top">
          <div>
            <span className="status-badge completed">Campaign Strategy</span>
            <small>Generated at: {new Date(request.created_at).toLocaleString('id-ID')}</small>
          </div>
          <div className="report-actions">
            <button onClick={() => onSaveRequest(request)} type="button"><Download size={15} /> Simpan</button>
            <button className="danger" disabled={deletingRequestId === request.id} onClick={() => onDeleteRequest(request)} type="button">
              <Trash2 size={15} /> Hapus
            </button>
          </div>
        </div>
        <h1>{result.campaign_object?.name ?? request.campaign_object_name}</h1>
        <p>{result.executive_summary}</p>
      </header>

      <div className="metric-grid">
        <Metric label="Strategic Fit" value={`${result.metrics?.strategic_fit ?? result.strategic_fit?.score ?? request.report.strategic_score ?? '-'}/100`} icon={BarChart3} />
        <Metric label="Message Clarity" value={result.metrics?.message_clarity ?? '-'} icon={Shield} />
        <Metric label="Risk Level" value={result.metrics?.risk_level ?? request.report.risk_level ?? '-'} icon={AlertTriangle} />
        <Metric label="Priority Segment" value={result.metrics?.priority_segment ?? '-'} icon={User} />
      </div>

      <ReportSection icon={<FileText size={17} />} title="1. Ringkasan Strategi">{result.executive_summary}</ReportSection>
      <ReportSection icon={<Database size={17} />} title="2. Konteks Kampanye">
        <p>{result.campaign_context?.summary ?? 'Konteks kampanye belum tersedia.'}</p>
        <CompactList items={result.campaign_context?.data_used ?? []} empty="Data yang digunakan belum tersedia." />
      </ReportSection>
      <ReportSection icon={<Shield size={17} />} title="3. Tujuan Kampanye">
        <p><strong>Tujuan:</strong> {result.campaign_object?.goal ?? request.campaign_goal}</p>
        <p>{result.campaign_goal_analysis ?? 'Analisis tujuan belum tersedia.'}</p>
      </ReportSection>
      <ReportSection icon={<Activity size={17} />} title="4. Analisis Situasi">{result.situation_analysis ?? 'Analisis situasi belum tersedia.'}</ReportSection>
      <ReportSection icon={<TrendingUp size={17} />} title="5. Positioning">
        <p><strong>Statement:</strong> {result.positioning?.statement}</p>
        <p><strong>Identitas inti:</strong> {result.positioning?.core_identity}</p>
        <p><strong>Diferensiasi:</strong> {result.positioning?.differentiator}</p>
        <p><strong>Perception gap:</strong> {result.positioning?.perception_gap}</p>
      </ReportSection>
      <ReportSection icon={<User size={17} />} title="6. Target Audiens / Segmentasi Pemilih">
        {(result.target_segments ?? []).map((segment, index) => (
          <div className="controversy" key={`${segment.segment}-${index}`}>
            <p><strong>{segment.segment}</strong> - {segment.priority}</p>
            <p><strong>Kebutuhan:</strong> {segment.needs}</p>
            <p><strong>Isu utama:</strong> {segment.main_issue}</p>
            <p><strong>Pesan:</strong> {segment.message}</p>
            <p><strong>Channel:</strong> {segment.channel}</p>
          </div>
        ))}
      </ReportSection>
      <ReportSection icon={<AlertTriangle size={17} />} title="7. Isu Prioritas">
        {(result.priority_issues ?? []).map((issue, index) => (
          <div className="controversy" key={`${issue.issue}-${index}`}>
            <p><strong>{issue.issue}</strong> - Prioritas {issue.priority} - Risk {issue.risk}</p>
            <p>{issue.reason}</p>
            <p><strong>Narasi rekomendasi:</strong> {issue.recommended_narrative}</p>
          </div>
        ))}
      </ReportSection>
      <ReportSection icon={<TrendingUp size={17} />} title="8. Narasi Utama">{result.main_narrative}</ReportSection>
      <ReportSection icon={<FileText size={17} />} title="9. Pesan Kunci">
        {(result.key_messages ?? []).map((message, index) => (
          <div className="controversy" key={`${message.message}-${index}`}>
            <p><strong>{message.message}</strong></p>
            <p>Target: {message.target} | Channel: {message.channel}</p>
            <p>{message.reason}</p>
          </div>
        ))}
      </ReportSection>
      <ReportSection icon={<Database size={17} />} title="10. Strategi Wilayah">
        {(result.regional_strategy ?? []).map((regionItem, index) => (
          <div className="controversy" key={`${regionItem.region}-${index}`}>
            <p><strong>{regionItem.region}</strong> - {regionItem.status}</p>
            <p>{regionItem.strategy}</p>
            <CompactList items={regionItem.actions ?? []} empty="Action wilayah belum tersedia." />
            <p><strong>Risk:</strong> {regionItem.risk}</p>
          </div>
        ))}
      </ReportSection>
      <ReportSection icon={<Globe size={17} />} title="11. Strategi Media Sosial">
        <p><strong>Style:</strong> {result.social_media_strategy?.content_style}</p>
        <p><strong>Frekuensi:</strong> {result.social_media_strategy?.posting_frequency}</p>
        <Swot title="Platform" items={result.social_media_strategy?.platforms ?? []} />
        <Swot title="Format" items={result.social_media_strategy?.formats ?? []} />
        <Swot title="Hashtag" items={result.social_media_strategy?.hashtags ?? []} />
      </ReportSection>
      <ReportSection icon={<Globe size={17} />} title="12. Strategi Media Lokal & PR">
        <Swot title="Media Prioritas" items={result.local_media_pr_strategy?.priority_media ?? []} />
        <Swot title="Story Angles" items={result.local_media_pr_strategy?.story_angles ?? []} />
        <Swot title="Agenda Press Release" items={result.local_media_pr_strategy?.press_release_agenda ?? []} />
        <p><strong>Respon berita negatif:</strong> {result.local_media_pr_strategy?.negative_news_response}</p>
      </ReportSection>
      <ReportSection icon={<Layers size={17} />} title="13. Strategi Kampanye Darat">
        {(result.ground_campaign_strategy ?? []).map((activity, index) => (
          <div className="controversy" key={`${activity.activity}-${index}`}>
            <p><strong>{activity.activity}</strong></p>
            <p>Target: {activity.target} | Wilayah: {activity.region}</p>
            <p>Output: {activity.expected_output}</p>
          </div>
        ))}
      </ReportSection>
      <ReportSection icon={<AlertTriangle size={17} />} title="14. Mitigasi Isu Negatif">
        {(result.negative_issue_mitigation ?? []).map((issue, index) => (
          <div className="controversy" key={`${issue.issue}-${index}`}>
            <p><strong>{issue.issue}</strong> - Risk {issue.risk}</p>
            <p><strong>Respon utama:</strong> {issue.main_response}</p>
            <p><strong>Counter narrative:</strong> {issue.counter_narrative}</p>
            <CompactList items={issue.supporting_evidence ?? []} empty="Bukti pendukung belum tersedia." />
          </div>
        ))}
      </ReportSection>
      <ReportSection icon={<Zap size={17} />} title="15. Rekomendasi Konten">
        {(result.content_recommendations ?? []).map((content, index) => (
          <div className="controversy" key={`${content.hook}-${index}`}>
            <p><strong>{content.hook}</strong> - {content.format}</p>
            <p>Target: {content.target}</p>
            <p>{content.message}</p>
            <p><strong>CTA:</strong> {content.cta}</p>
          </div>
        ))}
      </ReportSection>
      <ReportSection icon={<Activity size={17} />} title="16. Rencana Aksi / Timeline 30 Hari">
        <Swot title="Week 1" items={result.action_plan_30_days?.week_1 ?? []} />
        <Swot title="Week 2" items={result.action_plan_30_days?.week_2 ?? []} />
        <Swot title="Week 3" items={result.action_plan_30_days?.week_3 ?? []} />
        <Swot title="Week 4" items={result.action_plan_30_days?.week_4 ?? []} />
      </ReportSection>
      <ReportSection icon={<BarChart3 size={17} />} title="17. Indikator Keberhasilan">
        <CompactList items={result.success_indicators ?? []} empty="KPI belum tersedia." />
      </ReportSection>
      <ReportSection icon={<AlertTriangle size={17} />} title="18. Risiko & Catatan Strategis">
        <CompactList items={result.risks_and_notes ?? []} empty="Catatan strategis belum tersedia." />
      </ReportSection>
      <ReportSection icon={<Database size={17} />} title="19. Sumber Data">
        {(result.sources ?? []).map((source, index) => (
          <p key={`${source.name}-${source.link}-${index}`}>{source.name ?? '-'} - {source.link ?? '-'} - {source.accessed_at ?? '-'} - {source.type ?? '-'}{source.data_used ? ` - ${source.data_used}` : ''}</p>
        ))}
      </ReportSection>
    </article>
  )
}

function CreativeStudioPage({
  activeProject,
  aspectRatio,
  assets,
  audience,
  jobs,
  loading,
  negativePrompt,
  objective,
  objectName,
  onAspectRatioChange,
  onAudienceChange,
  onGenerateAsset,
  onGeneratePackage,
  onNegativePromptChange,
  onObjectNameChange,
  onObjectiveChange,
  onPickProject,
  onPlatformChange,
  onPromptChange,
  onResolutionChange,
  onReviewAsset,
  onToneChange,
  onVideoDurationChange,
  platform,
  projects,
  prompt,
  resolution,
  tone,
  videoDuration,
}: {
  activeProject: CreativeProject | null
  aspectRatio: string
  assets: CreativeAsset[]
  audience: string
  jobs: CreativeJob[]
  loading: boolean
  negativePrompt: string
  objective: string
  objectName: string
  onAspectRatioChange: (value: string) => void
  onAudienceChange: (value: string) => void
  onGenerateAsset: (type: 'image' | 'video') => void
  onGeneratePackage: (event: FormEvent<HTMLFormElement>) => void
  onNegativePromptChange: (value: string) => void
  onObjectNameChange: (value: string) => void
  onObjectiveChange: (value: string) => void
  onPickProject: (project: CreativeProject) => void
  onPlatformChange: (value: string) => void
  onPromptChange: (value: string) => void
  onResolutionChange: (value: string) => void
  onReviewAsset: (asset: CreativeAsset, status: 'approve' | 'reject') => void
  onToneChange: (value: string) => void
  onVideoDurationChange: (value: string) => void
  platform: string
  projects: CreativeProject[]
  prompt: string
  resolution: string
  tone: string
  videoDuration: string
}) {
  const [studioMode, setStudioMode] = useState<'hub' | 'prompt' | 'image' | 'video'>('hub')
  const pkg = activeProject?.package
  const imageAssets = assets.filter((asset) => asset.asset_type === 'image')
  const videoAssets = assets.filter((asset) => asset.asset_type === 'video')
  const latestJobs = jobs.slice(0, 5)

  if (studioMode === 'image' || studioMode === 'video') {
    const isVideo = studioMode === 'video'
    const visibleAssets = isVideo ? videoAssets : imageAssets
    const visibleJobs = jobs.filter((job) => job.asset_type === studioMode)

    return (
      <section className="creative-generator-page">
        <div className="creative-mode-tabs">
          <button onClick={() => setStudioMode('hub')} type="button">Explore</button>
          <button className={!isVideo ? 'active' : ''} onClick={() => setStudioMode('image')} type="button">Image Generation</button>
          <button className={isVideo ? 'active' : ''} onClick={() => setStudioMode('video')} type="button">Video Generation</button>
          <button onClick={() => setStudioMode('prompt')} type="button">Prompt Generator</button>
        </div>

        <div className="creative-generator-shell">
          <aside className="creative-generator-sidebar">
            <div className="creative-model-card">
              <span>{isVideo ? 'V' : 'I'}</span>
              <div>
                <strong>{isVideo ? 'Video 1.6' : 'Image 3.0'}</strong>
                <small>{isVideo ? 'Campaign video, social short, storyboard-ready' : 'Campaign visual, poster, social graphic'}</small>
              </div>
              <ChevronRight size={17} />
            </div>

            <div className="creative-reference-box">
              <div className="mini-heading">
                <h3>{isVideo ? 'Storyboard / Reference' : 'Image Reference'}</h3>
                <span>Optional</span>
              </div>
              <button type="button"><FileText size={24} /><small>Add reference</small></button>
            </div>

            <label className="creative-prompt-area">
              <span>Prompt</span>
              <textarea value={prompt} onChange={(event) => onPromptChange(event.target.value)} />
            </label>

            <label className="creative-prompt-area">
              <span>Negative Prompt</span>
              <textarea value={negativePrompt} onChange={(event) => onNegativePromptChange(event.target.value)} />
            </label>

            <div className="creative-bottom-settings">
              <label><span>Aspect</span><select value={aspectRatio} onChange={(event) => onAspectRatioChange(event.target.value)}><option>1:1</option><option>16:9</option><option>9:16</option><option>4:5</option><option>3:4</option><option>21:9</option></select></label>
              {!isVideo && <label><span>Resolution</span><select value={resolution} onChange={(event) => onResolutionChange(event.target.value)}><option>512x512</option><option>768x768</option><option>1024x1024</option><option>1024x1792</option><option>1792x1024</option><option>1080x1350</option><option>1920x1080</option><option>1080x1920</option></select></label>}
              {isVideo && <label><span>Duration</span><select value={videoDuration} onChange={(event) => onVideoDurationChange(event.target.value)}><option>5s</option><option>10s</option><option>15s</option><option>30s</option><option>60s</option></select></label>}
              <button className="primary-cta" disabled={loading} onClick={() => onGenerateAsset(studioMode)} type="button">{loading ? 'Generating...' : 'Generate'}</button>
            </div>
          </aside>

          <main className="creative-generator-feed">
            <div className="creative-feed-toolbar">
              <div>
                <strong>{isVideo ? 'Video' : 'Image'}</strong>
                <small>{isVideo ? `${videoDuration} | ${aspectRatio} | 1080p` : `${aspectRatio} | ${resolution}`}</small>
              </div>
              <div>
                <button className="icon-button" type="button"><Database size={16} /></button>
                <button className="icon-button" onClick={() => setStudioMode('hub')} type="button"><ChevronRight size={16} /></button>
              </div>
            </div>

            {visibleAssets.length === 0 && (
              <div className="creative-feed-empty">
                <Zap size={28} />
                <h2>Belum ada hasil {isVideo ? 'video' : 'image'}</h2>
                <p>Isi prompt di panel kiri lalu klik Generate. Hasil akan muncul di feed ini dan masuk Asset Library.</p>
              </div>
            )}

            <div className="creative-feed-list">
              {visibleAssets.map((asset) => (
                <article className="creative-feed-card" key={asset.id}>
                  <div className={isVideo ? 'creative-feed-preview video' : 'creative-feed-preview'}>
                    <span>{isVideo ? 'VIDEO PREVIEW' : 'IMAGE PREVIEW'}</span>
                    <small>{asset.aspect_ratio ?? aspectRatio}</small>
                  </div>
                  <div className="creative-feed-meta">
                    <div>
                      <strong>{asset.title ?? `Asset #${asset.id}`}</strong>
                      <small>{asset.provider_used ?? 'Provider'} | {asset.model_used ?? 'Model'} | {asset.resolution ?? '-'}</small>
                      <p>{asset.prompt_used}</p>
                    </div>
                    <div className="creative-asset-actions">
                      <span className="status-badge">{asset.approval_status}</span>
                      <button onClick={() => onReviewAsset(asset, 'approve')} type="button">Approve</button>
                      <button className="danger" onClick={() => onReviewAsset(asset, 'reject')} type="button">Reject</button>
                    </div>
                  </div>
                </article>
              ))}
            </div>

            {visibleJobs.length > 0 && (
              <div className="creative-feed-jobs">
                {visibleJobs.slice(0, 4).map((job) => (
                  <div className="creative-job-row" key={job.id}>
                    <strong>#{job.id} {job.asset_type}</strong>
                    <span className={`status-badge ${job.status === 'failed' ? 'failed' : job.status === 'completed' ? 'completed' : 'processing'}`}>{job.status}</span>
                    <small>{job.aspect_ratio ?? '-'} - {job.resolution ?? '-'} - cost {job.cost_estimate ?? '-'}</small>
                  </div>
                ))}
              </div>
            )}
          </main>
        </div>
      </section>
    )
  }

  return (
    <section className="creative-page">
      <header className="creative-hero">
        <div>
          <span className="status-badge completed">Production Creative Generator</span>
          <h1>Creative Studio</h1>
          <p>Buat prompt, image, video, dan creative package kampanye dari satu workspace. Semua output masuk Asset Library dan tetap pending review sebelum dipakai publik.</p>
        </div>
        <div className="creative-hero-stats">
          <Metric label="Projects" value={`${projects.length}`} icon={FileText} />
          <Metric label="Jobs" value={`${jobs.length}`} icon={Activity} />
          <Metric label="Assets" value={`${assets.length}`} icon={Database} />
        </div>
      </header>

      <div className="creative-generator-grid">
        <button className="creative-generator-card featured" onClick={() => setStudioMode('prompt')} type="button">
          <span><FileText size={22} /></span>
          <strong>Prompt Generator</strong>
          <p>Creative brief, hook, caption, CTA, script, storyboard, image prompt, dan video prompt.</p>
          <em>Open generator <ChevronRight size={15} /></em>
        </button>
        <button className="creative-generator-card" onClick={() => setStudioMode('image')} type="button">
          <span><Zap size={22} /></span>
          <strong>Image Generator</strong>
          <p>Generate image asset dari prompt, aspect ratio, resolusi, style, dan negative prompt.</p>
          <em>Open image generator <ChevronRight size={15} /></em>
        </button>
        <button className="creative-generator-card" onClick={() => setStudioMode('video')} type="button">
          <span><Activity size={22} /></span>
          <strong>Video Generator</strong>
          <p>Generate video job dari prompt, durasi, camera style, aspect ratio, dan metadata storyboard.</p>
          <em>Open video generator <ChevronRight size={15} /></em>
        </button>
      </div>

      <div className="creative-workspace">
        <section className="creative-panel" id="creative-package-form">
          <div className="mini-heading">
            <h3>Prompt Generator Setup</h3>
            <span>{activeProject?.title ?? 'Manual mode'}</span>
          </div>
          <form onSubmit={onGeneratePackage} className="creative-form-grid">
            <Field label="Campaign Object" value={objectName} onChange={onObjectNameChange} />
            <Field label="Target Audience" value={audience} onChange={onAudienceChange} />
            <Field label="Platform" value={platform} onChange={onPlatformChange} />
            <Field label="Content Objective" value={objective} onChange={onObjectiveChange} />
            <Field label="Tone" value={tone} onChange={onToneChange} />
            <button className="primary-cta" disabled={loading} type="submit"><Zap size={16} /> {loading ? 'Generating...' : 'Generate Creative Package'}</button>
          </form>
          {projects.length > 0 && (
            <div className="creative-project-strip">
              {projects.slice(0, 6).map((project) => (
                <button className={activeProject?.id === project.id ? 'selected' : ''} key={project.id} onClick={() => onPickProject(project)} type="button">
                  <strong>{project.title}</strong>
                  <small>{project.status} - {project.platform ?? '-'}</small>
                </button>
              ))}
            </div>
          )}
        </section>

        <section className="creative-panel">
          <div className="mini-heading">
            <h3>Asset Generator Settings</h3>
            <span>{assets.filter((asset) => asset.approval_status === 'pending_review').length} pending review</span>
          </div>
          <div className="creative-prompt-box">
            <label>
              <span>Prompt</span>
              <textarea value={prompt} onChange={(event) => onPromptChange(event.target.value)} />
            </label>
            <label>
              <span>Negative Prompt</span>
              <textarea value={negativePrompt} onChange={(event) => onNegativePromptChange(event.target.value)} />
            </label>
          </div>
          <div className="creative-settings-row">
            <label><span>Aspect Ratio</span><select value={aspectRatio} onChange={(event) => onAspectRatioChange(event.target.value)}><option>1:1</option><option>16:9</option><option>9:16</option><option>4:5</option><option>3:4</option><option>21:9</option></select></label>
            <label><span>Image Resolution</span><select value={resolution} onChange={(event) => onResolutionChange(event.target.value)}><option>512x512</option><option>768x768</option><option>1024x1024</option><option>1024x1792</option><option>1792x1024</option><option>1080x1350</option><option>1920x1080</option><option>1080x1920</option></select></label>
            <label><span>Video Duration</span><select value={videoDuration} onChange={(event) => onVideoDurationChange(event.target.value)}><option>5s</option><option>10s</option><option>15s</option><option>30s</option><option>60s</option></select></label>
          </div>
          <div className="settings-actions">
            <button className="primary-cta" disabled={loading} onClick={() => onGenerateAsset('image')} type="button">Generate Image</button>
            <button className="primary-cta" disabled={loading} onClick={() => onGenerateAsset('video')} type="button">Generate Video</button>
            {pkg?.image_prompts_json?.[0]?.prompt && <button onClick={() => onPromptChange(pkg.image_prompts_json?.[0]?.prompt ?? prompt)} type="button">Use Package Image Prompt</button>}
            {pkg?.video_prompts_json?.[0]?.prompt && <button onClick={() => onPromptChange(pkg.video_prompts_json?.[0]?.prompt ?? prompt)} type="button">Use Package Video Prompt</button>}
          </div>
        </section>
      </div>

      {pkg && (
        <section className="creative-package-summary">
          <div>
            <span className="status-badge completed">Creative Package</span>
            <h2>{pkg.big_idea}</h2>
            <p>{pkg.creative_brief}</p>
          </div>
          <div className="creative-copy-grid">
            <Swot title="Hooks" items={pkg.hook_options_json ?? []} />
            <Swot title="Captions" items={pkg.caption_options_json ?? []} />
            <Swot title="CTA" items={pkg.cta_options_json ?? []} />
          </div>
        </section>
      )}

      <section className="creative-results">
        <div className="mini-heading">
          <h3>Generated Assets</h3>
          <span>{imageAssets.length} image - {videoAssets.length} video</span>
        </div>
        {assets.length === 0 && (
          <div className="creative-empty">
            <Zap size={26} />
            <p>Belum ada hasil image/video. Generate dari card di atas, nanti hasilnya muncul di sini.</p>
          </div>
        )}
        <div className="creative-asset-grid">
          {assets.map((asset) => (
            <article className={asset.asset_type === 'video' ? 'creative-asset-card video' : 'creative-asset-card'} key={asset.id}>
              <div className="creative-asset-preview">
                <span>{asset.asset_type === 'video' ? 'VIDEO' : 'IMAGE'}</span>
                <small>{asset.aspect_ratio ?? '-'}</small>
              </div>
              <div className="creative-asset-body">
                <strong>{asset.title ?? `Asset #${asset.id}`}</strong>
                <small>{asset.provider_used ?? 'Provider'} - {asset.model_used ?? 'Model'}</small>
                <p>{asset.file_path ?? 'File path belum tersedia.'}</p>
                <div className="creative-asset-actions">
                  <span className="status-badge">{asset.approval_status}</span>
                  <button onClick={() => onReviewAsset(asset, 'approve')} type="button">Approve</button>
                  <button className="danger" onClick={() => onReviewAsset(asset, 'reject')} type="button">Reject</button>
                </div>
              </div>
            </article>
          ))}
        </div>
      </section>

      {latestJobs.length > 0 && (
        <section className="creative-jobs">
          <div className="mini-heading">
            <h3>Recent Jobs</h3>
            <span>{latestJobs.length} terbaru</span>
          </div>
          {latestJobs.map((job) => (
            <div className="creative-job-row" key={job.id}>
              <strong>#{job.id} {job.asset_type}</strong>
              <span className={`status-badge ${job.status === 'failed' ? 'failed' : job.status === 'completed' ? 'completed' : 'processing'}`}>{job.status}</span>
              <small>{job.aspect_ratio ?? '-'} - {job.resolution ?? '-'} - cost {job.cost_estimate ?? '-'}</small>
            </div>
          ))}
        </section>
      )}
    </section>
  )
}
function ImpactBlock({ data, reason, title }: { data?: string[]; reason?: string; title: string }) {
  return (
    <div className="controversy">
      <p><strong>Dampak:</strong> {title}</p>
      <p><strong>Kenapa:</strong> {reason ?? 'Belum tersedia.'}</p>
      {data && data.length > 0 && <CompactList items={data} empty="" />}
    </div>
  )
}

function ReportsPage({
  reports,
  activeReport,
  deletingReportId,
  onDeleteReport,
  onPickReport,
  onSaveReport,
}: {
  reports: ScreeningReport[]
  activeReport: ScreeningReport | null
  deletingReportId: number | null
  onDeleteReport: (report: ScreeningReport) => void
  onPickReport: (report: ScreeningReport) => void
  onSaveReport: (report: ScreeningReport) => void
}) {
  return (
    <section className="page">
      <div className="page-heading">
        <div>
          <h1>Reports History</h1>
          <p>Semua laporan screening tersimpan di sini. Pilih salah satu untuk buka detailnya.</p>
        </div>
      </div>

      <div className="report-list">
        {reports.length === 0 && (
          <div className="empty-list"><FileText size={28} /><p>Belum ada laporan screening.</p></div>
        )}
        {reports.map((report) => (
          <div
            className={activeReport?.id === report.id ? 'report-row selected' : 'report-row'}
            key={report.id}
          >
            <span className="report-row-icon"><FileText size={17} /></span>
            <button className="report-row-main" onClick={() => onPickReport(report)}>
              <strong>{report.subject_name}</strong>
              <small>{new Date(report.created_at).toLocaleString('id-ID')}</small>
            </button>
            <em className={`status-badge ${report.status}`}>{statusLabel(report)}</em>
            <strong>{report.final_score ? `${report.final_score}/100` : '-'}</strong>
            <div className="row-actions">
              <button
                disabled={!report.result_json}
                onClick={() => onSaveReport(report)}
                title="Simpan JSON"
                type="button"
              >
                <Download size={15} />
              </button>
              <button
                className="danger"
                disabled={deletingReportId === report.id}
                onClick={() => onDeleteReport(report)}
                title="Hapus laporan"
                type="button"
              >
                <Trash2 size={15} />
              </button>
            </div>
          </div>
        ))}
      </div>
    </section>
  )
}

function AgentSettingsPage({
  activeView,
  agents,
  drafts,
  isSuperAdmin,
  loading,
  savingSkillAgentId,
  onSave,
  onToggleSkill,
  onUpdateDraft,
}: {
  activeView: View
  agents: Agent[]
  drafts: Record<number, AgentDraft>
  isSuperAdmin: boolean
  loading: boolean
  savingSkillAgentId: number | null
  onSave: (agent: Agent) => void
  onToggleSkill: (agent: Agent, skillId: number) => void
  onUpdateDraft: (agentId: number, key: keyof AgentDraft, value: string) => void
}) {
  if (!isSuperAdmin) {
    return (
      <section className="page">
        <div className="empty-list"><Lock size={28} /><p>403 Forbidden. Area ini khusus Super Admin.</p></div>
      </section>
    )
  }

  return (
    <section className="page">
      <div className="page-heading">
        <div>
          <h1>{viewTitle(activeView)}</h1>
          <p>Area teknis khusus Super Admin: provider, API key, model, skill, dan routing.</p>
        </div>
        <span className="role-pill super">Super Admin Only</span>
      </div>

      {agents.length === 0 && (
        <div className="empty-list"><Settings size={28} /><p>Agent settings belum termuat.</p></div>
      )}

      {agents.map((agent) => {
        const draft = drafts[agent.id] ?? emptyDraft()
        return (
          <article className="settings-card" key={agent.id}>
            <div className="settings-header">
              <div>
                <h2>{agent.name}</h2>
                <p>{agent.role_description}</p>
              </div>
              <span className="status-badge completed">{agent.status}</span>
            </div>

            <div className="settings-grid">
              <Field label="Provider Name" value={draft.providerName} onChange={(value) => onUpdateDraft(agent.id, 'providerName', value)} />
              <Field label="Base URL" value={draft.baseUrl} onChange={(value) => onUpdateDraft(agent.id, 'baseUrl', value)} />
              <Field label="Model Name" value={draft.modelName} onChange={(value) => onUpdateDraft(agent.id, 'modelName', value)} />
              <Field label="Display Name" value={draft.displayName} onChange={(value) => onUpdateDraft(agent.id, 'displayName', value)} />
              <Field label="API Key Baru" type="password" value={draft.apiKey} placeholder={agent.provider?.masked_api_key ?? 'Isi API key'} onChange={(value) => onUpdateDraft(agent.id, 'apiKey', value)} />
              <Field label="Temperature" type="number" value={draft.temperature} onChange={(value) => onUpdateDraft(agent.id, 'temperature', value)} />
              <Field label="Max Token" type="number" value={draft.maxTokens} onChange={(value) => onUpdateDraft(agent.id, 'maxTokens', value)} />
            </div>

            <div className="settings-actions">
              <button className="primary-cta" disabled={loading || !agent.provider || !agent.model} onClick={() => onSave(agent)}>
                {loading ? 'Menyimpan...' : 'Save Settings'}
              </button>
              <span>API key lama: {agent.provider?.masked_api_key ?? 'belum ada'}</span>
            </div>

            <div className="skill-panel">
              <div className="mini-heading">
                <h3>Pengaturan Skill Agent</h3>
                <span>{agent.skills.filter((skill) => skill.pivot.enabled).length}/{agent.skills.length} aktif</span>
              </div>
              <div className="skill-toggle-grid">
                {agent.skills.map((skill) => (
                  <button
                    className={skill.pivot.enabled ? 'skill-toggle enabled' : 'skill-toggle'}
                    disabled={savingSkillAgentId === agent.id}
                    key={skill.id}
                    onClick={() => onToggleSkill(agent, skill.id)}
                    type="button"
                  >
                    <span className="skill-toggle-copy">
                      <strong><Zap size={13} /> {skill.name}</strong>
                      <small>{skill.risk_level} risk{skill.description ? ` - ${skill.description}` : ''}</small>
                    </span>
                    <span className="switch" aria-hidden="true"><i /></span>
                  </button>
                ))}
              </div>
              {savingSkillAgentId === agent.id && <p className="hint">Menyimpan perubahan skill...</p>}
            </div>

            <div className="routing-grid">
              <RouteRow task="Light Task" model={agent.model?.display_name ?? draft.displayName} />
              <RouteRow task="Heavy Reasoning" model={agent.model?.model_name ?? draft.modelName} />
              <RouteRow task="Report Format" model={agent.model?.display_name ?? draft.displayName} />
            </div>
          </article>
        )
      })}
    </section>
  )
}

function Report({
  deletingReportId,
  onDeleteReport,
  onSaveReport,
  report,
}: {
  deletingReportId: number | null
  onDeleteReport: (report: ScreeningReport) => void
  onSaveReport: (report: ScreeningReport) => void
  report: ScreeningReport | null
}) {
  if (!report) {
    return (
      <article className="report empty-state">
        <FileText size={30} />
        <h2>Belum ada laporan aktif</h2>
        <p>Generate screening atau pilih history untuk melihat laporan satu halaman.</p>
      </article>
    )
  }

  if (report.status !== 'completed' || !report.result_json) {
    return (
      <article className="report empty-state">
        <div className="report-actions floating-actions">
          <button
            className="danger"
            disabled={deletingReportId === report.id}
            onClick={() => onDeleteReport(report)}
            type="button"
          >
            <Trash2 size={15} /> Hapus
          </button>
        </div>
        {report.status === 'failed' ? <AlertTriangle size={30} /> : <Search size={30} />}
        <span className={`status-badge ${report.status}`}>{statusLabel(report)}</span>
        <h2>{report.subject_name}</h2>
        <p>{statusDescription(report)}</p>
        {report.error_message && <p className="error">{report.error_message}</p>}
      </article>
    )
  }

  const result = report.result_json

  return (
    <article className="report">
      <header className="report-header">
        <div className="report-header-top">
          <div>
            <span className="status-badge completed">Screening Report</span>
            <small>Generated at: {new Date(report.created_at).toLocaleString('id-ID')}</small>
          </div>
          <div className="report-actions">
            <button onClick={() => onSaveReport(report)} type="button"><Download size={15} /> Simpan</button>
            <button
              className="danger"
              disabled={deletingReportId === report.id}
              onClick={() => onDeleteReport(report)}
              type="button"
            >
              <Trash2 size={15} /> Hapus
            </button>
          </div>
        </div>
        <h1>{result.subject_name}</h1>
        <p>{result.executive_summary}</p>
      </header>

      <div className="metric-grid">
        <Metric label="Skor Akhir" value={`${result.final_score.score}/100`} icon={TrendingUp} />
        <Metric label="Kategori" value={result.final_score.category} icon={Shield} />
        <Metric label="Kontroversi" value={`${result.controversies.length} isu`} icon={AlertTriangle} />
        <Metric label="Sumber" value={`${result.sources.length}+`} icon={Database} />
      </div>

      <ReportSection icon={<User size={17} />} title="1. Profil Tokoh">{result.profile}</ReportSection>
      <ReportSection icon={<Shield size={17} />} title="2. Karier Politik">{result.political_career}</ReportSection>
      <ReportSection icon={<Globe size={17} />} title="3. Jejak Digital">{result.digital_footprint}</ReportSection>
      <ReportSection icon={<AlertTriangle size={17} />} title="4. Kontroversi">
        {result.controversies.map((item) => (
          <div className="controversy" key={item.issue}>
            <p><strong>Isu:</strong> {item.issue}</p>
            <p><strong>Status:</strong> {item.status}</p>
            <p><strong>Sumber:</strong> {item.source}</p>
            <p><strong>Risiko Politik:</strong> {item.political_risk}</p>
          </div>
        ))}
      </ReportSection>
      <ReportSection icon={<TrendingUp size={17} />} title="5. Analisis Sentimen">{result.sentiment_analysis}</ReportSection>
      <ReportSection icon={<BarChart3 size={17} />} title="6. Data Elektabilitas">{result.electability_data}</ReportSection>
      <ReportSection icon={<Database size={17} />} title="7. Analisis Basis Daerah">{result.regional_base_analysis}</ReportSection>
      <ReportSection icon={<FileText size={17} />} title="8. SWOT Analysis">
        <Swot title="Strengths" items={result.swot.strengths} />
        <Swot title="Weaknesses" items={result.swot.weaknesses} />
        <Swot title="Opportunities" items={result.swot.opportunities} />
        <Swot title="Threats" items={result.swot.threats} />
      </ReportSection>
      <ReportSection icon={<BarChart3 size={17} />} title="9. Skor Akhir">
        <div className="score-box">
          <strong>{result.final_score.score}/100</strong>
          <span>{result.final_score.category}</span>
        </div>
        {result.final_score.indicators && result.final_score.indicators.length > 0 && (
          <div className="score-indicators">
            {result.final_score.indicators.map((indicator) => (
              <div key={indicator.name}>
                <strong>{indicator.name}</strong>
                <span>{indicator.score}/10</span>
                <p>{indicator.note}</p>
              </div>
            ))}
          </div>
        )}
        <p>{result.final_score.reason}</p>
      </ReportSection>
      <ReportSection icon={<TrendingUp size={17} />} title="10. Insight Menaikkan Elektabilitas">{result.electability_improvement_insights}</ReportSection>
      <ReportSection icon={<Settings size={17} />} title="11. Rekomendasi Strategis">
        <Swot title="Prioritas Tinggi" items={result.strategic_recommendations.high} />
        <Swot title="Prioritas Sedang" items={result.strategic_recommendations.medium} />
        <Swot title="Prioritas Rendah" items={result.strategic_recommendations.low} />
      </ReportSection>
      <ReportSection icon={<Globe size={17} />} title="12. Sumber Data">
        {result.sources.map((source) => (
          <p key={`${source.name}-${source.link}`}>{source.name} - {source.link} - {source.accessed_at} - {source.type}</p>
        ))}
      </ReportSection>
    </article>
  )
}

function Metric({ label, value, icon: Icon }: { label: string; value: string; icon: typeof TrendingUp }) {
  return (
    <div className="metric">
      <span><Icon size={16} /></span>
      <div>
        <small>{label}</small>
        <strong>{value}</strong>
      </div>
    </div>
  )
}

function Field({
  label,
  onChange,
  placeholder,
  type = 'text',
  value,
}: {
  label: string
  onChange: (value: string) => void
  placeholder?: string
  type?: string
  value: string
}) {
  return (
    <label className="field">
      <span>{label}</span>
      <input
        type={type}
        value={value}
        placeholder={placeholder}
        onChange={(event) => onChange(event.target.value)}
      />
    </label>
  )
}

function RouteRow({ task, model }: { task: string; model: string }) {
  return (
    <div className="route-row">
      <small>{task}</small>
      <strong>{model || '-'}</strong>
    </div>
  )
}

function draftFromAgent(agent: Agent): AgentDraft {
  return {
    providerName: agent.provider?.name ?? '',
    baseUrl: agent.provider?.base_url ?? '',
    apiKey: '',
    modelName: agent.model?.model_name ?? '',
    displayName: agent.model?.display_name ?? '',
    temperature: String(agent.temperature),
    maxTokens: String(agent.max_tokens),
  }
}

function emptyDraft(): AgentDraft {
  return {
    providerName: '',
    baseUrl: '',
    apiKey: '',
    modelName: '',
    displayName: '',
    temperature: '0.4',
    maxTokens: '4096',
  }
}

function viewTitle(view: View) {
  if (view === 'screening') return 'Screening Tokoh'
  if (view === 'media-monitoring') return 'Media Monitoring'
  if (view === 'policy-intelligence') return 'Policy Intelligence'
  if (view === 'campaign-strategy') return 'Campaign Strategy'
  if (view === 'creative-studio') return 'Creative Studio'
  if (view === 'reports') return 'Reports History'
  if (view === 'settings') return 'Agent Settings'

  return 'Dashboard'
}

function moduleIcon(slug: string) {
  if (slug.includes('screening')) return Search
  if (slug.includes('creative') || slug.includes('studio')) return Zap
  if (slug.includes('campaign') || slug.includes('kampanye')) return BarChart3
  if (slug.includes('policy')) return FileText
  if (slug.includes('peta') || slug.includes('elektoral')) return BarChart3
  if (slug.includes('media')) return Globe
  if (slug.includes('monitor')) return Activity

  return FileText
}

function statusLabel(report: ScreeningReport) {
  if (report.status === 'pending') return 'Antri'
  if (report.status === 'processing') return 'Screening'
  if (report.status === 'failed') return 'Gagal'

  return 'Selesai'
}

function statusDescription(report: ScreeningReport) {
  if (report.status === 'pending') {
    return 'Tokoh sudah masuk antrian screening. Worker akan memproses di latar belakang.'
  }

  if (report.status === 'processing') {
    return 'Tokoh sedang di-screening. Proses detail bisa cukup lama karena agent sedang riset dan menyusun 12 bagian laporan.'
  }

  if (report.status === 'failed') {
    return 'Screening gagal diproses. Cek konfigurasi model/API atau jalankan ulang.'
  }

  return 'Laporan selesai.'
}

function mediaStatusLabel(run: MediaMonitoringRun) {
  if (run.status === 'pending') return 'Antri'
  if (run.status === 'processing') return 'Monitoring'
  if (run.status === 'failed') return 'Gagal'

  return 'Selesai'
}

function mediaStatusDescription(run: MediaMonitoringRun) {
  if (run.status === 'pending') {
    return 'Keyword sudah masuk antrian. Worker media monitoring akan memproses di latar belakang.'
  }

  if (run.status === 'processing') {
    return 'Keyword sedang dimonitor. Agent sedang membaca sumber publik, mengekstrak data, dan menyusun insight.'
  }

  if (run.status === 'failed') {
    return 'Monitoring gagal diproses. Cek konfigurasi model/API, FastAPI, atau worker queue.'
  }

  return 'Monitoring selesai.'
}

function policyStatusLabel(request: PolicyRequest) {
  if (request.status === 'pending') return 'Antri'
  if (request.status === 'processing') return 'Analisis'
  if (request.status === 'failed') return 'Gagal'

  return 'Selesai'
}

function policyStatusDescription(request: PolicyRequest) {
  if (request.status === 'pending') {
    return 'Topik kebijakan sudah masuk antrian. Worker akan memproses di latar belakang.'
  }

  if (request.status === 'processing') {
    return 'Policy Intelligence Agent sedang membaca sumber, respon publik, risiko, skenario, dan menyusun laporan.'
  }

  if (request.status === 'failed') {
    return 'Analisis kebijakan gagal diproses. Cek konfigurasi model/API, FastAPI, atau worker queue.'
  }

  return 'Analisis selesai.'
}

function campaignStatusLabel(request: CampaignStrategyRequest) {
  if (request.status === 'pending') return 'Antri'
  if (request.status === 'processing') return 'Strategizing'
  if (request.status === 'failed') return 'Gagal'

  return 'Selesai'
}

function campaignStatusDescription(request: CampaignStrategyRequest) {
  if (request.status === 'pending') {
    return 'Objek kampanye sudah masuk antrian. Worker Campaign Strategy akan memproses di latar belakang.'
  }

  if (request.status === 'processing') {
    return 'Campaign Strategy Agent sedang menyusun positioning, segmentasi, narasi, strategi wilayah, media, kampanye darat, mitigasi, konten, timeline, dan KPI.'
  }

  if (request.status === 'failed') {
    return 'Campaign Strategy gagal diproses. Cek konfigurasi model/API, FastAPI, atau worker queue.'
  }

  return 'Strategi selesai.'
}

function SentimentBar({ label, value, total }: { label: string; value: number; total: number }) {
  const percent = total > 0 ? Math.round((value / total) * 100) : 0

  return (
    <div className="sentiment-bar">
      <div>
        <strong>{label}</strong>
        <span>{value} item - {percent}%</span>
      </div>
      <i><b style={{ width: `${percent}%` }} /></i>
    </div>
  )
}

function CompactList({ empty, items }: { empty: string; items: unknown[] }) {
  if (items.length === 0) return <p>{empty}</p>

  return <ul className="compact-list">{items.map((item, index) => <li key={`${index}-${toDisplayText(item)}`}>{toDisplayText(item)}</li>)}</ul>
}

function ReportSection({ icon, title, children }: { icon: ReactNode; title: string; children: ReactNode }) {
  return (
    <section className="report-section">
      <h2>{icon} {title}</h2>
      <div className="report-section-body">
        {typeof children === 'string' ? <p>{children}</p> : children}
      </div>
    </section>
  )
}

function Swot({ title, items }: { title: string; items: unknown[] }) {
  return (
    <div className="swot">
      <strong>{title}</strong>
      <ul>{items.map((item, index) => <li key={`${title}-${index}-${toDisplayText(item)}`}>{toDisplayText(item)}</li>)}</ul>
    </div>
  )
}

function toDisplayText(value: unknown): string {
  if (value === null || value === undefined) return '-'
  if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') return String(value)

  if (Array.isArray(value)) {
    return value.map((item) => toDisplayText(item)).join(', ')
  }

  if (typeof value === 'object') {
    const record = value as Record<string, unknown>
    const preferred = ['message', 'text', 'summary', 'narrative', 'point', 'name', 'segment', 'channel', 'reason']
      .filter((key) => record[key] !== undefined)
      .map((key) => `${humanizeKey(key)}: ${toDisplayText(record[key])}`)

    if (preferred.length > 0) return preferred.join(' - ')

    return Object.entries(record)
      .map(([key, item]) => `${humanizeKey(key)}: ${toDisplayText(item)}`)
      .join(' - ')
  }

  return String(value)
}

function humanizeKey(key: string): string {
  return key.replace(/_/g, ' ').replace(/\b\w/g, (letter) => letter.toUpperCase())
}

export default App
