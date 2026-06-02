<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateCampaignStrategy;
use App\Models\Agent;
use App\Models\CampaignStrategyRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CampaignStrategyController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['data' => $this->requestQuery($request)->limit(20)->get()]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'campaign_object_type' => ['required', 'string', Rule::in(['candidate', 'party', 'policy', 'issue', 'organization', 'other'])],
            'campaign_object_name' => ['required', 'string', 'max:220'],
            'campaign_goal' => ['required', 'string', 'max:260'],
            'region' => ['nullable', 'string', 'max:160'],
        ], [
            'campaign_object_type.required' => 'Tipe objek kampanye wajib dipilih.',
            'campaign_object_name.required' => 'Objek kampanye wajib diisi.',
            'campaign_goal.required' => 'Tujuan kampanye wajib diisi.',
        ]);

        $agent = Agent::with(['provider', 'model', 'skills'])
            ->where('name', 'Campaign Strategy Agent')
            ->where('status', 'active')
            ->first()
            ?? Agent::with(['provider', 'model', 'skills'])->where('status', 'active')->firstOrFail();

        $campaignRequest = CampaignStrategyRequest::create([
            'user_id' => $request->user()->id,
            'agent_id' => $agent->id,
            'campaign_object_type' => $validated['campaign_object_type'],
            'campaign_object_name' => trim($validated['campaign_object_name']),
            'campaign_goal' => trim($validated['campaign_goal']),
            'region' => isset($validated['region']) ? trim((string) $validated['region']) : null,
            'status' => 'pending',
            'queued_at' => now(),
        ]);

        GenerateCampaignStrategy::dispatch($campaignRequest->id);

        return response()->json(['data' => $campaignRequest->load($this->relations())], 202);
    }

    public function reports(Request $request)
    {
        return response()->json(['data' => $this->requestQuery($request)->limit(25)->get()]);
    }

    public function show(Request $request, CampaignStrategyRequest $campaignStrategyRequest)
    {
        if (! $this->canAccess($request, $campaignStrategyRequest)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json(['data' => $campaignStrategyRequest->load($this->relations())]);
    }

    public function destroy(Request $request, CampaignStrategyRequest $campaignStrategyRequest)
    {
        if (! $this->canAccess($request, $campaignStrategyRequest)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $campaignStrategyRequest->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    private function requestQuery(Request $request)
    {
        $query = CampaignStrategyRequest::query()
            ->with($this->relations())
            ->latest();

        if (! in_array($request->user()->role, ['super_admin', 'admin'], true)) {
            $query->where('user_id', $request->user()->id);
        }

        return $query;
    }

    private function canAccess(Request $request, CampaignStrategyRequest $campaignStrategyRequest): bool
    {
        return in_array($request->user()->role, ['super_admin', 'admin'], true)
            || $campaignStrategyRequest->user_id === $request->user()->id;
    }

    private function relations(): array
    {
        return ['report', 'sources', 'segments', 'issues', 'regions', 'recommendations'];
    }
}
