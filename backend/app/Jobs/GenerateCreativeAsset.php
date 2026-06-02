<?php

namespace App\Jobs;

use App\Models\CreativeAsset;
use App\Models\CreativeGenerationJob;
use App\Models\CreativeProviderLog;
use App\Services\FastAiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateCreativeAsset implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 0;

    public function __construct(public int $jobId)
    {
        $this->onQueue('creative-studio');
    }

    public function handle(FastAiClient $client): void
    {
        @set_time_limit(0);

        $job = CreativeGenerationJob::query()->findOrFail($this->jobId);
        $job->update(['status' => 'processing', 'started_at' => now(), 'error_message' => null]);

        $started = microtime(true);

        try {
            $result = $client->generateCreativeAsset($job);
            $assets = data_get($result, 'assets', []);

            foreach ($assets as $index => $asset) {
                CreativeAsset::create([
                    'project_id' => $job->project_id,
                    'job_id' => $job->id,
                    'asset_type' => $job->asset_type,
                    'title' => data_get($asset, 'title', ucfirst($job->asset_type).' Asset #'.($index + 1)),
                    'file_path' => data_get($asset, 'file_path'),
                    'thumbnail_path' => data_get($asset, 'thumbnail_path'),
                    'prompt_used' => $job->prompt,
                    'negative_prompt_used' => $job->negative_prompt,
                    'provider_used' => data_get($result, 'provider', 'Creative Studio Mock Provider'),
                    'model_used' => data_get($result, 'model', 'creative-local'),
                    'width' => data_get($asset, 'width'),
                    'height' => data_get($asset, 'height'),
                    'duration' => $job->duration,
                    'fps' => $job->fps,
                    'aspect_ratio' => $job->aspect_ratio,
                    'resolution' => $job->resolution,
                    'status' => 'generated',
                    'approval_status' => 'pending_review',
                    'metadata_json' => is_array($asset) ? $asset : ['value' => $asset],
                ]);
            }

            $job->update([
                'status' => 'completed',
                'cost_final' => data_get($result, 'cost_final', $job->cost_estimate),
                'provider_job_id' => data_get($result, 'provider_job_id'),
                'finished_at' => now(),
            ]);

            CreativeProviderLog::create([
                'user_id' => $job->user_id,
                'provider_id' => $job->provider_id,
                'model_id' => $job->model_id,
                'job_id' => $job->id,
                'request_payload_json' => $job->toArray(),
                'response_payload_json' => $result,
                'status' => 'success',
                'cost_estimate' => $job->cost_estimate,
                'cost_final' => data_get($result, 'cost_final', $job->cost_estimate),
                'latency_ms' => (int) ((microtime(true) - $started) * 1000),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $job->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            CreativeProviderLog::create([
                'user_id' => $job->user_id,
                'provider_id' => $job->provider_id,
                'model_id' => $job->model_id,
                'job_id' => $job->id,
                'request_payload_json' => $job->toArray(),
                'status' => 'error',
                'error_message' => $exception->getMessage(),
                'latency_ms' => (int) ((microtime(true) - $started) * 1000),
                'created_at' => now(),
            ]);

            throw $exception;
        }
    }
}
