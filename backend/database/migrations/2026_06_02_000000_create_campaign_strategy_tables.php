<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_strategy_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('campaign_object_type', 40);
            $table->string('campaign_object_name', 220);
            $table->string('campaign_goal', 260);
            $table->string('region', 160)->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_data_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('campaign_strategy_requests')->cascadeOnDelete();
            $table->string('source_type')->default('other');
            $table->string('source_name')->nullable();
            $table->text('url')->nullable();
            $table->text('title')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->longText('content_text')->nullable();
            $table->json('raw_json')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('campaign_strategy_requests')->cascadeOnDelete();
            $table->string('segment');
            $table->string('priority')->default('medium');
            $table->text('needs')->nullable();
            $table->text('main_issue')->nullable();
            $table->text('message')->nullable();
            $table->string('channel')->nullable();
            $table->json('raw_json')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('campaign_strategy_requests')->cascadeOnDelete();
            $table->string('issue');
            $table->string('priority')->default('medium');
            $table->text('reason')->nullable();
            $table->string('risk')->nullable();
            $table->text('recommended_narrative')->nullable();
            $table->json('raw_json')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('campaign_strategy_requests')->cascadeOnDelete();
            $table->string('region');
            $table->string('status')->default('swing');
            $table->longText('strategy')->nullable();
            $table->json('actions_json')->nullable();
            $table->text('risk')->nullable();
            $table->json('raw_json')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('campaign_strategy_requests')->cascadeOnDelete();
            $table->string('recommendation_type')->default('content');
            $table->text('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('target')->nullable();
            $table->string('channel')->nullable();
            $table->json('raw_json')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_strategy_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('campaign_strategy_requests')->cascadeOnDelete();
            $table->longText('executive_summary')->nullable();
            $table->longText('positioning_statement')->nullable();
            $table->longText('main_narrative')->nullable();
            $table->json('final_strategy_json')->nullable();
            $table->json('sources_json')->nullable();
            $table->unsignedInteger('strategic_score')->nullable();
            $table->string('risk_level')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_agent_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('request_id')->nullable()->constrained('campaign_strategy_requests')->cascadeOnDelete();
            $table->foreignId('skill_id')->nullable()->constrained('skills')->nullOnDelete();
            $table->string('action');
            $table->json('input_json')->nullable();
            $table->json('output_json')->nullable();
            $table->string('status')->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_agent_logs');
        Schema::dropIfExists('campaign_strategy_reports');
        Schema::dropIfExists('campaign_recommendations');
        Schema::dropIfExists('campaign_regions');
        Schema::dropIfExists('campaign_issues');
        Schema::dropIfExists('campaign_segments');
        Schema::dropIfExists('campaign_data_sources');
        Schema::dropIfExists('campaign_strategy_requests');
    }
};
