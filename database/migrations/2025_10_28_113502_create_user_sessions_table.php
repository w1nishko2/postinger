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
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('bot_id')->constrained()->onDelete('cascade');
            $table->bigInteger('telegram_user_id');
            $table->bigInteger('telegram_chat_id');
            $table->string('state')->default('idle'); // idle, creating_post, selecting_channels
            $table->text('post_content')->nullable();
            $table->string('media_type')->nullable();
            $table->json('media_files')->nullable();
            $table->json('selected_channels')->nullable();
            $table->json('session_data')->nullable(); // Дополнительные данные сессии
            $table->timestamps();
            
            $table->index(['bot_id', 'telegram_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
