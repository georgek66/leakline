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
        Schema::create('labor_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workorder_id')
                  ->constrained('work_orders')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->decimal('hours', 6, 2)->nullable();
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labor_logs');
    }
};
