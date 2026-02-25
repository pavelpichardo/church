<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_membership', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->unique()->constrained('people')->cascadeOnDelete();
            $table->foreignId('current_stage_id')->constrained('membership_stages');
            $table->date('class_taken_at')->nullable();
            $table->foreignId('class_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('document_signed_at')->nullable();
            $table->foreignId('document_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->timestamp('pastor_approved_at')->nullable();
            $table->foreignId('pastor_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_membership');
    }
};
