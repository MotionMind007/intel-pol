<?php

use App\Http\Controllers\Api\Admin\AgentController;
use App\Http\Controllers\Api\Admin\AgentSkillController;
use App\Http\Controllers\Api\Admin\AiModelController;
use App\Http\Controllers\Api\Admin\AiProviderController;
use App\Http\Controllers\Api\Admin\SkillController;
use App\Http\Controllers\Api\Admin\UsageLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ScreeningReportController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/modules', [ModuleController::class, 'index']);

    Route::apiResource('screening-reports', ScreeningReportController::class)
        ->only(['index', 'store', 'show', 'destroy']);

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
