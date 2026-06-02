<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_providers', function (Blueprint $table) {
            $table->string('provider_type')->default('text')->after('name');
            $table->unsignedInteger('rate_limit_per_minute')->nullable()->after('status');
            $table->decimal('cost_limit_per_day', 12, 4)->nullable()->after('rate_limit_per_minute');
            $table->unsignedInteger('timeout_seconds')->nullable()->after('cost_limit_per_day');
        });

        Schema::table('ai_models', function (Blueprint $table) {
            $table->string('modality')->default('text')->after('provider_id');
            $table->json('capabilities_json')->nullable()->after('display_name');
            $table->decimal('unit_price', 12, 6)->nullable()->after('output_price_per_million_tokens');
        });
    }

    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            $table->dropColumn(['modality', 'capabilities_json', 'unit_price']);
        });

        Schema::table('ai_providers', function (Blueprint $table) {
            $table->dropColumn(['provider_type', 'rate_limit_per_minute', 'cost_limit_per_day', 'timeout_seconds']);
        });
    }
};
