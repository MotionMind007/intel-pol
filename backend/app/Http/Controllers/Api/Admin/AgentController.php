<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Agent::with(['provider', 'model', 'skills'])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $agent = Agent::create($data);
        $this->audit($request, 'agent.created', 'agent', $agent->id, null, $agent->toArray());

        return response()->json(['data' => $agent], 201);
    }

    public function show(Agent $agent)
    {
        return response()->json(['data' => $agent->load(['provider', 'model', 'skills'])]);
    }

    public function update(Request $request, Agent $agent)
    {
        $old = $agent->toArray();
        $agent->update($this->validated($request));
        $this->audit($request, 'agent.updated', 'agent', $agent->id, $old, $agent->fresh()->toArray());

        return response()->json(['data' => $agent->fresh(['provider', 'model', 'skills'])]);
    }

    public function destroy(Request $request, Agent $agent)
    {
        $old = $agent->toArray();
        $agent->delete();
        $this->audit($request, 'agent.deleted', 'agent', $agent->id, $old, null);

        return response()->json(['message' => 'Deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role_description' => ['required', 'string', 'max:180'],
            'system_prompt' => ['nullable', 'string'],
            'provider_id' => ['nullable', 'integer'],
            'model_id' => ['nullable', 'integer'],
            'temperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['required', 'integer', 'min:256', 'max:200000'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    private function audit(Request $request, string $action, string $entityType, ?int $entityId, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values_json' => $old,
            'new_values_json' => $new,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
