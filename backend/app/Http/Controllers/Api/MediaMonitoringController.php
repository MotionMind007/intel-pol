<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RunMediaMonitoring;
use App\Models\Agent;
use App\Models\MediaItem;
use App\Models\MediaMonitoringRun;
use App\Models\MediaSource;
use App\Models\MonitoringKeyword;
use Illuminate\Http\Request;

class MediaMonitoringController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'runs' => $this->runQuery($request)->limit(20)->get(),
            'sources' => MediaSource::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function run(Request $request)
    {
        $validated = $request->validate([
            'keyword' => ['required', 'string', 'max:180'],
        ], [
            'keyword.required' => 'Keyword wajib diisi.',
        ]);

        $agent = Agent::with(['provider', 'model', 'skills'])
            ->where('name', 'Media Monitoring Agent')
            ->where('status', 'active')
            ->first()
            ?? Agent::with(['provider', 'model', 'skills'])->where('status', 'active')->firstOrFail();

        $keyword = MonitoringKeyword::create([
            'user_id' => $request->user()->id,
            'keyword' => trim($validated['keyword']),
            'status' => 'active',
        ]);

        $run = MediaMonitoringRun::create([
            'user_id' => $request->user()->id,
            'agent_id' => $agent->id,
            'keyword_id' => $keyword->id,
            'status' => 'pending',
            'queued_at' => now(),
        ]);

        RunMediaMonitoring::dispatch($run->id);

        return response()->json(['data' => $run->load(['keyword', 'insight', 'items.analysis'])], 202);
    }

    public function runs(Request $request)
    {
        return response()->json(['data' => $this->runQuery($request)->limit(25)->get()]);
    }

    public function show(Request $request, MediaMonitoringRun $run)
    {
        if (! $this->canAccessRun($request, $run)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json(['data' => $run->load(['keyword', 'insight', 'items.analysis', 'items.source'])]);
    }

    public function item(Request $request, MediaItem $item)
    {
        $item->load(['analysis', 'source']);
        $run = $item->run_id ? MediaMonitoringRun::query()->find($item->run_id) : null;

        if ($run && ! $this->canAccessRun($request, $run)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json(['data' => $item]);
    }

    public function destroyRun(Request $request, MediaMonitoringRun $run)
    {
        if (! $this->canAccessRun($request, $run)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $run->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    public function sources()
    {
        return response()->json(['data' => MediaSource::query()->orderBy('name')->get()]);
    }

    public function storeSource(Request $request)
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'domain' => ['nullable', 'string', 'max:180'],
            'source_type' => ['required', 'string', 'max:80'],
            'platform' => ['nullable', 'string', 'max:80'],
            'credibility_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        return response()->json(['data' => MediaSource::create($validated)], 201);
    }

    public function updateSource(Request $request, MediaSource $source)
    {
        $this->authorizeSuperAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'domain' => ['nullable', 'string', 'max:180'],
            'source_type' => ['required', 'string', 'max:80'],
            'platform' => ['nullable', 'string', 'max:80'],
            'credibility_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $source->update($validated);

        return response()->json(['data' => $source]);
    }

    public function destroyKeyword(Request $request, MonitoringKeyword $keyword)
    {
        if ($request->user()->role !== 'super_admin' && $keyword->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $keyword->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    private function runQuery(Request $request)
    {
        $query = MediaMonitoringRun::query()
            ->with(['keyword', 'insight', 'items.analysis', 'items.source'])
            ->latest();

        if (! in_array($request->user()->role, ['super_admin', 'admin'], true)) {
            $query->where('user_id', $request->user()->id);
        }

        return $query;
    }

    private function canAccessRun(Request $request, MediaMonitoringRun $run): bool
    {
        return in_array($request->user()->role, ['super_admin', 'admin'], true) || $run->user_id === $request->user()->id;
    }

    private function authorizeSuperAdmin(Request $request): void
    {
        abort_unless($request->user()->role === 'super_admin', 403, 'Forbidden.');
    }
}
