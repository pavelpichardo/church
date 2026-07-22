<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('door_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('door_id')->constrained('doors')->cascadeOnDelete();
            $table->foreignId('referral_id')->nullable()->constrained('door_referrals')->nullOnDelete();
            $table->string('type');
            $table->text('message');
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->dateTime('read_at')->nullable();
            $table->timestamps();

            $table->index(['door_id', 'read_at']);
            $table->index(['severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('door_alerts');
    }
};
