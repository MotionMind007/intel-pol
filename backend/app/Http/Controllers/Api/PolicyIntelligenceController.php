<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzePolicyIntelligence;
use App\Models\Agent;
use App\Models\PolicyResearchRequest;
use Illuminate\Http\Request;

class PolicyIntelligenceController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['data' => $this->requestQuery($request)->limit(20)->get()]);
    }

    public function analyze(Request $request)
    {
        $validated = $request->validate([
            'policy_topic' => ['required', 'string', 'max:220'],
        ], [
            'policy_topic.required' => 'Topik kebijakan wajib diisi.',
        ]);

        $agent = Agent::with(['provider', 'model', 'skills'])
            ->where('name', 'Policy Intelligence Agent')
            ->where('status', 'active')
            ->first()
            ?? Agent::with(['provider', 'model', 'skills'])->where('status', 'active')->firstOrFail();

        $policyRequest = PolicyResearchRequest::create([
            'user_id' => $request->user()->id,
            'agent_id' => $agent->id,
            'policy_topic' => trim($validated['policy_topic']),
            'status' => 'pending',
            'queued_at' => now(),
        ]);

        AnalyzePolicyIntelligence::dispatch($policyRequest->id);

        return response()->json(['data' => $policyRequest->load(['report', 'sources', 'publicResponses', 'stakeholders', 'impactAnalysis'])], 202);
    }

    public function reports(Request $request)
    {
        return response()->json(['data' => $this->requestQuery($request)->limit(25)->get()]);
    }

    public function show(Request $request, PolicyResearchRequest $policyResearchRequest)
    {
        if (! $this->canAccess($request, $policyResearchRequest)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json(['data' => $policyResearchRequest->load(['report', 'sources', 'publicResponses', 'stakeholders', 'impactAnalysis'])]);
    }

    public function destroy(Request $request, PolicyResearchRequest $policyResearchRequest)
    {
        if (! $this->canAccess($request, $policyResearchRequest)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $policyResearchRequest->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    private function requestQuery(Request $request)
    {
        $query = PolicyResearchRequest::query()
            ->with(['report', 'sources', 'publicResponses', 'stakeholders', 'impactAnalysis'])
            ->latest();

        if (! in_array($request->user()->role, ['super_admin', 'admin'], true)) {
            $query->where('user_id', $request->user()->id);
        }

        return $query;
    }

    private function canAccess(Request $request, PolicyResearchRequest $policyResearchRequest): bool
    {
        return in_array($request->user()->role, ['super_admin', 'admin'], true)
            || $policyResearchRequest->user_id === $request->user()->id;
    }
}
