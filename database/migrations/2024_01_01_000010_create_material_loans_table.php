<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_material_id')->constrained('study_materials')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at');
            $table->date('due_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->enum('status', ['borrowed', 'returned', 'lost', 'overdue'])->default('borrowed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['study_material_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_loans');
    }
};
