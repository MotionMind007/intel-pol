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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role_description');
            $table->longText('system_prompt')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable()->index();
            $table->unsignedBigInteger('model_id')->nullable()->index();
            $table->decimal('temperature', 3, 2)->default(0.40);
            $table->unsignedInteger('max_tokens')->default(8000);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
