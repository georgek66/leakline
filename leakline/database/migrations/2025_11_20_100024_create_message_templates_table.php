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
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->enum('channel', ['email', 'sms', 'push', 'webhook']);
            $table->string('locale', 10)->default('en');
            $table->string('subject', 191)->nullable();
            $table->text('body');
            $table->json('placeholders')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
