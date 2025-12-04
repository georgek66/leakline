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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')
                  ->constrained('incidents')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('assigned_team_id')
                  ->nullable()
                  ->constrained('teams')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->unsignedTinyInteger('priority')->default(3);

            $table->dateTime('due_date')->nullable();

            $table->enum('status', [
                'queued', 'assigned', 'in_progress', 'on_hold', 'done', 'cancelled'
            ])->default('queued')->index();

            $table->foreignId('resolution_code_id')
                  ->nullable()
                  ->constrained('resolution_codes')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->decimal('estimated_water_saved_liters', 12, 2)->nullable();

            $table->text('closure_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
