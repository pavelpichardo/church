<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('door_ai_inferences', function (Blueprint $table) {
            $table->id();
            $table->string('triggering_event_type');
            $table->json('triggering_event_payload')->nullable();
            $table->foreignId('person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->string('model_used')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('cached_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->decimal('cost_usd', 10, 6)->nullable();
            $table->json('raw_response')->nullable();
            $table->json('decisions')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->enum('status', ['success', 'failed', 'fallback_used'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['triggering_event_type']);
            $table->index(['person_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('door_ai_inferences');
    }
};
