<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AgentSkillController extends Controller
{
    public function update(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'skills' => ['required', 'array'],
            'skills.*.skill_id' => ['required', 'integer'],
            'skills.*.enabled' => ['required', 'boolean'],
            'skills.*.requires_approval' => ['nullable', 'boolean'],
            'skills.*.daily_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $sync = collect($data['skills'])->mapWithKeys(fn ($item) => [
            $item['skill_id'] => [
                'enabled' => $item['enabled'],
                'requires_approval' => $item['requires_approval'] ?? false,
                'daily_limit' => $item['daily_limit'] ?? null,
            ],
        ])->all();

        $agent->skills()->sync($sync);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'agent.skills.updated',
            'entity_type' => 'agent',
            'entity_id' => $agent->id,
            'new_values_json' => $sync,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return response()->json(['data' => $agent->fresh(['skills'])]);
    }
}
