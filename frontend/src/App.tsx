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

type View = 'modules' | 'screening' | 'reports' | 'settings'

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
  const [agents, setAgents] = useState<Agent[]>([])
  const [settingsDraft, setSettingsDraft] = useState<Record<number, AgentDraft>>({})
  const [savingSkillAgentId, setSavingSkillAgentId] = useState<number | null>(null)
  const [deletingReportId, setDeletingReportId] = useState<number | null>(null)
  const [view, setView] = useState<View>('modules')
  const [email, setEmail] = useState('superadmin@example.com')
  const [password, setPassword] = useState('password')
  const [subjectName, setSubjectName] = useState('Jhony Banua Rouw')
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
    const [moduleResponse, reportResponse] = await Promise.all([
      fetch(`${API_BASE}/modules`, { headers }).then((response) => response.json()),
      fetch(`${API_BASE}/screening-reports`, { headers }).then((response) => response.json()),
    ])

    setModules(moduleResponse.modules ?? [])
    setAdminMenus(moduleResponse.admin_menus ?? [])
    const nextReports = reportResponse.data ?? []
    setReports(nextReports)
    setActiveReport((current) => {
      if (!current) return nextReports[0] ?? null

      return nextReports.find((report: ScreeningReport) => report.id === current.id) ?? current
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
      if (view === 'screening' || view === 'reports' || hasRunningReports) {
        void loadDashboard()
      }
    }, hasRunningReports ? 5000 : 15000)

    return () => window.clearInterval(interval)
  }, [token, user, view, hasRunningReports, loadDashboard])

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
    setView('modules')
  }

  if (!user || !token) {
    return (
      <main className={dark ? 'app dark' : 'app'}>
        <section className="login-shell">
          <div className="login-copy">
            <div className="logo-lockup">
              <span className="logo-mark"><Shield size={20} /></span>
              <div>
                <strong>Political Intel</strong>
                <small>AI Intelligence Platform</small>
              </div>
            </div>
            <h1>Screening tokoh politik berbasis agent AI.</h1>
            <p>Masuk untuk membuat laporan intelligence 12 bagian: profil, karier, jejak digital, kontroversi, sentimen, basis daerah, SWOT, skor, dan rekomendasi strategis.</p>
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
      <div className="app-shell">
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
              isSuperAdmin={isSuperAdmin}
              onOpenScreening={() => setView('screening')}
              onOpenSettings={() => void openSettings('settings')}
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
  const nav = [
    { key: 'modules' as const, label: 'Modules', icon: Layers },
    { key: 'screening' as const, label: 'Screening Tokoh', icon: Search },
    { key: 'reports' as const, label: 'Reports', icon: FileText },
  ]
  const adminNav = [
    { key: 'settings' as const, label: 'Agent Settings', icon: Settings },
  ]

  return (
    <aside className="sidebar">
      <div className="logo-lockup sidebar-logo">
        <span className="logo-mark"><Shield size={20} /></span>
        <div>
          <strong>Political Intel</strong>
          <small>AI Intelligence Platform</small>
        </div>
      </div>

      <nav className="sidebar-nav">
        {nav.map((item) => {
          const Icon = item.icon
          return (
            <button
              className={currentView === item.key ? 'active' : ''}
              key={item.key}
              onClick={() => onNavigate(item.key)}
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
      <div>
        <strong>{viewTitle(view)}</strong>
        <small>MVP Political Intelligence Platform</small>
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

function ModulesPage({
  modules,
  adminMenus,
  isSuperAdmin,
  onOpenScreening,
  onOpenSettings,
}: {
  modules: ModuleItem[]
  adminMenus: string[]
  isSuperAdmin: boolean
  onOpenScreening: () => void
  onOpenSettings: () => void
}) {
  return (
    <section className="page">
      <div className="page-heading">
        <div>
          <h1>Pilih Modul Intelligence</h1>
          <p>Mulai dari modul Screening Tokoh. Modul lain dapat ditambahkan bertahap setelah MVP stabil.</p>
        </div>
        {isSuperAdmin && (
          <button className="secondary-cta" onClick={onOpenSettings}><Settings size={16} /> Agent Settings</button>
        )}
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
              onClick={onOpenScreening}
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
  if (view === 'reports') return 'Reports History'
  if (view === 'settings') return 'Agent Settings'

  return 'Dashboard'
}

function moduleIcon(slug: string) {
  if (slug.includes('screening')) return Search
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

function ReportSection({ icon, title, children }: { icon: ReactNode; title: string; children: ReactNode }) {
  return (
    <section className="report-section">
      <h2>{icon} {title}</h2>
      {typeof children === 'string' ? <p>{children}</p> : children}
    </section>
  )
}

function Swot({ title, items }: { title: string; items: string[] }) {
  return (
    <div className="swot">
      <strong>{title}</strong>
      <ul>{items.map((item) => <li key={item}>{item}</li>)}</ul>
    </div>
  )
}

export default App
