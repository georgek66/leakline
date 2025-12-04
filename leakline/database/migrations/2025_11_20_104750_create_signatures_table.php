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
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workorder_id')
                  ->constrained('work_orders')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->string('signed_by_name', 160)->nullable();
            $table->string('file_url', 2048);
            $table->dateTime('signed_at');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
