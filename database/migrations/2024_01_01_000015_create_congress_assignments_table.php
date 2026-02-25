<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('congress_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('congress_role_id')->constrained('congress_roles')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('tasks')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->enum('status', ['assigned', 'confirmed', 'declined', 'completed'])->default('assigned');
            $table->timestamps();

            $table->unique(['congress_role_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('congress_assignments');
    }
};
