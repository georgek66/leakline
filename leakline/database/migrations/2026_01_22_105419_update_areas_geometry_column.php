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
        // Change geometry column to MultiPolygon with SRID 4326
        DB::statement("
            ALTER TABLE areas
            ALTER COLUMN geometry
            TYPE geometry(MultiPolygon, 4326)
            USING ST_SetSRID(geometry::geometry, 4326)
        ");

        // Add spatial index (if not exists)
        DB::statement("
            CREATE INDEX IF NOT EXISTS areas_geometry_gix
            ON areas
            USING GIST (geometry)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS areas_geometry_gix");

        // Revert to generic geometry (no subtype)
        DB::statement("
            ALTER TABLE areas
            ALTER COLUMN geometry
            TYPE geometry
        ");
    }
};
