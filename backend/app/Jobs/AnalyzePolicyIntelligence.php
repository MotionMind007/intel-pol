<?php

namespace App\Jobs;

use App\Models\Agent;
use App\Models\PolicyAgentLog;
use App\Models\PolicyImpactAnalysis;
use App\Models\PolicyReport;
use App\Models\PolicyResearchRequest;
use App\Models\PolicySource;
use App\Models\PolicyStakeholder;
use App\Models\PublicResponse;
use App\Services\FastAiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class AnalyzePolicyIntelligence implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 600; // 10 minutes max

    public function __construct(public int $requestId)
    {
        $this->onQueue('policy-intelligence');
    }

    public function handle(FastAiClient $client): void
    {
        @set_time_limit(0);

        $policyRequest = PolicyResearchRequest::query()->findOrFail($this->requestId);
        $agent = Agent::with(['provider', 'model', 'skills'])->findOrFail($policyRequest->agent_id);

        $policyRequest->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            $result = $client->analyzePolicy($policyRequest->policy_topic, $agent);
            $score = data_get($result, 'policy_score.score');
            $risk = data_get($result, 'political_reputation_risk.level')
                ?? data_get($result, 'risk_level')
                ?? 'medium';

            PolicyReport::updateOrCreate(
                ['request_id' => $policyRequest->id],
                [
                    'executive_summary' => data_get($result, 'executive_summary'),
                    'result_json' => $result,
                    'result_markdown' => null,
                    'sources_json' => data_get($result, 'sources', []),
                    'final_score' => is_numeric($score) ? (int) $score : null,
                    'risk_level' => $risk,
                ],
            );

            PolicyImpactAnalysis::updateOrCreate(
                ['request_id' => $policyRequest->id],
                [
                    'positive_impact_json' => data_get($result, 'positive_impacts', []),
                    'negative_impact_json' => data_get($result, 'negative_impacts', []),
                    'implementation_risk_json' => data_get($result, 'implementation_risks', []),
                    'political_risk_json' => data_get($result, 'political_reputation_risk', []),
                    'reputation_risk_json' => data_get($result, 'political_reputation_risk', []),
                    'scenario_json' => data_get($result, 'scenario_simulation', []),
                    'policy_score_json' => data_get($result, 'policy_score', []),
                    'recommendation_json' => data_get($result, 'policy_improvement_recommendations', []),
                ],
            );

            collect(data_get($result, 'sources', []))->take(60)->each(function ($source) use ($policyRequest) {
                PolicySource::create([
                    'request_id' => $policyRequest->id,
                    'source_type' => data_get($source, 'type', data_get($source, 'source_type', 'other')),
                    'source_name' => data_get($source, 'name', data_get($source, 'source_name')),
                    'url' => data_get($source, 'link', data_get($source, 'url')),
                    'title' => data_get($source, 'title'),
                    'published_at' => data_get($source, 'published_at') ?: null,
                    'content_text' => data_get($source, 'data_used'),
                    'raw_json' => is_array($source) ? $source : ['value' => $source],
                ]);
            });

            collect(data_get($result, 'public_response.items', []))->take(80)->each(function ($response) use ($policyRequest) {
                PublicResponse::create([
                    'request_id' => $policyRequest->id,
                    'platform' => data_get($response, 'platform', 'web'),
                    'author_or_account' => data_get($response, 'author_or_account'),
                    'content_text' => data_get($response, 'content_text', data_get($response, 'summary')),
                    'url' => data_get($response, 'url'),
                    'published_at' => data_get($response, 'published_at') ?: null,
                    'engagement_json' => data_get($response, 'engagement', []),
                    'sentiment' => data_get($response, 'sentiment', 'neutral'),
                    'response_type' => data_get($response, 'response_type', 'neutral'),
                ]);
            });

            collect(data_get($result, 'stakeholders', []))->take(50)->each(function ($stakeholder) use ($policyRequest) {
                PolicyStakeholder::create([
                    'request_id' => $policyRequest->id,
                    'name' => data_get($stakeholder, 'name', 'Stakeholder tidak diketahui'),
                    'type' => data_get($stakeholder, 'type', 'other'),
                    'position' => data_get($stakeholder, 'position', 'unclear'),
                    'influence_level' => data_get($stakeholder, 'influence', data_get($stakeholder, 'influence_level', 'medium')),
                    'notes' => data_get($stakeholder, 'notes'),
                ]);
            });

            $policyRequest->update([
                'status' => 'completed',
                'finished_at' => now(),
            ]);

            PolicyAgentLog::create([
                'user_id' => $policyRequest->user_id,
                'agent_id' => $agent->id,
                'request_id' => $policyRequest->id,
                'action' => 'policy_intelligence.analyze',
                'input_json' => ['policy_topic' => $policyRequest->policy_topic],
                'output_json' => ['final_score' => $score, 'risk_level' => $risk],
                'status' => 'success',
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $policyRequest->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            PolicyAgentLog::create([
                'user_id' => $policyRequest->user_id,
                'agent_id' => $agent->id,
                'request_id' => $policyRequest->id,
                'action' => 'policy_intelligence.analyze',
                'input_json' => ['policy_topic' => $policyRequest->policy_topic],
                'status' => 'error',
                'error_message' => $exception->getMessage(),
                'created_at' => now(),
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        PolicyResearchRequest::query()
            ->whereKey($this->requestId)
            ->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);
    }
}
