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
        Schema::create('incident_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')
                  ->constrained('incidents')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->string('name', 120)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('preferred_locale', 10)->nullable();
            $table->string('consent_version', 20)->nullable();
            $table->dateTime('consented_at')->nullable();
            $table->char('gdpr_token', 36)->nullable();

            $table->timestamps();
            $table->unique('incident_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_contacts');
    }
};
