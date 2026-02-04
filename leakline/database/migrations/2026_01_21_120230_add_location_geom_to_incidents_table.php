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
        Schema::table('incidents', function (Blueprint $table) {
            $table->geometry('location_geom', subtype: 'point', srid: 4326)->nullable();
        });

        DB::statement("CREATE INDEX incidents_location_geom_gix ON incidents USING GIST (location_geom)");
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS incidents_location_geom_gix");

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('location_geom');
        });

    }
};
