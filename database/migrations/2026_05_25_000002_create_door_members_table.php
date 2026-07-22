<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('door_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('door_id')->constrained('doors')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->enum('role', ['leader', 'co_leader', 'volunteer'])->default('volunteer');
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['door_id', 'role']);
            $table->index(['person_id']);
            $table->index(['left_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('door_members');
    }
};
