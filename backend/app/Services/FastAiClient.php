<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\CampaignStrategyRequest;
use App\Models\CreativeGenerationJob;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class FastAiClient
{
    /**
     * @throws RequestException
     */
    public function generateScreening(string $subjectName, Agent $agent): array
    {
        $provider = $agent->provider;
        $model = $agent->model;

        $payload = [
            'subject_name' => $subjectName,
            'agent' => [
                'name' => $agent->name,
                'role' => $agent->role_description,
                'system_prompt' => $agent->system_prompt,
                'temperature' => $agent->temperature,
                'max_tokens' => $agent->max_tokens,
                'provider' => $provider ? [
                    'name' => $provider->name,
                    'base_url' => $provider->base_url,
                    'api_key' => $provider->getDecryptedApiKey(),
                ] : null,
                'model' => $model?->model_name,
                'skills' => $agent->skills
                    ->filter(fn ($skill) => (bool) $skill->pivot->enabled)
                    ->map(fn ($skill) => [
                        'name' => $skill->name,
                        'slug' => $skill->slug,
                        'risk_level' => $skill->risk_level,
                        'prompt_content' => $skill->prompt_content,
                    ])
                    ->values()
                    ->all(),
            ],
        ];

        $response = Http::timeout((int) config('services.fastapi.timeout'))
            ->withHeaders([
                'X-Internal-Token' => (string) config('services.fastapi.internal_token'),
                'Accept' => 'application/json',
            ])
            ->post(rtrim((string) config('services.fastapi.base_url'), '/').'/internal/ai/screening/generate', $payload)
            ->throw();

        return $response->json();
    }

    /**
     * @throws RequestException
     */
    public function runMediaMonitoring(string $keyword, Agent $agent): array
    {
        $provider = $agent->provider;
        $model = $agent->model;

        $payload = [
            'keyword' => $keyword,
            'agent' => [
                'name' => $agent->name,
                'role' => $agent->role_description,
                'system_prompt' => $agent->system_prompt,
                'temperature' => $agent->temperature,
                'max_tokens' => $agent->max_tokens,
                'provider' => $provider ? [
                    'name' => $provider->name,
                    'base_url' => $provider->base_url,
                    'api_key' => $provider->getDecryptedApiKey(),
                ] : null,
                'model' => $model?->model_name,
                'skills' => $agent->skills
                    ->filter(fn ($skill) => (bool) $skill->pivot->enabled)
                    ->map(fn ($skill) => [
                        'name' => $skill->name,
                        'slug' => $skill->slug,
                        'risk_level' => $skill->risk_level,
                        'prompt_content' => $skill->prompt_content,
                    ])
                    ->values()
                    ->all(),
            ],
        ];

        $response = Http::timeout((int) config('services.fastapi.timeout'))
            ->withHeaders([
                'X-Internal-Token' => (string) config('services.fastapi.internal_token'),
                'Accept' => 'application/json',
            ])
            ->post(rtrim((string) config('services.fastapi.base_url'), '/').'/internal/ai/media-monitoring/run', $payload)
            ->throw();

        return $response->json();
    }

    /**
     * @throws RequestException
     */
    public function analyzePolicy(string $policyTopic, Agent $agent): array
    {
        $provider = $agent->provider;
        $model = $agent->model;

        $payload = [
            'policy_topic' => $policyTopic,
            'agent' => [
                'name' => $agent->name,
                'role' => $agent->role_description,
                'system_prompt' => $agent->system_prompt,
                'temperature' => $agent->temperature,
                'max_tokens' => $agent->max_tokens,
                'provider' => $provider ? [
                    'name' => $provider->name,
                    'base_url' => $provider->base_url,
                    'api_key' => $provider->getDecryptedApiKey(),
                ] : null,
                'model' => $model?->model_name,
                'skills' => $agent->skills
                    ->filter(fn ($skill) => (bool) $skill->pivot->enabled)
                    ->map(fn ($skill) => [
                        'name' => $skill->name,
                        'slug' => $skill->slug,
                        'risk_level' => $skill->risk_level,
                        'prompt_content' => $skill->prompt_content,
                    ])
                    ->values()
                    ->all(),
            ],
        ];

        $response = Http::timeout((int) config('services.fastapi.timeout'))
            ->withHeaders([
                'X-Internal-Token' => (string) config('services.fastapi.internal_token'),
                'Accept' => 'application/json',
            ])
            ->post(rtrim((string) config('services.fastapi.base_url'), '/').'/internal/ai/policy-intelligence/analyze', $payload)
            ->throw();

        return $response->json();
    }

    /**
     * @throws RequestException
     */
    public function generateCampaignStrategy(CampaignStrategyRequest $campaignRequest, Agent $agent): array
    {
        $provider = $agent->provider;
        $model = $agent->model;

        $payload = [
            'campaign_object_type' => $campaignRequest->campaign_object_type,
            'campaign_object_name' => $campaignRequest->campaign_object_name,
            'campaign_goal' => $campaignRequest->campaign_goal,
            'region' => $campaignRequest->region,
            'agent' => [
                'name' => $agent->name,
                'role' => $agent->role_description,
                'system_prompt' => $agent->system_prompt,
                'temperature' => $agent->temperature,
                'max_tokens' => $agent->max_tokens,
                'provider' => $provider ? [
                    'name' => $provider->name,
                    'base_url' => $provider->base_url,
                    'api_key' => $provider->getDecryptedApiKey(),
                ] : null,
                'model' => $model?->model_name,
                'skills' => $agent->skills
                    ->filter(fn ($skill) => (bool) $skill->pivot->enabled)
                    ->map(fn ($skill) => [
                        'name' => $skill->name,
                        'slug' => $skill->slug,
                        'risk_level' => $skill->risk_level,
                        'prompt_content' => $skill->prompt_content,
                    ])
                    ->values()
                    ->all(),
            ],
        ];

        $response = Http::timeout((int) config('services.fastapi.timeout'))
            ->withHeaders([
                'X-Internal-Token' => (string) config('services.fastapi.internal_token'),
                'Accept' => 'application/json',
            ])
            ->post(rtrim((string) config('services.fastapi.base_url'), '/').'/internal/ai/campaign-strategy/generate', $payload)
            ->throw();

        return $response->json();
    }

    /**
     * @throws RequestException
     */
    public function generateCreativePackage(array $creativeInput, Agent $agent): array
    {
        $provider = $agent->provider;
        $model = $agent->model;

        $payload = [
            ...$creativeInput,
            'agent' => [
                'name' => $agent->name,
                'role' => $agent->role_description,
                'system_prompt' => $agent->system_prompt,
                'temperature' => $agent->temperature,
                'max_tokens' => $agent->max_tokens,
                'provider' => $provider ? [
                    'name' => $provider->name,
                    'base_url' => $provider->base_url,
                    'api_key' => $provider->getDecryptedApiKey(),
                ] : null,
                'model' => $model?->model_name,
                'skills' => $agent->skills
                    ->filter(fn ($skill) => (bool) $skill->pivot->enabled)
                    ->map(fn ($skill) => [
                        'name' => $skill->name,
                        'slug' => $skill->slug,
                        'risk_level' => $skill->risk_level,
                        'prompt_content' => $skill->prompt_content,
                    ])
                    ->values()
                    ->all(),
            ],
        ];

        $response = Http::timeout((int) config('services.fastapi.timeout'))
            ->withHeaders([
                'X-Internal-Token' => (string) config('services.fastapi.internal_token'),
                'Accept' => 'application/json',
            ])
            ->post(rtrim((string) config('services.fastapi.base_url'), '/').'/internal/ai/creative-studio/package', $payload)
            ->throw();

        return $response->json();
    }

    /**
     * @throws RequestException
     */
    public function generateCreativeAsset(CreativeGenerationJob $job): array
    {
        $job->loadMissing(['assets']);
        $provider = $job->provider_id ? \App\Models\AiProvider::query()->find($job->provider_id) : null;
        $model = $job->model_id ? \App\Models\AiModel::query()->find($job->model_id) : null;

        $payload = [
            'asset_type' => $job->asset_type,
            'prompt' => $job->prompt,
            'negative_prompt' => $job->negative_prompt,
            'aspect_ratio' => $job->aspect_ratio,
            'resolution' => $job->resolution,
            'duration' => $job->duration,
            'fps' => $job->fps,
            'quality' => $job->quality,
            'style' => $job->style,
            'camera_style' => $job->camera_style,
            'output_count' => $job->output_count,
            'provider' => $provider ? [
                'name' => $provider->name,
                'provider_type' => $provider->provider_type,
                'base_url' => $provider->base_url,
                'api_key' => $provider->getDecryptedApiKey(),
            ] : null,
            'model' => $model?->model_name,
        ];

        $response = Http::timeout((int) config('services.fastapi.timeout'))
            ->withHeaders([
                'X-Internal-Token' => (string) config('services.fastapi.internal_token'),
                'Accept' => 'application/json',
            ])
            ->post(rtrim((string) config('services.fastapi.base_url'), '/').'/internal/ai/creative-studio/asset', $payload)
            ->throw();

        return $response->json();
    }

    /**
     * @throws RequestException
     */
    public function testConnection(array $provider): array
    {
        $response = Http::timeout((int) config('services.fastapi.timeout'))
            ->withHeaders([
                'X-Internal-Token' => (string) config('services.fastapi.internal_token'),
                'Accept' => 'application/json',
            ])
            ->post(rtrim((string) config('services.fastapi.base_url'), '/').'/internal/ai/provider/test-connection', $provider)
            ->throw();

        return $response->json();
    }
}
