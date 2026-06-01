<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Models\AuditLog;
use App\Services\FastAiClient;
use Illuminate\Http\Request;

class AiProviderController extends Controller
{
    public function index()
    {
        $providers = AiProvider::latest()->get()->each(function ($provider) {
            $provider->makeHidden('api_key_encrypted');
        });

        return response()->json(['data' => $providers]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'base_url' => ['required', 'url'],
            'api_key' => ['nullable', 'string', 'max:4000'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $provider = AiProvider::create([
            'name' => $data['name'],
            'base_url' => $data['base_url'],
            'api_key_encrypted' => $data['api_key'] ?? null,
            'status' => $data['status'],
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $this->audit($request, 'provider.created', 'ai_provider', $provider->id, null, $provider->toArray());

        return response()->json(['data' => $provider->makeHidden('api_key_encrypted')], 201);
    }

    public function update(Request $request, AiProvider $aiProvider)
    {
        $old = $aiProvider->toArray();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'base_url' => ['required', 'url'],
            'api_key' => ['nullable', 'string', 'max:4000'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $update = [
            'name' => $data['name'],
            'base_url' => $data['base_url'],
            'status' => $data['status'],
            'updated_by' => $request->user()->id,
        ];

        if (! empty($data['api_key'])) {
            $update['api_key_encrypted'] = $data['api_key'];
        }

        $aiProvider->update($update);
        $this->audit($request, 'provider.updated', 'ai_provider', $aiProvider->id, $old, $aiProvider->fresh()->toArray());

        return response()->json(['data' => $aiProvider->fresh()->makeHidden('api_key_encrypted')]);
    }

    public function testConnection(AiProvider $aiProvider, FastAiClient $client)
    {
        return response()->json([
            'data' => $client->testConnection([
                'name' => $aiProvider->name,
                'base_url' => $aiProvider->base_url,
                'api_key' => $aiProvider->api_key_encrypted,
            ]),
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
