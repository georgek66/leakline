<?php

namespace App\Console\Commands;

use App\Models\Area;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportAreasFromGeoJson extends Command
{
    protected $signature = 'areas:import {path? : Path to GeoJSON file}';
    protected $description = 'Import areas from a GeoJSON file';

    public function handle(): int
    {
        $path = $this->argument('path') ?? public_path('geojson/nicosia_cities.geojson');

        if (!file_exists($path)) {
            $this->error("GeoJSON file not found at: {$path}");
            return self::FAILURE;
        }

        $json = json_decode(file_get_contents($path), true);

        if (!isset($json['features']) || !is_array($json['features'])) {
            $this->error('Invalid GeoJSON: missing features array.');
            return self::FAILURE;
        }

        foreach ($json['features'] as $feature) {
            $name = $feature['properties']['name'] ?? 'Unnamed Area';
            $geometry = $feature['geometry'] ?? null;

            if (!$geometry) {
                $this->warn("Skipping {$name}: missing geometry.");
                continue;
            }

            // Insert and set PostGIS geometry (MultiPolygon/Polygon) with SRID 4326
            $area = Area::create([
                'name' => $name,
                'geometry' => null,
            ]);

            DB::statement(
                "UPDATE areas
                 SET geometry = ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)
                 WHERE id = ?",
                [json_encode($geometry), $area->id]
            );
        }

        $this->info('Import finished.');
        return self::SUCCESS;
    }
}
