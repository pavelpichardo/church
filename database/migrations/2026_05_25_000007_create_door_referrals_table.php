<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('door_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('door_id')->constrained('doors')->restrictOnDelete();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->enum('source', ['manual', 'cell', 'rule', 'self'])->default('manual');
            $table->foreignId('source_cell_id')->nullable()->constrained('cells')->nullOnDelete();
            $table->foreignId('source_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('triggered_by_rule_id')->nullable()->constrained('door_rules')->nullOnDelete();
            $table->foreignId('ai_inference_id')->nullable()->constrained('door_ai_inferences')->nullOnDelete();
            $table->decimal('ai_confidence', 3, 2)->nullable();
            $table->text('ai_reasoning')->nullable();
            $table->string('category')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'in_progress', 'pending_review', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('assigned_to_person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->date('due_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['door_id', 'status']);
            $table->index(['person_id']);
            $table->index(['assigned_to_person_id']);
            $table->index(['source']);
            $table->index(['priority', 'status']);
            $table->index(['due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('door_referrals');
    }
};
