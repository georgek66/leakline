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
        Schema::create('incident_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')
                  ->constrained('incidents')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('actor_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->enum('event_type', [
                'created', 'status_changed', 'assigned', 'comment', 'media_added', 'merged'
            ]);

            $table->text('message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('incident_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_events');
    }
};
