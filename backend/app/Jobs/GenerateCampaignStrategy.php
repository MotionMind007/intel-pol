<?php

namespace App\Jobs;

use App\Models\Agent;
use App\Models\CampaignAgentLog;
use App\Models\CampaignDataSource;
use App\Models\CampaignIssue;
use App\Models\CampaignRecommendation;
use App\Models\CampaignRegion;
use App\Models\CampaignSegment;
use App\Models\CampaignStrategyReport;
use App\Models\CampaignStrategyRequest;
use App\Services\FastAiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateCampaignStrategy implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 600; // 10 minutes max

    public function __construct(public int $requestId)
    {
        $this->onQueue('campaign-strategy');
    }

    public function handle(FastAiClient $client): void
    {
        @set_time_limit(0);

        $campaignRequest = CampaignStrategyRequest::query()->findOrFail($this->requestId);
        $agent = Agent::with(['provider', 'model', 'skills'])->findOrFail($campaignRequest->agent_id);

        $campaignRequest->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            $result = $client->generateCampaignStrategy($campaignRequest, $agent);
            $score = data_get($result, 'strategic_fit.score', data_get($result, 'strategic_score.score'));
            $risk = data_get($result, 'risk_level')
                ?? data_get($result, 'metrics.risk_level')
                ?? data_get($result, 'negative_issue_mitigation.0.risk')
                ?? 'medium';

            CampaignStrategyReport::updateOrCreate(
                ['request_id' => $campaignRequest->id],
                [
                    'executive_summary' => data_get($result, 'executive_summary'),
                    'positioning_statement' => data_get($result, 'positioning.statement'),
                    'main_narrative' => data_get($result, 'main_narrative'),
                    'final_strategy_json' => $result,
                    'sources_json' => data_get($result, 'sources', []),
                    'strategic_score' => is_numeric($score) ? (int) $score : null,
                    'risk_level' => $risk,
                ],
            );

            collect(data_get($result, 'sources', []))->take(80)->each(function ($source) use ($campaignRequest) {
                CampaignDataSource::create([
                    'request_id' => $campaignRequest->id,
                    'source_type' => data_get($source, 'type', data_get($source, 'source_type', 'other')),
                    'source_name' => data_get($source, 'name', data_get($source, 'source_name')),
                    'url' => data_get($source, 'link', data_get($source, 'url')),
                    'title' => data_get($source, 'title'),
                    'published_at' => data_get($source, 'published_at') ?: null,
                    'content_text' => data_get($source, 'data_used'),
                    'raw_json' => is_array($source) ? $source : ['value' => $source],
                ]);
            });

            collect(data_get($result, 'target_segments', []))->take(50)->each(function ($segment) use ($campaignRequest) {
                CampaignSegment::create([
                    'request_id' => $campaignRequest->id,
                    'segment' => data_get($segment, 'segment', 'Segmen belum diberi nama'),
                    'priority' => data_get($segment, 'priority', 'medium'),
                    'needs' => data_get($segment, 'needs'),
                    'main_issue' => data_get($segment, 'main_issue'),
                    'message' => data_get($segment, 'message'),
                    'channel' => data_get($segment, 'channel'),
                    'raw_json' => is_array($segment) ? $segment : ['value' => $segment],
                ]);
            });

            collect(data_get($result, 'priority_issues', []))->take(50)->each(function ($issue) use ($campaignRequest) {
                CampaignIssue::create([
                    'request_id' => $campaignRequest->id,
                    'issue' => data_get($issue, 'issue', 'Isu belum diberi nama'),
                    'priority' => data_get($issue, 'priority', 'medium'),
                    'reason' => data_get($issue, 'reason'),
                    'risk' => data_get($issue, 'risk'),
                    'recommended_narrative' => data_get($issue, 'recommended_narrative'),
                    'raw_json' => is_array($issue) ? $issue : ['value' => $issue],
                ]);
            });

            collect(data_get($result, 'regional_strategy', []))->take(50)->each(function ($region) use ($campaignRequest) {
                CampaignRegion::create([
                    'request_id' => $campaignRequest->id,
                    'region' => data_get($region, 'region', $campaignRequest->region ?? 'Wilayah target'),
                    'status' => data_get($region, 'status', 'swing'),
                    'strategy' => data_get($region, 'strategy'),
                    'actions_json' => data_get($region, 'actions', []),
                    'risk' => data_get($region, 'risk'),
                    'raw_json' => is_array($region) ? $region : ['value' => $region],
                ]);
            });

            collect(data_get($result, 'content_recommendations', []))->take(50)->each(function ($recommendation) use ($campaignRequest) {
                CampaignRecommendation::create([
                    'request_id' => $campaignRequest->id,
                    'recommendation_type' => data_get($recommendation, 'format', 'content'),
                    'title' => data_get($recommendation, 'hook'),
                    'description' => data_get($recommendation, 'message'),
                    'target' => data_get($recommendation, 'target'),
                    'channel' => data_get($recommendation, 'channel', data_get($recommendation, 'format')),
                    'raw_json' => is_array($recommendation) ? $recommendation : ['value' => $recommendation],
                ]);
            });

            $campaignRequest->update([
                'status' => 'completed',
                'finished_at' => now(),
            ]);

            CampaignAgentLog::create([
                'user_id' => $campaignRequest->user_id,
                'agent_id' => $agent->id,
                'request_id' => $campaignRequest->id,
                'action' => 'campaign_strategy.generate',
                'input_json' => [
                    'campaign_object_type' => $campaignRequest->campaign_object_type,
                    'campaign_object_name' => $campaignRequest->campaign_object_name,
                    'campaign_goal' => $campaignRequest->campaign_goal,
                    'region' => $campaignRequest->region,
                ],
                'output_json' => ['strategic_score' => $score, 'risk_level' => $risk],
                'status' => 'success',
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $campaignRequest->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            CampaignAgentLog::create([
                'user_id' => $campaignRequest->user_id,
                'agent_id' => $agent->id,
                'request_id' => $campaignRequest->id,
                'action' => 'campaign_strategy.generate',
                'input_json' => [
                    'campaign_object_name' => $campaignRequest->campaign_object_name,
                    'campaign_goal' => $campaignRequest->campaign_goal,
                ],
                'status' => 'error',
                'error_message' => $exception->getMessage(),
                'created_at' => now(),
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        CampaignStrategyRequest::query()
            ->whereKey($this->requestId)
            ->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);
    }
}
