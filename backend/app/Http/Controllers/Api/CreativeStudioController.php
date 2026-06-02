<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateCreativeAsset;
use App\Models\Agent;
use App\Models\CreativeAsset;
use App\Models\CreativeAssetReview;
use App\Models\CreativeGenerationJob;
use App\Models\CreativePackage;
use App\Models\CreativeProject;
use App\Services\FastAiClient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CreativeStudioController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'data' => [
                'projects' => $this->projectQuery($request)->limit(20)->get(),
                'assets' => $this->assetQuery($request)->limit(30)->get(),
                'jobs' => $this->jobQuery($request)->limit(20)->get(),
                'options' => $this->options(),
            ],
        ]);
    }

    public function projects(Request $request)
    {
        return response()->json(['data' => $this->projectQuery($request)->limit(30)->get()]);
    }

    public function storeProject(Request $request)
    {
        $data = $request->validate([
            'campaign_strategy_report_id' => ['nullable', 'integer'],
            'title' => ['nullable', 'string', 'max:180'],
            'campaign_object_type' => ['nullable', 'string', 'max:60'],
            'campaign_object_name' => ['required', 'string', 'max:220'],
            'objective' => ['nullable', 'string', 'max:260'],
            'platform' => ['nullable', 'string', 'max:160'],
            'tone' => ['nullable', 'string', 'max:180'],
        ]);

        $project = CreativeProject::create([
            'user_id' => $request->user()->id,
            'campaign_strategy_report_id' => $data['campaign_strategy_report_id'] ?? null,
            'title' => $data['title'] ?? $data['campaign_object_name'].' Creative Project',
            'campaign_object_type' => $data['campaign_object_type'] ?? 'other',
            'campaign_object_name' => trim($data['campaign_object_name']),
            'objective' => $data['objective'] ?? null,
            'platform' => $data['platform'] ?? null,
            'tone' => $data['tone'] ?? null,
            'status' => 'draft',
        ]);

        return response()->json(['data' => $project->load(['package', 'assets', 'jobs'])], 201);
    }

    public function showProject(Request $request, CreativeProject $creativeProject)
    {
        if (! $this->canAccessProject($request, $creativeProject)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json(['data' => $creativeProject->load(['package', 'assets', 'jobs'])]);
    }

    public function updateProject(Request $request, CreativeProject $creativeProject)
    {
        if (! $this->canAccessProject($request, $creativeProject)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'campaign_object_type' => ['nullable', 'string', 'max:60'],
            'campaign_object_name' => ['required', 'string', 'max:220'],
            'objective' => ['nullable', 'string', 'max:260'],
            'platform' => ['nullable', 'string', 'max:160'],
            'tone' => ['nullable', 'string', 'max:180'],
            'status' => ['nullable', 'string', 'max:40'],
        ]);

        $creativeProject->update($data);

        return response()->json(['data' => $creativeProject->fresh()->load(['package', 'assets', 'jobs'])]);
    }

    public function destroyProject(Request $request, CreativeProject $creativeProject)
    {
        if (! $this->canAccessProject($request, $creativeProject)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $creativeProject->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    public function generatePackage(Request $request, FastAiClient $client)
    {
        $data = $request->validate([
            'project_id' => ['nullable', 'integer'],
            'campaign_object_type' => ['nullable', 'string', 'max:60'],
            'campaign_object_name' => ['required', 'string', 'max:220'],
            'campaign_goal' => ['nullable', 'string', 'max:260'],
            'target_audience' => ['nullable', 'string', 'max:220'],
            'platform' => ['nullable', 'string', 'max:160'],
            'content_objective' => ['nullable', 'string', 'max:220'],
            'tone' => ['nullable', 'string', 'max:180'],
            'source_strategy_report' => ['nullable', 'array'],
        ]);

        $project = isset($data['project_id'])
            ? CreativeProject::query()->find($data['project_id'])
            : null;

        if ($project && ! $this->canAccessProject($request, $project)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $project ??= CreativeProject::create([
            'user_id' => $request->user()->id,
            'title' => $data['campaign_object_name'].' Creative Project',
            'campaign_object_type' => $data['campaign_object_type'] ?? 'other',
            'campaign_object_name' => trim($data['campaign_object_name']),
            'objective' => $data['content_objective'] ?? $data['campaign_goal'] ?? null,
            'platform' => $data['platform'] ?? null,
            'tone' => $data['tone'] ?? null,
            'status' => 'draft',
        ]);

        $agent = $this->creativeAgent();
        $result = $client->generateCreativePackage($data, $agent);

        CreativePackage::updateOrCreate(
            ['project_id' => $project->id],
            [
                'creative_brief' => data_get($result, 'creative_brief'),
                'big_idea' => data_get($result, 'big_idea'),
                'content_angles_json' => data_get($result, 'content_angles', []),
                'hook_options_json' => data_get($result, 'hook_options', []),
                'caption_options_json' => data_get($result, 'caption_options', []),
                'cta_options_json' => data_get($result, 'cta_options', []),
                'visual_style' => data_get($result, 'visual_style'),
                'script_json' => data_get($result, 'script', []),
                'storyboard_json' => data_get($result, 'storyboard', []),
                'image_prompts_json' => data_get($result, 'image_prompts', []),
                'video_prompts_json' => data_get($result, 'video_prompts', []),
                'asset_specs_json' => data_get($result, 'asset_specs', []),
                'safety_notes_json' => data_get($result, 'safety_notes', []),
                'raw_json' => $result,
            ],
        );

        $project->update(['status' => 'generated']);

        return response()->json(['data' => $project->fresh()->load(['package', 'assets', 'jobs'])], 202);
    }

    public function generateImage(Request $request)
    {
        return $this->queueAsset($request, 'image');
    }

    public function generateVideo(Request $request)
    {
        return $this->queueAsset($request, 'video');
    }

    public function showJob(Request $request, CreativeGenerationJob $job)
    {
        if (! $this->canAccessJob($request, $job)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json(['data' => $job->load('assets')]);
    }

    public function assets(Request $request)
    {
        return response()->json(['data' => $this->assetQuery($request)->limit(80)->get()]);
    }

    public function showAsset(Request $request, CreativeAsset $asset)
    {
        if (! $this->canAccessAsset($request, $asset)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json(['data' => $asset]);
    }

    public function approveAsset(Request $request, CreativeAsset $asset)
    {
        return $this->reviewAsset($request, $asset, 'approved');
    }

    public function rejectAsset(Request $request, CreativeAsset $asset)
    {
        return $this->reviewAsset($request, $asset, 'rejected');
    }

    public function destroyAsset(Request $request, CreativeAsset $asset)
    {
        if (! $this->canAccessAsset($request, $asset)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $asset->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    private function queueAsset(Request $request, string $assetType)
    {
        $data = $request->validate([
            'project_id' => ['nullable', 'integer'],
            'prompt' => ['required', 'string', 'max:12000'],
            'negative_prompt' => ['nullable', 'string', 'max:4000'],
            'aspect_ratio' => ['nullable', 'string', 'max:20'],
            'resolution' => ['nullable', 'string', 'max:30'],
            'duration' => ['nullable', 'string', 'max:30'],
            'fps' => ['nullable', 'string', 'max:20'],
            'quality' => ['nullable', 'string', 'max:40'],
            'style' => ['nullable', 'string', 'max:80'],
            'camera_style' => ['nullable', 'string', 'max:80'],
            'output_count' => ['nullable', 'integer', 'min:1', 'max:4'],
        ]);

        $project = isset($data['project_id']) ? CreativeProject::query()->find($data['project_id']) : null;
        if ($project && ! $this->canAccessProject($request, $project)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $provider = \App\Models\AiProvider::query()
            ->where('provider_type', $assetType)
            ->where('status', 'active')
            ->first();
        $model = $provider
            ? \App\Models\AiModel::query()->where('provider_id', $provider->id)->where('modality', $assetType)->where('is_active', true)->first()
            : null;

        $job = CreativeGenerationJob::create([
            'user_id' => $request->user()->id,
            'project_id' => $project?->id,
            'asset_type' => $assetType,
            'provider_id' => $provider?->id,
            'model_id' => $model?->id,
            'prompt' => $data['prompt'],
            'negative_prompt' => $data['negative_prompt'] ?? null,
            'aspect_ratio' => $data['aspect_ratio'] ?? ($assetType === 'image' ? '1:1' : '9:16'),
            'resolution' => $data['resolution'] ?? ($assetType === 'image' ? '1024x1024' : '1080p'),
            'duration' => $data['duration'] ?? ($assetType === 'video' ? '10s' : null),
            'fps' => $data['fps'] ?? ($assetType === 'video' ? '30' : null),
            'quality' => $data['quality'] ?? 'standard',
            'style' => $data['style'] ?? null,
            'camera_style' => $data['camera_style'] ?? null,
            'output_count' => $data['output_count'] ?? 1,
            'status' => 'queued',
            'cost_estimate' => $this->estimateCost($assetType, (int) ($data['output_count'] ?? 1), $data['duration'] ?? null),
        ]);

        GenerateCreativeAsset::dispatch($job->id);

        return response()->json(['data' => $job->load('assets')], 202);
    }

    private function reviewAsset(Request $request, CreativeAsset $asset, string $status)
    {
        if (! in_array($request->user()->role, ['super_admin', 'admin'], true)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);
        $asset->update(['approval_status' => $status]);
        CreativeAssetReview::create([
            'asset_id' => $asset->id,
            'reviewer_id' => $request->user()->id,
            'status' => $status,
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json(['data' => $asset->fresh()]);
    }

    private function creativeAgent(): Agent
    {
        return Agent::with(['provider', 'model', 'skills'])
            ->where('name', 'Creative Studio Agent')
            ->where('status', 'active')
            ->first()
            ?? Agent::with(['provider', 'model', 'skills'])->where('status', 'active')->firstOrFail();
    }

    private function projectQuery(Request $request)
    {
        $query = CreativeProject::query()->with(['package', 'assets', 'jobs'])->latest();
        if (! in_array($request->user()->role, ['super_admin', 'admin'], true)) {
            $query->where('user_id', $request->user()->id);
        }

        return $query;
    }

    private function assetQuery(Request $request)
    {
        $query = CreativeAsset::query()->latest();
        if (! in_array($request->user()->role, ['super_admin', 'admin'], true)) {
            $query->whereHas('project', fn ($project) => $project->where('user_id', $request->user()->id));
        }

        return $query;
    }

    private function jobQuery(Request $request)
    {
        $query = CreativeGenerationJob::query()->with('assets')->latest();
        if (! in_array($request->user()->role, ['super_admin', 'admin'], true)) {
            $query->where('user_id', $request->user()->id);
        }

        return $query;
    }

    private function canAccessProject(Request $request, CreativeProject $project): bool
    {
        return in_array($request->user()->role, ['super_admin', 'admin'], true) || $project->user_id === $request->user()->id;
    }

    private function canAccessJob(Request $request, CreativeGenerationJob $job): bool
    {
        return in_array($request->user()->role, ['super_admin', 'admin'], true) || $job->user_id === $request->user()->id;
    }

    private function canAccessAsset(Request $request, CreativeAsset $asset): bool
    {
        if (in_array($request->user()->role, ['super_admin', 'admin'], true)) {
            return true;
        }

        return $asset->project_id
            ? CreativeProject::query()->whereKey($asset->project_id)->where('user_id', $request->user()->id)->exists()
            : false;
    }

    private function estimateCost(string $assetType, int $count, ?string $duration): float
    {
        if ($assetType === 'video') {
            $seconds = (int) filter_var($duration ?? '10', FILTER_SANITIZE_NUMBER_INT) ?: 10;

            return round(max(1, $seconds / 10) * 0.35 * $count, 4);
        }

        return round(0.08 * $count, 4);
    }

    private function options(): array
    {
        return [
            'image_aspect_ratios' => ['1:1', '16:9', '9:16', '4:5', '3:4', '21:9'],
            'image_resolutions' => ['512x512', '768x768', '1024x1024', '1024x1792', '1792x1024', '1080x1350', '1920x1080', '1080x1920'],
            'video_aspect_ratios' => ['9:16', '16:9', '1:1', '4:5', '21:9'],
            'video_resolutions' => ['720p', '1080p', '2K', '4K'],
            'durations' => ['5s', '10s', '15s', '30s', '60s'],
            'fps' => ['24', '30', '60'],
            'qualities' => ['standard', 'high', 'ultra'],
            'styles' => ['Realistic', 'Poster campaign', 'Editorial', 'Cinematic', 'Social media graphic', 'Minimalist', 'Youth campaign', 'Government/public service'],
        ];
    }
}
