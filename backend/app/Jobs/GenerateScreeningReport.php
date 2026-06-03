<?php

namespace App\Jobs;

use App\Models\Agent;
use App\Models\AgentLog;
use App\Models\ScreeningReport;
use App\Services\FastAiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateScreeningReport implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 600; // 10 minutes max

    /**
     * Create a new job instance.
     */
    public function __construct(public int $reportId)
    {
        $this->onQueue('screening');
    }

    /**
     * Execute the job.
     */
    public function handle(FastAiClient $client): void
    {
        $report = ScreeningReport::query()->findOrFail($this->reportId);
        $agent = Agent::with(['provider', 'model', 'skills'])->findOrFail($report->agent_id);

        $report->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            $result = $client->generateScreening($report->subject_name, $agent);

            $report->update([
                'status' => 'completed',
                'final_score' => data_get($result, 'final_score.score'),
                'result_json' => $result,
                'result_markdown' => null,
                'sources_json' => data_get($result, 'sources', []),
                'completed_at' => now(),
            ]);

            AgentLog::create([
                'user_id' => $report->user_id,
                'agent_id' => $agent->id,
                'action' => 'screening.generate',
                'input_json' => ['subject_name' => $report->subject_name, 'report_id' => $report->id],
                'output_json' => ['report_id' => $report->id, 'final_score' => $report->final_score],
                'status' => 'success',
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $report->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

            AgentLog::create([
                'user_id' => $report->user_id,
                'agent_id' => $agent->id,
                'action' => 'screening.generate',
                'input_json' => ['subject_name' => $report->subject_name, 'report_id' => $report->id],
                'status' => 'error',
                'error_message' => $exception->getMessage(),
                'created_at' => now(),
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        ScreeningReport::query()
            ->whereKey($this->reportId)
            ->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);
    }
}
