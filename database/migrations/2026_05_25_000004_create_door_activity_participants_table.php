<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('door_activity_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('door_activity_id')->constrained('door_activities')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->boolean('attended')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['door_activity_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('door_activity_participants');
    }
};
