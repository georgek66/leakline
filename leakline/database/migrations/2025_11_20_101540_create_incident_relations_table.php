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
        Schema::create('incident_relations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_incident_id')
                  ->constrained('incidents')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('target_incident_id')
                  ->constrained('incidents')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->enum('relation', ['duplicate_of', 'related_to']);
            $table->decimal('confidence', 5, 2)->nullable();

            $table->foreignId('merged_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->dateTime('merged_at')->nullable();
            $table->text('note')->nullable();

            $table->unique(
                ['source_incident_id', 'target_incident_id', 'relation'],
                'incident_rel_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_relations');
    }
};
