<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_research_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('policy_topic', 220);
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('policy_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('policy_research_requests')->cascadeOnDelete();
            $table->string('source_type')->default('other');
            $table->string('source_name')->nullable();
            $table->text('url')->nullable();
            $table->text('title')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->longText('content_text')->nullable();
            $table->json('raw_json')->nullable();
            $table->timestamps();
        });

        Schema::create('public_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('policy_research_requests')->cascadeOnDelete();
            $table->foreignId('source_id')->nullable()->constrained('policy_sources')->nullOnDelete();
            $table->string('platform')->default('web');
            $table->string('author_or_account')->nullable();
            $table->longText('content_text')->nullable();
            $table->text('url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('engagement_json')->nullable();
            $table->string('sentiment')->default('neutral');
            $table->decimal('sentiment_score', 5, 2)->nullable();
            $table->string('response_type')->default('neutral');
            $table->timestamps();
        });

        Schema::create('policy_stakeholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('policy_research_requests')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('other');
            $table->string('position')->default('unclear');
            $table->string('influence_level')->default('medium');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('policy_impact_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('policy_research_requests')->cascadeOnDelete();
            $table->json('positive_impact_json')->nullable();
            $table->json('negative_impact_json')->nullable();
            $table->json('implementation_risk_json')->nullable();
            $table->json('political_risk_json')->nullable();
            $table->json('reputation_risk_json')->nullable();
            $table->json('scenario_json')->nullable();
            $table->json('policy_score_json')->nullable();
            $table->json('recommendation_json')->nullable();
            $table->timestamps();
        });

        Schema::create('policy_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('policy_research_requests')->cascadeOnDelete();
            $table->longText('executive_summary')->nullable();
            $table->json('result_json')->nullable();
            $table->longText('result_markdown')->nullable();
            $table->json('sources_json')->nullable();
            $table->unsignedInteger('final_score')->nullable();
            $table->string('risk_level')->nullable();
            $table->timestamps();
        });

        Schema::create('policy_agent_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('request_id')->nullable()->constrained('policy_research_requests')->cascadeOnDelete();
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
        Schema::dropIfExists('policy_agent_logs');
        Schema::dropIfExists('policy_reports');
        Schema::dropIfExists('policy_impact_analyses');
        Schema::dropIfExists('policy_stakeholders');
        Schema::dropIfExists('public_responses');
        Schema::dropIfExists('policy_sources');
        Schema::dropIfExists('policy_research_requests');
    }
};
