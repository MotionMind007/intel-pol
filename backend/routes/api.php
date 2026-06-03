<?php

use App\Http\Controllers\Api\Admin\AgentController;
use App\Http\Controllers\Api\Admin\AgentSkillController;
use App\Http\Controllers\Api\Admin\AiModelController;
use App\Http\Controllers\Api\Admin\AiProviderController;
use App\Http\Controllers\Api\Admin\SkillController;
use App\Http\Controllers\Api\Admin\UsageLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaignStrategyController;
use App\Http\Controllers\Api\CreativeStudioController;
use App\Http\Controllers\Api\MediaMonitoringController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\PolicyIntelligenceController;
use App\Http\Controllers\Api\ScreeningReportController;
use Illuminate\Support\Facades\Route;

// Rate limited: 5 attempts per minute per IP to prevent brute force
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/modules', [ModuleController::class, 'index']);

    // Screening Reports - write operations require 'can_generate' middleware
    Route::get('/screening-reports', [ScreeningReportController::class, 'index']);
    Route::get('/screening-reports/{screening_report}', [ScreeningReportController::class, 'show']);
    Route::middleware('can_generate')->group(function () {
        Route::post('/screening-reports', [ScreeningReportController::class, 'store'])
            ->middleware('throttle:10,1');
        Route::delete('/screening-reports/{screening_report}', [ScreeningReportController::class, 'destroy']);
    });

    // Media Monitoring - write operations require 'can_generate' middleware
    Route::get('/media-monitoring', [MediaMonitoringController::class, 'index']);
    Route::get('/media-monitoring/runs', [MediaMonitoringController::class, 'runs']);
    Route::get('/media-monitoring/runs/{run}', [MediaMonitoringController::class, 'show']);
    Route::get('/media-monitoring/items/{item}', [MediaMonitoringController::class, 'item']);
    Route::get('/media-monitoring/sources', [MediaMonitoringController::class, 'sources']);
    Route::middleware('can_generate')->group(function () {
        Route::post('/media-monitoring/run', [MediaMonitoringController::class, 'run'])
            ->middleware('throttle:10,1');
        Route::delete('/media-monitoring/runs/{run}', [MediaMonitoringController::class, 'destroyRun']);
        Route::post('/media-monitoring/sources', [MediaMonitoringController::class, 'storeSource']);
        Route::put('/media-monitoring/sources/{source}', [MediaMonitoringController::class, 'updateSource']);
        Route::delete('/media-monitoring/keywords/{keyword}', [MediaMonitoringController::class, 'destroyKeyword']);
    });

    // Policy Intelligence - write operations require 'can_generate' middleware
    Route::get('/policy-intelligence', [PolicyIntelligenceController::class, 'index']);
    Route::get('/policy-intelligence/reports', [PolicyIntelligenceController::class, 'reports']);
    Route::get('/policy-intelligence/reports/{policyResearchRequest}', [PolicyIntelligenceController::class, 'show']);
    Route::middleware('can_generate')->group(function () {
        Route::post('/policy-intelligence/analyze', [PolicyIntelligenceController::class, 'analyze'])
            ->middleware('throttle:10,1');
        Route::delete('/policy-intelligence/reports/{policyResearchRequest}', [PolicyIntelligenceController::class, 'destroy']);
    });

    // Campaign Strategy - write operations require 'can_generate' middleware
    Route::get('/campaign-strategy', [CampaignStrategyController::class, 'index']);
    Route::get('/campaign-strategy/reports', [CampaignStrategyController::class, 'reports']);
    Route::get('/campaign-strategy/reports/{campaignStrategyRequest}', [CampaignStrategyController::class, 'show']);
    Route::middleware('can_generate')->group(function () {
        Route::post('/campaign-strategy/generate', [CampaignStrategyController::class, 'generate'])
            ->middleware('throttle:10,1');
        Route::delete('/campaign-strategy/reports/{campaignStrategyRequest}', [CampaignStrategyController::class, 'destroy']);
    });

    // Creative Studio - write operations require 'can_generate' middleware
    Route::get('/creative-studio', [CreativeStudioController::class, 'index']);
    Route::get('/creative-studio/projects', [CreativeStudioController::class, 'projects']);
    Route::get('/creative-studio/projects/{creativeProject}', [CreativeStudioController::class, 'showProject']);
    Route::get('/creative-studio/jobs/{job}', [CreativeStudioController::class, 'showJob']);
    Route::get('/creative-studio/assets', [CreativeStudioController::class, 'assets']);
    Route::get('/creative-studio/assets/{asset}', [CreativeStudioController::class, 'showAsset']);
    Route::middleware('can_generate')->group(function () {
        Route::post('/creative-studio/projects', [CreativeStudioController::class, 'storeProject']);
        Route::put('/creative-studio/projects/{creativeProject}', [CreativeStudioController::class, 'updateProject']);
        Route::delete('/creative-studio/projects/{creativeProject}', [CreativeStudioController::class, 'destroyProject']);
        Route::post('/creative-studio/packages/generate', [CreativeStudioController::class, 'generatePackage'])
            ->middleware('throttle:10,1');
        Route::post('/creative-studio/images/generate', [CreativeStudioController::class, 'generateImage'])
            ->middleware('throttle:10,1');
        Route::post('/creative-studio/videos/generate', [CreativeStudioController::class, 'generateVideo'])
            ->middleware('throttle:10,1');
        Route::post('/creative-studio/assets/{asset}/approve', [CreativeStudioController::class, 'approveAsset']);
        Route::post('/creative-studio/assets/{asset}/reject', [CreativeStudioController::class, 'rejectAsset']);
        Route::delete('/creative-studio/assets/{asset}', [CreativeStudioController::class, 'destroyAsset']);
    });

    Route::prefix('admin')->middleware('super_admin')->group(function () {
        Route::apiResource('agents', AgentController::class);
        Route::get('/ai-providers', [AiProviderController::class, 'index']);
        Route::post('/ai-providers', [AiProviderController::class, 'store']);
        Route::put('/ai-providers/{aiProvider}', [AiProviderController::class, 'update']);
        Route::post('/ai-providers/{aiProvider}/test-connection', [AiProviderController::class, 'testConnection']);
        Route::get('/ai-models', [AiModelController::class, 'index']);
        Route::post('/ai-models', [AiModelController::class, 'store']);
        Route::put('/ai-models/{aiModel}', [AiModelController::class, 'update']);
        Route::get('/skills', [SkillController::class, 'index']);
        Route::put('/agents/{agent}/skills', [AgentSkillController::class, 'update']);
        Route::get('/agent-logs', [UsageLogController::class, 'agentLogs']);
        Route::get('/audit-logs', [UsageLogController::class, 'auditLogs']);
    });
});
