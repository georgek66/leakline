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
        Schema::create('resolution_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('label', 160);
            $table->text('description')->nullable();
            $table->decimal('default_estimated_savings_liters', 12, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resolution_codes');
    }
};
