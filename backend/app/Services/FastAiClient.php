<?php

namespace App\Services;

use App\Models\Agent;
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
                    'api_key' => $provider->api_key_encrypted, // This will be decrypted by Laravel's encrypted cast
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
