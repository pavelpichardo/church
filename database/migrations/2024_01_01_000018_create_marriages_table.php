<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marriages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->date('date');
            $table->string('location')->nullable();
            $table->foreignId('officiant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('spouse1_person_id')->constrained('people');
            $table->foreignId('spouse2_person_id')->constrained('people');
            $table->foreignId('document_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marriages');
    }
};
