<?php

namespace App\Jobs;

use App\Models\Agent;
use App\Models\AgentLog;
use App\Models\MediaEntity;
use App\Models\MediaItem;
use App\Models\MediaItemAnalysis;
use App\Models\MediaMonitoringInsight;
use App\Models\MediaMonitoringRun;
use App\Models\MediaSource;
use App\Services\FastAiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

class RunMediaMonitoring implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 0;

    public function __construct(public int $runId)
    {
        $this->onQueue('media-monitoring');
    }

    public function handle(FastAiClient $client): void
    {
        @set_time_limit(0);

        $run = MediaMonitoringRun::query()->with('keyword')->findOrFail($this->runId);
        $agent = Agent::with(['provider', 'model', 'skills'])->findOrFail($run->agent_id);

        $run->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            $result = $client->runMediaMonitoring($run->keyword->keyword, $agent);
            $items = collect(data_get($result, 'items', []));
            $sentiment = data_get($result, 'sentiment', []);
            $breakdown = data_get($result, 'source_breakdown', []);

            $run->update([
                'status' => 'completed',
                'total_items' => (int) (data_get($result, 'total_items') ?? $items->count()),
                'news_count' => (int) (data_get($breakdown, 'news_national', 0) + data_get($breakdown, 'news_local_papua', 0)),
                'social_count' => (int) data_get($breakdown, 'social_media', 0),
                'google_search_count' => (int) data_get($breakdown, 'google_search', 0),
                'google_trends_count' => (int) data_get($breakdown, 'google_trends', 0),
                'positive_count' => (int) data_get($sentiment, 'positive', 0),
                'neutral_count' => (int) data_get($sentiment, 'neutral', 0),
                'negative_count' => (int) data_get($sentiment, 'negative', 0),
                'risk_level' => data_get($result, 'risk_level'),
                'finished_at' => now(),
            ]);

            MediaMonitoringInsight::updateOrCreate(
                ['run_id' => $run->id],
                [
                    'executive_summary' => data_get($result, 'executive_summary'),
                    'dominant_issues_json' => data_get($result, 'dominant_issues', []),
                    'positive_issues_json' => data_get($result, 'positive_issues', []),
                    'negative_issues_json' => data_get($result, 'negative_issues', []),
                    'top_actors_json' => data_get($result, 'top_actors', []),
                    'top_sources_json' => data_get($result, 'top_sources', []),
                    'trend_json' => data_get($result, 'trend', []),
                    'google_trends_json' => data_get($result, 'google_trends_insight', []),
                    'risk_assessment' => data_get($result, 'risk_assessment') ?? data_get($result, 'risk_level'),
                    'strategic_recommendation' => data_get($result, 'strategic_recommendation', []),
                    'raw_json' => $result,
                ],
            );

            $items->take(80)->each(fn (array $item) => $this->storeItem($run, $item));

            AgentLog::create([
                'user_id' => $run->user_id,
                'agent_id' => $agent->id,
                'action' => 'media_monitoring.run',
                'input_json' => ['keyword' => $run->keyword->keyword, 'run_id' => $run->id],
                'output_json' => ['run_id' => $run->id, 'total_items' => $run->total_items, 'risk_level' => $run->risk_level],
                'status' => 'success',
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $run->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            AgentLog::create([
                'user_id' => $run->user_id,
                'agent_id' => $agent->id,
                'action' => 'media_monitoring.run',
                'input_json' => ['keyword' => $run->keyword->keyword, 'run_id' => $run->id],
                'status' => 'error',
                'error_message' => $exception->getMessage(),
                'created_at' => now(),
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        MediaMonitoringRun::query()
            ->whereKey($this->runId)
            ->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);
    }

    private function storeItem(MediaMonitoringRun $run, array $item): void
    {
        $sourceName = trim((string) data_get($item, 'source', 'Sumber tidak diketahui'));
        $source = MediaSource::query()->firstOrCreate(
            ['name' => $sourceName ?: 'Sumber tidak diketahui'],
            [
                'domain' => parse_url((string) data_get($item, 'url'), PHP_URL_HOST),
                'source_type' => data_get($item, 'source_type', 'other'),
                'platform' => data_get($item, 'platform', 'web'),
                'credibility_score' => 70,
                'is_active' => true,
            ],
        );

        $url = (string) data_get($item, 'url', '');
        $title = (string) data_get($item, 'title', data_get($item, 'summary', 'Tanpa judul'));
        $mediaItem = MediaItem::create([
            'keyword_id' => $run->keyword_id,
            'run_id' => $run->id,
            'source_id' => $source->id,
            'source_type' => data_get($item, 'source_type', 'other'),
            'platform' => data_get($item, 'platform', 'web'),
            'title' => $title,
            'content_text' => data_get($item, 'content_text'),
            'snippet' => data_get($item, 'summary') ?? data_get($item, 'snippet'),
            'url' => $url,
            'published_at' => data_get($item, 'published_at') ?: null,
            'captured_at' => now(),
            'engagement_json' => data_get($item, 'engagement', []),
            'content_hash' => Str::of($url ?: $title)->lower()->slug('-')->limit(180, ''),
            'screenshot_path' => data_get($item, 'screenshot_path'),
            'raw_json' => $item,
        ]);

        MediaItemAnalysis::create([
            'media_item_id' => $mediaItem->id,
            'summary' => data_get($item, 'summary'),
            'sentiment' => data_get($item, 'sentiment', 'neutral'),
            'issue_category' => data_get($item, 'issue_category'),
            'risk_level' => data_get($item, 'risk_level'),
            'risk_reason' => data_get($item, 'risk_reason'),
            'recommendation' => data_get($item, 'recommendation'),
        ]);

        collect(data_get($item, 'entities', []))
            ->take(12)
            ->each(fn ($entity) => MediaEntity::create([
                'media_item_id' => $mediaItem->id,
                'entity_name' => is_array($entity) ? data_get($entity, 'name', '') : (string) $entity,
                'entity_type' => is_array($entity) ? data_get($entity, 'type', 'other') : 'other',
                'confidence_score' => is_array($entity) ? data_get($entity, 'confidence_score') : null,
                'created_at' => now(),
            ]));
    }
}
