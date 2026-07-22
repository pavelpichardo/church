<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cells', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('leader_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('assistant_id')->nullable()->constrained('people')->nullOnDelete();
            $table->foreignId('host_id')->nullable()->constrained('people')->nullOnDelete();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->enum('status', ['active', 'inactive', 'multiplied'])->default('active');
            $table->foreignId('parent_cell_id')->nullable()->constrained('cells')->nullOnDelete();
            $table->unsignedSmallInteger('max_capacity')->default(15);
            $table->enum('meeting_day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable();
            $table->time('meeting_time')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['leader_id']);
        });

        Schema::create('cell_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cell_id')->constrained('cells')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->timestamps();

            $table->unique(['cell_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cell_members');
        Schema::dropIfExists('cells');
    }
};
