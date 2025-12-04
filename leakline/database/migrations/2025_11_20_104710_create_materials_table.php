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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workorder_id')
                  ->constrained('work_orders')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('inventory_item_id')
                  ->nullable()
                  ->constrained('inventory_items')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->string('item_name', 160);
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->string('unit', 32)->nullable();
            $table->decimal('cost', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
