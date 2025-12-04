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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('severity_id')
                  ->constrained('severity_levels')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('area_id')
                  ->nullable()
                  ->constrained('areas')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->string('location')->nullable();
            $table->text('description')->nullable();

            $table->enum('status', [
                'open', 'assigned', 'in_progress', 'resolved', 'closed', 'cancelled'
            ])->default('open')->index();

            $table->timestamps();
            $table->timestamp('closed_at')->nullable();
            $table->dateTime('response_due_at')->nullable();
            $table->dateTime('resolution_due_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
