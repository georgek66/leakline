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
        Schema::create('sla_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('severity_id')
                  ->constrained('severity_levels')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->unsignedInteger('response_time');   // hours
            $table->unsignedInteger('resolution_time'); // hours
            $table->unique('severity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_rules');
    }
};
