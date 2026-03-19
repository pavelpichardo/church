<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('congress_role_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('congress_role_id')->constrained('congress_roles')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('phase', ['before', 'during', 'after'])->default('before');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('congress_task_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('congress_role_task_id')->constrained('congress_role_tasks')->cascadeOnDelete();
            $table->foreignId('congress_assignment_id')->constrained('congress_assignments')->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['congress_role_task_id', 'congress_assignment_id'], 'task_assignment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('congress_task_completions');
        Schema::dropIfExists('congress_role_tasks');
    }
};
