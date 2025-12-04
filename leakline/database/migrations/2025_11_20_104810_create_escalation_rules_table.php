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
        Schema::create('escalation_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('severity_id')
                  ->constrained('severity_levels')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->enum('when_status', ['open', 'assigned', 'in_progress']);
            $table->unsignedInteger('after_minutes');

            $table->foreignId('notify_role_id')
                  ->nullable()
                  ->constrained('roles')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('notify_team_id')
                  ->nullable()
                  ->constrained('teams')
                 ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('notify_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->enum('action', ['notify', 'reassign', 'escalate_severity'])
                  ->default('notify');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escalation_rules');
    }
};
