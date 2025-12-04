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
        Schema::create('gdpr_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('incident_id')
                ->nullable()
                ->constrained('incidents')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('email', 191);
            $table->enum('type', ['access', 'export', 'delete']);
            $table->enum('status', ['open', 'in_progress', 'done', 'rejected'])->default('open');

            $table->dateTime('submitted_at');
            $table->dateTime('processed_at')->nullable();
            $table->text('note')->nullable();

            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gdpr_requests');
    }
};
