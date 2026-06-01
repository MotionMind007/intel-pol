<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('keyword', 180);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('media_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->nullable();
            $table->string('source_type')->default('news_national');
            $table->string('platform')->default('web');
            $table->unsignedTinyInteger('credibility_score')->default(70);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('media_monitoring_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('keyword_id')->constrained('monitoring_keywords')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('news_count')->default(0);
            $table->unsignedInteger('social_count')->default(0);
            $table->unsignedInteger('google_search_count')->default(0);
            $table->unsignedInteger('google_trends_count')->default(0);
            $table->unsignedInteger('positive_count')->default(0);
            $table->unsignedInteger('neutral_count')->default(0);
            $table->unsignedInteger('negative_count')->default(0);
            $table->string('risk_level')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_id')->constrained('monitoring_keywords')->cascadeOnDelete();
            $table->foreignId('run_id')->nullable()->constrained('media_monitoring_runs')->cascadeOnDelete();
            $table->foreignId('source_id')->nullable()->constrained('media_sources')->nullOnDelete();
            $table->string('source_type')->default('other');
            $table->string('platform')->default('web');
            $table->text('title')->nullable();
            $table->longText('content_text')->nullable();
            $table->text('snippet')->nullable();
            $table->text('url')->nullable();
            $table->string('author')->nullable();
            $table->string('account_name')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->json('engagement_json')->nullable();
            $table->string('content_hash')->nullable()->index();
            $table->string('screenshot_path')->nullable();
            $table->json('raw_json')->nullable();
            $table->timestamps();
        });

        Schema::create('media_item_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_item_id')->constrained('media_items')->cascadeOnDelete();
            $table->text('summary')->nullable();
            $table->string('sentiment')->default('neutral');
            $table->decimal('sentiment_score', 5, 2)->nullable();
            $table->string('issue_category')->nullable();
            $table->string('risk_level')->nullable();
            $table->text('risk_reason')->nullable();
            $table->text('framing')->nullable();
            $table->text('recommendation')->nullable();
            $table->timestamps();
        });

        Schema::create('media_entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_item_id')->constrained('media_items')->cascadeOnDelete();
            $table->string('entity_name');
            $table->string('entity_type')->default('other');
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('media_monitoring_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('media_monitoring_runs')->cascadeOnDelete();
            $table->longText('executive_summary')->nullable();
            $table->json('dominant_issues_json')->nullable();
            $table->json('positive_issues_json')->nullable();
            $table->json('negative_issues_json')->nullable();
            $table->json('top_actors_json')->nullable();
            $table->json('top_sources_json')->nullable();
            $table->json('trend_json')->nullable();
            $table->json('google_trends_json')->nullable();
            $table->text('risk_assessment')->nullable();
            $table->json('strategic_recommendation')->nullable();
            $table->json('raw_json')->nullable();
            $table->timestamps();
        });

        Schema::create('browser_action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('run_id')->nullable()->constrained('media_monitoring_runs')->cascadeOnDelete();
            $table->string('action_type');
            $table->text('target_url')->nullable();
            $table->string('domain')->nullable();
            $table->json('input_json')->nullable();
            $table->json('output_json')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->string('status')->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('browser_action_logs');
        Schema::dropIfExists('media_monitoring_insights');
        Schema::dropIfExists('media_entities');
        Schema::dropIfExists('media_item_analyses');
        Schema::dropIfExists('media_items');
        Schema::dropIfExists('media_monitoring_runs');
        Schema::dropIfExists('media_sources');
        Schema::dropIfExists('monitoring_keywords');
    }
};
