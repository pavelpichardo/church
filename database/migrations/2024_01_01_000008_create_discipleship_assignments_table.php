<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipleship_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discipleship_id')->constrained('discipleships')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['discipleship_id', 'person_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipleship_assignments');
    }
};
