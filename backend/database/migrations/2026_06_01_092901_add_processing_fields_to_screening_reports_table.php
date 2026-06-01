<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('screening_reports', function (Blueprint $table) {
            $table->text('error_message')->nullable()->after('sources_json');
            $table->timestamp('queued_at')->nullable()->after('error_message');
            $table->timestamp('started_at')->nullable()->after('queued_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('screening_reports', function (Blueprint $table) {
            $table->dropColumn(['error_message', 'queued_at', 'started_at', 'completed_at']);
        });
    }
};
