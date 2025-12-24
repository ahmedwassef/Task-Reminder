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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->enum('default_view', ['list', 'calendar', 'kanban'])->default('list');
            $table->integer('items_per_page')->default(15);
            $table->enum('date_format', ['gregorian', 'hijri'])->default('gregorian');
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->boolean('weekend_notifications')->default(true);
            $table->json('default_advance_notice')->default('[7, 1]');
            $table->boolean('default_notify_email')->default(true);
            $table->boolean('default_notify_sms')->default(false);
            $table->boolean('default_notify_whatsapp')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
