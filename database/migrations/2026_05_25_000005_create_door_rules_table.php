<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('door_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('door_id')->constrained('doors')->cascadeOnDelete();
            $table->string('name');
            $table->text('description');
            $table->json('event_types')->nullable();
            $table->enum('priority_hint', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->index(['door_id', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('door_rules');
    }
};
