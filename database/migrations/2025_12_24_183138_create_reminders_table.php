<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->dateTime('due_date');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'completed', 'overdue'])->default('pending');
            $table->enum('frequency_type', ['once', 'daily', 'weekly', 'monthly', 'yearly', 'custom'])->default('once');
            $table->json('frequency_value')->nullable(); // e.g., {"days": [1,3,5]} for weekly
            $table->json('advance_notice_days')->nullable(); // e.g., [30, 7, 1]
            $table->boolean('notify_email')->default(true);
            $table->boolean('notify_sms')->default(false);
            $table->boolean('notify_whatsapp')->default(false);
            $table->dateTime('next_reminder_date')->nullable();
            $table->dateTime('last_sent_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('reminders')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
