<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AiModelController extends Controller
{
    public function index()
    {
        return response()->json(['data' => AiModel::latest()->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $model = AiModel::create($data);
        $this->audit($request, 'model.created', 'ai_model', $model->id, null, $model->toArray());

        return response()->json(['data' => $model], 201);
    }

    public function update(Request $request, AiModel $aiModel)
    {
        $old = $aiModel->toArray();
        $aiModel->update($this->validated($request));
        $this->audit($request, 'model.updated', 'ai_model', $aiModel->id, $old, $aiModel->fresh()->toArray());

        return response()->json(['data' => $aiModel->fresh()]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'provider_id' => ['required', 'integer'],
            'model_name' => ['required', 'string', 'max:180'],
            'display_name' => ['required', 'string', 'max:180'],
            'context_window' => ['nullable', 'integer', 'min:1'],
            'input_price_per_million_tokens' => ['nullable', 'numeric', 'min:0'],
            'output_price_per_million_tokens' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
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
