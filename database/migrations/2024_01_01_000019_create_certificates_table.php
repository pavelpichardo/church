<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['discipleship', 'baptism', 'marriage', 'other']);
            $table->foreignId('person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->foreignId('discipleship_assignment_id')->nullable()->constrained('discipleship_assignments')->nullOnDelete();
            $table->foreignId('baptism_id')->nullable()->constrained('baptisms')->nullOnDelete();
            $table->foreignId('marriage_id')->nullable()->constrained('marriages')->nullOnDelete();
            $table->date('issued_at');
            $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
