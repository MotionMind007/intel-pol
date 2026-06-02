<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creative_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_strategy_report_id')->nullable()->constrained('campaign_strategy_reports')->nullOnDelete();
            $table->string('title');
            $table->string('campaign_object_type')->nullable();
            $table->string('campaign_object_name');
            $table->string('objective')->nullable();
            $table->string('platform')->nullable();
            $table->string('tone')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('creative_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('creative_projects')->cascadeOnDelete();
            $table->longText('creative_brief')->nullable();
            $table->longText('big_idea')->nullable();
            $table->json('content_angles_json')->nullable();
            $table->json('hook_options_json')->nullable();
            $table->json('caption_options_json')->nullable();
            $table->json('cta_options_json')->nullable();
            $table->string('visual_style')->nullable();
            $table->json('script_json')->nullable();
            $table->json('storyboard_json')->nullable();
            $table->json('image_prompts_json')->nullable();
            $table->json('video_prompts_json')->nullable();
            $table->json('asset_specs_json')->nullable();
            $table->json('safety_notes_json')->nullable();
            $table->json('raw_json')->nullable();
            $table->timestamps();
        });

        Schema::create('creative_generation_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('creative_projects')->nullOnDelete();
            $table->string('asset_type');
            $table->foreignId('provider_id')->nullable()->constrained('ai_providers')->nullOnDelete();
            $table->foreignId('model_id')->nullable()->constrained('ai_models')->nullOnDelete();
            $table->longText('prompt');
            $table->longText('negative_prompt')->nullable();
            $table->string('aspect_ratio')->nullable();
            $table->string('resolution')->nullable();
            $table->string('duration')->nullable();
            $table->string('fps')->nullable();
            $table->string('quality')->nullable();
            $table->string('style')->nullable();
            $table->string('camera_style')->nullable();
            $table->unsignedInteger('output_count')->default(1);
            $table->unsignedBigInteger('reference_asset_id')->nullable()->index();
            $table->string('status')->default('queued');
            $table->string('provider_job_id')->nullable();
            $table->decimal('cost_estimate', 12, 4)->nullable();
            $table->decimal('cost_final', 12, 4)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('creative_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('creative_projects')->nullOnDelete();
            $table->foreignId('job_id')->nullable()->constrained('creative_generation_jobs')->nullOnDelete();
            $table->string('asset_type');
            $table->string('title')->nullable();
            $table->text('file_path')->nullable();
            $table->text('thumbnail_path')->nullable();
            $table->longText('prompt_used')->nullable();
            $table->longText('negative_prompt_used')->nullable();
            $table->string('provider_used')->nullable();
            $table->string('model_used')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('duration')->nullable();
            $table->string('fps')->nullable();
            $table->string('aspect_ratio')->nullable();
            $table->string('resolution')->nullable();
            $table->string('status')->default('generated');
            $table->string('approval_status')->default('pending_review');
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('creative_asset_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('creative_assets')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('creative_provider_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('ai_providers')->nullOnDelete();
            $table->foreignId('model_id')->nullable()->constrained('ai_models')->nullOnDelete();
            $table->foreignId('job_id')->nullable()->constrained('creative_generation_jobs')->nullOnDelete();
            $table->json('request_payload_json')->nullable();
            $table->json('response_payload_json')->nullable();
            $table->string('status')->default('success');
            $table->text('error_message')->nullable();
            $table->decimal('cost_estimate', 12, 4)->nullable();
            $table->decimal('cost_final', 12, 4)->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('creative_usage_limits', function (Blueprint $table) {
            $table->id();
            $table->string('role')->unique();
            $table->unsignedInteger('max_images_per_day')->default(20);
            $table->unsignedInteger('max_videos_per_day')->default(3);
            $table->unsignedInteger('max_video_duration')->default(10);
            $table->decimal('max_cost_per_day', 12, 4)->default(10);
            $table->decimal('requires_approval_above_cost', 12, 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creative_usage_limits');
        Schema::dropIfExists('creative_provider_logs');
        Schema::dropIfExists('creative_asset_reviews');
        Schema::dropIfExists('creative_assets');
        Schema::dropIfExists('creative_generation_jobs');
        Schema::dropIfExists('creative_packages');
        Schema::dropIfExists('creative_projects');
    }
};
